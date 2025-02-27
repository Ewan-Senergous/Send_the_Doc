<?php
// Fonction pour gérer l'envoi par AJAX
function cenov_handle_upload() {
    // Vérification du nonce pour la sécurité
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cenov_upload_nonce')) {
        wp_send_json_error('Erreur de sécurité');
        die();
    }

    // Récupération des données du formulaire (correction des noms des champs)
    $nom = sanitize_text_field($_POST['cenov_nom']);
    $email = sanitize_email($_POST['cenov_email']);
    $telephone = sanitize_text_field($_POST['cenov_telephone']);
    $message = sanitize_textarea_field($_POST['cenov_message']);
    
    // Vérification des champs obligatoires
    if (empty($nom) || empty($email)) {
        wp_send_json_error('Veuillez remplir tous les champs obligatoires');
        die();
    }

    // Gestion du fichier
    if (!isset($_FILES['cenov_plaque']) || empty($_FILES['cenov_plaque']['name'])) {
        wp_send_json_error('Veuillez joindre votre plaque signalétique');
        die();
    }

    $file = $_FILES['cenov_plaque'];
    $allowed_types = array('image/jpeg', 'image/png', 'application/pdf');
    $max_size = 10 * 1024 * 1024; // 10 Mo

    // Vérification du type et de la taille du fichier
    if (!in_array($file['type'], $allowed_types)) {
        wp_send_json_error('Format de fichier non supporté. Formats acceptés : JPG, PNG, PDF');
        die();
    }

    if ($file['size'] > $max_size) {
        wp_send_json_error('Le fichier est trop volumineux (10 Mo maximum)');
        die();
    }

    // Déplacement du fichier dans un dossier temporaire
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/cenov_temp';
    
    // Création du dossier s'il n'existe pas
    if (!file_exists($temp_dir)) {
        wp_mkdir_p($temp_dir);
    }

    $filename = sanitize_file_name($file['name']);
    $temp_file = $temp_dir . '/' . $filename;
    
    // Déplacement du fichier
    if (!move_uploaded_file($file['tmp_name'], $temp_file)) {
        wp_send_json_error('Erreur lors du téléchargement du fichier');
        die();
    }

    // Préparation de l'email
    $to = 'ewan16270409@outlook.fr'; // Votre adresse email personnelle
    $subject = 'Nouvelle plaque signalétique envoyée par ' . $nom;
    
    $email_content = "Nouvelle plaque signalétique reçue :\n\n";
    $email_content .= "Nom : " . $nom . "\n";
    $email_content .= "Email : " . $email . "\n";
    $email_content .= "Téléphone : " . ($telephone ? $telephone : 'Non renseigné') . "\n\n";
    $email_content .= "Message : \n" . ($message ? $message : 'Aucun message') . "\n";
    
    $headers = array(
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . $nom . ' <' . $email . '>',
        'Content-Type: text/html; charset=UTF-8'
    );
    
    // Pièce jointe
    $attachments = array($temp_file);
    
    // Envoi de l'email avec wp_mail()
    $mail_sent = wp_mail($to, $subject, $email_content, $headers, $attachments);
    
    // Suppression du fichier temporaire
    @unlink($temp_file);
    
    if ($mail_sent) {
        wp_send_json_success('Votre plaque signalétique a bien été envoyée.');
    } else {
        wp_send_json_error('Une erreur est survenue lors de l\'envoi. Veuillez réessayer ou nous contacter directement.');
    }
    
    die();
}

// Enregistrement de l'action AJAX pour les utilisateurs connectés et non connectés
add_action('wp_ajax_cenov_upload', 'cenov_handle_upload');
add_action('wp_ajax_nopriv_cenov_upload', 'cenov_handle_upload');

// Ajout de la variable ajaxurl pour les visiteurs non connectés
function cenov_add_ajaxurl() {
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_head', 'cenov_add_ajaxurl');

// Contenu HTML du formulaire - Code qui sera exécuté quand le snippet est appelé
?>
<div class="cenov-upload-form-container">
  <h3>Envoyez-nous votre plaque signalétique</h3>

  <form id="cenov-upload-form" enctype="multipart/form-data">
    <?php wp_nonce_field('cenov_upload_nonce', 'cenov_nonce'); ?>
    
    <div class="form-row">
      <label for="cenov-nom">* Nom :</label>
      <input type="text" id="cenov-nom" name="cenov_nom" required />
    </div>

    <div class="form-row">
      <label for="cenov-email">* Email :</label>
      <input type="email" id="cenov-email" name="cenov_email" required />
    </div>

    <div class="form-row">
      <label for="cenov-telephone">Téléphone :</label>
      <input type="tel" id="cenov-telephone" name="cenov_telephone" />
    </div>

    <div class="form-row">
      <label for="cenov-message">Message :</label>
      <textarea id="cenov-message" name="cenov_message" rows="4"></textarea>
    </div>

    <div class="form-row file-upload">
      <label for="cenov-plaque">* Votre plaque signalétique :</label>
      <input
        type="file"
        id="cenov-plaque"
        name="cenov_plaque"
        accept=".jpg, .jpeg, .png, .pdf"
        required
      />
      <p class="file-desc">
        Formats acceptés : JPG, PNG, PDF. Taille max : 10 Mo
      </p>
    </div>

    <div class="form-row">
      <div class="cenov-gdpr-consent">
        <input type="checkbox" id="cenov-gdpr" name="cenov_gdpr" required />
        <label for="cenov-gdpr"
          >J'accepte que mes données soient utilisées pour traiter ma demande
          *</label
        >
      </div>
    </div>

    <div class="form-submit">
      <button type="submit" id="cenov-submit">Envoyer</button>
      <div class="cenov-loader" style="display: none"></div>
    </div>

    <div class="cenov-message-result"></div>
  </form>
</div>

<style>
  .cenov-upload-form-container {
    max-width: 600px;
    margin: 20px auto;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .cenov-upload-form-container h3 {
    margin-top: 0;
    margin-bottom: 20px;
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

  .cenov-upload-form-container .form-row input[type="text"],
  .cenov-upload-form-container .form-row input[type="email"],
  .cenov-upload-form-container .form-row input[type="tel"],
  .cenov-upload-form-container .form-row textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd !important;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s ease;
  }

  .cenov-upload-form-container .form-row input[type="text"]:focus,
  .cenov-upload-form-container .form-row input[type="email"]:focus,
  .cenov-upload-form-container .form-row input[type="tel"]:focus,
  .cenov-upload-form-container .form-row textarea:focus {
    border: 2px solid #000 !important;
    outline: none;
  }

  .form-row.file-upload {
    background: #fff;
    padding: 15px;
    border: 1px dashed #ccc;
    border-radius: 4px;
  }

  .file-desc {
    margin-top: 5px;
    font-size: 12px;
    color: #666;
  }

  .cenov-gdpr-consent {
    display: flex;
    align-items: flex-start;
  }

  .cenov-gdpr-consent input {
    margin-top: 4px;
    margin-right: 8px;
  }

  .form-submit {
    margin-top: 20px;
    display: flex;
    align-items: center;
  }

  #cenov-submit {
    background-color: #0073aa;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
  }

  #cenov-submit:hover {
    background-color: #005177;
  }

  .cenov-loader {
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #0073aa;
    border-radius: 50%;
    margin-left: 15px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  .cenov-message-result {
    margin-top: 15px;
    padding: 10px;
    border-radius: 4px;
    display: none;
  }

  .success-message {
    background-color: #dff0d8;
    color: #3c763d;
    padding: 10px;
    border-radius: 4px;
  }

  .error-message {
    background-color: #f2dede;
    color: #a94442;
    padding: 10px;
    border-radius: 4px;
  }
</style>

<script>
  (function($) {
    $(document).ready(function() {
      console.log("Formulaire initialisé");
      const form = $("#cenov-upload-form");
      const msgContainer = $(".cenov-message-result");
      const loader = $(".cenov-loader");
      const submitBtn = $("#cenov-submit");

      form.on("submit", function(e) {
        e.preventDefault();
        console.log("Formulaire soumis");
        
        // Reset message
        msgContainer.html("").hide();
        
        // Show loader
        loader.show();
        submitBtn.prop("disabled", true);
        
        // Create FormData object
        const formData = new FormData();
        console.log("FormData créé");
        
        // Ajout des champs du formulaire avec leurs noms corrects
        formData.append('action', 'cenov_upload');
        formData.append('nonce', $('#cenov_nonce').val());
        formData.append('cenov_nom', $('#cenov-nom').val());
        formData.append('cenov_email', $('#cenov-email').val());
        formData.append('cenov_telephone', $('#cenov-telephone').val());
        formData.append('cenov_message', $('#cenov-message').val());
        
        console.log("Données du formulaire ajoutées:", {
          action: 'cenov_upload',
          nonce: $('#cenov_nonce').val() ? "présent" : "absent",
          cenov_nom: $('#cenov-nom').val(),
          cenov_email: $('#cenov-email').val(),
          cenov_telephone: $('#cenov-telephone').val(),
          cenov_message: $('#cenov-message').val().substring(0, 20) + "..."
        });
        
        // Ajout du fichier avec le nom correct
        const fileInput = $('#cenov-plaque')[0];
        if (fileInput.files.length > 0) {
          const file = fileInput.files[0];
          formData.append('cenov_plaque', file);
          console.log("Fichier ajouté:", {
            name: file.name,
            type: file.type,
            size: file.size + " bytes"
          });
        } else {
          console.log("Erreur: Aucun fichier sélectionné");
        }
        
        console.log("Envoi de la requête AJAX à:", ajaxurl);
        
        // Send AJAX request
        $.ajax({
          url: ajaxurl, // WordPress AJAX URL
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            console.log("Réponse AJAX reçue:", response);
            loader.hide();
            submitBtn.prop("disabled", false);
            
            if (response.success) {
              console.log("Succès:", response.data);
              msgContainer.html('<div class="success-message">' + response.data + '</div>').show();
              form[0].reset();
            } else {
              console.log("Erreur:", response.data);
              msgContainer.html('<div class="error-message">' + response.data + '</div>').show();
            }
          },
          error: function(xhr, status, error) {
            console.log("Erreur AJAX:", {
              status: status,
              error: error,
              responseText: xhr.responseText
            });
            loader.hide();
            submitBtn.prop("disabled", false);
            msgContainer.html('<div class="error-message">Une erreur est survenue. Veuillez réessayer plus tard.</div>').show();
          }
        });
        
        console.log("Requête AJAX envoyée");
      });
    });
  })(jQuery);
</script>