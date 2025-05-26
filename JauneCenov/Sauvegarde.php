<?php
if (!function_exists('cenovContactForm')) {
    function cenovContactForm() {
        $hasError = false;
        $result = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cenov_prenom']) && isset($_POST['g-recaptcha-response'])) {
            // Protection contre les attaques de force brute
            if (!cenovCheckSubmissionRate()) {
                return '<div class="error-message">Trop de tentatives. Veuillez réessayer dans une heure.</div>';
            }
            // Vérification du nonce CSRF
            if (!isset($_POST['cenov_nonce']) || !wp_verify_nonce($_POST['cenov_nonce'], 'cenov_contact_action')) {
                return '<div class="error-message">Erreur de sécurité. Veuillez rafraîchir la page et réessayer.</div>';
            }
            // Vérification honeypot - si rempli, c'est probablement un bot
            if (!empty($_POST['cenov_website'])) {
                // Bot détecté, mais on simule un succès pour ne pas alerter le bot
                return '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            }
            
            // Vérification du temps de soumission
            $submissionTime = isset($_POST['cenov_timestamp']) ? (int)$_POST['cenov_timestamp'] : 0;
            $currentTime = time();
            $timeDifference = $currentTime - $submissionTime;
            
            // Si le formulaire est soumis en moins de 2 secondes, c'est probablement un bot
            if ($timeDifference < 2) {
                // Simulation d'un succès pour ne pas alerter le bot
                return '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            }

           // Vérification reCAPTCHA
$recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';

if (empty($recaptcha_response)) {
    return '<div class="error-message">Échec de la vérification de sécurité: Réponse reCAPTCHA manquante.</div>';
}

// Déboguer la clé secrète
$recaptcha_secret = get_option('cenov_recaptcha_secret', '');
if (empty($recaptcha_secret)) {
    return '<div class="error-message">Erreur de configuration: Clé secrète reCAPTCHA non définie dans WordPress.</div>';
}

// Affichage des 10 premiers caractères de la clé pour débogage
$debug_secret = substr($recaptcha_secret, 0, 5) . '...';

$verify_response = wp_remote_get(
    "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"
);

if (is_wp_error($verify_response)) {
    $error_message = $verify_response->get_error_message();
    return '<div class="error-message">Erreur de connexion avec l\'API reCAPTCHA: ' . $error_message . '</div>';
}

$result = json_decode(wp_remote_retrieve_body($verify_response));

// Débogage plus détaillé de la réponse reCAPTCHA
if (!$result->success || (isset($result->score) && $result->score < 0.5)) {
    $debug_info = '';
    if (isset($result->score)) {
        $debug_info .= ' Score: ' . $result->score;
    }
    if (isset($result->{'error-codes'}) && !empty($result->{'error-codes'})) {
        $debug_info .= ' Codes d\'erreur: ' . implode(', ', $result->{'error-codes'});
    }
    return '<div class="error-message">La vérification de sécurité a échoué. ' . $debug_info . '</div>';
}
            
            // Récupération des données du formulaire
            // Récupération des données du formulaire
$prenom = isset($_POST['cenov_prenom']) ? sanitize_text_field($_POST['cenov_prenom']) : '';
$nom_famille = isset($_POST['cenov_nom']) ? sanitize_text_field($_POST['cenov_nom']) : '';
$nom = $prenom . ' ' . $nom_famille;
$email = isset($_POST['cenov_email']) ? sanitize_email($_POST['cenov_email']) : '';
$telephone = isset($_POST['cenov_telephone']) ? sanitize_text_field($_POST['cenov_telephone']) : '';
$message = isset($_POST['cenov_message']) ? sanitize_textarea_field($_POST['cenov_message']) : '';

// Récupération des champs optionnels
$societe = isset($_POST['cenov_societe']) ? sanitize_text_field($_POST['cenov_societe']) : '';
$adresse = isset($_POST['cenov_adresse']) ? sanitize_text_field($_POST['cenov_adresse']) : '';
$codepostal = isset($_POST['cenov_codepostal']) ? sanitize_text_field($_POST['cenov_codepostal']) : '';
$ville = isset($_POST['cenov_ville']) ? sanitize_text_field($_POST['cenov_ville']) : '';
$produit = isset($_POST['cenov_produit']) ? sanitize_text_field($_POST['cenov_produit']) : '';

// Vérification des champs obligatoires
if (empty($prenom) || empty($nom_famille) || empty($email) || empty($telephone)) {
    return '<div class="error-message">Veuillez remplir tous les champs obligatoires.</div>';
}

// Préparation de l'email
$to = 'ventes@cenov-distribution.fr';
$subject = 'Nouvelle plaque signalétique de ' . $nom;

// Construction du corps de l'email avec tous les champs
$content = "--- INFORMATIONS PERSONNELLES ---\r\n";
            $content .= "Prénom : " . $prenom . "\r\n";
            $content .= "Nom : " . $nom_famille . "\r\n";
            $content .= "Email : " . $email . "\r\n";
            $content .= "Téléphone : " . $telephone . "\r\n\r\n";
            
            $content .= "--- INFORMATIONS PROFESSIONNELLES ---\r\n";
            $content .= "Société : " . ($societe ? $societe : 'Non renseignée') . "\r\n";
            $content .= "Adresse : " . ($adresse ? $adresse : 'Non renseignée') . "\r\n";
            $content .= "Code postal : " . ($codepostal ? $codepostal : 'Non renseigné') . "\r\n";
            $content .= "Ville : " . ($ville ? $ville : 'Non renseignée') . "\r\n";
            $content .= "Produit concerné : " . ($produit ? $produit : 'Non renseigné') . "\r\n\r\n";
            
            $content .= "--- MESSAGE ---\r\n";
            $content .= !empty($message) ? $message : 'Aucun message spécifique fourni';
            $content .= "\r\n\r\n";
            
            $headers = [
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
                'Reply-To: ' . $nom . ' <' . $email . '>'
            ];

           $fileWarning = '';
if (empty($_FILES['cenov_plaque']['name'][0])) {
    $fileWarning = '<div class="warning-message">Attention : aucune plaque signalétique n\'a été jointe à votre message.</div>';
}
            
// Gestion des fichiers uploadés
$attachments = array();
$fileNames = array();

if (!empty($_FILES['cenov_plaque']['name'][0])) {
    foreach($_FILES['cenov_plaque']['name'] as $key => $name) {
        if(empty($name)) continue;
        
        // Créer un tableau de fichier individuel pour faciliter le traitement
        $file = array(
            'name' => $_FILES['cenov_plaque']['name'][$key],
            'type' => $_FILES['cenov_plaque']['type'][$key],
            'tmp_name' => $_FILES['cenov_plaque']['tmp_name'][$key],
            'error' => $_FILES['cenov_plaque']['error'][$key],
            'size' => $_FILES['cenov_plaque']['size'][$key]
        );
        
        // Vérification des erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Erreur lors de l'upload du fichier " . $file['name'] . ": ";
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
            return '<div class="error-message">' . $error_message . '</div>';
        }
        
        // Vérification du type de fichier
        $allowed_types = array('image/jpeg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
        if (!in_array($file['type'], $allowed_types)) {
            return '<div class="error-message">Format de fichier non supporté pour "' . $file['name'] . '". Formats acceptés : JPG, JPEG, PNG, PDF, HEIC, WEBP</div>';
        }
        
        // Vérification de la taille
        $max_size = 10 * 1024 * 1024; // 10 Mo
        if ($file['size'] > $max_size) {
            return '<div class="error-message">Le fichier "' . $file['name'] . '" est trop volumineux (10 Mo maximum)</div>';
        }
        
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
            $attachments[] = $temp_file;
            $fileNames[] = $file['name'];
        } else {
            return '<div class="error-message">Erreur lors du téléchargement du fichier "' . $file['name'] . '". Veuillez réessayer.</div>';
        }
    }
}

// Mise à jour du contenu de l'email avec la liste des pièces jointes
if (!empty($fileNames)) {
    $content .= "\r\n\r\nPièces jointes :\r\n";
    foreach ($fileNames as $fileName) {
        $content .= "- " . $fileName . "\r\n";
    }
} else {
    $content .= "\r\n\r\nAucune plaque signalétique n'a été jointe à ce message.";
}
            
            // Envoi de l'email
            $sent = wp_mail($to, $subject, $content, $headers, $attachments);
            
            // Nettoyage des fichiers temporaires
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
            }
            
            if ($sent) {
                return $fileWarning . '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            } else {
                return '<div class="error-message">Une erreur est survenue lors de l\'envoi de votre message. Veuillez nous contacter par téléphone.</div>';
            }
        }
        
        return $result;
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
    if ($submission_count >= 30) {
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

<div class=cenov-chat-bubble-container>
<div class=cenov-chat-bubble-text id=cenov-chat-btn>
<svg xmlns=http://www.w3.org/2000/svg viewBox="0 0 24 24" fill=none stroke=black stroke-width=2 stroke-linecap=round stroke-linejoin=round>
<rect x=2 y=6 width=20 height=14 rx=2 ry=2 />
<circle cx=12 cy=13 r=4 />
<path d="M10 6 L8 2 h8 L14 6"/>
<circle cx=17.5 cy=8.5 r=1 fill=white stroke=none />
</svg>
<span class=cenov-chat-bubble-text-content>Une Plaque, Un prix (<2h)</span>
</div>
<div class=cenov-overlay id=cenov-overlay></div>
<div class=cenov-chat-popup id=cenov-chat-popup>
<div class=cenov-chat-header>
<span>Identification de votre plaque en moins de 2h ⏱️, meilleurs prix négociés</span>
<button class=cenov-chat-close id=cenov-close-btn>×</button>
</div>
<div class=cenov-chat-content id=cenov-chat-content>
<p style=margin-bottom:15px;color:#444;font-size:1.1em>
<strong>Comment ça marche :</strong> Prenez une photo de votre plaque signalétique, envoyez-la, et nos experts vous identifieront précisément la machine et ses pièces compatibles (retour en moins de 2 heures garanti ou 2% remise octroyée). </p><p style=margin-bottom:15px;color:#444;font-size:1.1em>Service gratuit, sans engagement !
</p>
<div id=cenov-shortcode-container>



<div class="cenov-form-container">
    <?php if ($result) : ?>
        <div class="cenov-message-result"><?php echo $result; ?></div>
    <?php endif; ?>

    <p style="margin-top: 0;margin-bottom: 25px;color: #333;font-size: 1.4rem;"><strong>Envoyez-nous votre plaque signalétique 📋 :</strong> </p>

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
                <label for="cenov-telephone">* Téléphone :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <input type="tel" id="cenov-telephone" name="cenov_telephone" placeholder="Votre numéro de téléphone" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-email">* Email :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <input type="email" id="cenov-email" name="cenov_email" placeholder="Votre adresse e-mail" required />
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
            <span id="file-name-display">Choisir un fichier ou glisser-déposer</span>
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
                <button type="submit" name="cenov_submit" value="1">Envoyer</button>
            </div>
        </div>
    </form>
</div>
</div>
</div>
</div>
</div>
<style>
.file-name,.form-row label,.form-submit button{font-weight:600;display:block}.file-name,.preview-item-header{white-space:nowrap;text-overflow:ellipsis}#image-preview,.thumbnail-preview{max-width:100%;object-fit:contain}.cenov-chat-popup.active,.cenov-overlay.active,.file-name,.form-row label{display:block}.file-name{overflow:hidden}.file-size{font-size:.7rem;color:#6b7280}.pdf-icon{display:flex;align-items:center;justify-content:center;color:#ef4444}.warning-message{background-color:#fff7ed;color:#9a3412;padding:12px;border-radius:6px;margin-bottom:20px;font-weight:500;border-left:4px solid #f97316}.honeypot-field{opacity:0;position:absolute;top:0;left:0;height:0;width:0;z-index:-1;overflow:hidden}.cenov-form-container{margin:20px auto;padding:30px;background:#f9f9f9;border-radius:8px;box-shadow:0 3px 8px rgba(0,0,0,.1)}.cenov-form-container h3{margin-top:0;margin-bottom:25px;color:#333;font-size:1.4rem}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}.full-width{grid-column:1/-1}.form-row{margin-bottom:0}.form-row label{margin-bottom:8px;font-size:.95rem;color:#444}.input-icon-wrapper{position:relative;display:flex;align-items:center}.input-icon-wrapper:focus-within{transform:translateY(-2px)}.input-icon{position:absolute;left:12px;display:flex;align-items:center;height:100%;color:#666;z-index:1;line-height:1}.textarea-wrapper .input-icon{align-items:flex-start;padding-top:12px}.input-icon-wrapper input[type=email],.input-icon-wrapper input[type=tel],.input-icon-wrapper input[type=text],.input-icon-wrapper textarea{width:100%;padding:12px 12px 12px 40px;border:1px solid #ddd!important;border-radius:6px;font-size:15px;line-height:1.4}.input-icon-wrapper input:focus,.input-icon-wrapper textarea:focus{border:2px solid #2563eb!important;outline:0}.file-input-container{position:relative;border:2px dashed #ccc;border-radius:8px;padding:35px 20px;text-align:center;background-color:#fff;transition:border-color .3s,transform .2s;margin-bottom:10px;cursor:pointer}.file-input-container:hover{border-color:#2563eb;transform:translateY(-2px)}.file-input-container input[type=file]{position:absolute;left:0;top:0;right:0;bottom:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:100;padding:0;margin:0}.file-upload-placeholder{position:relative;z-index:5;display:flex;flex-direction:column;align-items:center;color:#6b7280;pointer-events:none}.file-upload-placeholder svg{margin-bottom:12px;color:#2563eb;width:28px;height:28px}.file-formats{display:flex;justify-content:center;flex-wrap:wrap;margin-top:12px;gap:10px}.format-item{background-color:#fff;padding:5px 12px;border-radius:16px;font-size:12px;color:#4b5563;display:flex;align-items:center;border:1px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,.05)}.format-icon,.remove-single-file svg{margin-right:4px}.cenov-gdpr-consent{display:flex;align-items:flex-start;margin-top:5px}#pdf-name,.form-submit{margin-top:10px}.cenov-gdpr-consent input{margin-top:4px;margin-right:10px}.form-submit button{width:auto;max-width:300px;margin:0 auto;color:#fff;background-color:#2563eb;border:none;border-radius:6px;font-size:1rem;padding:12px 24px;cursor:pointer;transition:.3s;box-shadow:0 2px 5px rgba(37,99,235,.2)}.error-message,.success-message{padding:12px;border-radius:6px;margin-bottom:20px;font-weight:500}.form-submit button:hover{background-color:#1e40af;transform:translateY(-2px);box-shadow:0 4px 8px rgba(37,99,235,.3)}.form-submit button:focus{outline:0;box-shadow:0 0 0 3px rgba(59,130,246,.5)}.success-message{background-color:#ecfdf5;color:#065f46;border-left:4px solid #10b981}.error-message{background-color:#fef2f2;color:#991b1b;border-left:4px solid #ef4444}.file-preview-container{margin-top:15px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background-color:#fff;box-shadow:0 2px 4px rgba(0,0,0,.05)}.preview-header{display:flex;justify-content:space-between;align-items:center;padding:10px 15px;background-color:#f9fafb;border-bottom:1px solid #e5e7eb}.preview-title{font-weight:600;font-size:.9rem;color:#4b5563}.remove-file-btn{background:0 0;border:none;color:#6b7280;cursor:pointer;padding:5px;border-radius:50%;display:flex;align-items:center;justify-content:center;transition:.2s}.remove-file-btn:hover{background-color:#f3f4f6;color:#ef4444}.preview-content{padding:15px;display:flex;justify-content:center;align-items:center;max-height:300px;overflow:auto}#image-preview{max-height:270px}#pdf-preview{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px}#pdf-name{font-size:.9rem;color:#4b5563;word-break:break-all}.preview-item{width:150px;border:1px solid #e5e7eb;border-radius:6px;background-color:#f9fafb;overflow:hidden;position:relative;transition:.2s}.preview-item:hover{box-shadow:0 4px 8px rgba(0,0,0,.1);transform:translateY(-2px)}.preview-item-header{padding:8px;font-size:.75rem;color:#4b5563;border-bottom:1px solid #e5e7eb;overflow:hidden}.preview-item-content{height:120px;display:flex;align-items:center;justify-content:center;background-color:#fff;padding:5px}.preview-item-actions{padding:8px;border-top:1px solid #e5e7eb;background-color:#f3f4f6}.remove-single-file{display:flex;align-items:center;justify-content:center;width:100%;padding:4px 8px;border:none;background-color:transparent;color:#6b7280;font-size:.7rem;cursor:pointer;transition:.2s;border-radius:4px}.remove-single-file:hover{background-color:#fee2e2;color:#dc2626}.thumbnail-preview{max-height:100%}.preview-content-multiple{display:flex;flex-wrap:wrap;gap:15px;padding:15px;max-height:300px;overflow-y:auto}@media (max-width:768px){.form-grid{grid-template-columns:1fr}.cenov-form-container{padding:20px}.preview-content-multiple{justify-content:center}}.cenov-chat-popup,.cenov-overlay{display:none}.cenov-chat-bubble-container{position:fixed;bottom:2vh;right:20px;z-index:2147483647;font-family:Arial,sans-serif}.cenov-chat-bubble-text{display:flex;align-items:center;background-color:#edf000;color:#fff;padding:12px 20px;border-radius:30px;box-shadow:0 4px 8px rgba(0,0,0,.2);cursor:pointer;transition:.3s;animation:5s infinite pulse;border:2px solid #fff}.cenov-chat-bubble-text:hover{background-color:#ffe500}.cenov-chat-bubble-text svg{margin-right:8px;width:20px;height:20px}.cenov-chat-bubble-text-content{font-weight:700;font-family:Arial,sans-serif;font-size:14px;color:black}.cenov-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background-color:rgba(0,0,0,.5);z-index:2147483646}.cenov-chat-popup{position:fixed;bottom:70px;right:20px;width:80vw;background-color:#fff;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,.2);overflow:visible;z-index:2147483647}@media (max-width:767px){.cenov-chat-popup{width:auto;left:20px}}.cenov-chat-header{background-color:#edf000;color:black;padding:15px;font-weight:700;display:flex;justify-content:space-between;align-items:center;border-radius:10px 10px 0 0}.cenov-chat-content{padding:15px;max-height:60vh;overflow-y:auto;background-color:#fff;position:relative;z-index:2147483647}.cenov-chat-content *{position:relative;z-index:2147483647!important}.cenov-chat-close{background:0 0;border:none;color:black;font-size:18px;cursor:pointer;width:30px;height:30px;display:flex;align-items:center;justify-content:center}.cenov-chat-content button,.cenov-chat-content form,.cenov-chat-content input,.cenov-chat-content label,.cenov-chat-content select,.cenov-chat-content textarea{position:relative!important;z-index:2147483647!important}span.input-icon{width:50px}.cenov-form-container{max-width:95%!important}
</style>

<script>document.addEventListener("DOMContentLoaded",(function(){const e=document.createElement("div");e.id="chat-bubble-portal",e.style.position="fixed",e.style.top="0",e.style.left="0",e.style.width="100%",e.style.height="100%",e.style.pointerEvents="none",e.style.zIndex="2147483647",document.body.appendChild(e);const t=document.querySelector(".cenov-chat-bubble-container");if(t){t.style.pointerEvents="auto";const n=t.cloneNode(!0);t.parentNode.removeChild(t),e.appendChild(n);const c=document.querySelector("#chat-bubble-portal #cenov-chat-btn"),l=document.querySelector("#chat-bubble-portal #cenov-chat-popup"),a=document.querySelector("#chat-bubble-portal #cenov-chat-content"),i=document.querySelector("#chat-bubble-portal #cenov-overlay"),s=document.querySelector("#chat-bubble-portal #cenov-close-btn");function o(){l.classList.remove("active"),i.classList.remove("active"),i.style.pointerEvents="none",document.body.style.overflow=""}c.addEventListener("click",(function(){l.classList.add("active"),i.classList.add("active"),i.style.pointerEvents="auto",l.style.pointerEvents="auto",setTimeout((function(){a.querySelectorAll("*").forEach((e=>{e.style.position="relative",e.style.zIndex="2147483647"}))}),500),document.body.style.overflow="hidden"})),s.addEventListener("click",(function(e){e.preventDefault(),o()})),i.addEventListener("click",o),l.addEventListener("click",(function(e){e.stopPropagation()}))}}))</script>
<script src="https://www.google.com/recaptcha/api.js?render=6LcXl_sqAAAAAP5cz7w1iul0Bu18KnGqQ6u2DZ7W"></script>


<script>document.addEventListener("DOMContentLoaded",(function(){const e=document.getElementById("cenov-plaque"),n=document.getElementById("file-name-display"),t=document.getElementById("file-preview"),i=document.querySelector(".file-input-container"),o=document.querySelector(".cenov-form-container form"),r=document.getElementById("g-recaptcha-response");o&&o.addEventListener("submit",(function(e){e.preventDefault(),grecaptcha.ready((function(){grecaptcha.execute("6LcXl_sqAAAAAP5cz7w1iul0Bu18KnGqQ6u2DZ7W",{action:"submit"}).then((function(e){e?r?(r.value=e,console.log("Token reCAPTCHA assigné"),setTimeout((function(){o.submit()}),500)):console.error("Erreur: Élément g-recaptcha-response introuvable"):console.error("Erreur: Token reCAPTCHA vide")})).catch((function(e){console.error("Erreur reCAPTCHA:",e)}))}))}));let s=[],a=!1;function l(e){const n=Array.from(e);for(let e=0;e<n.length;e++){const t=n[e];s.some((e=>e.name===t.name&&e.size===t.size))||s.push(t)}}function c(e){const n=new DataTransfer;return e.forEach((e=>{n.items.add(e)})),n.files}function d(i){if(a=!1,e.files.length>0){l(e.files),e.files=c(s),n.textContent=`${s.length} fichier(s) sélectionné(s)`,t.innerHTML="",t.style.display="block";const i=document.createElement("div");i.className="preview-header",i.innerHTML='\n                <span class="preview-title">Aperçu des fichiers</span>\n                <button type="button" id="remove-all-files" class="remove-file-btn">\n                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x">\n                        <path d="M18 6 6 18"></path>\n                        <path d="m6 6 12 12"></path>\n                    </svg>\n                </button>\n            ',t.appendChild(i);const o=document.createElement("div");o.className="preview-content-multiple",t.appendChild(o),s.forEach(((e,n)=>{const t=document.createElement("div");t.className="preview-item";const i=(e.size/1024/1024).toFixed(2);if(e.type.startsWith("image/")){const o=URL.createObjectURL(e);t.innerHTML=`\n                        <div class="preview-item-header">\n                            <span class="file-name">${e.name}</span>\n                            <span class="file-size">(${i} MB)</span>\n                        </div>\n                        <div class="preview-item-content">\n                            <img src="${o}" alt="Aperçu de l'image" class="thumbnail-preview" />\n                        </div>\n                        <div class="preview-item-actions">\n                            <button type="button" class="remove-single-file" data-index="${n}">\n                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2">
    <path d="M3 6h18"/>
    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
    <line x1="10" x2="10" y1="11" y2="17"/>
    <line x1="14" x2="14" y1="11" y2="17"/>
</svg>\n                                Supprimer\n                            </button>\n                        </div>\n                    `}else t.innerHTML=`\n                        <div class="preview-item-header">\n                            <span class="file-name">${e.name}</span>\n                            <span class="file-size">(${i} MB)</span>\n                        </div>\n                        <div class="preview-item-content">\n                            <div class="pdf-icon">\n                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file">\n                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>\n                                    <polyline points="14 2 14 8 20 8"/>\n                                </svg>\n                            </div>\n                        </div>\n                        <div class="preview-item-actions">\n                            <button type="button" class="remove-single-file" data-index="${n}">\n                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">\n                                    <path d="M3 6h18"></path>\n                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>\n                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>\n                                </svg>\n                                Supprimer\n                            </button>\n                        </div>\n                    `;o.appendChild(t)})),document.getElementById("remove-all-files").addEventListener("click",u),document.querySelectorAll(".remove-single-file").forEach((e=>{e.addEventListener("click",p)}))}else 0===s.length&&(n.textContent="Choisir un fichier ou glisser-déposer",t.style.display="none")}function p(i){const o=parseInt(i.currentTarget.getAttribute("data-index"));s.splice(o,1),e.files=c(s),s.length>0?d():(n.textContent="Choisir un fichier ou glisser-déposer",t.style.display="none"),i.stopPropagation()}function u(i){s=[],e.value="",n.textContent="Choisir un fichier ou glisser-déposer",t.style.display="none",i.stopPropagation()}if(i&&i.addEventListener("click",(function(n){a||e.click()})),e&&(e.addEventListener("click",(function(){a=!0,setTimeout((function(){a=!1}),500)})),e.addEventListener("change",d)),i){function h(e){e.preventDefault(),e.stopPropagation()}function v(){i.style.borderColor="#2563eb",i.style.backgroundColor="#eff6ff"}function f(){i.style.borderColor="#ccc",i.style.backgroundColor="white"}["dragenter","dragover","dragleave","drop"].forEach((e=>{i.addEventListener(e,h,!1)})),["dragenter","dragover"].forEach((e=>{i.addEventListener(e,v,!1)})),["dragleave","drop"].forEach((e=>{i.addEventListener(e,f,!1)})),i.addEventListener("drop",(function(n){let t=n.dataTransfer.files;if(t.length>0){l(t),e.files=c(s);const n=new Event("change",{bubbles:!0});e.dispatchEvent(n)}}),!1)}}))</script>