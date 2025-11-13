<?php
// Page de récapitulatif pour les demandes de moteurs asynchrones triphasés
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir une constante pour les champs non renseignés
define('MOTEUR_NOT_PROVIDED', 'Non renseigné');

// Vérifier si nous avons un numéro de demande et une clé dans l'URL
if (isset($_GET['order']) && isset($_GET['key'])) {
    $order_number = intval($_GET['order']);
    $order_key = sanitize_text_field($_GET['key']);

    // Vérifier la validité de la clé
    $stored_key = get_option('cenov_moteur_key_' . $order_number);
    $key_expiration = get_option('cenov_moteur_key_expires_' . $order_number);

    // Si la clé est valide et n'a pas expiré
    if ($stored_key && $stored_key === $order_key && $key_expiration > time()) {
        // Récupérer les données de session si elles existent
        if (!isset($_SESSION['moteur_data']) || empty($_SESSION['moteur_data']) || $_SESSION['moteur_data']['order_number'] != $order_number) {
            // Tenter de reconstruire les données complètes depuis la base de données
            $_SESSION['moteur_data'] = array(
                'order_number' => $order_number,
                'date_demande' => date_i18n('j F Y', get_option('cenov_moteur_date_' . $order_number, time())),
                'societe' => get_option('cenov_moteur_societe_' . $order_number, MOTEUR_NOT_PROVIDED),
                'nom_prenom' => get_option('cenov_moteur_nom_prenom_' . $order_number, MOTEUR_NOT_PROVIDED),
                'email' => get_option('cenov_moteur_email_' . $order_number, MOTEUR_NOT_PROVIDED),
                'telephone' => get_option('cenov_moteur_telephone_' . $order_number, MOTEUR_NOT_PROVIDED),
                'ville_pays' => get_option('cenov_moteur_ville_pays_' . $order_number, MOTEUR_NOT_PROVIDED),
                'fonction' => get_option('cenov_moteur_fonction_' . $order_number, MOTEUR_NOT_PROVIDED),
                'quantite' => get_option('cenov_moteur_quantite_' . $order_number, MOTEUR_NOT_PROVIDED),
                'budget' => get_option('cenov_moteur_budget_' . $order_number, MOTEUR_NOT_PROVIDED),
                'delai' => get_option('cenov_moteur_delai_' . $order_number, MOTEUR_NOT_PROVIDED),
                'puissance_kw' => get_option('cenov_moteur_puissance_kw_' . $order_number, MOTEUR_NOT_PROVIDED),
                'vitesse' => get_option('cenov_moteur_vitesse_' . $order_number, MOTEUR_NOT_PROVIDED),
                'tension' => get_option('cenov_moteur_tension_' . $order_number, MOTEUR_NOT_PROVIDED),
                'frequence' => get_option('cenov_moteur_frequence_' . $order_number, MOTEUR_NOT_PROVIDED),
                'montage' => get_option('cenov_moteur_montage_' . $order_number, MOTEUR_NOT_PROVIDED),
                'file_names' => get_option('cenov_moteur_file_names_' . $order_number, array()),
                'file_images' => get_option('cenov_moteur_file_images_' . $order_number, array()),
            );
        }
    } else {
        // Si la clé est invalide ou a expiré, rediriger vers la page d'accueil
        wp_redirect(home_url());
        exit;
    }
} elseif (!isset($_SESSION['moteur_data']) || empty($_SESSION['moteur_data'])) {
    // Si pas de paramètres d'URL et pas de données en session, rediriger vers la page du formulaire
    wp_redirect(home_url('/formulaire-moteur/'));
    exit;
}

// Récupérer les données
$data = $_SESSION['moteur_data'];

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
    $_SESSION['moteur_data']['file_images'] = $file_images;

    // Stocker également les images dans la base de données pour une récupération future
    if (isset($_SESSION['moteur_data']['order_number'])) {
        $order_number = $_SESSION['moteur_data']['order_number'];
        update_option('cenov_moteur_file_images_' . $order_number, $file_images);
    }

    $data = $_SESSION['moteur_data']; // Mettre à jour la variable $data
}

$order_number = isset($data['order_number']) ? $data['order_number'] : 'N/A';
$date_demande = isset($data['date_demande']) ? $data['date_demande'] : date_i18n('j F Y');

// Si nous avons un numéro de demande, sauvegarder les données complètes dans la base de données
// pour permettre la récupération ultérieure depuis un autre navigateur ou session
if ($order_number != 'N/A' && isset($_SESSION['moteur_data'])) {
    // Sauvegarder toutes les données dans la base de données
    update_option('cenov_moteur_societe_' . $order_number, isset($data['societe']) ? $data['societe'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_nom_prenom_' . $order_number, isset($data['nom_prenom']) ? $data['nom_prenom'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_email_' . $order_number, isset($data['email']) ? $data['email'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_telephone_' . $order_number, isset($data['telephone']) ? $data['telephone'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_ville_pays_' . $order_number, isset($data['ville_pays']) ? $data['ville_pays'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_fonction_' . $order_number, isset($data['fonction']) ? $data['fonction'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_quantite_' . $order_number, isset($data['quantite']) ? $data['quantite'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_budget_' . $order_number, isset($data['budget']) ? $data['budget'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_delai_' . $order_number, isset($data['delai']) ? $data['delai'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_puissance_kw_' . $order_number, isset($data['puissance_kw']) ? $data['puissance_kw'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_vitesse_' . $order_number, isset($data['vitesse']) ? $data['vitesse'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_tension_' . $order_number, isset($data['tension']) ? $data['tension'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_frequence_' . $order_number, isset($data['frequence']) ? $data['frequence'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_montage_' . $order_number, isset($data['montage']) ? $data['montage'] : MOTEUR_NOT_PROVIDED);
    update_option('cenov_moteur_file_names_' . $order_number, isset($data['file_names']) ? $data['file_names'] : array());

    // Sauvegarder les images encodées si elles existent
    if (isset($data['file_images'])) {
        update_option('cenov_moteur_file_images_' . $order_number, $data['file_images']);
    }
}
?>

<div class="cenov-moteur-recap-container">
    <div class="recap-header">
        <div class="confirmation-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-icon lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <h2>Merci pour votre demande de moteur !</h2>
        <p class="reference-number">Référence : <strong><?php echo esc_html($order_number); ?></strong> - <?php echo esc_html($date_demande); ?></p>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-round"><path d="M18 20a6 6 0 0 0-12 0"/><circle cx="12" cy="10" r="4"/><circle cx="12" cy="12" r="10"/></svg> Informations de contact</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Société :</span>
                <span class="info-value"><?php echo isset($data['societe']) ? esc_html($data['societe']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Nom & Prénom :</span>
                <span class="info-value"><?php echo isset($data['nom_prenom']) ? esc_html($data['nom_prenom']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email :</span>
                <span class="info-value"><?php echo isset($data['email']) ? esc_html($data['email']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Téléphone :</span>
                <span class="info-value"><?php echo isset($data['telephone']) ? esc_html($data['telephone']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Ville/Pays :</span>
                <span class="info-value"><?php echo isset($data['ville_pays']) ? esc_html($data['ville_pays']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Fonction :</span>
                <span class="info-value"><?php echo isset($data['fonction']) ? esc_html($data['fonction']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
        </div>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-briefcase"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Informations projet</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Quantité prévue :</span>
                <span class="info-value"><?php echo isset($data['quantite']) ? esc_html($data['quantite']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Budget estimatif :</span>
                <span class="info-value"><?php echo isset($data['budget']) ? esc_html($data['budget']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item full-width">
                <span class="info-label">Délai souhaité :</span>
                <span class="info-value"><?php echo isset($data['delai']) ? esc_html($data['delai']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
        </div>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Caractéristiques moteur</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Puissance (kW) :</span>
                <span class="info-value"><?php echo isset($data['puissance_kw']) ? esc_html($data['puissance_kw']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Vitesse :</span>
                <span class="info-value"><?php echo isset($data['vitesse']) ? esc_html($data['vitesse']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tension :</span>
                <span class="info-value"><?php echo isset($data['tension']) ? esc_html($data['tension']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Fréquence :</span>
                <span class="info-value"><?php echo isset($data['frequence']) ? esc_html($data['frequence']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
            <div class="info-item full-width">
                <span class="info-label">Type de montage :</span>
                <span class="info-value"><?php echo isset($data['montage']) ? esc_html($data['montage']) : MOTEUR_NOT_PROVIDED; ?></span>
            </div>
        </div>
    </div>

    <div class="recap-section">
        <h3><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip"><path d="M13.234 20.252 21 12.3"/><path d="m16 6-8.414 8.586a2 2 0 0 0 0 2.828 2 2 0 0 0 2.828 0l8.414-8.586a4 4 0 0 0 0-5.656 4 4 0 0 0-5.656 0l-8.415 8.585a6 6 0 1 0 8.486 8.486"/></svg> Pièces jointes</h3>
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
                        if (!isset($_SESSION['moteur_data']['file_images'])) {
                            $_SESSION['moteur_data']['file_images'] = array();
                        }
                        $_SESSION['moteur_data']['file_images'][$file_name] = [
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
        <p>Un email de confirmation a été envoyé à votre adresse <?php echo isset($data['email']) ? esc_html($data['email']) : MOTEUR_NOT_PROVIDED; ?>.</p>
        <p>Notre équipe commerciale vous contactera prochainement.</p>

        <div class="action-buttons">
            <a href="<?php echo home_url(); ?>" class="btn btn-primary"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg> Retour à l'accueil</a>
        </div>
    </div>
</div>

<style>
.cenov-moteur-recap-container {
    max-width: 800px !important;
    margin: auto !important;
    background: #ffffff !important;
    border-radius: 10px !important;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1) !important;
    padding: 30px !important;
    border: 2px solid #4338ca !important;
}

.recap-header {
    text-align: center !important;
    margin-bottom: 30px !important;
    padding-bottom: 25px !important;
    border-bottom: 1px solid #6b7280 !important;
}

.confirmation-icon {
    width: 60px !important;
    height: 60px !important;
    margin: 0 auto 15px !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
}

.recap-header h2 {
    color: #4338ca !important;
    font-size: 26px !important;
    margin: 0 0 10px !important;
}

.reference-number {
    color: #4b5563 !important;
    font-size: 16px !important;
    margin: 0 !important;
}

.recap-section {
    margin-bottom: 30px !important;
    background: #f3f4f6 !important;
    border-radius: 8px !important;
    padding: 20px !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
    border: 1px solid #6b7280 !important;
}

.recap-section h3 {
    display: flex !important;
    align-items: center !important;
    font-size: 18px !important;
    color: #1e3c72 !important;
    margin-top: 0 !important;
    margin-bottom: 15px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid #6b7280 !important;
}

.recap-section h3 svg {
    margin-right: 8px !important;
}

.info-grid {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 15px !important;
}

.info-item {
    background: white !important;
    border-radius: 6px !important;
    padding: 10px 15px !important;
    border: 1px solid #6b7280 !important;
}

.info-item.full-width {
    grid-column: 1 / -1 !important;
}

.info-item .info-label {
    font-weight: 600 !important;
    font-size: 14px !important;
    margin-bottom: 5px !important;
    display: block !important;
    color: #374151 !important;
}

.info-item .info-value {
    font-size: 15px !important;
    color: #111827 !important;
    display: block !important;
}

.attachments-list {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)) !important;
    gap: 20px !important;
    margin-top: 20px !important;
}

.attachment-item {
    background: white !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
    display: flex !important;
    flex-direction: column !important;
    height: 220px !important;
    border: 1px solid #6b7280 !important;
    transition: transform 0.2s ease !important;
}

.attachment-item:hover {
    transform: translateY(-5px) !important;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
}

.attachment-preview {
    height: 150px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: #FFF !important;
    padding: 10px !important;
    overflow: hidden !important;
}

.attachment-preview img {
    max-width: 100% !important;
    max-height: 150px !important;
    object-fit: contain !important;
}

.attachment-icon {
    height: 150px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: #f9fafb !important;
    color: #6b7280 !important;
}

.attachment-info {
    padding: 12px !important;
    flex-grow: 1 !important;
    display: flex !important;
    flex-direction: column !important;
    border-top: 1px solid #6b7280 !important;
}

.attachment-name {
    font-weight: 500 !important;
    color: #374151 !important;
    font-size: 14px !important;
    margin-bottom: 4px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.attachment-type {
    color: #6b7280 !important;
    font-size: 12px !important;
    letter-spacing: 0.5px !important;
}

.no-attachments {
    text-align: center !important;
    padding: 20px !important;
    background: white !important;
    border-radius: 6px !important;
}

.recap-footer {
    text-align: center !important;
    margin-top: 30px !important;
    padding-top: 20px !important;
    border-top: 1px solid #6b7280 !important;
}

.recap-footer p {
    color: #4b5563 !important;
    font-size: 16px !important;
    margin: 10px 0 !important;
}

.action-buttons {
    margin-top: 25px !important;
    display: flex !important;
    justify-content: center !important;
}

.btn {
    display: inline-flex !important;
    align-items: center !important;
    padding: 12px 24px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}

.btn-primary {
    background-color: #4338ca !important;
    color: white !important;
    box-shadow: 0 1px 3px rgba(67, 56, 202, 0.2) !important;
}

.btn-primary:hover {
    background-color: #3730a3 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(67, 56, 202, 0.3) !important;
}

.btn-primary:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.5) !important;
}

.btn svg {
    margin-right: 8px !important;
}

@media (max-width: 768px) {
    .cenov-moteur-recap-container {
        margin: 20px 10px !important;
        padding: 20px !important;
    }

    .info-grid {
        grid-template-columns: 1fr !important;
    }

    .action-buttons {
        flex-direction: column !important;
        gap: 10px !important;
    }

    .btn {
        width: 100% !important;
        text-align: center !important;
        justify-content: center !important;
    }

    .attachments-list {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
    }

    .attachment-item {
        height: 200px !important;
    }

    .attachment-preview, .attachment-icon {
        height: 120px !important;
    }

    .attachment-preview img {
        max-height: 120px !important;
    }

    .recap-footer p {
        font-size: 14px !important;
    }
}
</style>

<?php
// Nettoyer les fichiers temporaires s'ils existent encore
if (isset($_SESSION['moteur_data']['file_paths'])) {
    foreach ($_SESSION['moteur_data']['file_paths'] as $file) {
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // Supprimer les chemins de fichiers de la session car ils ne sont plus nécessaires
    unset($_SESSION['moteur_data']['file_paths']);
}

// Ne pas supprimer la session pour préserver les données y compris les images en base64
// unset($_SESSION['moteur_data']);
?>

<script>
// Événements Google Analytics pour le tracking de conversion
window.dataLayer = window.dataLayer || [];

// Flag pour éviter les déclenchements multiples
let eventSent = false;

function sendGAEvents() {
    if (eventSent) return;

    // Récupération des données de demande
    const orderNumber = '<?php echo esc_js($order_number); ?>';

    // GA4 Event - Demande de moteur
    window.dataLayer.push({
        event: 'moteur_request_submitted',
        order_id: orderNumber,
        value: 0,
        currency: 'EUR'
    });

    console.log('Événement moteur_request_submitted déclenché');
    eventSent = true;
}

// Déclenchement au chargement de la page
document.addEventListener('DOMContentLoaded', sendGAEvents);

// Également en cas de chargement retardé du DOM
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(sendGAEvents, 100);
}
</script>
