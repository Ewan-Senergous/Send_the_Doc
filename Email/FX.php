<?php
// Définir les constantes pour éviter la duplication de chaînes littérales
define('CENOV_NOT_PROVIDED', 'Non renseignée');
define('CENOV_NOT_PROVIDED_M', 'Non renseigné');
define('CENOV_DOUBLE_NEWLINE', "\r\n\r\n");

if (!function_exists('cenovContactForm')) {
    function cenovContactForm() {
        $result = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cenov_prenom']) && isset($_POST['g-recaptcha-response'])) {
            // Vérifications de sécurité
            $security_result = checkFormSecurity();
            if ($security_result === true) {
                // Validation des données
                $form_data = validateFormData();
                if ($form_data !== false) {
                    // Traitement des fichiers
                    $file_result = processUploadedFiles();
                    if (!isset($file_result['error'])) {
                        // Envoyer l'email
                        $result = sendContactEmail($form_data, $file_result['attachments'], $file_result['warning']);
                    } else {
                        $result = $file_result['error'];
                    }
                } else {
                    $result = '<div class="error-message">Veuillez remplir tous les champs obligatoires.</div>';
                }
            } else {
                $result = $security_result;
            }
        }
        
        return $result;
    }
    
    function checkFormSecurity() {
        $result = true;
        
        // Vérifications de base
        if (checkSpamTraps()) {
            $result = '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
        } elseif (checkBasicSecurityMeasures()) {
            $result = checkRecaptcha();
        }
        
        return $result;
    }
    
    function checkSpamTraps() {
        // Court-circuit pour les bots (vérifications rapides)
        return !empty($_POST['cenov_website']) || (isset($_POST['cenov_timestamp']) && time() - (int)$_POST['cenov_timestamp'] < 3);
    }
    
    function checkBasicSecurityMeasures() {
        // Protection contre les attaques de force brute
        static $submission_rate_checked = null;
        if ($submission_rate_checked === null) {
            $submission_rate_checked = cenovCheckSubmissionRate();
        }
        
        if (!$submission_rate_checked) {
            return '<div class="error-message">Trop de tentatives. Veuillez réessayer dans une heure.</div>';
        }
        
        // Vérification du nonce CSRF
        if (!isset($_POST['cenov_nonce']) || !wp_verify_nonce($_POST['cenov_nonce'], 'cenov_contact_action')) {
            return '<div class="error-message">Erreur de sécurité. Veuillez rafraîchir la page et réessayer.</div>';
        }
        
        return true;
    }
    
    function checkRecaptcha() {
        // Vérification de reCAPTCHA (optimisée)
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        if (empty($recaptcha_response)) {
            return '<div class="error-message">Échec de la vérification de sécurité. Veuillez réessayer.</div>';
        }
        
        // Préparer et vérifier la requête reCAPTCHA
        return processRecaptchaVerification($recaptcha_response);
    }
    
    function processRecaptchaVerification($recaptcha_response) {
        // Cache pour la clé secrète
        static $recaptcha_secret = null;
        if ($recaptcha_secret === null) {
            $recaptcha_secret = get_option('cenov_recaptcha_secret', '');
        }
        
        $verify_response = wp_remote_get(
            "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"
        );
        
        // Vérifier l'erreur de requête ou le résultat du reCAPTCHA
        if (is_wp_error($verify_response)) {
            return '<div class="error-message">Erreur de vérification. Veuillez réessayer plus tard.</div>';
        }
        
        $recaptcha_result = json_decode(wp_remote_retrieve_body($verify_response));
        
        if (!isset($recaptcha_result->success) || !$recaptcha_result->success ||
            (isset($recaptcha_result->score) && $recaptcha_result->score < 0.5)) {
            return '<div class="error-message">La vérification de sécurité a échoué. Veuillez réessayer.</div>';
        }
        
        return true;
    }
    
    /**
     * Valide les données du formulaire
     * @return array|bool Données validées ou false si validation échouée
     */
    function validateFormData() {
        $data = [
            'prenom' => isset($_POST['cenov_prenom']) ? sanitize_text_field($_POST['cenov_prenom']) : '',
            'nom_famille' => isset($_POST['cenov_nom']) ? sanitize_text_field($_POST['cenov_nom']) : '',
            'email' => isset($_POST['cenov_email']) ? sanitize_email($_POST['cenov_email']) : '',
            'telephone' => isset($_POST['cenov_telephone']) ? sanitize_text_field($_POST['cenov_telephone']) : '',
            'message' => isset($_POST['cenov_message']) ? sanitize_textarea_field($_POST['cenov_message']) : '',
            'societe' => isset($_POST['cenov_societe']) ? sanitize_text_field($_POST['cenov_societe']) : '',
            'adresse' => isset($_POST['cenov_adresse']) ? sanitize_text_field($_POST['cenov_adresse']) : '',
            'codepostal' => isset($_POST['cenov_codepostal']) ? sanitize_text_field($_POST['cenov_codepostal']) : '',
            'ville' => isset($_POST['cenov_ville']) ? sanitize_text_field($_POST['cenov_ville']) : '',
            'produit' => isset($_POST['cenov_produit']) ? sanitize_text_field($_POST['cenov_produit']) : ''
        ];
        
        $data['nom'] = $data['prenom'] . ' ' . $data['nom_famille'];
        
        // Vérification des champs obligatoires
        if (empty($data['prenom']) || empty($data['nom_famille']) || empty($data['email']) || empty($data['telephone'])) {
            return false;
        }
        
        return $data;
    }
    
    /**
     * Traite les fichiers téléchargés
     * @return array Résultat du traitement avec pièces jointes et avertissements
     */
    function processUploadedFiles() {
        $result = [
            'attachments' => [],
            'warning' => '',
            'error' => null
        ];
        
        // Si aucun fichier n'est joint
        if (empty($_FILES['cenov_plaque']['name'][0])) {
            $result['warning'] = '<div class="warning-message">Attention : aucune plaque signalétique n\'a été jointe à votre message.</div>';
            return $result;
        }
        
        // Traiter chaque fichier
        foreach($_FILES['cenov_plaque']['name'] as $key => $name) {
            if(empty($name)) {
                continue;
            }
            
            // Préparer les données du fichier
            $file = prepareFileData($key);
            
            // Valider et traiter le fichier
            $validation = validateFile($file, $key);
            
            // Si validation échouée, retourner l'erreur
            if (!$validation['success']) {
                $result['error'] = $validation['error'];
                return $result;
            }
            
            // Ajouter le fichier aux pièces jointes
            $result['attachments'][] = $validation['file_path'];
        }
        
        return $result;
    }
    
    /**
     * Prépare les données d'un fichier téléchargé
     * @param int $key Index du fichier
     * @return array Données du fichier
     */
    function prepareFileData($key) {
        return [
            'name' => $_FILES['cenov_plaque']['name'][$key],
            'type' => $_FILES['cenov_plaque']['type'][$key],
            'tmp_name' => $_FILES['cenov_plaque']['tmp_name'][$key],
            'error' => $_FILES['cenov_plaque']['error'][$key],
            'size' => $_FILES['cenov_plaque']['size'][$key]
        ];
    }
    
    /**
     * Valide un fichier téléchargé
     * @param array $file Données du fichier
     * @param int $key Index du fichier
     * @return array Résultat de la validation
     */
    function validateFile($file, $key) {
        $result = ['success' => false, 'error' => null, 'file_path' => null];
        $valid = true;
        
        // Vérification des erreurs d'upload
        if ($valid && $file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Erreur lors de l'upload du fichier: ";
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message .= "Le fichier dépasse la taille maximale autorisée par le serveur.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message .= "Le fichier dépasse la taille maximale autorisée par le formulaire.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message .= "Le fichier n'a été que partiellement uploadé.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message .= "Aucun fichier n'a été uploadé.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message .= "Dossier temporaire manquant.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message .= "Échec d'écriture du fichier sur le disque.";
                    break;
                default:
                    $error_message .= "Erreur inconnue.";
            }
            $result['error'] = '<div class="error-message">' . $error_message . '</div>';
            $valid = false;
        }
        
        // Vérification du type de fichier
        $allowed_types = array('image/jpeg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
        if ($valid && !in_array($file['type'], $allowed_types)) {
            $result['error'] = '<div class="error-message">Format de fichier non supporté. Formats acceptés : JPG, JPEG, PNG, PDF, HEIC, WEBP</div>';
            $valid = false;
        }
        
        // Vérification de la taille
        $max_size = 10 * 1024 * 1024; // 10 Mo
        if ($valid && $file['size'] > $max_size) {
            $result['error'] = '<div class="error-message">Le fichier est trop volumineux (10 Mo maximum)</div>';
            $valid = false;
        }
        
        // Traitement du fichier si valide
        if ($valid) {
            $temp_file = saveUploadedFile($file, $key);
            if ($temp_file) {
                $result['success'] = true;
                $result['file_path'] = $temp_file;
            } else {
                $result['error'] = '<div class="error-message">Erreur lors du téléchargement du fichier. Veuillez réessayer.</div>';
            }
        }
        
        return $result;
    }
    
    /**
     * Sauvegarde un fichier téléchargé
     * @param array $file Données du fichier
     * @param int $key Index du fichier
     * @return string|false Chemin du fichier sauvegardé ou false si erreur
     */
    function saveUploadedFile($file, $key) {
        // Préparation du dossier temporaire
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/cenov_temp';
        
        // Création du dossier s'il n'existe pas
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        // Génération d'un nom de fichier unique
        $filename = sanitize_file_name($file['name']);
        $filename = time() . '_' . $key . '_' . $filename;
        $temp_file = $temp_dir . '/' . $filename;
        
        // Déplacement du fichier
        if (move_uploaded_file($file['tmp_name'], $temp_file)) {
            return $temp_file;
        }
        
        return false;
    }
    
    /**
     * Envoie l'email avec les pièces jointes
     * @param array $data Données du formulaire
     * @param array $attachments Liste des pièces jointes
     * @param string $file_warning Message d'avertissement
     * @return string Message de résultat
     */
    function sendContactEmail($data, $attachments, $file_warning) {
        // Création du contenu HTML de l'email
        $html_content = buildEmailContent($data, $attachments);
        
        // Envoi de l'email
        $to = 'ventes@cenov-distribution.fr';
        $subject = 'Nouvelle plaque signalétique de ' . $data['nom'];
        $headers = [
            'From: Cenov Distribution <ventes@cenov-distribution.fr>',
            'Reply-To: ' . $data['nom'] . ' <' . $data['email'] . '>',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        $sent = wp_mail($to, $subject, $html_content, $headers, $attachments);
        
        // Nettoyage des fichiers temporaires
        foreach ($attachments as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        
        if ($sent) {
            return $file_warning . '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
        } else {
            return '<div class="error-message">Une erreur est survenue lors de l\'envoi de votre message. Veuillez nous contacter par téléphone.</div>';
        }
    }
    
    /**
     * Construit le contenu HTML de l'email
     * @param array $data Données du formulaire
     * @param array $attachments Liste des pièces jointes
     * @return string Contenu HTML
     */
    function buildEmailContent($data, $attachments) {
        $html_content = '
        <div style="font-family: Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #2563eb; margin-bottom: 5px; font-size: 28px;">Nouvelle plaque signalétique :</h1>
                <p style="margin-top: 0; margin-bottom: 5px;">Demande de : ' . $data['nom'] . '</p>
            </div>
            
            <div style="margin-bottom: 25px;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Informations personnelles :</h3>
                <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">
                    <p style="margin: 5px 0;"><strong>Prénom :</strong> ' . $data['prenom'] . '</p>
                    <p style="margin: 5px 0;"><strong>Nom :</strong> ' . $data['nom_famille'] . '</p>
                    <p style="margin: 5px 0;"><strong>Email :</strong> ' . $data['email'] . '</p>
                    <p style="margin: 5px 0;"><strong>Téléphone :</strong> ' . $data['telephone'] . '</p>
                </div>
            </div>
            
            <div style="margin-bottom: 25px;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Informations professionnelles :</h3>
                <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">
                    <p style="margin: 5px 0;"><strong>Société :</strong> ' . ($data['societe'] ? $data['societe'] : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Adresse :</strong> ' . ($data['adresse'] ? $data['adresse'] : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Code postal :</strong> ' . ($data['codepostal'] ? $data['codepostal'] : CENOV_NOT_PROVIDED_M) . '</p>
                    <p style="margin: 5px 0;"><strong>Ville :</strong> ' . ($data['ville'] ? $data['ville'] : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Produit concerné :</strong> ' . ($data['produit'] ? $data['produit'] : CENOV_NOT_PROVIDED_M) . '</p>
                </div>
            </div>';

        // Ajouter le message s'il existe
        if (!empty($data['message'])) {
            $html_content .= '
            <div style="margin-bottom: 25px;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Message :</h3>
                <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">
                    <p style="margin: 5px 0;">' . nl2br(htmlspecialchars($data['message'])) . '</p>
                </div>
            </div>';
        }

        // Ajouter l'information sur les pièces jointes
        if (empty($attachments)) {
            $html_content .= '
            <div style="margin-bottom: 25px;">
                <p style="color: #9a3412; font-style: italic;">Aucune plaque signalétique n\'a été jointe à ce message.</p>
            </div>';
        }
        
        // Ajout du pied de page
        $html_content .= '
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: rgb(68, 71, 75); font-size: 14px;">
            <p>© Cenov Distribution - Tous droits réservés</p>
        </div>
    </div>';
        
        return $html_content;
    }
}

function cenovCheckSubmissionRate() {
    // Récupération de l'adresse IP du visiteur
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Utilisez un hachage MD5 de l'IP pour éviter des caractères spéciaux dans les noms des transients
    $ip_hash = md5($ip);
    
    // Récupérer le compteur de soumissions pour cette IP
    $submission_count = get_transient('cenov_submission_count_' . $ip_hash);
    
    // Si aucun compteur n'existe encore, l'initialiser à 1 et définir l'expiration à 1 heure
    if ($submission_count === false) {
        set_transient('cenov_submission_count_' . $ip_hash, 1, HOUR_IN_SECONDS);
        return true;
    }
    
    // Si le nombre maximal de tentatives est atteint (5 par défaut)
    if ($submission_count >= 15) {
        // Vous pouvez également journaliser cette tentative
        if (function_exists('error_log')) {
            error_log('Tentative de force brute détectée de l\'IP: ' . $ip);
        }
        return false;
    }
    
    // Incrémenter le compteur pour cette IP
    set_transient('cenov_submission_count_' . $ip_hash, $submission_count + 1, HOUR_IN_SECONDS);
    
    return true;
}

// Affichage du résultat
$result = cenovContactForm();
?>

<div class="cenov-form-container">
    <?php if ($result) : ?>
        <div class="cenov-message-result"><?php echo $result; ?></div>
    <?php endif; ?>

    <h3>Envoyez-nous votre plaque signalétique 📋 : </h3>

    <form method="post" action="" enctype="multipart/form-data">
         <!-- Champ honeypot caché visuellement mais accessible aux robots -->
         <div class="honeypot-field">
         <input type="text" name="cenov_website" id="cenov_website" autocomplete="off" tabindex="-1" placeholder="Ne pas remplir ce champ">
        </div>

        <?php wp_nonce_field('cenov_contact_action', 'cenov_nonce'); ?>
        
        <!-- Timestamp caché pour vérifier le temps de soumission -->
        <input type="hidden" name="cenov_timestamp" value="<?php echo time(); ?>">

        <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">

        <div class="form-grid">
            <!-- Première ligne: Prénom et Nom côte à côte -->
            <div class="form-row">
                <label for="cenov-prenom">* Prénom :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </span>
                    <input type="text" id="cenov-prenom" name="cenov_prenom" placeholder="Prénom" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-nom">* Nom :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </span>
                    <input type="text" id="cenov-nom" name="cenov_nom" placeholder="Nom" required />
                </div>
            </div>

            <!-- Deuxième ligne: Téléphone et Email côte à côte -->
            <div class="form-row">
                <label for="cenov-email">* Email :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <input type="email" id="cenov-email" name="cenov_email" placeholder="Votre adresse e-mail" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-telephone">* Téléphone :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <input type="tel" id="cenov-telephone" name="cenov_telephone" placeholder="Votre numéro de téléphone" required />
                </div>
            </div>

            <!-- Troisième ligne: Société (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-societe">* Nom de ma société :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-factory"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                    </span>
                    <input type="text" id="cenov-societe" name="cenov_societe" placeholder="Nom de ma société" required />
                </div>
            </div>

            <!-- Quatrième ligne: Adresse (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-adresse">* Adresse :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    </span>
                    <input type="text" id="cenov-adresse" name="cenov_adresse" placeholder="Adresse" required />
                </div>
            </div>

            <!-- Cinquième ligne: Code Postal et Ville côte à côte -->
            <div class="form-row">
                <label for="cenov-codepostal">* Code Postal :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <input type="text" id="cenov-codepostal" name="cenov_codepostal" placeholder="Code Postal" required  />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-ville">* Ville :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                    </span>
                    <input type="text" id="cenov-ville" name="cenov_ville" placeholder="Ville" required  />
                </div>
            </div>

            <!-- Sixième ligne: Produit concerné (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-produit">Produit concerné :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/></svg>
                    </span>
                    <input type="text" id="cenov-produit" name="cenov_produit" placeholder="Produit concerné" />
                </div>
            </div>

            <!-- Message (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-message">Votre message :</label>
                <div class="input-icon-wrapper textarea-wrapper">
                    <span class="input-icon textarea-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <textarea id="cenov-message" name="cenov_message" rows="4" placeholder="Votre message"></textarea>
                </div>
            </div>

            <!-- Upload de plaque signalétique (occupe toute la largeur) -->
            <div class="form-row full-width file-upload">
                <label for="cenov-plaque">Votre plaque signalétique 📋 :</label>
                <div class="file-input-container">
                    <input type="file" id="cenov-plaque" name="cenov_plaque[]" multiple accept=".jpg, .jpeg, .png, .pdf, .heic, .webp"/>
                    <div class="file-upload-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        <span id="file-name-display">Choisir un ou plusieurs fichiers ou glisser-déposer</span>
                    </div>
                </div>
                
                <!-- Zone de prévisualisation du fichier -->
                <div id="file-preview" class="file-preview-container" style="display: none;">
                    <div class="preview-header">
                        <span class="preview-title">Aperçu du fichier</span>
                        <button type="button" id="remove-file" class="remove-file-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="preview-content">
                        <!-- Pour les images -->
                        <img id="image-preview" src="#" alt="Aperçu de l'img" />
                        <!-- Pour les PDF -->
                        <div id="pdf-preview">
                            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-type-pdf">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <path d="M9 13v-1h6v1"/>
                                <path d="M11 15v4"/>
                                <path d="M9 19h4"/>
                            </svg>
                            <p id="pdf-name"></p>
                        </div>
                    </div>
                </div>
                
                <div class="file-formats">
                    <div class="format-item"><span class="format-icon"></span> JPG</div>
                    <div class="format-item"><span class="format-icon"></span> JPEG</div>
                    <div class="format-item"><span class="format-icon"></span> PNG</div>
                    <div class="format-item"><span class="format-icon"></span> PDF</div>
                    <div class="format-item"><span class="format-icon"></span> HEIC</div>
                    <div class="format-item"><span class="format-icon"></span> WEBP</div>
                    <div class="format-item"><span class="format-icon"></span> Max: 10 Mo</div>
                </div>
            </div>

            <!-- RGPD (occupe toute la largeur) -->
            <div class="form-row full-width">
                <div class="cenov-gdpr-consent">
                    <input type="checkbox" id="cenov-gdpr" name="cenov_gdpr" required />
                    <label for="cenov-gdpr">J'accepte que mes données soient utilisées pour traiter ma demande *</label>
                </div>
            </div>

            <!-- Bouton d'envoi (occupe toute la largeur) -->
            <div class="form-row full-width form-submit">
                <button type="submit" name="cenov_submit" value="1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-horizontal-icon lucide-send-horizontal"><path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"/><path d="M6 12h16"/></svg> Envoyer</button>
            </div>
        </div>
    </form>
</div>

<style>
    .warning-message {
    background-color: #fff7ed;
    color: #9a3412;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
    border-left: 4px solid #f97316;
}
     .honeypot-field {
        opacity: 0;
        position: absolute;
        top: 0;
        left: 0;
        height: 0;
        width: 0;
        z-index: -1;
        overflow: hidden;
    }

  .cenov-form-container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 30px;
    background: #f3f4f6 !important;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1) !important;
    border: 2px solid #2563eb !important;
  }

  .cenov-form-container h3 {
    margin-top: 0;
    margin-bottom: 25px;
    color: #333;
    font-size: 1.4rem;
  }
  
  /* Grille pour la disposition en deux colonnes */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }
  
  /* Les éléments qui doivent prendre toute la largeur */
  .full-width {
    grid-column: 1 / -1;
  }

  .form-row {
    margin-bottom: 0;
  }

  .form-row label {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
    font-size: 0.95rem;
    color: #444;
  }
  
  /* Style pour les inputs avec icônes */
  .input-icon-wrapper {
    position: relative;
    display: flex;
    align-items: center;
  }
  
  .input-icon-wrapper:focus-within {
    transform: translateY(-2px);
  }
  
  .input-icon {
    position: absolute;
    left: 12px;
    display: flex;
    align-items: center;
    height: 100%;
    color: #666;
    z-index: 1;
    line-height: 1;
  }
  
  .textarea-wrapper .input-icon {
    align-items: flex-start;
    padding-top: 12px;
  }
  
  .input-icon-wrapper input[type="text"],
  .input-icon-wrapper input[type="email"],
  .input-icon-wrapper input[type="tel"] {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #6b7280 !important;
    border-radius: 6px;
    font-size: 15px;
    line-height: 1.4;
  }
  
  .input-icon-wrapper textarea {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #6b7280 !important;
    border-radius: 6px;
    font-size: 15px;
    line-height: 1.4;
  }

  .input-icon-wrapper input:focus,
  .input-icon-wrapper textarea:focus {
    border: 2px solid #2563eb !important;
    outline: none;
  }
  
  /* Styles pour le glisser-déposer de fichiers */
  .file-input-container {
    position: relative !important;
    border: 2px dashed #ccc !important;
    border-radius: 8px !important;
    padding: 35px 20px !important;
    text-align: center !important;
    background-color: white !important;
    transition: border-color 0.3s, transform 0.2s !important;
    margin-bottom: 10px !important;
    cursor: pointer !important;
  }

  .file-input-container:hover {
    border-color: #2563eb !important;
    transform: translateY(-2px) !important;
  }

  .file-input-container input[type="file"] {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    opacity: 0 !important;
    cursor: pointer !important;
    z-index: 100 !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .file-upload-placeholder {
    position: relative !important;
    z-index: 5 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    color: #6b7280 !important;
    pointer-events: none !important;
  }

  .file-upload-placeholder svg {
    margin-bottom: 12px !important;
    color: #2563eb !important;
    width: 28px !important;
    height: 28px !important;
  }

  .file-formats {
    display: flex !important;
    justify-content: center !important;
    flex-wrap: wrap !important;
    margin-top: 12px !important;
    gap: 10px !important;
  }

  .format-item {
    background-color: #fff !important;
    padding: 5px 12px !important;
    border-radius: 16px !important;
    font-size: 12px !important;
    color: #4b5563 !important;
    display: flex !important;
    align-items: center !important;
    border: 1px solid #e5e7eb !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .05) !important;
  }

  .file-preview-container {
    margin-top: 15px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    background-color: #fff !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .05) !important;
  }

  .preview-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 10px 15px !important;
    background-color: #f9fafb !important;
    border-bottom: 1px solid #e5e7eb !important;
  }

  .preview-title {
    font-weight: 600 !important;
    font-size: .9rem !important;
    color: #4b5563 !important;
  }

  .remove-file-btn {
    background: 0 0 !important;
    border: none !important;
    color: #6b7280 !important;
    cursor: pointer !important;
    padding: 5px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: .2s !important;
  }

  .remove-file-btn:hover {
    background-color: #f3f4f6 !important;
    color: #ef4444 !important;
  }

  .preview-content {
    padding: 15px !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    max-height: 300px !important;
    overflow: auto !important;
  }

  #image-preview {
    max-width: 100% !important;
    max-height: 270px !important;
    object-fit: contain !important;
  }

  #pdf-preview {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    padding: 20px !important;
  }

  #pdf-name {
    margin-top: 10px !important;
    font-size: .9rem !important;
    color: #4b5563 !important;
    word-break: break-all !important;
  }

  /* Styles pour plusieurs fichiers */
  .preview-content-multiple {
    display: flex !important;
    flex-wrap: wrap !important;
    gap: 15px !important;
    padding: 15px !important;
    max-height: 300px !important;
    overflow-y: auto !important;
  }

  .preview-item {
    width: 150px !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 6px !important;
    background-color: #f9fafb !important;
    overflow: hidden !important;
    position: relative !important;
    transition: .2s !important;
  }

  .preview-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, .1) !important;
    transform: translateY(-2px) !important;
  }

  .preview-item-header {
    padding: 8px !important;
    font-size: .75rem !important;
    color: #4b5563 !important;
    border-bottom: 1px solid #e5e7eb !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
  }

  .preview-item-content {
    height: 120px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: #fff !important;
    padding: 5px !important;
  }

  .preview-item-actions {
    padding: 8px !important;
    border-top: 1px solid #e5e7eb !important;
    background-color: #f3f4f6 !important;
  }

  .remove-single-file {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    padding: 4px 8px !important;
    border: none !important;
    background-color: transparent !important;
    color: #6b7280 !important;
    font-size: .7rem !important;
    cursor: pointer !important;
    transition: .2s !important;
    border-radius: 4px !important;
  }

  .remove-single-file:hover {
    background-color: #fee2e2 !important;
    color: #dc2626 !important;
  }

  .thumbnail-preview {
    max-width: 100% !important;
    max-height: 100% !important;
    object-fit: contain !important;
  }

  .file-name {
    font-weight: 600 !important;
    display: block !important;
    overflow: hidden !important;
    white-space: nowrap !important;
    text-overflow: ellipsis !important;
  }

  .file-size {
    font-size: .7rem !important;
    color: #6b7280 !important;
  }

  .pdf-icon {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #ef4444 !important;
  }

  .format-icon {
    margin-right: 4px !important;
  }

  .cenov-gdpr-consent {
    display: flex;
    align-items: flex-start;
    margin-top: 5px;
  }

  .cenov-gdpr-consent input {
    margin-top: 4px;
    margin-right: 10px;
  }

  .form-submit button {
    width: auto !important;
    max-width: 300px !important;
    margin: 0 auto !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: white !important;
    background-color: #2563eb !important;
    border: none !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    font-size: 1rem !important;
    padding: 12px 24px !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.2) !important;
  }

  .form-submit button svg {
    margin-right: 8px !important;
  }

  .form-submit button:hover {
    background-color: #1e40af !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3) !important;
  }

  .form-submit button:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5) !important;
  }

  .success-message {
    background-color: #ecfdf5;
    color: #065f46;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
    border-left: 4px solid #10b981;
  }

  .error-message {
    background-color: #fef2f2;
    color: #991b1b;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-weight: 500;
    border-left: 4px solid #ef4444;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .form-grid {
      grid-template-columns: 1fr;
    }
    
    .cenov-form-container {
      padding: 20px;
    }
  }
</style>

<script src="https://www.google.com/recaptcha/api.js?render=6LcXl_sqAAAAAP5cz7w1iul0Bu18KnGqQ6u2DZ7W"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Références aux éléments
    const fileInput = document.getElementById("cenov-plaque");
    const fileNameDisplay = document.getElementById("file-name-display");
    const filePreview = document.getElementById("file-preview");
    const fileInputContainer = document.querySelector(".file-input-container");
    const form = document.querySelector(".cenov-form-container form");
    const recaptchaResponse = document.getElementById("g-recaptcha-response");
    
    // Configuration du reCAPTCHA pour la soumission du formulaire
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault();
            
            // Exécuter reCAPTCHA
            grecaptcha.ready(function() {
                grecaptcha.execute('6LcXl_sqAAAAAP5cz7w1iul0Bu18KnGqQ6u2DZ7W', {action: 'submit'})
                .then(function(token) {
                    // Ajouter le token au champ caché
                    document.getElementById('g-recaptcha-response').value = token;
                    // Supprimer les données du localStorage
                    localStorage.removeItem(storageKey);
                    // Soumettre le formulaire
                    form.submit();
                });
            });
        });
    }

    // Variables pour gérer les fichiers
    let selectedFiles = [];
    let isInputClick = false;

    // Fonction pour ajouter des fichiers sans doublons
    function addFiles(files) {
        const fileArray = Array.from(files);
        for (let i = 0; i < fileArray.length; i++) {
            const file = fileArray[i];
            // Vérifier si le fichier n'existe pas déjà
            if (!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        }
    }

    // Fonction pour créer une FileList personnalisée
    function createFileList(files) {
        const dt = new DataTransfer();
        files.forEach(file => {
            dt.items.add(file);
        });
        return dt.files;
    }

    // Fonction principale pour gérer la sélection de fichiers
    function handleFileSelect(event) {
        isInputClick = false;
        
        if (fileInput.files.length > 0) {
            addFiles(fileInput.files);
            fileInput.files = createFileList(selectedFiles);
            
            fileNameDisplay.textContent = `${selectedFiles.length} fichier(s) sélectionné(s)`;
            filePreview.innerHTML = "";
            filePreview.style.display = "block";
            
            // Créer l'en-tête
            const header = document.createElement("div");
            header.className = "preview-header";
            header.innerHTML = `
                <span class="preview-title">Aperçu des fichiers</span>
                <button type="button" id="remove-all-files" class="remove-file-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">
                        <path d="M18 6 6 18"></path>
                        <path d="m6 6 12 12"></path>
                    </svg>
                </button>
            `;
            filePreview.appendChild(header);
            
            // Créer le conteneur pour les prévisualisations
            const contentContainer = document.createElement("div");
            contentContainer.className = "preview-content-multiple";
            filePreview.appendChild(contentContainer);
            
            // Afficher chaque fichier
            selectedFiles.forEach((file, index) => {
                const previewItem = document.createElement("div");
                previewItem.className = "preview-item";
                
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                if (file.type.startsWith("image/")) {
                    const fileURL = URL.createObjectURL(file);
                    previewItem.innerHTML = `
                        <div class="preview-item-header">
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">(${fileSize} MB)</span>
                        </div>
                        <div class="preview-item-content">
                            <img src="${fileURL}" alt="Aperçu de l'image" class="thumbnail-preview" />
                        </div>
                        <div class="preview-item-actions">
                            <button type="button" class="remove-single-file" data-index="${index}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    <line x1="10" x2="10" y1="11" y2="17"/>
                                    <line x1="14" x2="14" y1="11" y2="17"/>
                                </svg>
                                &nbsp;Supprimer
                            </button>
                        </div>
                    `;
                } else {
                    previewItem.innerHTML = `
                        <div class="preview-item-header">
                            <span class="file-name">${file.name}</span>
                            <span class="file-size">(${fileSize} MB)</span>
                        </div>
                        <div class="preview-item-content">
                            <div class="pdf-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file">
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                        </div>
                        <div class="preview-item-actions">
                            <button type="button" class="remove-single-file" data-index="${index}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    <line x1="10" x2="10" y1="11" y2="17"/>
                                    <line x1="14" x2="14" y1="11" y2="17"/>
                                </svg>
                                &nbsp;Supprimer
                            </button>
                        </div>
                    `;
                }
                
                contentContainer.appendChild(previewItem);
            });
            
            // Ajouter les écouteurs d'événements pour les boutons
            document.getElementById("remove-all-files").addEventListener("click", removeAllFiles);
            document.querySelectorAll(".remove-single-file").forEach(btn => {
                btn.addEventListener("click", removeSingleFile);
            });
        } else {
            if (selectedFiles.length === 0) {
                fileNameDisplay.textContent = "Choisir un fichier ou glisser-déposer";
                filePreview.style.display = "none";
            }
        }
    }

    // Fonction pour supprimer un fichier spécifique
    function removeSingleFile(event) {
        const index = parseInt(event.currentTarget.getAttribute("data-index"));
        selectedFiles.splice(index, 1);
        fileInput.files = createFileList(selectedFiles);
        
        if (selectedFiles.length > 0) {
            handleFileSelect();
        } else {
            fileNameDisplay.textContent = "Choisir un fichier ou glisser-déposer";
            filePreview.style.display = "none";
        }
        
        event.stopPropagation();
    }

    // Fonction pour supprimer tous les fichiers
    function removeAllFiles(event) {
        selectedFiles = [];
        fileInput.value = "";
        fileNameDisplay.textContent = "Choisir un fichier ou glisser-déposer";
        filePreview.style.display = "none";
        event.stopPropagation();
    }

    // Gestion du clic sur le conteneur
    if (fileInputContainer) {
        fileInputContainer.addEventListener("click", function(e) {
            if (!isInputClick) {
                fileInput.click();
            }
        });
    }

    // Gestion des événements de fichier
    if (fileInput) {
        fileInput.addEventListener("click", function() {
            isInputClick = true;
            setTimeout(function() {
                isInputClick = false;
            }, 500);
        });
        fileInput.addEventListener("change", handleFileSelect);
    }

    // Gestion du drag and drop
    if (fileInputContainer) {
        function preventDefault(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight() {
            fileInputContainer.style.borderColor = "#2563eb";
            fileInputContainer.style.backgroundColor = "#eff6ff";
        }

        function unhighlight() {
            fileInputContainer.style.borderColor = "#ccc";
            fileInputContainer.style.backgroundColor = "white";
        }

        ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, preventDefault, false);
        });

        ["dragenter", "dragover"].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, highlight, false);
        });

        ["dragleave", "drop"].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, unhighlight, false);
        });

        fileInputContainer.addEventListener("drop", function(e) {
            let dt = e.dataTransfer;
            let files = dt.files;

            if (files.length > 0) {
                addFiles(files);
                fileInput.files = createFileList(selectedFiles);
                const changeEvent = new Event("change", {bubbles: true});
                fileInput.dispatchEvent(changeEvent);
            }
        }, false);
    }
    
    // Fonctionnalité de localStorage pour les champs du formulaire
    // Liste des IDs des champs à sauvegarder/restaurer
    const champs = [
        {id: "cenov-prenom", type: "input"},
        {id: "cenov-nom", type: "input"},
        {id: "cenov-email", type: "input"},
        {id: "cenov-telephone", type: "input"},
        {id: "cenov-societe", type: "input"},
        {id: "cenov-adresse", type: "input"},
        {id: "cenov-codepostal", type: "input"},
        {id: "cenov-ville", type: "input"},
        {id: "cenov-produit", type: "input"},
        {id: "cenov-message", type: "textarea"},
        {id: "cenov-gdpr", type: "checkbox"}
    ];
    const storageKey = "cenov_fx_form_data";

    // Fonction pour sauvegarder les champs dans le localStorage
    function saveFormData() {
        const data = {};
        champs.forEach(champ => {
            const el = document.getElementById(champ.id);
            if (!el) return;
            if (champ.type === "checkbox") {
                data[champ.id] = el.checked;
            } else {
                data[champ.id] = el.value;
            }
        });
        localStorage.setItem(storageKey, JSON.stringify(data));
    }

    // Fonction pour restaurer les champs depuis le localStorage
    function restoreFormData() {
        const data = localStorage.getItem(storageKey);
        if (!data) return;
        let parsed;
        try {
            parsed = JSON.parse(data);
        } catch (e) { return; }
        champs.forEach(champ => {
            const el = document.getElementById(champ.id);
            if (!el || !(champ.id in parsed)) return;
            if (champ.type === "checkbox") {
                el.checked = !!parsed[champ.id];
            } else {
                el.value = parsed[champ.id];
            }
        });
    }

    // Sauvegarde à chaque modification
    champs.forEach(champ => {
        const el = document.getElementById(champ.id);
        if (!el) return;
        el.addEventListener(champ.type === "checkbox" ? "change" : "input", saveFormData);
    });

    // Restauration au chargement
    restoreFormData();
});
</script>
