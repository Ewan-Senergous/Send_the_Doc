<?php
// Si le formulaire est soumis
if (isset($_POST['test_email_submit'])) {
    // Journaliser le début du traitement
    error_log('Tentative d\'envoi d\'email de test à ewan16270409@outlook.fr');
    
    // Adresse email de destination (votre adresse email personnelle)
    $to = 'ewan16270409@outlook.fr';
    
    // Récupération des données du formulaire
    $subject = 'Test d\'envoi d\'email';
    $message = sanitize_textarea_field($_POST['test_message']);
    
    // En-têtes de l'email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
    );
    
    // Journaliser les informations avant envoi
    error_log('Détails email: To=' . $to . ', Subject=' . $subject . ', From=' . get_option('admin_email'));
    
    // Tentative d'envoi de l'email
    $mail_sent = wp_mail($to, $subject, $message, $headers);
    
    // Journaliser le résultat
    error_log('Résultat wp_mail(): ' . ($mail_sent ? 'Succès' : 'Échec'));
    
    // Message de résultat
    if ($mail_sent) {
        $result_message = '<div class="email-success">Email envoyé avec succès à ' . $to . '</div>';
    } else {
        $result_message = '<div class="email-error">Échec de l\'envoi de l\'email. Vérifiez la configuration de votre serveur.</div>';
    }
    
    // Ajouter un script JavaScript pour afficher dans la console
    echo '<script>console.log("Tentative d\'envoi d\'email: ' . ($mail_sent ? 'Succès' : 'Échec') . '");</script>';
}
?>

<div class="test-email-container">
    <h3>Test d'envoi d'email</h3>
    
    <?php if (isset($result_message)): ?>
        <div class="result-message"><?php echo $result_message; ?></div>
        <script>
            console.log("Statut envoi email:", {
                destinataire: "ewan16270409@outlook.fr",
                resultat: "<?php echo $mail_sent ? 'Succès' : 'Échec'; ?>",
                message: "<?php echo $mail_sent ? 'Email envoyé avec succès' : 'Échec de l\'envoi'; ?>"
            });
        </script>
    <?php endif; ?>
    
    <form method="post" class="test-email-form">
        <div class="form-row">
            <label for="test-message">Message de test:</label>
            <textarea id="test-message" name="test_message" rows="4" required>Ceci est un message de test pour vérifier la fonctionnalité d'envoi d'email.</textarea>
        </div>
        
        <div class="form-submit">
            <button type="submit" name="test_email_submit" value="1">Envoyer un email de test</button>
        </div>
    </form>
    
    <div class="email-info">
        <h4>Informations sur la configuration email:</h4>
        <ul>
            <li>Fonction wp_mail() utilisée</li>
            <li>Destinataire: ewan16270409@outlook.fr</li>
            <li>Expéditeur: <?php echo get_bloginfo('name') . ' <' . get_option('admin_email') . '>'; ?></li>
            <li>Email admin WordPress: <?php echo get_option('admin_email'); ?></li>
        </ul>
        <script>
            console.log("Configuration email WordPress:", {
                siteUrl: "<?php echo site_url(); ?>",
                adminEmail: "<?php echo get_option('admin_email'); ?>",
                siteName: "<?php echo get_bloginfo('name'); ?>"
            });
            
            // Vérifier si la fonction wp_mail existe
            console.log("Fonction wp_mail disponible:", <?php echo function_exists('wp_mail') ? 'true' : 'false'; ?>);
        </script>
    </div>
</div>

<?php
// Ajouter un script pour vérifier les paramètres PHP qui peuvent affecter l'envoi d'emails
echo '<script>';
echo 'console.log("Vérification PHP mail():", {';
echo '  mail_enabled: "' . (function_exists('mail') ? 'Oui' : 'Non') . '",';
echo '  sendmail_path: "' . ini_get('sendmail_path') . '",';
echo '  SMTP: "' . ini_get('SMTP') . '",';
echo '  smtp_port: "' . ini_get('smtp_port') . '"';
echo '});';
echo '</script>';
?>

<style>
    .test-email-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .test-email-container h3 {
        margin-top: 0;
        color: #333;
    }
    
    .form-row {
        margin-bottom: 15px;
    }
    
    .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }
    
    .form-row textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .form-submit button {
        background-color: #0073aa;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .form-submit button:hover {
        background-color: #005177;
    }
    
    .result-message {
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 4px;
    }
    
    .email-success {
        background-color: #dff0d8;
        color: #3c763d;
        padding: 10px;
        border-radius: 4px;
    }
    
    .email-error {
        background-color: #f2dede;
        color: #a94442;
        padding: 10px;
        border-radius: 4px;
    }
    
    .email-info {
        margin-top: 20px;
        padding: 15px;
        background: #f0f0f0;
        border-left: 4px solid #0073aa;
    }
    
    .email-info h4 {
        margin-top: 0;
    }
    
    .email-info ul {
        margin-bottom: 0;
    }
</style>

<script>
    // Exécuté au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page de test email chargée');
        
        // Vérification de l'intégration WordPress
        console.log('WordPress AJAX URL:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'Non défini');
        
        // Vérifier si la page est dans WordPress
        console.log('Dans WordPress:', typeof wp !== 'undefined' ? 'Oui' : 'Non');
    });
</script>
