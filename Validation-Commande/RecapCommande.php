<?php
// Vérifier si les données de session existent
session_start();

// Définir une constante pour les champs non renseignés
define('NOT_PROVIDED', 'Non renseigné');

// Vérifier si nous avons un numéro de commande et une clé dans l'URL
if (isset($_GET['order']) && isset($_GET['key'])) {
    $order_number = intval($_GET['order']);
    $order_key = sanitize_text_field($_GET['key']);
    
    // Vérifier la validité de la clé
    $stored_key = get_option('cenov_order_key_' . $order_number);
    $key_expiration = get_option('cenov_order_key_expires_' . $order_number);
    
    // Si la clé est valide et n'a pas expiré
    if ($stored_key && $stored_key === $order_key && $key_expiration > time()) {
        // Récupérer les données de session si elles existent
        if (!isset($_SESSION['commande_data']) || empty($_SESSION['commande_data']) || $_SESSION['commande_data']['commande_number'] != $order_number) {
            // Tenter de reconstruire les données complètes depuis la base de données
            $_SESSION['commande_data'] = array(
                'commande_number' => $order_number,
                'date_commande' => date_i18n('j F Y', get_option('cenov_order_date_' . $order_number, time())),
                'client_name' => get_option('cenov_order_client_' . $order_number, NOT_PROVIDED),
                'client_email' => get_option('cenov_order_email_' . $order_number, NOT_PROVIDED),
                'client_phone' => get_option('cenov_order_phone_' . $order_number, NOT_PROVIDED),
                'client_company' => get_option('cenov_order_company_' . $order_number, NOT_PROVIDED),
                'client_address' => get_option('cenov_order_address_' . $order_number, NOT_PROVIDED),
                'client_postcode' => get_option('cenov_order_postcode_' . $order_number, NOT_PROVIDED),
                'client_city' => get_option('cenov_order_city_' . $order_number, NOT_PROVIDED),
                'client_country' => get_option('cenov_order_country_' . $order_number, NOT_PROVIDED),
                'client_reference' => get_option('cenov_order_reference_' . $order_number, ''),
                'client_materiel_equivalent' => get_option('cenov_order_materiel_equivalent_' . $order_number, false),
                'client_message' => get_option('cenov_order_message_' . $order_number, ''),
                'products' => get_option('cenov_order_products_' . $order_number, array()),
                'file_names' => get_option('cenov_order_file_names_' . $order_number, array()),
                'file_images' => get_option('cenov_order_file_images_' . $order_number, array()),
            );
        }
    } else {
        // Si la clé est invalide ou a expiré, rediriger vers la page d'accueil
        wp_redirect(home_url());
        exit;
    }
} elseif (!isset($_SESSION['commande_data']) || empty($_SESSION['commande_data'])) {
    // Si pas de paramètres d'URL et pas de données en session, rediriger vers la page précédente
    wp_redirect(home_url('/validation-commande/'));
    exit;
}

// Récupérer les données
$data = $_SESSION['commande_data'];

// Vérifier si les données des fichiers existent mais que les images encodées n'ont pas encore été créées
if (isset($data['file_paths']) && !empty($data['file_paths']) && !isset($data['file_images'])) {
    // Créer un tableau pour stocker les images encodées en base64
    $file_images = array();
    
    foreach ($data['file_paths'] as $file_name => $file_path) {
        if (file_exists($file_path)) {
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic']);
            
            if ($is_image) {
                // Encoder l'image en base64
                $image_data = base64_encode(file_get_contents($file_path));
                $mime_type = mime_content_type($file_path);
                $file_images[$file_name] = [
                    'data' => $image_data,
                    'mime' => $mime_type
                ];
            }
        }
    }
    
    // Ajouter les images encodées à la session
    $_SESSION['commande_data']['file_images'] = $file_images;
    
    // Stocker également les images dans la base de données pour une récupération future
    if (isset($_SESSION['commande_data']['commande_number'])) {
        $order_number = $_SESSION['commande_data']['commande_number'];
        update_option('cenov_order_file_images_' . $order_number, $file_images);
    }
    
    $data = $_SESSION['commande_data']; // Mettre à jour la variable $data
}

$commande_number = isset($data['commande_number']) ? $data['commande_number'] : 'N/A';
$date_commande = isset($data['date_commande']) ? $data['date_commande'] : date_i18n('j F Y');
$products = isset($data['products']) ? $data['products'] : [];

// Si nous avons un numéro de commande, sauvegarder les données complètes dans la base de données
// pour permettre la récupération ultérieure depuis un autre navigateur ou session
if ($commande_number != 'N/A' && isset($_SESSION['commande_data'])) {
    $order_number = $commande_number;
    
    // Sauvegarder toutes les données client dans la base de données
    update_option('cenov_order_phone_' . $order_number, isset($data['client_phone']) ? $data['client_phone'] : NOT_PROVIDED);
    update_option('cenov_order_company_' . $order_number, isset($data['client_company']) ? $data['client_company'] : NOT_PROVIDED);
    update_option('cenov_order_address_' . $order_number, isset($data['client_address']) ? $data['client_address'] : NOT_PROVIDED);
    update_option('cenov_order_postcode_' . $order_number, isset($data['client_postcode']) ? $data['client_postcode'] : NOT_PROVIDED);
    update_option('cenov_order_city_' . $order_number, isset($data['client_city']) ? $data['client_city'] : NOT_PROVIDED);
    update_option('cenov_order_country_' . $order_number, isset($data['client_country']) ? $data['client_country'] : NOT_PROVIDED);
    update_option('cenov_order_reference_' . $order_number, isset($data['client_reference']) ? $data['client_reference'] : '');
    update_option('cenov_order_materiel_equivalent_' . $order_number, isset($data['client_materiel_equivalent']) ? $data['client_materiel_equivalent'] : false);
    update_option('cenov_order_message_' . $order_number, isset($data['client_message']) ? $data['client_message'] : '');
    update_option('cenov_order_products_' . $order_number, isset($data['products']) ? $data['products'] : array());
    update_option('cenov_order_file_names_' . $order_number, isset($data['file_names']) ? $data['file_names'] : array());
    
    // Sauvegarder les images encodées si elles existent
    if (isset($data['file_images'])) {
        update_option('cenov_order_file_images_' . $order_number, $data['file_images']);
    }
}
?>

<div class="cenov-recap-container">
    <div class="recap-header">
        <div class="confirmation-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <h2>Merci pour votre demande de prix !</h2>
        <p class="reference-number">Référence : <strong><?php echo esc_html($commande_number); ?></strong> - <?php echo esc_html($date_commande); ?></p>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg> Vos informations :</h3>
        <div class="info-grid">
            <div class="info-item">
                <label for="client-name">Nom :</label>
                <span id="client-name"><?php echo isset($data['client_name']) ? esc_html($data['client_name']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-email">Email :</label>
                <span id="client-email"><?php echo isset($data['client_email']) ? esc_html($data['client_email']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-phone">Téléphone :</label>
                <span id="client-phone"><?php echo isset($data['client_phone']) ? esc_html($data['client_phone']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-company">Société :</label>
                <span id="client-company"><?php echo isset($data['client_company']) ? esc_html($data['client_company']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-address">Adresse :</label>
                <span id="client-address"><?php echo isset($data['client_address']) ? esc_html($data['client_address']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-postcode">Code Postal :</label>
                <span id="client-postcode"><?php echo isset($data['client_postcode']) ? esc_html($data['client_postcode']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-city">Ville :</label>
                <span id="client-city"><?php echo isset($data['client_city']) ? esc_html($data['client_city']) : NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <label for="client-country">Pays :</label>
                <span id="client-country"><?php echo isset($data['client_country']) ? esc_html($data['client_country']) : NOT_PROVIDED; ?></span>
            </div>
            <?php if (isset($data['client_reference']) && !empty($data['client_reference'])) : ?>
            <div class="info-item full-width">
                <label for="client-reference">Référence client :</label>
                <span id="client-reference"><?php echo esc_html($data['client_reference']); ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($data['client_materiel_equivalent'])) : ?>
            <div class="info-item full-width">
                <label for="client-materiel">Matériel équivalent :</label>
                <span id="client-materiel"><?php echo $data['client_materiel_equivalent'] ? 'Oui' : 'Non'; ?></span>
            </div>
            <?php endif; ?>
            <?php if (isset($data['client_message']) && !empty($data['client_message'])) : ?>
            <div class="info-item full-width">
                <label for="client-message">Message :</label>
                <div class="message-content" id="client-message"><?php echo nl2br(esc_html($data['client_message'])); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package"><path d="M16.5 9.4 7.55 4.24"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/></svg>Commande :</h3>
        
        <?php if (!empty($products)) : ?>
            <div class="products-list">
                <?php foreach ($products as $product) : ?>
                <div class="product-item">
                    <div class="product-image">
                        <img src="<?php echo esc_url($product['image']); ?>" alt="<?php echo esc_attr($product['name']); ?>">
                    </div>
                    <div class="product-details">
                        <h4><?php echo esc_html($product['name']); ?></h4>
                        <p class="product-sku">SKU : <?php echo esc_html($product['sku']); ?></p>
                        <p class="product-quantity">Quantité : <?php echo esc_html($product['quantity']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="empty-products">
                <p>Aucun produit n'a été demandé</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip"><path d="M13.234 20.252 21 12.3"/><path d="m16 6-8.414 8.586a2 2 0 0 0 0 2.828 2 2 0 0 0 2.828 0l8.414-8.586a4 4 0 0 0 0-5.656 4 4 0 0 0-5.656 0l-8.415 8.585a6 6 0 1 0 8.486 8.486"/></svg> Pièces jointes :</h3>
        <?php if (isset($data['file_names']) && !empty($data['file_names'])) : ?>
        <div class="attachments-list">
            <?php foreach ($data['file_names'] as $file_name) :
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic']);
            ?>
            <div class="attachment-item<?php echo $is_image ? ' attachment-image' : ''; ?>">
                <?php if ($is_image) :
                    // Pour les images, vérifier si nous avons des données encodées en base64
                    if (isset($data['file_images']) && isset($data['file_images'][$file_name])) {
                        // Utiliser l'image encodée en base64
                        $image_data = $data['file_images'][$file_name]['data'];
                        $mime_type = $data['file_images'][$file_name]['mime'];
                        echo '<div class="attachment-preview">';
                        echo '<img src="data:' . $mime_type . ';base64,' . $image_data . '" alt="' . esc_attr($file_name) . '">';
                        echo '</div>';
                    }
                    // Vérifier si le fichier temporaire existe encore
                    elseif (isset($data['file_paths']) && isset($data['file_paths'][$file_name]) && file_exists($data['file_paths'][$file_name])) {
                        // Utiliser le fichier temporaire
                        $temp_path = $data['file_paths'][$file_name];
                        $image_data = base64_encode(file_get_contents($temp_path));
                        $mime_type = mime_content_type($temp_path);
                        echo '<div class="attachment-preview">';
                        echo '<img src="data:' . $mime_type . ';base64,' . $image_data . '" alt="' . esc_attr($file_name) . '">';
                        echo '</div>';
                        
                        // Enregistrer cette image dans la session pour les futures actualisations
                        if (!isset($_SESSION['commande_data']['file_images'])) {
                            $_SESSION['commande_data']['file_images'] = array();
                        }
                        $_SESSION['commande_data']['file_images'][$file_name] = [
                            'data' => $image_data,
                            'mime' => $mime_type
                        ];
                    } else {
                        // Si aucune donnée n'est disponible, afficher une icône image
                        echo '<div class="attachment-icon">';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>';
                        echo '</div>';
                    }
                ?>
                <div class="attachment-info">
                    <span class="attachment-name"><?php echo esc_html($file_name); ?></span>
                    <span class="attachment-type"><?php echo strtoupper($file_extension); ?></span>
                </div>
                <?php else : ?>
                <div class="attachment-icon">
                    <?php if ($file_extension === 'pdf') : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-type-pdf"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13v-1h6v1"/><path d="M11 15v4"/><path d="M9 19h4"/></svg>
                    <?php else : ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <?php endif; ?>
                </div>
                <div class="attachment-info">
                    <span class="attachment-name"><?php echo esc_html($file_name); ?></span>
                    <span class="attachment-type"><?php echo strtoupper($file_extension); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="no-attachments">
            <p>Aucune plaque signalétique n'a été jointe à cette demande.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="recap-footer">
        <p>Un email de confirmation a été envoyé à votre adresse <?php echo isset($data['client_email']) ? esc_html($data['client_email']) : NOT_PROVIDED; ?>.</p>
        <p>Notre équipe commerciale vous contactera prochainement.</p>
        
        <div class="action-buttons">
            <a href="<?php echo home_url(); ?>" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Retour à l'accueil</a>
        </div>
    </div>
</div>

<style>
.cenov-recap-container {
    max-width: 800px;
    margin: auto;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    padding: 30px;
    border: 2px solid #2563eb !important;
}

.recap-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #6b7280;
}

.confirmation-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.recap-header h2 {
    color: #2563eb;
    font-size: 26px;
    margin: 0 0 10px;
}

.reference-number {
    color: #4b5563;
    font-size: 16px;
    margin: 0;
}

.recap-section {
    margin-bottom: 30px;
    background: #f3f4f6;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #6b7280 !important;
}

.recap-section h3 {
    display: flex;
    align-items: center;
    font-size: 18px;
    color: #1e3a8a;
    margin-top: 0;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #6b7280 !important;
}

.recap-section h3 svg {
    margin-right: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.info-item {
    background: white;
    border-radius: 6px;
    padding: 10px 15px;
    border: 1px solid #6b7280 !important;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.info-item label {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-item span {
    font-size: 15px;
}

.message-content {
    background: #f3f4f6;
    padding: 10px;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.5;
    margin-top: 0.3rem;
    border: 1px solid #ddd !important;
}

.products-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    border: 1px solid #6b7280;
}

.product-item {
    display: flex;
    background: white;
    overflow: hidden;
}

.product-image {
    width: 100px;
    min-width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
}

.product-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border: 1px solid #000;
}

.product-details {
    padding: 7px;
    flex: 1;
}

.product-details h4 {
    font-size: 16px;
    color: #111827;
}

.product-sku, .product-quantity {
    font-size: 14px;
    padding: 0 !important;
}

.empty-products, .no-attachments {
    text-align: center;
    padding: 20px;
    background: white;
    border-radius: 6px;
}

.attachments-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.attachment-item {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: 220px;
    border: 1px solid #6b7280;
    transition: transform 0.2s ease;
}

.attachment-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.attachment-preview {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #FFF;
    padding: 10px;
    overflow: hidden;
}

.attachment-preview img {
    max-width: 100%;
    max-height: 150px;
    object-fit: contain;
}

.attachment-icon {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f9fafb;
    color: #6b7280;
}

.attachment-info {
    padding: 12px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    border-top: 1px solid #6b7280 !important;
}

.attachment-name {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.attachment-type {
    color: #6b7280;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.recap-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #6b7280 !important;
}

.action-buttons {
    margin-top: 25px;
    display: flex;
    justify-content: center;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.2);
}

.btn-primary:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.2);
}

.btn-primary:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
  }

.btn svg {
    margin-right: 8px;
    vertical-align: -3px;
}

@media (max-width: 768px) {
    .cenov-recap-container {
        margin: 20px 10px;
        padding: 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .product-item {
        flex-direction: column;
    }
    
    .product-image {
        width: 100%;
        height: 150px;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
    
    .attachments-list {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .attachment-item {
        height: 200px;
    }
    
    .attachment-preview, .attachment-icon {
        height: 120px;
    }
    
    .attachment-preview img {
        max-height: 120px;
    }

    .recap-footer p {
    font-size: 16px;
}
}
</style>

<?php
// Nettoyer les fichiers temporaires s'ils existent encore
if (isset($_SESSION['commande_data']['file_paths'])) {
    foreach ($_SESSION['commande_data']['file_paths'] as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
    
    // Supprimer les chemins de fichiers de la session car ils ne sont plus nécessaires
    unset($_SESSION['commande_data']['file_paths']);
}

// Ne pas décommenter cette ligne pour préserver les données de session y compris les images en base64
// unset($_SESSION['commande_data']);
?>
