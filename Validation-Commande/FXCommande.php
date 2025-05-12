<?php
if (!function_exists('cenovContactForm')) {
    function cenovContactForm() {
        $hasError = false;
        $result = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_first_name']) && isset($_POST['g-recaptcha-response'])) {
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
            
            // Si le formulaire est soumis en moins de 3 secondes, c'est probablement un bot
            if ($timeDifference < 3) {
                // Simulation d'un succès pour ne pas alerter le bot
                return '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            }

            // Vérification reCAPTCHA
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
            
            if (empty($recaptcha_response)) {
                return '<div class="error-message">Échec de la vérification de sécurité. Veuillez réessayer.</div>';
            }
            
            $recaptcha_secret = get_option('cenov_recaptcha_secret', '');
            $verify_response = wp_remote_get(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"
            );
            
            if (is_wp_error($verify_response)) {
                return '<div class="error-message">Erreur de vérification. Veuillez réessayer plus tard.</div>';
            }
            
            $result = json_decode(wp_remote_retrieve_body($verify_response));
            
            // Vérifier que le score est acceptable (0.0 = bot, 1.0 = humain)
            if (!$result->success || $result->score < 0.5) {
                return '<div class="error-message">La vérification de sécurité a échoué. Veuillez réessayer.</div>';
            }
            
            // Récupération des données du formulaire
            $debug_messages = [];
            // Afficher les logs pour tout le monde (plus seulement admin)
            $debug_messages[] = '=== DÉBUT DU TRAITEMENT DU FORMULAIRE ===';
            $debug_messages[] = 'Méthode de requête : ' . $_SERVER['REQUEST_METHOD'];
            $debug_messages[] = 'Données POST reçues : ' . print_r($_POST, true);
            $debug_messages[] = 'Fichiers reçus : ' . print_r($_FILES, true);

            $content = "--- INFORMATIONS PERSONNELLES ---\r\n";

            // Champs natifs WooCommerce
            $content .= "Prénom : " . (isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : 'Non renseigné') . "\r\n";
            $content .= "Nom : " . (isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : 'Non renseigné') . "\r\n";
            $content .= "Email : " . (isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : 'Non renseigné') . "\r\n";
            $content .= "Téléphone : " . (isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : 'Non renseigné') . "\r\n";

            // Champs personnalisés
            $content .= "Référence client : " . (isset($_POST['billing_reference']) ? sanitize_text_field($_POST['billing_reference']) : 'Non renseigné') . "\r\n";
            $content .= "Message : " . (isset($_POST['billing_message']) ? sanitize_textarea_field($_POST['billing_message']) : 'Non renseigné') . "\r\n";
            $content .= "Matériel équivalent : " . (isset($_POST['billing_materiel_equivalent']) ? 'Oui' : 'Non') . "\r\n";

            // Informations professionnelles
            $content .= "\r\n--- INFORMATIONS PROFESSIONNELLES ---\r\n";
            $content .= "Société : " . (isset($_POST['billing_company']) ? sanitize_text_field($_POST['billing_company']) : 'Non renseigné') . "\r\n";
            $content .= "Adresse : " . (isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : 'Non renseigné') . "\r\n";
            $content .= "Code postal : " . (isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : 'Non renseigné') . "\r\n";
            $content .= "Ville : " . (isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : 'Non renseigné') . "\r\n";
            $content .= "Pays : " . (isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : 'Non renseigné') . "\r\n";

            $debug_messages[] = 'Contenu de l\'email préparé : ' . $content;

            // Gestion des fichiers
            if (!empty($_FILES['billing_plaque']['name'])) {
                $content .= "\r\n--- FICHIER JOINT ---\r\n";
                $content .= "Nom du fichier : " . sanitize_file_name($_FILES['billing_plaque']['name']) . "\r\n";
                $debug_messages[] = 'Fichier joint détecté : ' . $_FILES['billing_plaque']['name'];
            }

            // Envoi de l'email
            $to = 'ventes@cenov-distribution.fr';
            $subject = 'Nouvelle demande de devis';
            $headers = [
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
                'Reply-To: ' . (isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '') . ' ' . 
                             (isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : '') . 
                             ' <' . (isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : '') . '>'
            ];
            $debug_messages[] = 'Tentative d\'envoi d\'email à : ' . $to;
            $debug_messages[] = 'En-têtes de l\'email : ' . print_r($headers, true);

            $fileWarning = '';
            if (empty($_FILES['billing_plaque']['name'])) {
                $fileWarning = '<div class="warning-message">Attention : aucune plaque signalétique n\'a été jointe à votre message.</div>';
            }
            
            // Gestion du fichier uploadé
            $attachments = array();
            
            if (!empty($_FILES['billing_plaque']['name'])) {
                $file = $_FILES['billing_plaque'];
                
                // Vérification des erreurs d'upload
                if ($file['error'] !== UPLOAD_ERR_OK) {
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
                    return '<div class="error-message">' . $error_message . '</div>';
                }
                
                // Vérification du type de fichier
                $allowed_types = array('image/jpeg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
                if (!in_array($file['type'], $allowed_types)) {
                    return '<div class="error-message">Format de fichier non supporté. Formats acceptés : JPG, JPEG, PNG, PDF, HEIC, WEBP</div>';
                }
                
                // Vérification de la taille
                $max_size = 10 * 1024 * 1024; // 10 Mo
                if ($file['size'] > $max_size) {
                    return '<div class="error-message">Le fichier est trop volumineux (10 Mo maximum)</div>';
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
                $filename = time() . '_' . $filename;
                $temp_file = $temp_dir . '/' . $filename;
                
                // Déplacement du fichier
                if (move_uploaded_file($file['tmp_name'], $temp_file)) {
                    $attachments[] = $temp_file;
                    $content .= "\r\nPièce jointe : " . $file['name'] . "\r\n";
                } else {
                    return '<div class="error-message">Erreur lors du téléchargement du fichier. Veuillez réessayer.</div>';
                }
            }

            if (empty($_FILES['billing_plaque']['name'])) {
                $content .= "\r\n\r\nAucune plaque signalétique n'a été jointe à ce message.";
            }
            
            // Envoi de l'email
            $sent = wp_mail($to, $subject, $content, $headers, $attachments);
            $debug_messages[] = 'Résultat de l\'envoi d\'email : ' . ($sent ? 'SUCCÈS' : 'ÉCHEC');
            
            // Nettoyage des fichiers temporaires
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
            }
            
            if ($sent) {
                $debug_messages[] = 'Email envoyé avec succès';
                return $fileWarning . '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            } else {
                $debug_messages[] = 'Échec de l\'envoi de l\'email';
                return '<div class="error-message">Une erreur est survenue lors de l\'envoi de votre message. Veuillez nous contacter par téléphone.</div>';
            }
        }
        
        // Affichage des messages de debug pour tous les utilisateurs
        if (!empty($debug_messages)) {
            echo '<div style="background:#222;color:#fff;padding:15px;margin:20px 0;white-space:pre-wrap;font-size:13px;border-radius:8px;">';
            echo '<strong>DEBUG FORMULAIRE :</strong><br>';
            foreach ($debug_messages as $msg) {
                echo htmlspecialchars($msg) . "\n";
            }
            echo '</div>';
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

// Gestionnaire d'erreurs PHP pour afficher dans la console JS
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<script>console.error('PHP ERROR: " . addslashes($errstr) . " in " . addslashes($errfile) . " line " . $errline . "');</script>";
});

// Affichage du résultat
$result = cenovContactForm();
?>

<div class="cenov-form-container">
    <?php if ($result) : ?>
        <div class="cenov-message-result"><?php echo $result; ?></div>
    <?php endif; ?>

    <h3>Validation de votre demande de devis 📋 : </h3>

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
                    <input type="text" id="cenov-prenom" name="billing_first_name" data-woocommerce-checkout="billing_first_name" placeholder="Prénom" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-nom">* Nom :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg>
                    </span>
                    <input type="text" id="cenov-nom" name="billing_last_name" data-woocommerce-checkout="billing_last_name" placeholder="Nom" required />
                </div>
            </div>

            <!-- Deuxième ligne: Téléphone et Email côte à côte -->
            <div class="form-row">
                <label for="cenov-telephone">* Téléphone :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <input type="tel" id="cenov-telephone" name="billing_phone" data-woocommerce-checkout="billing_phone" placeholder="Votre numéro de téléphone" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-email">* Email :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <input type="email" id="cenov-email" name="billing_email" data-woocommerce-checkout="billing_email" placeholder="Votre adresse e-mail" required />
                </div>
            </div>

            <!-- Troisième ligne: Société (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-societe">* Nom de ma société :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-factory"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M17 18h1"/><path d="M12 18h1"/><path d="M7 18h1"/></svg>
                    </span>
                    <input type="text" id="cenov-societe" name="billing_company" data-woocommerce-checkout="billing_company" placeholder="Nom de ma société" required />
                </div>
            </div>

            <!-- Quatrième ligne: Adresse et Pays côte à côte -->
            <div class="form-row">
                <label for="cenov-adresse">* Adresse :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                    </span>
                    <input type="text" id="cenov-adresse" name="billing_address_1" data-woocommerce-checkout="billing_address_1" placeholder="Adresse" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-pays">* Pays :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </span>
                    <select id="cenov-pays" name="billing_country" data-woocommerce-checkout="billing_country" required>
                        <?php
                        $countries = WC()->countries->get_countries();
                        foreach ($countries as $code => $name) {
                            $selected = ($code === 'FR') ? 'selected' : '';
                            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Cinquième ligne: Code Postal et Ville côte à côte -->
            <div class="form-row">
                <label for="cenov-codepostal">* Code Postal :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <input type="text" id="cenov-codepostal" name="billing_postcode" data-woocommerce-checkout="billing_postcode" placeholder="Code Postal" required  />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-ville">* Ville :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                    </span>
                    <input type="text" id="cenov-ville" name="billing_city" data-woocommerce-checkout="billing_city" placeholder="Ville" required  />
                </div>
            </div>

            <!-- Référence client -->
            <div class="form-row full-width">
                <label for="cenov-reference">Référence client :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hash"><line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/></svg>
                    </span>
                    <input type="text" id="cenov-reference" name="billing_reference" data-woocommerce-checkout="billing_reference" placeholder="Votre référence client" />
                </div>
            </div>

            <!-- Message (occupe toute la largeur) -->
            <div class="form-row full-width">
                <label for="cenov-message">Votre message :</label>
                <div class="input-icon-wrapper textarea-wrapper">
                    <span class="input-icon textarea-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <textarea id="cenov-message" name="billing_message" data-woocommerce-checkout="billing_message" rows="4" placeholder="Votre message"></textarea>
                </div>
            </div>

            <!-- Upload de plaque signalétique (occupe toute la largeur) -->
            <div class="form-row full-width file-upload">
                <label for="cenov-plaque">Votre plaque signalétique 📋 :</label>
                <div class="file-input-container">
                    <input type="file" id="cenov-plaque" name="billing_plaque" data-woocommerce-checkout="billing_plaque" accept=".jpg, .jpeg, .png, .pdf, .heic, .webp"/>
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

            <!-- Case à cocher matériel équivalent -->
            <div class="form-row full-width">
                <div class="cenov-gdpr-consent">
                    <input type="checkbox" id="cenov-materiel-equivalent" name="billing_materiel_equivalent" data-woocommerce-checkout="billing_materiel_equivalent" />
                    <label for="cenov-materiel-equivalent">Proposez-moi un matériel équivalent</label>
                </div>
            </div>

            <!-- RGPD (occupe toute la largeur) -->
            <div class="form-row full-width">
                <div class="cenov-gdpr-consent">
                    <input type="checkbox" id="cenov-gdpr" name="billing_gdpr" data-woocommerce-checkout="billing_gdpr" required />
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

<style>
    /* Cacher les champs WooCommerce par défaut */
    .woocommerce-checkout .woocommerce-billing-fields,
    .woocommerce-checkout .woocommerce-shipping-fields,
    .woocommerce-checkout .woocommerce-additional-fields {
        display: none !important;
    }

    /* Afficher uniquement le récapitulatif de commande */
    .woocommerce-checkout .woocommerce-checkout-review-order {
        display: block !important;
    }

    /* Style pour le select de pays */
    .input-icon-wrapper select {
        width: 100% !important;
        padding: 12px 12px 12px 40px !important;
        border: 1px solid #ddd !important;
        border-radius: 6px !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
        background-color: white !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 12px center !important;
        background-size: 16px !important;
        padding-right: 40px !important;
    }

    .input-icon-wrapper select:focus {
        border: 2px solid #2563eb !important;
        outline: none !important;
    }

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
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
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
    margin-bottom: 8px;
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
    border: 1px solid #ddd !important;
    border-radius: 6px;
    font-size: 15px;
    line-height: 1.4;
  }
  
  .input-icon-wrapper textarea {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid #ddd !important;
    border-radius: 6px;
    font-size: 15px;
    line-height: 1.4;
  }

  .input-icon-wrapper input:focus,
  .input-icon-wrapper textarea:focus {
    border: 2px solid #2563eb !important;
    outline: none;
  }
  
  /* Styles pour le sélecteur de fichier */
  .file-input-container {
    position: relative;
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 35px 20px;
    text-align: center;
    background-color: white;
    transition: border-color 0.3s, transform 0.2s;
    margin-bottom: 10px;
  }

  .file-input-container:hover {
    border-color: #2563eb;
    transform: translateY(-2px);
  }

  .file-input-container input[type="file"] {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
  }

  .file-upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #6b7280;
  }

  .file-upload-placeholder svg {
    margin-bottom: 12px;
    color: #2563eb;
    width: 28px;
    height: 28px;
  }

  .file-formats {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 12px;
    gap: 10px;
  }

  .format-item {
    background-color: white;
    padding: 5px 12px;
    border-radius: 16px;
    font-size: 12px;
    color: #4b5563;
    display: flex;
    align-items: center;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  }

  .format-icon {
    margin-right: 4px;
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

  .form-submit {
    margin-top: 10px;
  }

  .form-submit button {
    width: auto;
    max-width: 300px;
    margin: 0 auto;
    display: block;
    color: white;
    background-color: #2563eb;
    border: none;
    font-weight: 600;
    border-radius: 6px;
    font-size: 1rem;
    padding: 12px 24px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.2);
  }

  .form-submit button:hover {
    background-color: #1e40af;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
  }
  
  .form-submit button:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
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

  .file-preview-container {
        margin-top: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow: hidden;
        background-color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }

    .preview-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #4b5563;
    }

    .remove-file-btn {
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 5px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .remove-file-btn:hover {
        background-color: #f3f4f6;
        color: #ef4444;
    }

    .preview-content {
        padding: 15px;
        display: flex;
        justify-content: center;
        align-items: center;
        max-height: 300px;
        overflow: auto;
    }

    #image-preview {
        max-width: 100%;
        max-height: 270px;
        object-fit: contain;
    }

    #pdf-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
    }

    #pdf-name {
        margin-top: 10px;
        font-size: 0.9rem;
        color: #4b5563;
        word-break: break-all;
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
document.addEventListener('DOMContentLoaded', function() {
    // Références aux éléments DOM
    const fileInput = document.getElementById('cenov-plaque');
    const fileNameDisplay = document.getElementById('file-name-display');
    const filePreview = document.getElementById('file-preview');
    const imagePreview = document.getElementById('image-preview');
    const pdfPreview = document.getElementById('pdf-preview');
    const pdfName = document.getElementById('pdf-name');
    const removeFileButton = document.getElementById('remove-file');
    
    // Fonction pour afficher la prévisualisation du fichier
    function handleFileSelect(event) {
        if (fileInput.files.length > 0) {
            const selectedFile = fileInput.files[0];
            const fileSize = (selectedFile.size / 1024 / 1024).toFixed(2); // Taille en MB
            
            // Mettre à jour le nom du fichier affiché
            fileNameDisplay.textContent = selectedFile.name + ' (' + fileSize + ' MB)';
            
            // Vérifier le type de fichier
            const fileType = selectedFile.type;
            
            // Réinitialiser les prévisualisations
            imagePreview.style.display = 'none';
            pdfPreview.style.display = 'none';
            
            if (fileType.startsWith('image/')) {
                // Pour les images
                const fileURL = URL.createObjectURL(selectedFile);
                imagePreview.src = fileURL;
                imagePreview.style.display = 'block';
                filePreview.style.display = 'block';
            } else if (fileType === 'application/pdf') {
                // Pour les PDF
                pdfName.textContent = selectedFile.name;
                pdfPreview.style.display = 'flex';
                filePreview.style.display = 'block';
            }
        } else {
            // Réinitialiser si aucun fichier n'est sélectionné
            fileNameDisplay.textContent = 'Choisir un fichier ou glisser-déposer';
            filePreview.style.display = 'none';
        }
    }
    
    // Fonction pour supprimer le fichier sélectionné
    function removeFile() {
        fileInput.value = ''; // Vider l'input de fichier
        fileNameDisplay.textContent = 'Choisir un fichier ou glisser-déposer';
        filePreview.style.display = 'none';
        
        // Révoquer les URLs d'objets pour libérer la mémoire
        if (imagePreview.src && imagePreview.src.startsWith('blob:')) {
            URL.revokeObjectURL(imagePreview.src);
        }
    }
    
    // Attacher les écouteurs d'événements
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
    
    if (removeFileButton) {
        removeFileButton.addEventListener('click', removeFile);
    }
    
    // Vider le champ honeypot
    const honeypotField = document.getElementById('cenov_website');
    if (honeypotField) {
        honeypotField.value = '';
    }
    
    // Configuration reCAPTCHA
    grecaptcha.ready(function() {
        // Ajouter un écouteur d'événement pour le formulaire
        const form = document.querySelector('.cenov-form-container form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Empêcher la soumission immédiate
                e.preventDefault();
                
                // Exécuter reCAPTCHA et obtenir un token
                grecaptcha.execute('6LcXl_sqAAAAAP5cz7w1iul0Bu18KnGqQ6u2DZ7W', {action: 'formulaire_contact'}).then(function(token) {
                    // Mettre le token dans le champ caché
                    document.getElementById('g-recaptcha-response').value = token;
                    
                    // Soumettre le formulaire
                    form.submit();
                }).catch(function(error) {
                    console.error('Erreur reCAPTCHA:', error);
                    // En cas d'erreur, permettre quand même la soumission
                    form.submit();
                });
            });
        }
    });
});
</script>