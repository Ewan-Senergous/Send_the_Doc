<?php
if (!function_exists('cenovContactForm')) {
    // Définir une constante pour les champs non renseignés
    define('CENOV_NOT_PROVIDED', 'Non renseigné');
    define('CENOV_RECAP_URL', '/recap-commande/');
    
    // Code pour gérer la suppression d'un article du panier
    if (isset($_GET['remove_item']) && !empty($_GET['remove_item'])) {
        $cart_item_key = sanitize_text_field($_GET['remove_item']);
        
        // Vérifier que WooCommerce est disponible
        if (function_exists('WC') && WC()->cart) {
            // Supprimer l'article du panier
            WC()->cart->remove_cart_item($cart_item_key);
            
            // Rediriger vers la même page sans le paramètre
            wp_redirect(remove_query_arg('remove_item'));
            exit;
        }
    }
    
    function cenovContactForm() {
        $result = '';
        
        // Démarrer la session si nécessaire
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['billing_first_name']) && isset($_POST['g-recaptcha-response'])) {
            // Effectuer les vérifications de sécurité
            $securityCheck = cenovPerformSecurityChecks();
            if ($securityCheck !== true) {
                $result = $securityCheck;
            } else {
                // Récupérer et formater les données du formulaire
                $content = prepareEmailContent();
                
                // Traiter les fichiers uploadés
                $uploadResult = processUploadedFiles();
                $attachments = $uploadResult['attachments'];
                $fileNames = $uploadResult['fileNames'];
                $fileWarning = $uploadResult['fileWarning'];
                
                // Mettre à jour le contenu de l'email avec la liste des pièces jointes
                $content = updateContentWithAttachments($content, $fileNames);
                
                // Préparer et envoyer l'email
                $emailResult = sendEmail($content, $attachments);
                
                // Ne pas nettoyer les fichiers temporaires immédiatement
                // pour permettre la prévisualisation sur la page de récapitulatif
                
                if ($emailResult === true) {
                    $result = $fileWarning . '<div class="success-message">Votre message a été envoyé avec succès. Nous vous contacterons rapidement.</div>';
                } else {
                    $result = '<div class="error-message">Une erreur est survenue lors de l\'envoi de votre message. Veuillez nous contacter par téléphone.</div>';
                    
                    // En cas d'erreur d'envoi, nettoyez les fichiers
                    cleanupAttachments($attachments);
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
    
    function prepareEmailContent() {
        // Constante pour les champs non renseignés
        $not_provided = CENOV_NOT_PROVIDED;
        
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
        
        // Obtenir le nom du pays
        $country_name = getCountryName($not_provided);
        $content .= "Pays : " . $country_name . "\r\n";
        
        // Ajout des produits du panier WooCommerce
        $content .= addCartProductsToContent();
        
        return $content;
    }
    
    function getCountryName($default_value) {
        $country_code = isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : '';
        $country_name = $default_value;
        
        if (!empty($country_code) && function_exists('WC')) {
            $countries = WC()->countries->get_countries();
            if (isset($countries[$country_code])) {
                $country_name = $countries[$country_code];
            }
        }
        
        return $country_name;
    }
    
    function addCartProductsToContent() {
        $content = "\r\n--- PRODUITS DEMANDÉS ---\r\n";
        
        if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $sku = $product->get_sku() ? $product->get_sku() : 'N/A';
                
                $content .= "Produit : " . $product->get_name() . " (" . $sku . ")\r\n";
                $content .= "SKU : #PRO" . $sku . "-SUP0000017\r\n";
                $content .= "Quantité : " . $quantity . "\r\n\r\n";
            }
        } else {
            $content .= "Aucun produit dans le panier\r\n";
        }
        
        return $content;
    }
    
    function processUploadedFiles() {
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
                continue;
            }
            
            // Vérification du type de fichier
            $allowed_types = array('image/jpeg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
            if (!in_array($file['type'], $allowed_types)) {
                continue;
            }
            
            // Vérification de la taille
            $max_size = 10 * 1024 * 1024; // 10 Mo
            if ($file['size'] > $max_size) {
                continue;
            }
            
            // Traitement du fichier
            $file_result = processFile($file, $key);
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
    
    function processFile($file, $key) {
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
            return array('success' => true, 'path' => $temp_file);
        } else {
            return array('success' => false, 'path' => '');
        }
    }
    
    function updateContentWithAttachments($content, $fileNames) {
        if (empty($fileNames)) {
            $content .= "\r\n\r\nAucune plaque signalétique n'a été jointe à ce message.";
        }
        return $content;
    }
    
    function sendEmail($content, $attachments) {
        // Préparation des données de base
        $emailData = prepareEmailData();
        
        // Génération du contenu HTML de l'email
        $html_content = generateEmailHtml($content, $emailData, $attachments);
        
        // Envoyer les emails
        $sent = sendEmails($html_content, $attachments, $emailData);
        
        // Si l'envoi a réussi, préparer et stocker les données
        if ($sent) {
            $fileData = storeOrderData($emailData, $attachments);
            
            // Fusionner les données de fichiers avec emailData pour la session
            $emailData['file_names'] = $fileData['file_names'];
            $emailData['file_paths'] = $fileData['file_paths'];
            
            setupSessionAndRedirect($emailData);
        }
        
        return $sent;
    }
    
    /**
     * Prépare les données de base pour l'email
     */
    function prepareEmailData() {
        // Récupérer l'email du client
        $client_email = isset($_POST['billing_email']) ? sanitize_email($_POST['billing_email']) : '';
        
        // Système de numérotation séquentiel
        $current_number = get_option('cenov_price_request_number', 987540000);  // Valeur initiale
        $commande_number = $current_number + 1;
        update_option('cenov_price_request_number', $commande_number);  // Sauvegarde pour la prochaine utilisation
        
        // Générer une clé unique pour cette commande
        $order_key = wp_generate_password(12, false);
        // Stocker cette clé dans les options WordPress avec une référence à l'ID de commande
        update_option('cenov_order_key_' . $commande_number, $order_key);
        // Définir une durée d'expiration pour cette clé (30 jours par défaut)
        update_option('cenov_order_key_expires_' . $commande_number, time() + (30 * DAY_IN_SECONDS));
        
        $date_commande = date_i18n('j F Y');
        
        // Prénom et nom du client pour l'affichage
        $client_firstname = isset($_POST['billing_first_name']) ? sanitize_text_field($_POST['billing_first_name']) : '';
        $client_lastname = isset($_POST['billing_last_name']) ? sanitize_text_field($_POST['billing_last_name']) : '';
        $client_name = $client_firstname . ' ' . $client_lastname;
        
        // Créer l'URL sécurisée pour la page de récapitulatif
        $recap_url = add_query_arg(
            array(
                'order' => $commande_number,
                'key' => $order_key
            ),
            home_url(CENOV_RECAP_URL)
        );
        
        // Préparation des produits pour la session
        $products_for_session = array();
        if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                $quantity = $cart_item['quantity'];
                $sku = $product->get_sku() ? $product->get_sku() : 'N/A';
                
                // Obtenir l'URL de l'image du produit
                $image_id = $product->get_image_id();
                $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src();
                
                // Ajouter au tableau pour la session
                $products_for_session[] = array(
                    'name' => $product->get_name(),
                    'sku' => $sku,
                    'quantity' => $quantity,
                    'image' => $image_url
                );
            }
        }
        
        return array(
            'client_email' => $client_email,
            'commande_number' => $commande_number,
            'order_key' => $order_key,
            'date_commande' => $date_commande,
            'client_name' => $client_name,
            'client_firstname' => $client_firstname,
            'client_lastname' => $client_lastname,
            'recap_url' => $recap_url,
            'products_for_session' => $products_for_session,
            'subject' => 'Demande de prix : n°' . $commande_number . ' (' . $date_commande . ')'
        );
    }
    
    /**
     * Génère le contenu HTML de l'email
     */
    function generateEmailHtml($content, $emailData, $attachments = []) {
        $html_header = generateEmailHeader($emailData);
        $html_personal_info = generatePersonalInfoSection($emailData);
        $html_order_details = generateOrderDetailsSection();
        $html_attachments_info = generateAttachmentsInfo($attachments);
        $html_footer = generateEmailFooter();
        
        // Ajouter une section pour le contenu brut si nécessaire
        $html_raw_content = '<div style="margin-bottom: 25px; display: none;">
            <pre>' . htmlspecialchars($content) . '</pre>
        </div>';
        
        return $html_header . $html_personal_info . $html_order_details . $html_attachments_info . $html_raw_content . $html_footer;
    }
    
    /**
     * Génère l'en-tête de l'email
     */
    function generateEmailHeader($emailData) {
        return '
        <div style="font-family: Helvetica, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: #2563eb; margin-bottom: 5px; font-size: 28px;">Demande de prix :</h1>
                <p style="margin-top: 0; margin-bottom: 5px;">Référence : ' . $emailData['commande_number'] . ' - ' . $emailData['date_commande'] . '</p>
                <p style="margin: 0;"><a href="' . esc_url($emailData['recap_url']) . '" style="color: #2563eb; text-decoration: underline;">[Commande n°' . $emailData['commande_number'] . ']</a></p>
                <p style="margin: 5px 0; font-size: 12px; color: #6b7280;">Ce lien est valable pendant 30 jours.</p>
            </div>';
    }
    
    /**
     * Génère la section d'informations personnelles
     */
    function generatePersonalInfoSection($emailData) {
        $html = '
            <div style="margin-bottom: 25px;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Informations personnelles :</h3>
                <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">
                    <p style="margin: 5px 0;"><strong>Nom :</strong> ' . $emailData['client_name'] . '</p>
                    <p style="margin: 5px 0;"><strong>Email :</strong> ' . $emailData['client_email'] . '</p>
                    <p style="margin: 5px 0;"><strong>Téléphone :</strong> ' . (isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Société :</strong> ' . (isset($_POST['billing_company']) ? sanitize_text_field($_POST['billing_company']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Adresse :</strong> ' . (isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Code postal :</strong> ' . (isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Ville :</strong> ' . (isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Pays :</strong> ' . (isset($_POST['billing_country']) ? WC()->countries->get_countries()[$_POST['billing_country']] : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Référence client :</strong> ' . (isset($_POST['billing_reference']) && !empty($_POST['billing_reference']) ? sanitize_text_field($_POST['billing_reference']) : CENOV_NOT_PROVIDED) . '</p>
                    <p style="margin: 5px 0;"><strong>Matériel équivalent :</strong> ' . (isset($_POST['billing_materiel_equivalent']) ? 'Oui' : 'Non') . '</p>';
        
        // Ajouter le message du client s'il existe
        if (isset($_POST['billing_message']) && !empty($_POST['billing_message'])) {
            $html .= '
                    <p style="margin: 5px 0;"><strong>Message :</strong> ' . nl2br(esc_html(sanitize_textarea_field($_POST['billing_message']))) . '</p>';
        }
        
        $html .= '
                </div>
            </div>';
            
        return $html;
    }
    
    /**
     * Génère la section des détails de la commande
     */
    function generateOrderDetailsSection() {
        $html = '
            <div style="margin-bottom: 25px;">
                <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Détail de la commande :</h3>
                <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">';
        
        if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
            $html .= generateProductList();
        } else {
            $html .= '<p>Aucun produit demandé</p>';
        }
        
        $html .= '
                </div>
            </div>';
            
        return $html;
    }
    
    /**
     * Génère la liste des produits de la commande
     */
    function generateProductList() {
        $html = '';
        
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            $quantity = $cart_item['quantity'];
            $sku = $product->get_sku() ? $product->get_sku() : 'N/A';
            
            // Obtenir l'URL de l'image du produit
            $image_id = $product->get_image_id();
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'thumbnail') : wc_placeholder_img_src();
            
            $html .= '
            <div style="background-color: #fff; padding: 10px; margin-bottom: 10px; border-radius: 4px; display: flex; align-items: center;">
                <div style="width: 60px; min-width: 60px; height: 60px; margin-right: 15px; background-color: #fff; border-radius: 4px; overflow: hidden;">
                    <img src="' . $image_url . '" alt="' . esc_attr($product->get_name()) . '" style="width: 100%; height: 100%; object-fit: contain;" />
                </div>
                <div>
                    <p style="margin: 5px 0;"><strong>Produit :</strong> ' . esc_html($product->get_name()) . '</p>
                    <p style="margin: 5px 0;"><strong>SKU :</strong> ' . $sku . '</p>
                    <p style="margin: 5px 0;"><strong>Quantité :</strong> ' . $quantity . '</p>
                </div>
            </div>';
        }
        
        return $html;
    }
    
    /**
     * Génère les informations sur les pièces jointes
     */
    function generateAttachmentsInfo($attachments) {
        if (empty($attachments)) {
            return '
            <div style="margin-bottom: 25px;">
                <p>Aucune plaque signalétique n\'a été jointe à cette demande.</p>
            </div>';
        }
        
        // Afficher la liste des pièces jointes
        $html = '
        <div style="margin-bottom: 25px;">
            <h3 style="color: #0f172a; margin-top: 0; margin-bottom: 10px;">Pièces jointes :</h3>
            <div style="background-color: #fff; padding: 15px; border-radius: 6px; border-left: 3px solid #2563eb;">';
        
        foreach ($attachments as $file) {
            $filename = basename($file);
            $html .= '<p style="margin: 5px 0;"><strong>Fichier :</strong> ' . $filename . '</p>';
        }
        
        $html .= '
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Génère le pied de page de l'email
     */
    function generateEmailFooter() {
        return '
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">
                <p>© Cenov Distribution - Tous droits réservés</p>
            </div>
        </div>';
    }
    
    /**
     * Envoie les emails à l'entreprise et au client
     */
    function sendEmails($html_content, $attachments, $emailData) {
        // Adresse email principal de l'entreprise
        $to = 'ventes@cenov-distribution.fr';
        
        $headers = [
            'From: Cenov Distribution <ventes@cenov-distribution.fr>',
            'Reply-To: Cenov Distribution <ventes@cenov-distribution.fr>',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        // Envoi de l'email au service commercial - TOUJOURS à ventes@cenov-distribution.fr
        $sent_to_company = wp_mail($to, $emailData['subject'], $html_content, $headers, $attachments);
        
        // Envoi d'une copie au client s'il a fourni une adresse email
        $sent_to_client = false;
        if (!empty($emailData['client_email'])) {
            $client_headers = [
                'From: Cenov Distribution <ventes@cenov-distribution.fr>',
                'Reply-To: Cenov Distribution <ventes@cenov-distribution.fr>',
                'Content-Type: text/html; charset=UTF-8'
            ];
            
            // Petit ajustement du message pour le client
            $client_html_content = str_replace('Confirmation de votre demande de prix', 'Confirmation : ' . $emailData['subject'], $html_content);
            
            // Ajouter le message de remerciement uniquement pour le client
            $client_html_content = str_replace(
                '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">
                <p>© Cenov Distribution - Tous droits réservés</p>',
                '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">
                <p>Merci pour votre demande de prix. Nous vous contacterons dans les plus brefs délais.</p>
                <p>© Cenov Distribution - Tous droits réservés</p>',
                $client_html_content
            );
            
            $sent_to_client = wp_mail($emailData['client_email'], 'Confirmation : ' . $emailData['subject'], $client_html_content, $client_headers, $attachments);
        }
        
        // Considérer l'envoi réussi si l'email à l'entreprise a été envoyé, peu importe celui au client
        return $sent_to_company && (!empty($emailData['client_email']) ? $sent_to_client : true);
    }
    
    /**
     * Stocke les données de la commande en base de données
     */
    function storeOrderData($emailData, $attachments) {
        // Préparer les noms de fichiers pour la session
        $file_names = array();
        $file_paths = array();
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $file_name = basename($file);
                $file_names[] = $file_name;
                $file_paths[$file_name] = $file; // Chemin complet du fichier temporaire
            }
        }
        
        // Stocker toutes les données client dans la base de données pour une récupération ultérieure
        update_option('cenov_order_date_' . $emailData['commande_number'], time());
        update_option('cenov_order_client_' . $emailData['commande_number'], $emailData['client_name']);
        update_option('cenov_order_email_' . $emailData['commande_number'], $emailData['client_email']);
        update_option('cenov_order_phone_' . $emailData['commande_number'], isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : CENOV_NOT_PROVIDED);
        update_option('cenov_order_company_' . $emailData['commande_number'], isset($_POST['billing_company']) ? sanitize_text_field($_POST['billing_company']) : CENOV_NOT_PROVIDED);
        update_option('cenov_order_address_' . $emailData['commande_number'], isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : CENOV_NOT_PROVIDED);
        update_option('cenov_order_postcode_' . $emailData['commande_number'], isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : CENOV_NOT_PROVIDED);
        update_option('cenov_order_city_' . $emailData['commande_number'], isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : CENOV_NOT_PROVIDED);
        update_option('cenov_order_country_' . $emailData['commande_number'], isset($_POST['billing_country']) ? WC()->countries->get_countries()[$_POST['billing_country']] : CENOV_NOT_PROVIDED);
        update_option('cenov_order_reference_' . $emailData['commande_number'], isset($_POST['billing_reference']) ? sanitize_text_field($_POST['billing_reference']) : '');
        update_option('cenov_order_materiel_equivalent_' . $emailData['commande_number'], isset($_POST['billing_materiel_equivalent']));
        update_option('cenov_order_message_' . $emailData['commande_number'], isset($_POST['billing_message']) ? sanitize_textarea_field($_POST['billing_message']) : '');
        update_option('cenov_order_products_' . $emailData['commande_number'], $emailData['products_for_session']);
        update_option('cenov_order_file_names_' . $emailData['commande_number'], $file_names);
        
        // Sauvegarder également les noms de fichiers et les chemins pour la session
        return array(
            'file_names' => $file_names,
            'file_paths' => $file_paths
        );
    }
    
    /**
     * Configure la session et redirige vers la page de récapitulatif
     */
    function setupSessionAndRedirect($emailData) {
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Récupérer les informations sur les fichiers
        $file_names = isset($emailData['file_names']) ? $emailData['file_names'] : array();
        $file_paths = isset($emailData['file_paths']) ? $emailData['file_paths'] : array();
        
        // Préparer les données pour la session
        $_SESSION['commande_data'] = array(
            'commande_number' => $emailData['commande_number'],
            'date_commande' => $emailData['date_commande'],
            'client_name' => $emailData['client_name'],
            'client_email' => $emailData['client_email'],
            'client_phone' => isset($_POST['billing_phone']) ? sanitize_text_field($_POST['billing_phone']) : '',
            'client_company' => isset($_POST['billing_company']) ? sanitize_text_field($_POST['billing_company']) : '',
            'client_address' => isset($_POST['billing_address_1']) ? sanitize_text_field($_POST['billing_address_1']) : '',
            'client_postcode' => isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : '',
            'client_city' => isset($_POST['billing_city']) ? sanitize_text_field($_POST['billing_city']) : '',
            'client_country' => isset($_POST['billing_country']) ? WC()->countries->get_countries()[$_POST['billing_country']] : '',
            'client_reference' => isset($_POST['billing_reference']) ? sanitize_text_field($_POST['billing_reference']) : '',
            'client_materiel_equivalent' => isset($_POST['billing_materiel_equivalent']),
            'client_message' => isset($_POST['billing_message']) ? sanitize_textarea_field($_POST['billing_message']) : '',
            'products' => $emailData['products_for_session'],
            'file_names' => $file_names,
            'file_paths' => $file_paths, // Chemins des fichiers temporaires
            'order_key' => $emailData['order_key'] // Ajouter la clé à la session
        );
        
        // Vider le panier après envoi
        if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();
        }
        
        // URL sécurisée pour la page de récapitulatif
        $recap_url = add_query_arg(
            array(
                'order' => $emailData['commande_number'],
                'key' => $emailData['order_key']
            ),
            home_url(CENOV_RECAP_URL)
        );
        
        // Rediriger vers la page de récapitulatif avec les paramètres d'URL
        wp_redirect($recap_url);
        exit;
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
                <label for="cenov-email">* Email :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </span>
                    <input type="email" id="cenov-email" name="billing_email" data-woocommerce-checkout="billing_email" placeholder="Votre adresse e-mail" required />
                </div>
            </div>

            <div class="form-row">
                <label for="cenov-telephone">* Téléphone :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <input type="tel" id="cenov-telephone" name="billing_phone" data-woocommerce-checkout="billing_phone" placeholder="Votre numéro de téléphone" required />
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
                <label for="cenov-codepostal">* Code Postal :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <input type="text" id="cenov-codepostal" name="billing_postcode" data-woocommerce-checkout="billing_postcode" placeholder="Code Postal" required />
                </div>
            </div>

            <!-- Cinquième ligne: Ville et Pays côte à côte -->
            <div class="form-row">
                <label for="cenov-ville">* Ville :</label>
                <div class="input-icon-wrapper">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                    </span>
                    <input type="text" id="cenov-ville" name="billing_city" data-woocommerce-checkout="billing_city" placeholder="Ville" required />
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

            <!-- Récapitulatif de la commande -->
            <div class="form-row full-width order-summary">
                <h4 class="order-summary-title">Récapitulatif de ma demande :</h4>
                <div class="order-summary-content">
                    <?php
                    if (class_exists('WC_Cart') && function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $product = $cart_item['data'];
                            $quantity = $cart_item['quantity'];
                            ?>
                            <div class="product-summary-item">
                                <div class="product-image">
                                    <?php
                                    $image_id = $product->get_image_id();
                                    if ($image_id) {
                                        echo wp_get_attachment_image($image_id, 'thumbnail');
                                    } else {
                                        echo '<img src="' . wc_placeholder_img_src() . '" alt="Placeholder" />';
                                    }
                                    ?>
                                </div>
                                <div class="product-details">
                                    <h5 class="product-title"><?php echo esc_html($product->get_name()); ?></h5>
                                    <div class="product-meta">
                                        <div class="product-quantity">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-icon lucide-package"><path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><polyline points="3.29 7 12 12 20.71 7"/><path d="m7.5 4.27 9 5.15"/></svg>
                                            <span>Quantité : <?php echo esc_html($quantity); ?></span>
                                        </div>
                                        <div class="product-remove">
                                            <a href="?remove_item=<?php echo esc_attr($cart_item_key); ?>" class="remove-product-btn">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                                </svg>
                                                <span>Supprimer</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="empty-cart-message">Aucun produit dans le panier</div>';
                    }
                    ?>
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
                <button type="submit" name="cenov_submit" value="1"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-horizontal-icon lucide-send-horizontal"><path d="M3.714 3.048a.498.498 0 0 0-.683.627l2.843 7.627a2 2 0 0 1 0 1.396l-2.842 7.627a.498.498 0 0 0 .682.627l18-8.5a.5.5 0 0 0 0-.904z"/><path d="M6 12h16"/></svg> Envoyer</button>
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

    /* Correction des marges et paddings WooCommerce */
    .woocommerce form .form-row label {
        line-height: 1 !important;
    }

    .woocommerce form .form-row {
        padding: 0 !important;
    }

    /* Style pour le select de pays */
    .input-icon-wrapper select {
        width: 100% !important;
        padding: 12px 12px 12px 40px !important;
        border: 1px solid #6b7280 !important;
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

    .warning-message {
        background-color: #fff7ed !important;
        color: #9a3412 !important;
        padding: 12px !important;
        border-radius: 6px !important;
        margin-bottom: 20px !important;
        font-weight: 500 !important;
        border-left: 4px solid #f97316 !important;
    }
    
    .honeypot-field {
        opacity: 0 !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        height: 0 !important;
        width: 0 !important;
        z-index: -1 !important;
        overflow: hidden !important;
    }

    .cenov-form-container {
        max-width: 1000px !important;
        margin: 20px auto !important;
        padding: 30px !important;
        background: #f3f4f6 !important;
        border-radius: 8px !important;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1) !important;
        border: 2px solid #2563eb !important;
    }

    .cenov-form-container h3 {
        margin-top: 0 !important;
        margin-bottom: 5px !important;
        color: #333 !important;
        font-size: 1.4rem !important;
    }
    
    /* Grille pour la disposition en deux colonnes */
    .form-grid {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 20px !important;
    }
    
    /* Les éléments qui doivent prendre toute la largeur */
    .full-width {
        grid-column: 1 / -1 !important;
    }

    .form-row {
        margin-bottom: 0 !important;
    }

    .form-row label {
        display: block !important;
        margin-bottom: 8px !important;
        font-weight: 600 !important;
        font-size: 0.95rem !important;
        color: #444 !important;
    }
    
    /* Supprimer la marge inférieure pour les labels des cases à cocher */
    .cenov-gdpr-consent label {
        margin-bottom: 0 !important;
    }
    
    /* Style pour les inputs avec icônes */
    .input-icon-wrapper {
        position: relative !important;
        display: flex !important;
        align-items: center !important;
    }
    
    .input-icon {
        position: absolute !important;
        left: 12px !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
        color: #666 !important;
        z-index: 1 !important;
        line-height: 1 !important;
    }
    
    .textarea-wrapper .input-icon {
        align-items: flex-start !important;
        padding-top: 12px !important;
    }
    
    .input-icon-wrapper input[type="text"],
    .input-icon-wrapper input[type="email"],
    .input-icon-wrapper input[type="tel"] {
        width: 100% !important;
        padding: 12px 12px 12px 40px !important;
        border: 1px solid #6b7280 !important;
        border-radius: 6px !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
    }
    
    .input-icon-wrapper textarea {
        width: 100% !important;
        padding: 12px 12px 12px 40px !important;
        border: 1px solid #6b7280 !important;
        border-radius: 6px !important;
        font-size: 15px !important;
        line-height: 1.4 !important;
        background-color: #FFF !important;
}
    

    .input-icon-wrapper input:focus,
    .input-icon-wrapper select:focus,
    .input-icon-wrapper textarea:focus {
        border: 2px solid #2563eb !important;
        outline: none !important;
    }
    
    /* Forcer les champs avec autofill à garder un fond blanc */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus,
    input:-webkit-autofill:active {
        -webkit-box-shadow: 0 0 0 30px white inset !important;
        -webkit-text-fill-color: inherit !important;
        transition: background-color 5000s ease-in-out 0s !important;
    }
    
    /* Sélecteurs pour d'autres navigateurs */
    input:autofill {
        background-color: white !important;
    }
    
    input:-internal-autofill-selected {
        background-color: white !important;
        appearance: none !important;
    }
    
    /* Styles pour le sélecteur de fichier */
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
        display: flex !important;
        align-items: flex-start !important;
    }

    .cenov-gdpr-consent input {
        margin-top: 4px !important;
        margin-right: 10px !important;
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
        background-color: #ecfdf5 !important;
        color: #065f46 !important;
        padding: 12px !important;
        border-radius: 6px !important;
        margin-bottom: 20px !important;
        font-weight: 500 !important;
        border-left: 4px solid #10b981 !important;
    }

    .error-message {
        background-color: #fef2f2 !important;
        color: #991b1b !important;
        padding: 12px !important;
        border-radius: 6px !important;
        margin-bottom: 20px !important;
        font-weight: 500 !important;
        border-left: 4px solid #ef4444 !important;
    }

    /* Styles pour le récapitulatif de commande */
    .order-summary {
        background-color: #f8fafc !important;
        border-radius: 10px !important;
        border: 1px solid #6b7280 !important;
        overflow: hidden !important;
    }

    .order-summary-title {
        font-size: 1.1rem !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        margin: 0 !important;
        padding: 15px 20px !important;
        background-color: #93c5fd !important;
        border-bottom: 1px solid #6b7280 !important;
    }

    .order-summary-content {
        padding: 15px 20px !important;
        background-color: #dbeafe !important;
    }

    .product-summary-item {
        display: flex !important;
        margin-bottom: 15px !important;
        padding-bottom: 15px !important;
        border-bottom: 1px dashed #000 !important;
    }

    .product-summary-item:last-child {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
        border-bottom: none !important;
    }

    .product-image {
        width: 80px !important;
        min-width: 80px !important;
        height: 80px !important;
        margin-right: 15px !important;
        border-radius: 6px !important;
        overflow: hidden !important;
        background-color: #fff !important;
        border: 1px solid #000 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .product-image img {
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
    }

    .product-details {
        flex: 1 !important;
    }

    .product-title {
        margin: 0 0 10px !important;
        font-size: 1rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
    }

    .product-meta {
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
    }

    .product-quantity {
        display: flex !important;
        align-items: center !important;
        font-size: 0.9rem !important;
        color: #000 !important;
    }

    .product-meta svg {
        margin-right: 5px !important;
    }

    .empty-cart-message {
        padding: 15px !important;
        text-align: center !important;
        font-style: italic !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr !important;
        }
        
        .cenov-form-container {
            padding: 20px !important;
        }

        .product-meta {
            flex-direction: row !important;
            gap: 8px !important;
        }

        .product-image {
            width: 60px !important;
            min-width: 60px !important;
            height: 60px !important;
        }
    }

    .remove-product-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        color: #000 !important;
        transition: color 0.2s ease !important;
        text-decoration: none !important;
    }
    
    .remove-product-btn span {
        font-size: 14px !important;
    }
    
    .remove-product-btn:hover {
        color: #dc2626 !important;
    }

    .preview-item:hover,
    .file-input-container:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, .1) !important;
        transform: translateY(-2px) !important;
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
        {id: "cenov-pays", type: "select"},
        {id: "cenov-reference", type: "input"},
        {id: "cenov-message", type: "textarea"},
        {id: "cenov-materiel-equivalent", type: "checkbox"},
        {id: "cenov-gdpr", type: "checkbox"}
    ];
    const storageKey = "cenov_form_data";

    // Fonction pour sauvegarder les champs dans le localStorage
    function saveFormData() {
        const data = {};
        champs.forEach(champ => {
            const el = document.getElementById(champ.id);
            if (!el) return;
            if (champ.type === "checkbox") {
                data[champ.id] = el.checked;
            } else if (champ.type === "select") {
                data[champ.id] = el.value;
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

    // Nettoyage du localStorage après soumission réussie
    if (form) {
        form.addEventListener("submit", function() {
            localStorage.removeItem(storageKey);
        });
    }
});
</script>
