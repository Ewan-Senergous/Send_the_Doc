<?php
if (!function_exists('cenovContactForm')) {
    function cenovContactForm() {
        $result = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_first_name']) && isset($_POST['g-recaptcha-response'])) {
            // Effectuer les vérifications de sécurité
            $securityCheck = cenovPerformSecurityChecks();
            if ($securityCheck !== true) {
                $result = $securityCheck;
            } else {
                // Initialiser les messages de débogage
                $debug_messages = initDebugMessages();
                
                // Récupérer et formater les données du formulaire
                $content = prepareEmailContent();
                
                // Traiter les fichiers uploadés
                $uploadResult = processUploadedFiles($debug_messages);
                $attachments = $uploadResult['attachments'];
                $fileNames = $uploadResult['fileNames'];
                $fileWarning = $uploadResult['fileWarning'];
                
                // Mettre à jour le contenu de l'email avec la liste des pièces jointes
                $content = updateContentWithAttachments($content, $fileNames);
                
                $debug_messages[] = 'Contenu de l\'email préparé : ' . $content;
                if (!empty($fileNames)) {
                    $debug_messages[] = 'Fichiers joints détectés : ' . implode(', ', $fileNames);
                }
                
                // Préparer et envoyer l'email
                $emailResult = sendEmail($content, $attachments, $debug_messages);
                
                // Nettoyer les fichiers temporaires
                cleanupAttachments($attachments);
                
                // Afficher les messages de débogage
                displayDebugMessages($debug_messages);
                
                if ($emailResult === true) {
                    $result = $fileWarning . '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
                } else {
                    $result = '<div class="error-message">Une erreur est survenue lors de l\'envoi de votre message. Veuillez nous contacter par téléphone.</div>';
                }
            }
        }
        
        return $result;
    }
    
    function cenovPerformSecurityChecks() {
        $result = true;
        
        // Protection contre les attaques de force brute
        if (!cenovCheckSubmissionRate()) {
            $result = '<div class="error-message">Trop de tentatives. Veuillez réessayer dans une heure.</div>';
        }
        // Vérification du nonce CSRF
        elseif (!isset($_POST['cenov_nonce']) || !wp_verify_nonce($_POST['cenov_nonce'], 'cenov_contact_action')) {
            $result = '<div class="error-message">Erreur de sécurité. Veuillez rafraîchir la page et réessayer.</div>';
        }
        // Vérification honeypot - si rempli, c'est probablement un bot
        elseif (!empty($_POST['cenov_website'])) {
            // Bot détecté, mais on simule un succès pour ne pas alerter le bot
            $result = '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
        }
        else {
            // Vérification du temps de soumission
            $submissionTime = isset($_POST['cenov_timestamp']) ? (int)$_POST['cenov_timestamp'] : 0;
            $currentTime = time();
            $timeDifference = $currentTime - $submissionTime;
            
            // Si le formulaire est soumis en moins de 3 secondes, c'est probablement un bot
            if ($timeDifference < 3) {
                // Simulation d'un succès pour ne pas alerter le bot
                $result = '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
            }
            else {
                // Vérification reCAPTCHA
                $recaptchaCheck = verifyRecaptcha();
                if ($recaptchaCheck !== true) {
                    $result = $recaptchaCheck;
                }
            }
        }
        
        return $result;
    }
    
    function verifyRecaptcha() {
        $result = true;
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        if (empty($recaptcha_response)) {
            $result = '<div class="error-message">Échec de la vérification de sécurité. Veuillez réessayer.</div>';
        }
        else {
            $recaptcha_secret = get_option('cenov_recaptcha_secret', '');
            $verify_response = wp_remote_get(
                "https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"
            );
            
            if (is_wp_error($verify_response)) {
                $result = '<div class="error-message">Erreur de vérification. Veuillez réessayer plus tard.</div>';
            }
            else {
                $response_body = json_decode(wp_remote_retrieve_body($verify_response));
                
                // Vérifier que le score est acceptable (0.0 = bot, 1.0 = humain)
                if (!isset($response_body->success) || !$response_body->success ||
                    (isset($response_body->score) && $response_body->score < 0.5)) {
                    $result = '<div class="error-message">La vérification de sécurité a échoué. Veuillez réessayer.</div>';
                }
            }
        }
        
        return $result;
    }
    
    function initDebugMessages() {
        $debug_messages = [];
        $debug_messages[] = '=== DÉBUT DU TRAITEMENT DU FORMULAIRE ===';
        $debug_messages[] = 'Méthode de requête : ' . $_SERVER['REQUEST_METHOD'];
        $debug_messages[] = 'Données POST reçues : ' . print_r($_POST, true);
        $debug_messages[] = 'Fichiers reçus : ' . print_r($_FILES, true);
        
        return $debug_messages;
    }
    
    function prepareEmailContent() {
        // Constante pour les champs non renseignés
        $not_provided = 'Non renseigné';
        
        $content = "--- INFORMATIONS PERSONNELLES ---\r\n";
        
        // Champs natifs WooCommerce
        $content .= "Prénom : " . (isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : $not_provided) . "\r\n";
        $content .= "Nom : " . (isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : $not_provided) . "\r\n";
        $content .= "Email : " . (isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : $not_provided) . "\r\n";
        $content .= "Téléphone : " . (isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : $not_provided) . "\r\n";
        
        // Champs personnalisés
        $content .= "Référence client : " . (isset($_POST['billing_reference']) ? sanitize_text_field($_POST['billing_reference']) : $not_provided) . "\r\n";
        $content .= "Message : " . (isset($_POST['billing_message']) ? sanitize_textarea_field($_POST['billing_message']) : $not_provided) . "\r\n";
        $content .= "Matériel équivalent : " . (isset($_POST['billing_materiel_equivalent']) ? 'Oui' : 'Non') . "\r\n";
        
        // Informations professionnelles
        $content .= "\r\n--- INFORMATIONS PROFESSIONNELLES ---\r\n";
        $content .= "Société : " . (isset($_POST['billing_company']) ? sanitize_text_field($_POST['billing_company']) : $not_provided) . "\r\n";
        $content .= "Adresse : " . (isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : $not_provided) . "\r\n";
        $content .= "Code postal : " . (isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : $not_provided) . "\r\n";
        $content .= "Ville : " . (isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : $not_provided) . "\r\n";
        $content .= "Pays : " . (isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : $not_provided) . "\r\n";
        
        // Ajout des produits du panier WooCommerce
        $content .= addCartProductsToContent();
        
        return $content;
    }
    
    function addCartProductsToContent() {
        $content = "\r\n--- PRODUITS DU PANIER ---\r\n";
        
        if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $content .= "Produit : " . $product->get_name() . "\r\n\r\n";
                $content .= "Quantité : " . $quantity . "\r\n";
                $prix_unitaire = number_format((float)$product->get_price(), 2, ',', ' ') . ' €';
                $sous_total = number_format((float)$cart_item['line_total'], 2, ',', ' ') . ' €';
                $content .= "Prix : " . $prix_unitaire . "\r\n";
                $content .= "Sous-total : " . $sous_total . "\r\n\r\n";
            }
            $content .= "Sous-total panier : " . number_format((float)WC()->cart->get_subtotal(), 2, ',', ' ') . ' €' . "\r\n";
            $content .= "TVA : " . number_format((float)WC()->cart->get_total_tax(), 2, ',', ' ') . ' €' . "\r\n";
            $sous_total_value = (float)WC()->cart->get_subtotal();
            $tva_value = (float)WC()->cart->get_total_tax();
            $total_value = $sous_total_value + $tva_value;
            $content .= "Total : " . number_format($total_value, 2, ',', ' ') . ' €' . "\r\n";
        } else {
            $content .= "Aucun produit dans le panier\r\n";
        }
        
        return $content;
    }
    
    function processUploadedFiles(&$debug_messages) {
        $fileWarning = '';
        $attachments = array();
        $fileNames = array();
        
        if (empty($_FILES['cenov_plaque']['name'][0])) {
            $fileWarning = '<div class="warning-message">Attention : aucune plaque signalétique n\'a été jointe à votre message.</div>';
            return array(
                'fileWarning' => $fileWarning,
                'attachments' => $attachments,
                'fileNames' => $fileNames
            );
        }
        
        foreach($_FILES['cenov_plaque']['name'] as $key => $name) {
            if(empty($name)) {
                continue;
            }
            
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
                $error_message = getUploadErrorMessage($file);
                $debug_messages[] = 'Erreur upload: ' . $error_message;
                continue;
            }
            
            // Vérification du type de fichier
            $allowed_types = array('image/jpeg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
            if (!in_array($file['type'], $allowed_types)) {
                $debug_messages[] = 'Type de fichier non supporté: ' . $file['name'] . ' (' . $file['type'] . ')';
                continue;
            }
            
            // Vérification de la taille
            $max_size = 10 * 1024 * 1024; // 10 Mo
            if ($file['size'] > $max_size) {
                $debug_messages[] = 'Fichier trop volumineux: ' . $file['name'];
                continue;
            }
            
            // Traitement du fichier
            $file_result = processFile($file, $key, $debug_messages);
            if ($file_result['success']) {
                $attachments[] = $file_result['path'];
                $fileNames[] = $file['name'];
            }
        }
        
        return array(
            'fileWarning' => $fileWarning,
            'attachments' => $attachments,
            'fileNames' => $fileNames
        );
    }
    
    function getUploadErrorMessage($file) {
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
        return $error_message;
    }
    
    function processFile($file, $key, &$debug_messages) {
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
            $debug_messages[] = 'Fichier uploadé avec succès: ' . $file['name'];
            return array('success' => true, 'path' => $temp_file);
        } else {
            $debug_messages[] = 'Échec du déplacement du fichier: ' . $file['name'];
            return array('success' => false, 'path' => '');
        }
    }
    
    function updateContentWithAttachments($content, $fileNames) {
        if (empty($fileNames)) {
            $content .= "\r\n\r\nAucune plaque signalétique n'a été jointe à ce message.";
        }
        return $content;
    }
    
    function sendEmail($content, $attachments, &$debug_messages) {
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
        
        // Envoi de l'email
        $sent = wp_mail($to, $subject, $content, $headers, $attachments);
        $debug_messages[] = 'Résultat de l\'envoi d\'email : ' . ($sent ? 'SUCCÈS' : 'ÉCHEC');
        
        // Vider le panier après envoi
        if ($sent && class_exists('WC_Cart') && function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();
        }
        
        return $sent;
    }
    
    function cleanupAttachments($attachments) {
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
        }
    }
    
    function displayDebugMessages($debug_messages) {
        // Ne pas afficher les messages de débogage pour les utilisateurs normaux
        // Vérifier si l'utilisateur est administrateur ou si WP_DEBUG est activé
        if (empty($debug_messages) ||
            !(current_user_can('administrator') || (defined('WP_DEBUG') && WP_DEBUG))) {
            return;
        }
        
        echo '<div style="background:#222;color:#fff;padding:15px;margin:20px 0;white-space:pre-wrap;font-size:13px;border-radius:8px;">';
        echo '<strong>DEBUG FORMULAIRE :</strong><br>';
        foreach ($debug_messages as $msg) {
            echo htmlspecialchars($msg) . "\n";
        }
        echo '</div>';
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
    
    // Si le nombre maximal de tentatives est atteint (15 par défaut)
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
set_error_handler(function($_, $errstr, $errfile, $errline) {
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
                    <input type="file" id="cenov-plaque" name="cenov_plaque[]" multiple accept=".jpg, .jpeg, .png, .pdf, .heic, .webp" data-woocommerce-checkout="billing_plaque"/>
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
    cursor: pointer;
  }

  .file-input-container:hover {
    border-color: #2563eb;
    transform: translateY(-2px);
  }

  .file-input-container input[type="file"] {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 100;
    padding: 0;
    margin: 0;
  }

  .file-upload-placeholder {
    position: relative;
    z-index: 5;
    display: flex;
    flex-direction: column;
    align-items: center;
    color: #6b7280;
    pointer-events: none;
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
    background-color: #fff;
    padding: 5px 12px;
    border-radius: 16px;
    font-size: 12px;
    color: #4b5563;
    display: flex;
    align-items: center;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
  }

  .file-preview-container {
    margin-top: 15px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
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
    font-size: .9rem;
    color: #4b5563;
  }

  .remove-file-btn {
    background: 0 0;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: .2s;
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
    font-size: .9rem;
    color: #4b5563;
    word-break: break-all;
  }

  /* Styles pour plusieurs fichiers */
  .preview-content-multiple {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    padding: 15px;
    max-height: 300px;
    overflow-y: auto;
  }

  .preview-item {
    width: 150px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background-color: #f9fafb;
    overflow: hidden;
    position: relative;
    transition: .2s;
  }

  .preview-item:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
    transform: translateY(-2px);
  }

  .preview-item-header {
    padding: 8px;
    font-size: .75rem;
    color: #4b5563;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .preview-item-content {
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    padding: 5px;
  }

  .preview-item-actions {
    padding: 8px;
    border-top: 1px solid #e5e7eb;
    background-color: #f3f4f6;
  }

  .remove-single-file {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 4px 8px;
    border: none;
    background-color: transparent;
    color: #6b7280;
    font-size: .7rem;
    cursor: pointer;
    transition: .2s;
    border-radius: 4px;
  }

  .remove-single-file:hover {
    background-color: #fee2e2;
    color: #dc2626;
  }

  .thumbnail-preview {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
  }

  .file-name {
    font-weight: 600;
    display: block;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  .file-size {
    font-size: .7rem;
    color: #6b7280;
  }

  .pdf-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
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
