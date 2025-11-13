<?php
// Questionnaire complet pour la vente de moteurs asynchrones triphasés

// Démarrer la session pour stocker les données du formulaire
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('cenovFormulaireMoteurAsyncDisplay')) {
    function cenovFormulaireMoteurAsyncDisplay() {
        ob_start();

        // Constante pour les champs non renseignés (vérifier si existe déjà)
        if (!defined('CENOV_MOTEUR_NOT_PROVIDED')) {
            define('CENOV_MOTEUR_NOT_PROVIDED', 'Non renseigné');
        }

        // Variable pour les messages d'erreur (les succès redirigent maintenant)
        $result = '';

        // ========== TRAITEMENT DU FORMULAIRE ==========
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_moteur'])) {

            // 1. Vérification du nonce WordPress pour la sécurité
            if (!isset($_POST['cenov_moteur_nonce']) || !wp_verify_nonce($_POST['cenov_moteur_nonce'], 'cenov_moteur_form')) {
                $result = '<div class="error-message">❌ Erreur de sécurité. Veuillez réessayer.</div>';
            }
            // 2. Vérification des champs obligatoires
            elseif (empty($_POST['societe']) || empty($_POST['nom_prenom']) || empty($_POST['email'])) {
                $result = '<div class="error-message">❌ Veuillez remplir tous les champs obligatoires : Société, Nom & Prénom, et Email.</div>';
            }
            // 3. Validation de l'email
            elseif (!is_email($_POST['email'])) {
                $result = '<div class="error-message">❌ Veuillez saisir une adresse email valide.</div>';
            }
            // 4. Tout est OK - Préparation et envoi
            else {
                // ÉTAPE 3.1 : Générer le numéro de demande et la clé de sécurité
                $orderData = generateMoteurOrderData();

                // ÉTAPE 3.2 : Préparer le contenu de l'email
                $content = prepareMoteurEmailContent();

                // ÉTAPE 3.3 : Traiter le fichier uploadé
                $uploadResult = processMoteurUploadedFiles();
                $attachments = $uploadResult['attachments'];
                $fileWarning = $uploadResult['warning'];

                // ÉTAPE 3.4 : Stocker les données en session et en base de données
                storeMoteurSessionData($orderData, $uploadResult);

                // ÉTAPE 3.5 : Envoyer l'email
                $emailSent = sendMoteurEmail($content, $attachments, $orderData);

                // ÉTAPE 3.6 : POST-Redirect-GET - Rediriger vers la page de récapitulatif
                if ($emailSent) {
                    // Ne pas nettoyer les fichiers temporaires maintenant
                    // Ils seront nettoyés par la page RecapMoteur.php

                    // Rediriger vers la page de récapitulatif (résout le problème de page blanche)
                    wp_redirect($orderData['recap_url']);
                    exit;
                } else {
                    // En cas d'erreur d'envoi, afficher le message d'erreur
                    $result = $fileWarning . '<div class="error-message">❌ Une erreur est survenue lors de l\'envoi. Veuillez réessayer ou nous contacter par téléphone.</div>';

                    // Nettoyer les fichiers uploadés en cas d'erreur
                    if (!empty($attachments)) {
                        foreach ($attachments as $file) {
                            if (file_exists($file)) {
                                @unlink($file);
                            }
                        }
                    }
                }
            }
        }

        // ========== FONCTIONS DE TRAITEMENT ==========

        /**
         * Génère les données de la demande (numéro, clé, date, URL)
         * @return array Tableau avec order_number, order_key, date_demande, recap_url
         */
        if (!function_exists('generateMoteurOrderData')) {
            function generateMoteurOrderData() {
            // Système de numérotation séquentiel
            $current_number = get_option('cenov_moteur_request_number', 987540000);
            $order_number = $current_number + 1;
            update_option('cenov_moteur_request_number', $order_number);

            // Générer une clé unique pour cette demande
            $order_key = wp_generate_password(12, false);

            // Stocker la clé avec expiration (30 jours)
            update_option('cenov_moteur_key_' . $order_number, $order_key);
            update_option('cenov_moteur_key_expires_' . $order_number, time() + (30 * DAY_IN_SECONDS));
            update_option('cenov_moteur_date_' . $order_number, time());

            // Construire l'URL de récapitulatif
            $recap_url = add_query_arg(
                array(
                    'order' => $order_number,
                    'key' => $order_key
                ),
                home_url('/recap-moteur/')
            );

            return array(
                'order_number' => $order_number,
                'order_key' => $order_key,
                'date_demande' => date_i18n('j F Y'),
                'recap_url' => $recap_url
            );
            }
        }

        /**
         * Stocke les données du formulaire en session et en base de données
         * @param array $orderData Données de la commande (numéro, clé, date, URL)
         * @param array $uploadResult Résultat de l'upload (attachments, fileNames, filePaths)
         */
        if (!function_exists('storeMoteurSessionData')) {
            function storeMoteurSessionData($orderData, $uploadResult) {
            // Démarrer la session si ce n'est pas déjà fait
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $not_provided = CENOV_MOTEUR_NOT_PROVIDED;

            // Préparer les données pour la session
            $_SESSION['moteur_data'] = array(
                'order_number' => $orderData['order_number'],
                'date_demande' => $orderData['date_demande'],
                'societe' => sanitize_text_field($_POST['societe']),
                'nom_prenom' => sanitize_text_field($_POST['nom_prenom']),
                'email' => sanitize_email($_POST['email']),
                'telephone' => isset($_POST['telephone']) && !empty($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : $not_provided,
                'ville_pays' => isset($_POST['ville_pays']) && !empty($_POST['ville_pays']) ? sanitize_text_field($_POST['ville_pays']) : $not_provided,
                'fonction' => isset($_POST['fonction']) && !empty($_POST['fonction']) ? sanitize_text_field($_POST['fonction']) : $not_provided,
                'quantite' => isset($_POST['quantite']) && !empty($_POST['quantite']) ? intval($_POST['quantite']) : $not_provided,
                'budget' => isset($_POST['budget']) && !empty($_POST['budget']) ? sanitize_text_field($_POST['budget']) : $not_provided,
                'delai' => isset($_POST['delai']) ? sanitize_text_field($_POST['delai']) : $not_provided,
                'puissance_kw' => isset($_POST['puissance_kw']) && !empty($_POST['puissance_kw']) ? sanitize_text_field($_POST['puissance_kw']) : $not_provided,
                'vitesse' => isset($_POST['vitesse']) ? sanitize_text_field($_POST['vitesse']) : $not_provided,
                'tension' => isset($_POST['tension']) ? sanitize_text_field($_POST['tension']) : $not_provided,
                'frequence' => isset($_POST['frequence']) && !empty($_POST['frequence']) ? sanitize_text_field($_POST['frequence']) : $not_provided,
                'montage' => isset($_POST['montage']) ? sanitize_text_field($_POST['montage']) : $not_provided,
                'file_names' => isset($uploadResult['fileNames']) ? $uploadResult['fileNames'] : array(),
                'file_paths' => isset($uploadResult['filePaths']) ? $uploadResult['filePaths'] : array(),
            );

            // Sauvegarder également en base de données pour récupération ultérieure
            $order_number = $orderData['order_number'];
            update_option('cenov_moteur_societe_' . $order_number, $_SESSION['moteur_data']['societe']);
            update_option('cenov_moteur_nom_prenom_' . $order_number, $_SESSION['moteur_data']['nom_prenom']);
            update_option('cenov_moteur_email_' . $order_number, $_SESSION['moteur_data']['email']);
            update_option('cenov_moteur_telephone_' . $order_number, $_SESSION['moteur_data']['telephone']);
            update_option('cenov_moteur_ville_pays_' . $order_number, $_SESSION['moteur_data']['ville_pays']);
            update_option('cenov_moteur_fonction_' . $order_number, $_SESSION['moteur_data']['fonction']);
            update_option('cenov_moteur_quantite_' . $order_number, $_SESSION['moteur_data']['quantite']);
            update_option('cenov_moteur_budget_' . $order_number, $_SESSION['moteur_data']['budget']);
            update_option('cenov_moteur_delai_' . $order_number, $_SESSION['moteur_data']['delai']);
            update_option('cenov_moteur_puissance_kw_' . $order_number, $_SESSION['moteur_data']['puissance_kw']);
            update_option('cenov_moteur_vitesse_' . $order_number, $_SESSION['moteur_data']['vitesse']);
            update_option('cenov_moteur_tension_' . $order_number, $_SESSION['moteur_data']['tension']);
            update_option('cenov_moteur_frequence_' . $order_number, $_SESSION['moteur_data']['frequence']);
            update_option('cenov_moteur_montage_' . $order_number, $_SESSION['moteur_data']['montage']);
            update_option('cenov_moteur_file_names_' . $order_number, $_SESSION['moteur_data']['file_names']);
            }
        }

        /**
         * Prépare le contenu de l'email formaté à partir des données du formulaire
         * @return string Contenu formaté pour l'email
         */
        if (!function_exists('prepareMoteurEmailContent')) {
            function prepareMoteurEmailContent() {
            $not_provided = CENOV_MOTEUR_NOT_PROVIDED;

            // Constantes pour les labels fréquemment utilisés
            $label_vitesse = "Vitesse : ";
            $label_tension = "Tension : ";
            $label_taille_carcasse = "Taille carcasse : ";
            $label_refroidissement = "Refroidissement : ";
            $label_altitude = "Altitude : ";

            $content = "=== DEMANDE DE PRIX - MOTEUR ASYNCHRONE TRIPHASÉ ===\r\n\r\n";

            // === SECTION 1 : CONTACT ===
            $content .= "--- INFORMATIONS DE CONTACT ---\r\n";
            $content .= "Société : " . sanitize_text_field($_POST['societe']) . "\r\n";
            $content .= "Nom & Prénom : " . sanitize_text_field($_POST['nom_prenom']) . "\r\n";
            $content .= "Email : " . sanitize_email($_POST['email']) . "\r\n";
            $content .= "Téléphone : " . (isset($_POST['telephone']) && !empty($_POST['telephone']) ? sanitize_text_field($_POST['telephone']) : $not_provided) . "\r\n";
            $content .= "Ville/Pays : " . (isset($_POST['ville_pays']) && !empty($_POST['ville_pays']) ? sanitize_text_field($_POST['ville_pays']) : $not_provided) . "\r\n";
            $content .= "Fonction : " . (isset($_POST['fonction']) && !empty($_POST['fonction']) ? sanitize_text_field($_POST['fonction']) : $not_provided) . "\r\n";

            // === SECTION 2 : PROJET ===
            $content .= "\r\n--- INFORMATIONS PROJET ---\r\n";
            $content .= "Quantité prévue : " . (isset($_POST['quantite']) && !empty($_POST['quantite']) ? intval($_POST['quantite']) : $not_provided) . "\r\n";
            $content .= "Budget estimatif : " . (isset($_POST['budget']) && !empty($_POST['budget']) ? sanitize_text_field($_POST['budget']) : $not_provided) . "\r\n";
            $content .= "Délai souhaité : " . (isset($_POST['delai']) ? sanitize_text_field($_POST['delai']) : $not_provided) . "\r\n";

            // === SECTION 3 : CARACTÉRISTIQUES APPLICATION ===
            $content .= "\r\n--- CARACTÉRISTIQUES DE L'APPLICATION ---\r\n";
            $content .= "Puissance (kW) : " . (isset($_POST['puissance_kw']) && !empty($_POST['puissance_kw']) ? sanitize_text_field($_POST['puissance_kw']) : $not_provided) . "\r\n";

            // Vitesse avec gestion du champ "autre"
            if (isset($_POST['vitesse'])) {
                $vitesse = sanitize_text_field($_POST['vitesse']);
                if ($vitesse === 'autre' && isset($_POST['vitesse_autre_rpm']) && !empty($_POST['vitesse_autre_rpm'])) {
                    $content .= $label_vitesse . sanitize_text_field($_POST['vitesse_autre_rpm']) . " tr/min (personnalisée)\r\n";
                } else {
                    $content .= $label_vitesse . $vitesse . " tr/min\r\n";
                }
            } else {
                $content .= $label_vitesse . $not_provided . "\r\n";
            }

            // === SECTION 4 : ALIMENTATION ÉLECTRIQUE ===
            $content .= "\r\n--- ALIMENTATION ÉLECTRIQUE ---\r\n";

            // Tension avec gestion du champ "autre"
            if (isset($_POST['tension'])) {
                $tension = sanitize_text_field($_POST['tension']);
                if ($tension === 'autre' && isset($_POST['tension_autre']) && !empty($_POST['tension_autre'])) {
                    $content .= $label_tension . sanitize_text_field($_POST['tension_autre']) . " (personnalisée)\r\n";
                } else {
                    $content .= $label_tension . $tension . "\r\n";
                }
            } else {
                $content .= $label_tension . $not_provided . "\r\n";
            }

            $content .= "Fréquence : " . (isset($_POST['frequence']) && !empty($_POST['frequence']) ? sanitize_text_field($_POST['frequence']) : $not_provided) . "\r\n";

            // === SECTION 5 : INSTALLATION TECHNIQUE ===
            $content .= "\r\n--- INSTALLATION TECHNIQUE ---\r\n";
            $content .= "Type de montage : " . (isset($_POST['montage']) ? sanitize_text_field($_POST['montage']) : $not_provided) . "\r\n";

            // Taille carcasse avec gestion du champ "autre"
            if (isset($_POST['taille_carcasse'])) {
                $taille = sanitize_text_field($_POST['taille_carcasse']);
                if ($taille === 'autre' && isset($_POST['taille_carcasse_autre']) && !empty($_POST['taille_carcasse_autre'])) {
                    $content .= $label_taille_carcasse . sanitize_text_field($_POST['taille_carcasse_autre']) . " (personnalisée)\r\n";
                } else {
                    $content .= $label_taille_carcasse . $taille . "\r\n";
                }
            } else {
                $content .= $label_taille_carcasse . $not_provided . "\r\n";
            }

            $content .= "Matière : " . (isset($_POST['matiere']) ? sanitize_text_field($_POST['matiere']) : $not_provided) . "\r\n";

            // Refroidissement avec gestion du champ "autre"
            if (isset($_POST['refroidissement'])) {
                $refroidissement = sanitize_text_field($_POST['refroidissement']);
                if ($refroidissement === 'autre' && isset($_POST['refroidissement_autre']) && !empty($_POST['refroidissement_autre'])) {
                    $content .= $label_refroidissement . sanitize_text_field($_POST['refroidissement_autre']) . " (personnalisé)\r\n";
                } else {
                    $content .= $label_refroidissement . $refroidissement . "\r\n";
                }
            } else {
                $content .= $label_refroidissement . $not_provided . "\r\n";
            }

            // === SECTION 6 : CONDITIONS D'UTILISATION & ENVIRONNEMENT ===
            $content .= "\r\n--- CONDITIONS D'UTILISATION & ENVIRONNEMENT ---\r\n";
            $content .= "Régime de service : " . (isset($_POST['regime']) ? sanitize_text_field($_POST['regime']) : $not_provided) . "\r\n";
            $content .= "Indice de protection : " . (isset($_POST['ip']) ? sanitize_text_field($_POST['ip']) : $not_provided) . "\r\n";

            // Température avec gestion personnalisée
            if (isset($_POST['temperature'])) {
                $temp = sanitize_text_field($_POST['temperature']);
                if ($temp === 'personnalise' && isset($_POST['temp_min']) && isset($_POST['temp_max'])) {
                    $temp_min = sanitize_text_field($_POST['temp_min']);
                    $temp_max = sanitize_text_field($_POST['temp_max']);
                    $content .= "Température : Personnalisée (Min: {$temp_min}°C, Max: {$temp_max}°C)\r\n";
                } else {
                    $content .= "Température : " . $temp . "\r\n";
                }
            } else {
                $content .= "Température : " . $not_provided . "\r\n";
            }

            // Altitude avec gestion personnalisée
            if (isset($_POST['altitude'])) {
                $altitude = sanitize_text_field($_POST['altitude']);
                if ($altitude === 'personnalise' && isset($_POST['altitude_custom']) && !empty($_POST['altitude_custom'])) {
                    $content .= $label_altitude . sanitize_text_field($_POST['altitude_custom']) . "m (personnalisée)\r\n";
                } else {
                    $content .= $label_altitude . $altitude . "\r\n";
                }
            } else {
                $content .= $label_altitude . $not_provided . "\r\n";
            }

            // Atmosphère (checkboxes multiples)
            $atmos = array();
            if (isset($_POST['atmos_saline'])) {
                $atmos[] = 'Saline';
            }
            if (isset($_POST['atmos_humide'])) {
                $atmos[] = 'Humide';
            }
            if (isset($_POST['atmos_chimique'])) {
                $atmos[] = 'Chimique';
            }
            if (isset($_POST['atmos_poussiere'])) {
                $atmos[] = 'Poussiéreuse';
            }
            $content .= "Atmosphère : " . (!empty($atmos) ? implode(', ', $atmos) : $not_provided) . "\r\n";

            // === SECTION 7 : ATEX (Conditionnel complexe) ===
            $content .= "\r\n--- CERTIFICATION ATEX ---\r\n";
            if (isset($_POST['atex']) && $_POST['atex'] === 'oui') {
                $content .= "ATEX : OUI\r\n";

                // ATEX GAZ
                if (isset($_POST['atex_type_gaz'])) {
                    $content .= "\n  >> ATMOSPHÈRES GAZEUSES <<\r\n";
                    $content .= "  Zone : " . (isset($_POST['atex_zone_gaz']) ? sanitize_text_field($_POST['atex_zone_gaz']) : $not_provided) . "\r\n";
                    $content .= "  Groupe : " . (isset($_POST['atex_groupe_gaz']) && !empty($_POST['atex_groupe_gaz']) ? sanitize_text_field($_POST['atex_groupe_gaz']) : $not_provided) . "\r\n";
                    $content .= "  Classe température : T" . (isset($_POST['atex_temp_gaz']) && !empty($_POST['atex_temp_gaz']) ? sanitize_text_field($_POST['atex_temp_gaz']) : $not_provided) . "\r\n";
                    $content .= "  Mode de protection : " . (isset($_POST['atex_protection_gaz']) && !empty($_POST['atex_protection_gaz']) ? sanitize_text_field($_POST['atex_protection_gaz']) : $not_provided) . "\r\n";
                }

                // ATEX POUSSIÈRES
                if (isset($_POST['atex_type_poussieres'])) {
                    $content .= "\n  >> ATMOSPHÈRES POUSSIÉREUSES <<\r\n";
                    $content .= "  Zone : " . (isset($_POST['atex_zone_poussieres']) ? sanitize_text_field($_POST['atex_zone_poussieres']) : $not_provided) . "\r\n";
                    $content .= "  Type poussières : " . (isset($_POST['atex_type_poussieres']) && !empty($_POST['atex_type_poussieres']) ? sanitize_text_field($_POST['atex_type_poussieres']) : $not_provided) . "\r\n";
                    $content .= "  Classe température : T" . (isset($_POST['atex_temp_poussieres']) && !empty($_POST['atex_temp_poussieres']) ? sanitize_text_field($_POST['atex_temp_poussieres']) : $not_provided) . "\r\n";
                    $content .= "  Mode de protection : " . (isset($_POST['atex_protection_poussieres']) && !empty($_POST['atex_protection_poussieres']) ? sanitize_text_field($_POST['atex_protection_poussieres']) : $not_provided) . "\r\n";
                }
            } else {
                $content .= "ATEX : NON\r\n";
            }

            // === SECTION 8 : PERFORMANCES ÉNERGÉTIQUES ===
            $content .= "\r\n--- PERFORMANCES ÉNERGÉTIQUES ---\r\n";
            $content .= "Classe de rendement : " . (isset($_POST['rendement']) ? sanitize_text_field($_POST['rendement']) : $not_provided) . "\r\n";
            $content .= "Classe d'isolation : " . (isset($_POST['isolation']) ? sanitize_text_field($_POST['isolation']) : $not_provided) . "\r\n";

            // === SECTION 9 : OPTIONS & ACCESSOIRES ===
            $content .= "\r\n--- OPTIONS & ACCESSOIRES ---\r\n";

            $options = array();

            // Équipements électriques
            if (isset($_POST['rechaufage'])) {
                $options[] = 'Réchauffage';
            }
            if (isset($_POST['sonde_thermique_ptc'])) {
                $options[] = 'Sonde thermique PTC';
            }

            // FREIN (conditionnel complexe)
            if (isset($_POST['has_frein']) && $_POST['has_frein'] === 'oui') {
                $frein_type = isset($_POST['frein_type']) ? strtoupper(sanitize_text_field($_POST['frein_type'])) : 'Non spécifié';
                $frein_tension = $not_provided;

                if (isset($_POST['frein_tension'])) {
                    $tension_val = sanitize_text_field($_POST['frein_tension']);
                    if (in_array($tension_val, ['autre_ca', 'autre_cc']) && isset($_POST['frein_tension_autre']) && !empty($_POST['frein_tension_autre'])) {
                        $frein_tension = sanitize_text_field($_POST['frein_tension_autre']);
                    } else {
                        $frein_tension = $tension_val;
                    }
                }

                $options[] = "Frein {$frein_type} - {$frein_tension}";
            }

            // Codeurs
            if (isset($_POST['codeur_incremental'])) {
                $codeur_info = 'Codeur incrémental';
                if (isset($_POST['codeur_incremental_resolution']) && !empty($_POST['codeur_incremental_resolution'])) {
                    $codeur_info .= ' (' . sanitize_text_field($_POST['codeur_incremental_resolution']) . ')';
                }
                $options[] = $codeur_info;
            }
            if (isset($_POST['codeur_absolu'])) {
                $options[] = 'Codeur absolu';
            }

            // Autres accessoires mécaniques
            if (isset($_POST['ventilation_forcee'])) {
                $options[] = 'Ventilation forcée';
            }
            if (isset($_POST['roulements_renforces'])) {
                $options[] = 'Roulements renforcés';
            }
            if (isset($_POST['roulements_nu'])) {
                $options[] = 'Roulements NU';
            }
            if (isset($_POST['graissage_permanent'])) {
                $options[] = 'Graissage permanent';
            }
            if (isset($_POST['autres_accessoires']) && isset($_POST['autres_accessoires_details']) && !empty($_POST['autres_accessoires_details'])) {
                $options[] = 'Autres accessoires : ' . sanitize_text_field($_POST['autres_accessoires_details']);
            }

            // Protection et revêtement
            if (isset($_POST['traitement_tropical'])) {
                $options[] = 'Traitement tropical';
            }
            if (isset($_POST['couleur_ral']) && isset($_POST['couleur_ral_code']) && !empty($_POST['couleur_ral_code'])) {
                $options[] = 'Couleur RAL ' . sanitize_text_field($_POST['couleur_ral_code']);
            }

            $content .= !empty($options) ? implode(', ', $options) : $not_provided;
            $content .= "\r\n";

            // === SECTION 10 : NORMES & CERTIFICATIONS ===
            $content .= "\r\n--- NORMES & CERTIFICATIONS ---\r\n";
            $certifs = array();

            if (isset($_POST['certification_ce'])) {
                $certifs[] = 'CE';
            }
            if (isset($_POST['certification_ul'])) {
                $certifs[] = 'UL/CSA';
            }
            if (isset($_POST['certification_eac'])) {
                $certifs[] = 'EAC (Russie)';
            }
            if (isset($_POST['certification_ccc'])) {
                $certifs[] = 'CCC (Chine)';
            }
            if (isset($_POST['certification_marine'])) {
                $certifs[] = 'Marine (DNV, ABS, Lloyd\'s)';
            }
            if (isset($_POST['certification_autre']) && isset($_POST['certification_autre_details']) && !empty($_POST['certification_autre_details'])) {
                $certifs[] = 'Autre : ' . sanitize_text_field($_POST['certification_autre_details']);
            }

            $content .= !empty($certifs) ? implode(', ', $certifs) : $not_provided;
            $content .= "\r\n";

            // === SECTION 11 : DESCRIPTION DU BESOIN ===
            if (isset($_POST['description_besoin']) && !empty($_POST['description_besoin'])) {
                $content .= "\r\n--- DESCRIPTION DU BESOIN ---\r\n";
                $content .= sanitize_textarea_field($_POST['description_besoin']) . "\r\n";
            }

            // === SECTION 12 : PIÈCE JOINTE ===
            if (!empty($_FILES['fichier_plaque']['name'])) {
                $content .= "\r\n--- PIÈCE JOINTE ---\r\n";
                $content .= "Fichier joint : " . sanitize_file_name($_FILES['fichier_plaque']['name']) . "\r\n";
            } else {
                $content .= "\r\n--- PIÈCE JOINTE ---\r\n";
                $content .= "Aucune plaque signalétique jointe\r\n";
            }

            $content .= "\r\n=== FIN DE LA DEMANDE ===\r\n";

            return $content;
            }
        }

        /**
         * Traite l'upload du fichier plaque signalétique
         * @return array Tableau avec 'attachments' (chemins) et 'fileNames' (noms) et 'warning' (message)
         */
        if (!function_exists('processMoteurUploadedFiles')) {
            function processMoteurUploadedFiles() {
            $attachments = array();
            $fileNames = array();
            $warning = '';

            // Vérifier si un fichier a été uploadé
            if (!isset($_FILES['fichier_plaque']) || empty($_FILES['fichier_plaque']['name'])) {
                return array(
                    'attachments' => $attachments,
                    'fileNames' => $fileNames,
                    'warning' => ''
                );
            }

            $file = $_FILES['fichier_plaque'];

            // Vérifier les erreurs d'upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error_messages = array(
                    UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
                    UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale autorisée.',
                    UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé.',
                    UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                    UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                    UPLOAD_ERR_EXTENSION => 'Une extension PHP a arrêté l\'upload du fichier.'
                );
                $warning = '<div style="background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;">⚠️ Erreur upload : ' .
                           (isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Erreur inconnue.') . '</div>';

                return array(
                    'attachments' => $attachments,
                    'fileNames' => $fileNames,
                    'warning' => $warning
                );
            }

            // Validation du type de fichier
            $allowed_types = array(
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/heic',
                'application/pdf'
            );

            $file_type = $file['type'];
            // Fallback avec finfo si le type MIME n'est pas fiable
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }

            if (!in_array($file_type, $allowed_types)) {
                $warning = '<div style="background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;">⚠️ Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP, HEIC, PDF.</div>';

                return array(
                    'attachments' => $attachments,
                    'fileNames' => $fileNames,
                    'warning' => $warning
                );
            }

            // Validation de la taille (5MB maximum)
            $max_size = 5 * 1024 * 1024; // 5MB en octets
            if ($file['size'] > $max_size) {
                $warning = '<div style="background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;">⚠️ Le fichier est trop volumineux. Taille maximale : 5 MB.</div>';

                return array(
                    'attachments' => $attachments,
                    'fileNames' => $fileNames,
                    'warning' => $warning
                );
            }

            // Préparer le répertoire de destination (WordPress uploads)
            $upload_dir = wp_upload_dir();
            $target_dir = $upload_dir['path'];

            // Créer un nom de fichier unique
            $filename = sanitize_file_name($file['name']);
            $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
            $file_basename = pathinfo($filename, PATHINFO_FILENAME);
            $unique_filename = $file_basename . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $file_extension;
            $target_file = $target_dir . '/' . $unique_filename;

            // Déplacer le fichier uploadé
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $attachments[] = $target_file;
                $fileNames[] = $filename; // Nom original pour affichage

                // Succès - pas de warning
                $warning = '';
            } else {
                $warning = '<div style="background: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;">⚠️ Échec de la sauvegarde du fichier. Veuillez réessayer.</div>';
            }

            return array(
                'attachments' => $attachments,
                'fileNames' => $fileNames,
                'warning' => $warning
            );
            }
        }

        /**
         * Envoie l'email avec le contenu du formulaire
         * @param string $content Contenu formaté de l'email
         * @param array $attachments Chemins des fichiers à attacher
         * @param array $orderData Données de la demande (order_number, recap_url, date_demande)
         * @return bool True si envoi réussi, False sinon
         */
        if (!function_exists('sendMoteurEmail')) {
            function sendMoteurEmail($content, $attachments, $orderData) {
            // Récupérer les informations client
            $client_email = sanitize_email($_POST['email']);
            $client_name = sanitize_text_field($_POST['nom_prenom']);
            $societe = sanitize_text_field($_POST['societe']);

            // Préparer le sujet avec le numéro de demande
            $subject = 'Demande de moteur n°' . $orderData['order_number'] . ' - ' . $societe;

            // Générer le template HTML
            $html_content = generateMoteurEmailHtml($content, $client_name, $client_email, $societe, $orderData);

            // Headers pour l'entreprise
            $headers = array(
                'From: Cenov Distribution <ventes@cenov-distribution.fr>',
                'Reply-To: ' . $client_name . ' <' . $client_email . '>',
                'Content-Type: text/html; charset=UTF-8'
            );

            // 1. Envoi à l'entreprise
            $to_company = 'ventes@cenov-distribution.fr';
            $sent_to_company = wp_mail($to_company, $subject, $html_content, $headers, $attachments);

            // 2. Envoi copie au client
            $sent_to_client = false;
            if (!empty($client_email)) {
                $client_subject = 'Confirmation de votre demande - Cenov Distribution';
                $client_headers = array(
                    'From: Cenov Distribution <ventes@cenov-distribution.fr>',
                    'Reply-To: Cenov Distribution <ventes@cenov-distribution.fr>',
                    'Content-Type: text/html; charset=UTF-8'
                );

                // Template client avec message de remerciement
                $client_html = str_replace(
                    '<div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">',
                    '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
                        <p style="margin: 0; color: #155724; font-weight: 500;">✅ Merci pour votre demande ! Nous vous contacterons rapidement.</p>
                    </div>
                    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">',
                    $html_content
                );

                $sent_to_client = wp_mail($client_email, $client_subject, $client_html, $client_headers, $attachments);
            }

            // Considérer l'envoi réussi si l'email entreprise est parti
            return $sent_to_company && (!empty($client_email) ? $sent_to_client : true);
            }
        }

        /**
         * Génère le template HTML de l'email
         * @param string $content Contenu texte formaté
         * @param string $client_name Nom du client
         * @param string $client_email Email du client
         * @param string $societe Nom de la société
         * @param array $orderData Données de la demande (order_number, recap_url, date_demande)
         * @return string HTML formaté
         */
        if (!function_exists('generateMoteurEmailHtml')) {
            function generateMoteurEmailHtml($content, $client_name, $client_email, $societe, $orderData) {
            return '
            <div style="font-family: Arial, Helvetica, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f9f9f9;">
                <!-- En-tête -->
                <div style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; padding: 30px 20px; text-align: center; border-radius: 10px 10px 0 0;">
                    <h1 style="margin: 0 0 10px 0; font-size: 28px; font-weight: 600;">⚡ Demande Moteur Asynchrone Triphasé</h1>
                    <p style="margin: 0 0 5px 0; font-size: 16px; opacity: 0.95;">Nouvelle demande reçue de <strong>' . esc_html($societe) . '</strong></p>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9;">Référence : n°' . esc_html($orderData['order_number']) . ' - ' . esc_html($orderData['date_demande']) . '</p>
                </div>

                <!-- Corps principal -->
                <div style="background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">

                    <!-- Informations client -->
                    <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #2196f3;">
                        <h3 style="margin: 0 0 10px 0; color: #1565c0; font-size: 16px;">👤 Contact</h3>
                        <p style="margin: 5px 0; font-size: 14px;"><strong>Nom :</strong> ' . esc_html($client_name) . '</p>
                        <p style="margin: 5px 0; font-size: 14px;"><strong>Email :</strong> <a href="mailto:' . esc_attr($client_email) . '" style="color: #2196f3; text-decoration: none;">' . esc_html($client_email) . '</a></p>
                        <p style="margin: 5px 0; font-size: 14px;"><strong>Société :</strong> ' . esc_html($societe) . '</p>
                    </div>

                    <!-- Lien vers le récapitulatif -->
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #ffc107; text-align: center;">
                        <p style="margin: 0 0 10px 0; font-size: 14px; color: #856404;"><strong>📋 Accéder au récapitulatif complet</strong></p>
                        <a href="' . esc_url($orderData['recap_url']) . '" style="display: inline-block; background: #4338ca; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 14px;">Voir la demande n°' . esc_html($orderData['order_number']) . '</a>
                        <p style="margin: 10px 0 0 0; font-size: 12px; color: #856404;">Ce lien est valable pendant 30 jours</p>
                    </div>

                    <!-- Contenu détaillé -->
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                        <pre style="white-space: pre-wrap; word-wrap: break-word; font-family: Consolas, Monaco, monospace; font-size: 13px; line-height: 1.6; margin: 0; color: #212529;">' . esc_html($content) . '</pre>
                    </div>

                </div>

                <!-- Pied de page -->
                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color:rgb(68, 71, 75); font-size: 14px;">
                    <p style="margin: 5px 0;">© Cenov Distribution - Tous droits réservés</p>
                    <p style="margin: 5px 0; font-size: 12px; color: #999;">Spécialiste pompes et moteurs industriels</p>
                </div>
            </div>';
            }
        }

        ?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Questions essentielles - Moteur Asynchrone Triphasé</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        line-height: 1.4;
        background: transparent;
        padding: 0;
      }

      .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        overflow: hidden;
      }

      .form-moteur-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: #fff;
        padding: 20px;
        text-align: center;
      }
      .form-moteur-header h1 {
        font-size: 2em;
        margin-bottom: 5px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      }
      .form-moteur-header p {
        font-size: 1em;
        opacity: 0.9;
      }

      .content {
        padding: 20px;
      }

      .section {
        margin-bottom: 20px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #4338ca;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      .section:hover {
        transform: translateX(5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }

      .category-title {
        background: linear-gradient(135deg, #4338ca 0%, #764ba2 100%);
        color: #fff;
        padding: 10px 15px;
        margin: -15px -15px 15px -15px;
        border-radius: 8px 8px 0 0;
        font-size: 1.1em;
        font-weight: 600;
        display: flex;
        gap: 8px;
        align-items: center;
      }

      .section-divider {
        height: 2px;
        background: linear-gradient(90deg, #4338ca 0%, #764ba2 100%);
        margin: 25px 0;
        border-radius: 2px;
      }

      .question {
        margin-bottom: 10px;
        padding: 10px;
        background: #fff;
        border-radius: 6px;
        border-left: 3px solid #764ba2;
      }
      .question strong {
        color: #2a5298;
        display: block;
        margin-bottom: 5px;
        font-size: 1em;
      }

      .annotation {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 10px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e8eaf6 100%);
        border-radius: 12px;
        font-size: 0.85em;
        color: #3d4752;
        font-weight: 500;
        border: 1px solid #d0d7de;
      }

      .info-box {
        background: #e3f2fd;
        padding: 8px;
        border-radius: 5px;
        margin-top: 5px;
        border-left: 3px solid #2196f3;
        font-size: 0.85em;
      }
      .highlight {
        background: #fff3cd;
        padding: 3px 8px;
        border-radius: 4px;
        color: #664d03;
        font-weight: 600;
      }

      .answer-field {
        margin-top: 10px;
        width: 100%;
      }
      .answer-field input,
      .answer-field textarea,
      .answer-field select {
        width: 100%;
        padding: 10px;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        font-size: 1em;
        font-family: inherit;
        transition: border-color 0.3s ease;
      }
      .answer-field input:focus,
      .answer-field textarea:focus,
      .answer-field select:focus {
        outline: none;
        border-color: #4338ca;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }
      .answer-field textarea {
        resize: vertical;
        min-height: 60px;
      }

      .radio-group,
      .checkbox-group {
        margin-top: 10px;
      }
      .radio-group label,
      .checkbox-group label {
        display: block;
        padding: 8px 12px;
        margin: 5px 0;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
      }
      .radio-group label:hover,
      .checkbox-group label:hover {
        background: #f8f9fa;
        border-color: #4338ca;
      }
      .radio-group input[type="radio"],
      .checkbox-group input[type="checkbox"] {
        margin-right: 10px;
        cursor: pointer;
      }

      /* === Boutons === */
      .button-group {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: center;
      }
      .btn {
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
      }
      .btn-primary {
        background: linear-gradient(135deg, #4338ca 0%, #764ba2 100%);
        color: #fff;
      }
      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
      }
      .btn-secondary {
        background: #fff;
        color: #4338ca;
        border: 2px solid #4338ca;
      }
      .btn-secondary:hover {
        background: #4338ca;
        color: #fff;
      }

      .required {
        color: #c92a2a;
        font-weight: bold;
        margin-left: 3px;
      }

      .form-moteur-footer {
        background: #1e3c72;
        color: #fff;
        text-align: center;
        padding: 20px;
        font-size: 0.9em;
      }

      @media (max-width: 768px) {
        .form-moteur-header h1 {
          font-size: 1.3em;
        }
        .content {
          padding: 20px;
        }
        .section {
          padding: 8px;
        }
      }

      /* ===== Popups (génériques) ===== */
      .popup-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
      }
      .popup-overlay.active {
        display: flex;
      }
      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }
      @keyframes slideDown {
        from {
          transform: translateY(-50px);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }

      .popup-content {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        max-width: 750px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
        animation: slideDown 0.4s ease;
        max-height: 90vh;
        overflow: auto;
        margin-top: 15rem;
      }
      .popup-close-x {
        position: absolute;
        top: 15px;
        right: 15px;
        background: transparent;
        border: none;
        font-size: 2em;
        color: #999;
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
      }
      .popup-close-x:hover {
        background: #f0f0f0;
        color: #333;
      }

      .popup-header {
        text-align: center;
        margin-bottom: 30px;
      }
      .popup-header h2 {
        color: #1e3c72;
        font-size: 2em;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
      }
      .popup-header p {
        color: #333;
        font-size: 1.1em;
        line-height: 1.6;
      }
      .popup-body {
        margin: 25px 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
      }
      .popup-feature {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 0;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 10px;
        border-left: 4px solid #4338ca;
      }
      @media (max-width: 600px) {
        .popup-body {
          grid-template-columns: 1fr;
        }
      }
      .popup-feature-icon {
        font-size: 1.3em;
        flex-shrink: 0;
      }
      .popup-close-btn {
        background: linear-gradient(135deg, #4338ca 0%, #764ba2 100%);
        color: #fff;
        border: none;
        padding: 15px 40px;
        font-size: 1.1em;
        font-weight: 600;
        border-radius: 10px;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      }
      .popup-close-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
      }

      /* ===== Popups spécifiques ===== */
      .popup-carcasse,
      .popup-humidite,
      .popup-ie4,
      .popup-montage {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
        z-index: 10000;
      }
      .popup-carcasse.active,
      .popup-humidite.active,
      .popup-ie4.active,
      .popup-montage.active {
        display: flex;
      }

      .popup-carcasse-content,
      .popup-humidite-content,
      .popup-ie4-content,
      .popup-montage-content {
        background: #fff;
        border-radius: 15px;
        padding: 25px;
        max-width: 520px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        position: relative;
        animation: slideDown 0.35s ease;
        max-height: 92vh;
        overflow: auto;
      }

      .popup-carcasse-header,
      .popup-humidite-header,
      .popup-ie4-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e8eaf6;
      }
      .popup-montage-content {
        max-width: 980px;
      }
      .popup-montage-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e8eaf6;
      }
      .popup-montage-close,
      .popup-humidite-close,
      .ie4-close,
      .popup-carcasse-close {
        background: linear-gradient(135deg, #4338ca 0%, #764ba2 100%);
        color: #fff;
        border: none;
        padding: 10px 22px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        margin-top: 14px;
      }

      .popup-carcasse-body,
      .popup-humidite-body,
      .popup-ie4-body {
        color: #333;
        line-height: 1.6;
      }
      .popup-carcasse-examples,
      .popup-humidite-items,
      .ie4-items {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 10px;
        margin-top: 10px;
        border-left: 4px solid #4a90e2;
      }

      /* Styles pour les catégories de montage */
      .montage-category {
        margin-bottom: 25px;
      }
      .montage-category-title {
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        color: #fff;
        padding: 12px 18px;
        border-radius: 8px;
        font-size: 1.05em;
        font-weight: 700;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(74, 144, 226, 0.2);
      }

      /* Grille d'images montage */
      .montage-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-top: 10px;
      }
      @media (max-width: 1100px) {
        .montage-grid {
          grid-template-columns: 1fr 1fr 1fr;
        }
      }
      @media (max-width: 900px) {
        .montage-grid {
          grid-template-columns: 1fr 1fr;
        }
      }
      @media (max-width: 520px) {
        .montage-grid {
          grid-template-columns: 1fr;
        }
      }
      .montage-card {
        background: #f8f9ff;
        border: 2px solid #e2e6ff;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease,
          border-color 0.15s ease;
      }
      .montage-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(102, 126, 234, 0.25);
        border-color: #4338ca;
      }
      .montage-card img {
        display: block;
        width: 100%;
        height: 180px;
        object-fit: cover;
        background: #eef2ff;
      }
      .montage-card figcaption {
        padding: 10px;
        text-align: center;
        font-weight: 700;
        color: #1e3c72;
        font-size: 0.95rem;
      }

      .ie4-note {
        margin-top: 12px;
        padding: 10px;
        background: #fff9e6;
        border-left: 4px solid #ffc107;
        border-radius: 6px;
        font-size: 0.95em;
        color: #5c4b00;
      }

      /* ======= Tableau Options + Normes ======= */
      .options-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 8px;
      }
      .options-card {
        background: #fff;
        border: 1px solid #d9d9d9;
        border-radius: 6px;
        padding: 10px 12px;
      }
      .options-card h3 {
        font-size: 1.02rem;
        margin-bottom: 6px;
        color: #2a2a2a;
        font-weight: 700;
      }
      .options-list {
        list-style: none;
      }
      .options-list li {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 4px 0;
        font-size: 0.96rem;
        color: #333;
        border-bottom: 1px solid #eee;
      }
      .options-list li:last-child {
        border-bottom: none;
      }
      .options-list input[type="checkbox"] {
        transform: translateY(2px);
      }
      .options-inline-input {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
      }
      .options-inline-input input[type="text"] {
        flex: 1 1 160px;
        min-width: 140px;
        max-width: 320px;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 0.95rem;
      }
      @media (max-width: 900px) {
        .options-grid {
          grid-template-columns: 1fr;
        }
      }

      /* ===== Groupes radio/checkbox sur UNE SEULE LIGNE ===== */
      .inline-one-line {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
        white-space: nowrap;
        overflow-x: auto;
        scrollbar-width: thin;
      }
      .inline-one-line label {
        display: inline-flex;
        align-items: center;
        margin: 0;
      }
      .inline-one-line input[type="radio"],
      .inline-one-line input[type="checkbox"] {
        margin-right: 8px;
      }

      /* ===== Frein à intégrer (détails) ===== */
      .frein-details {
        background: #f7f9ff;
        border-left: 3px solid #4a90e2;
        padding: 10px;
        border-radius: 6px;
        margin-top: 8px;
      }

      /* ===== Grille compacte pour paires Nom/Email etc. ===== */
      .two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
      }
      @media (max-width: 700px) {
        .two-col {
          grid-template-columns: 1fr;
        }
      }

      /* ===== Indication "Plaque signalétique" ===== */
      .question[data-from-plaque="true"] {
        border-left-color: #22c55e; /* vert */
        position: relative;
      }
      .badge-plaque {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 8px;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.82em;
        font-weight: 600;
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
      }
      .badge-plaque i {
        font-style: normal;
      }
      .mode-plaque .question[data-from-plaque="true"] {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
      }
      .plaque-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 14px;
        background: #f0fdf4;
        border: 1px solid #dcfce7;
        color: #065f46;
        padding: 10px 12px;
        border-radius: 8px;
        margin: 14px 0 18px;
      }
      .plaque-bar label {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
      }
      .plaque-bar input[type="checkbox"] {
        transform: translateY(1px);
      }
      .only-plaque .question:not([data-from-plaque="true"]) {
        display: none;
      }
    </style>
  </head>
  <body>
    <!-- Popup Humidité -->
    <div class="popup-humidite" id="humiditePopup">
      <div class="popup-humidite-content">
        <button class="popup-close-x" onclick="closeHumiditePopup">
          &times;
        </button>
        <div class="popup-humidite-header">
          <span style="font-size: 1.6em">💧</span>
          <h2>Humidité relative (HR)</h2>
        </div>
        <div class="popup-humidite-body">
          <p style="margin-bottom: 12px">
            L'humidité relative indique le niveau d'humidité dans l'air ambiant
            :
          </p>
          <div class="popup-humidite-items">
            <div class="popup-humidite-item">
              <span>•</span><strong>HR &lt; 60%</strong
              ><span>Atmosphère sèche</span>
            </div>
            <div class="popup-humidite-item">
              <span>•</span><strong>HR 60-80%</strong
              ><span>Atmosphère normale</span>
            </div>
            <div class="popup-humidite-item">
              <span>•</span><strong>HR 80-95%</strong
              ><span>Atmosphère humide</span>
            </div>
            <div class="popup-humidite-item">
              <span>•</span><strong>HR &gt; 95%</strong
              ><span>Atmosphère très humide</span>
            </div>
          </div>
        </div>
        <button class="popup-montage-close" onclick="closeHumiditePopup()">
          Compris !
        </button>
      </div>
    </div>

    <!-- Popup IE4 - NOUVELLE VERSION AVEC IMAGE -->
    <div class="popup-ie4" id="ie4Popup">
      <div class="popup-ie4-content">
        <button class="popup-close-x" onclick="closeIE4Popup()">&times;</button>
        <div class="popup-ie4-header">
          <span style="font-size: 1.4em">♻️</span>
          <h2>IE (indice énérgétique) – Plage de puissance</h2>
        </div>

        <div class="popup-ie4-body">
          <p style="margin-bottom: 15px">
            La classe de rendement <strong>IE4</strong> (super premium) désigne
            des moteurs très efficaces, réduisant les pertes et la consommation.
            Voici les échéances réglementaires (applicable depuis le 1er juillet
            2023) pour les différentes classes d'efficacité énergétique :
          </p>

          <div style="text-align: center; margin: 20px 0">
            <img
              src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/moteurs-echeance-2023.webp"
              alt="Échéances réglementaires moteurs IE"
              style="
                max-width: 100%;
                height: auto;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
              "
            />
          </div>

          <!-- ✅ SECTION AJOUTÉE : explication des plages de puissance -->
          <div class="ie4-details" style="margin: 15px 0">
            <h3 style="margin-bottom: 8px">Plages de puissance par classe :</h3>
            <ul style="margin-left: 18px; margin-bottom: 10px">
              <li>
                <strong>IE2</strong> → Environ
                <strong>0,12 à 0,75 kW</strong> (faible puissance)
              </li>
              <li>
                <strong>IE3</strong> → Environ
                <strong>0,75 à 1 000 kW</strong> (moyenne à forte puissance)
              </li>
              <li>
                <strong>IE4</strong> → Environ
                <strong>75 à 200 kW</strong> (très haute efficacité)
              </li>
            </ul>
            <p>
              <em>À retenir :</em> IE4 offre le meilleur rendement mais sur une
              plage de puissance plus restreinte, tandis que IE3 couvre la
              majorité des usages et constitue la norme minimale actuelle en
              Europe.
            </p>
          </div>

          <button class="ie4-close" onclick="closeIE4Popup()">Compris !</button>
        </div>
      </div>
    </div>

    <!-- Popup Carcasse -->
    <div class="popup-carcasse" id="carcassePopup">
      <div class="popup-carcasse-content">
        <button class="popup-close-x" onclick="closeCarcassePopup()">
          &times;
        </button>
        <div class="popup-carcasse-header">
          <span style="font-size: 1.3em">📐</span>
          <h2>Comprendre la taille de carcasse</h2>
        </div>
        <div class="popup-carcasse-body">
          <p>
            Les chiffres correspondent à la
            <strong style="color: #1e3c72">hauteur d'axe (mm)</strong>.
          </p>
          <div class="popup-carcasse-examples">
            <div style="margin-bottom: 10px; font-weight: 600; color: #1e3c72">
              <span style="font-size: 1.1em">💡</span> Exemples :
            </div>
            <div class="popup-carcasse-example">
              <span>•</span
              ><span
                ><strong>90</strong>S = <strong>90 mm</strong> hauteur d'axe,
                longueur S</span
              >
            </div>
            <div class="popup-carcasse-example">
              <span>•</span
              ><span
                ><strong>132</strong>M = <strong>132 mm</strong> hauteur d'axe,
                longueur M</span
              >
            </div>
            <div class="popup-carcasse-example">
              <span>•</span
              ><span
                ><strong>160</strong>L = <strong>160 mm</strong> hauteur d'axe,
                longueur L</span
              >
            </div>
          </div>
        </div>
        <button class="popup-carcasse-close" onclick="closeCarcassePopup()">
          Compris !
        </button>
      </div>
    </div>

    <!-- Popup Type de montage -->
    <div class="popup-montage" id="montagePopup">
      <div class="popup-montage-content">
        <button class="popup-close-x" onclick="closeMontagePopup()">
          &times;
        </button>
        <div class="popup-montage-header">
          <span style="font-size: 1.3em">🔩</span>
          <h2>Types de montage (exemples en photos)</h2>
        </div>

        <p style="color: #333; margin-bottom: 15px; font-size: 0.95em">
          Cliquez sur une photo pour sélectionner le type de montage
          correspondant.<br />
          <small style="color: #888"
            >Classification selon norme IEC 60034-7</small
          >
        </p>

        <div class="montage-category">
          <div class="montage-category-title">🔧 Montage à pattes</div>
          <div class="montage-grid">
            <figure
              class="montage-card"
              data-value="B3"
              data-description="À pattes B3 (sol, arbre horizontal)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B3-400x284-1.webp"
                alt="Montage B3"
              />
              <figcaption>
                <strong>B3</strong><br />
                <small style="color: #666"
                  >Pattes au sol, arbre horizontal</small
                >
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="B6"
              data-description="À pattes murales B6"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B6-400x284-1.webp"
                alt="Montage B6"
              />
              <figcaption>
                <strong>B6</strong><br />
                <small style="color: #666"
                  >Pattes murales, arbre horizontal</small
                >
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="B7"
              data-description="À pattes murales B7 (arbre vertical bas)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B7-400x284-1.webp"
                alt="Montage B7"
              />
              <figcaption>
                <strong>B7</strong><br />
                <small style="color: #666"
                  >Pattes murales, arbre vertical ↓</small
                >
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="B8"
              data-description="À pattes inversées B8 (plafond)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B8-400x284-1.webp"
                alt="Montage B8"
              />
              <figcaption>
                <strong>B8</strong><br />
                <small style="color: #666">Pattes inversées au plafond</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V5"
              data-description="V5 (arbre vers le bas)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V5-400x284-1.webp"
                alt="Montage V5"
              />
              <figcaption>
                <strong>V5</strong><br />
                <small style="color: #666">Fixation murale, arbre ↓</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V6"
              data-description="V6 (arbre vers le haut)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V6-400x284-1.webp"
                alt="Montage V6"
              />
              <figcaption>
                <strong>V6</strong><br />
                <small style="color: #666">Fixation murale, arbre ↑</small>
              </figcaption>
            </figure>
          </div>
        </div>

        <div class="montage-category">
          <div class="montage-category-title">
            ⚙️ Montage à bride à trous lisses (FF)
          </div>
          <div class="montage-grid">
            <figure
              class="montage-card"
              data-value="B5"
              data-description="Bride FF B5 (arbre horizontal)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B5-400x284-1.webp"
                alt="Montage B5"
              />
              <figcaption>
                <strong>B5</strong><br />
                <small style="color: #666">Bride FF, arbre horizontal</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="B35"
              data-description="Pattes + Bride FF B35"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B35-400x284-1.webp"
                alt="Montage B35"
              />
              <figcaption>
                <strong>B35</strong><br />
                <small style="color: #666">Pattes + Bride FF</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V3"
              data-description="V3 (arbre vertical haut)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V3-400x284-1.webp"
                alt="Montage V3"
              />
              <figcaption>
                <strong>V3</strong><br />
                <small style="color: #666">Bride en bas, arbre ↑</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V1"
              data-description="V1 (arbre vertical bas)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V1-400x284-1.webp"
                alt="Montage V1"
              />
              <figcaption>
                <strong>V1</strong><br />
                <small style="color: #666">Bride en haut, arbre ↓</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V15"
              data-description="V15"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V15-400x284-1.webp"
                alt="Montage V15"
              />
              <figcaption>
                <strong>V15</strong><br />
                <small style="color: #666">fixation</small>
              </figcaption>
            </figure>
          </div>
        </div>

        <div class="montage-category">
          <div class="montage-category-title">
            🔩 Montage à bride à trous taraudés (FT)
          </div>
          <div class="montage-grid">
            <figure
              class="montage-card"
              data-value="B14"
              data-description="Bride FT B14 (arbre horizontal)"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B14-400x284-1.webp"
                alt="Montage B14"
              />
              <figcaption>
                <strong>B14</strong><br />
                <small style="color: #666">Bride FT, arbre horizontal</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="B34"
              data-description="Pattes + Bride FT B34"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-B34-400x284-1.webp"
                alt="Montage B34"
              />
              <figcaption>
                <strong>B34</strong><br />
                <small style="color: #666">Pattes + Bride FT</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V58"
              data-description="V58"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V58-400x284-1.webp"
                alt="Montage V58"
              />
              <figcaption>
                <strong>V58</strong><br />
                <small style="color: #666">fixation</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V19"
              data-description="V19"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V19-400x284-1.webp"
                alt="Montage V19"
              />
              <figcaption>
                <strong>V19</strong><br />
                <small style="color: #666">fixation</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V18"
              data-description="V18"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V18-400x284-1.webp"
                alt="Montage V18"
              />
              <figcaption>
                <strong>V18</strong><br />
                <small style="color: #666">fixation</small>
              </figcaption>
            </figure>
            <figure
              class="montage-card"
              data-value="V69"
              data-description="V69"
            >
              <img
                src="https://www.cenov-distribution.fr/wp-content/uploads/2025/11/fixation-V69-400x284-1.webp"
                alt="Montage V69"
              />
              <figcaption>
                <strong>V69</strong><br />
                <small style="color: #666">fixation</small>
              </figcaption>
            </figure>
          </div>
        </div>

        <button class="popup-montage-close" onclick="closeMontagePopup()">
          Fermer
        </button>
      </div>
    </div>

    <!-- Popup d'accueil -->
    <div class="popup-overlay" id="welcomePopup">
      <div class="popup-content">
        <button class="popup-close-x" onclick="closePopup()">&times;</button>
        <div class="popup-header">
          <h2><span>⚡</span><span>Bienvenue !</span><span>⚙️</span></h2>
          <p>
            Ce questionnaire vous guide pour choisir le moteur asynchrone
            triphasé adapté à vos besoins.
          </p>
        </div>
        <div class="popup-body">
          <div class="popup-feature">
            <div class="popup-feature-icon">📋</div>
            <div>
              <strong>Questions structurées</strong>
              <p>10 catégories couvrant les points techniques essentiels</p>
            </div>
          </div>
          <div class="popup-feature">
            <div class="popup-feature-icon">💡</div>
            <div>
              <strong>Infos plaque signalétique</strong>
              <p>Les champs identifiables sur la plaque sont indiqués</p>
            </div>
          </div>
          <div class="popup-feature">
            <div class="popup-feature-icon">🔗</div>
            <div>
              <strong>Articles explicatifs</strong>
              <p>Des liens pour approfondir chaque notion</p>
            </div>
          </div>
          <div class="popup-feature">
            <div class="popup-feature-icon">⚠️</div>
            <div>
              <strong>Champs obligatoires</strong>
              <p>
                Repérés par un
                <span style="color: #c92a2a; font-weight: bold">*</span>
              </p>
            </div>
          </div>
        </div>
        <button class="popup-close-btn" onclick="closePopup()">
          Commencer le questionnaire
        </button>
      </div>
    </div>

    <script>
      /* ==== Gestion scroll body quand popup ouverte ==== */
      function lockScroll() {
        document.body.style.overflow = "hidden";
      }
      function unlockScroll() {
        document.body.style.overflow = "";
      }

      /* ==== Helpers ouverture/fermeture ==== */
      function closePopup() {
        document.getElementById("welcomePopup").classList.remove("active");
        unlockScroll();
      }
      function openCarcassePopup() {
        document.getElementById("carcassePopup").classList.add("active");
        lockScroll();
      }
      function closeCarcassePopup() {
        document.getElementById("carcassePopup").classList.remove("active");
        unlockScroll();
      }
      function openHumiditePopup() {
        document.getElementById("humiditePopup").classList.add("active");
        lockScroll();
      }
      function closeHumiditePopup() {
        document.getElementById("humiditePopup").classList.remove("active");
        unlockScroll();
      }
      function openIE4Popup() {
        document.getElementById("ie4Popup").classList.add("active");
        lockScroll();
      }
      function closeIE4Popup() {
        document.getElementById("ie4Popup").classList.remove("active");
        unlockScroll();
      }
      function openMontagePopup() {
        document.getElementById("montagePopup").classList.add("active");
        lockScroll();
      }
      function closeMontagePopup() {
        document.getElementById("montagePopup").classList.remove("active");
        unlockScroll();
      }

      /* ==== Afficher la popup d'accueil au chargement ==== */
      window.addEventListener("load", () =>
        setTimeout(() => {
          document.getElementById("welcomePopup").classList.add("active");
          lockScroll();
        }, 500)
      );

      /* ==== Fermer en cliquant sur l'overlay ==== */
      for (const id of [
        "welcomePopup",
        "carcassePopup",
        "humiditePopup",
        "ie4Popup",
        "montagePopup",
      ]) {
        const el = document.getElementById(id);
        if (!el) continue;
        el.addEventListener("click", (e) => {
          if (e.target === el) {
            el.classList.remove("active");
            unlockScroll();
          }
        });
      }

      /* ==== Échap pour toutes ==== */
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
          for (const id of [
            "welcomePopup",
            "carcassePopup",
            "humiditePopup",
            "ie4Popup",
            "montagePopup",
          ]) {
            const el = document.getElementById(id);
            if (el) el.classList.remove("active");
          }
          unlockScroll();
        }
      });

      /* ==== Sélection par clic image dans popup montage ==== */
      document.addEventListener("DOMContentLoaded", () => {
        const cards = document.querySelectorAll(".montage-card");
        for (const card of cards) {
          card.addEventListener("click", () => {
            const value = card.dataset.value;
            const input = document.querySelector(
              `input[name="montage"][value="${value}"]`
            );
            if (input) {
              input.checked = true;
              input.dispatchEvent(new Event("change"));
            }
            closeMontagePopup();
          });
        }
      });

      /* ==== Affichages dynamiques (autre / perso) ==== */
      document.addEventListener("DOMContentLoaded", () => {
        // Vitesse "autre"
        const vAutre = document.getElementById("vitesse_autre_input");
        const refreshVitesseAutre = () => {
          const val = document.querySelector('input[name="vitesse"]:checked');
          vAutre.style.display =
            val && val.value === "autre" ? "block" : "none";
        };
        for (const r of document.querySelectorAll('input[name="vitesse"]')) {
          r.addEventListener("change", refreshVitesseAutre);
        }
        refreshVitesseAutre();

        // Température personnalisée
        const tempBox = document.getElementById("temp_personnalise_input");
        const refreshTemp = () => {
          const val = document.querySelector(
            'input[name="temperature"]:checked'
          );
          tempBox.style.display =
            val && val.value === "personnalise" ? "block" : "none";
        };
        for (const r of document.querySelectorAll('input[name="temperature"]')) {
          r.addEventListener("change", refreshTemp);
        }
        refreshTemp();

        // Altitude personnalisée
        const altBox = document.getElementById("alt_personnalise_input");
        const refreshAlt = () => {
          const val = document.querySelector('input[name="altitude"]:checked');
          altBox.style.display =
            val && val.value === "personnalise" ? "block" : "none";
        };
        for (const r of document.querySelectorAll('input[name="altitude"]')) {
          r.addEventListener("change", refreshAlt);
        }
        refreshAlt();

        // Refroidissement "autre"
        const refAutre = document.getElementById("refroidissement_autre");
        const refreshRef = () => {
          const val = document.querySelector(
            'input[name="refroidissement"]:checked'
          );
          refAutre.style.display =
            val && val.value === "autre" ? "block" : "none";
        };
        for (const r of document.querySelectorAll('input[name="refroidissement"]')) {
          r.addEventListener("change", refreshRef);
        }
        refreshRef();

        // Secteur "Autre"
        const secteurSelect = document.getElementById("secteur");
        const secteurAutre = document.getElementById("secteur_autre_wrap");
        if (secteurSelect && secteurAutre) {
          const toggleSecteur = () =>
            (secteurAutre.style.display =
              secteurSelect.value === "autre" ? "block" : "none");
          secteurSelect.addEventListener("change", toggleSecteur);
          toggleSecteur();
        }

        // ATEX - Détails si "Oui"
        const atexDetails = document.getElementById("atex_details");
        const refreshAtex = () => {
          const val = document.querySelector('input[name="atex"]:checked');
          atexDetails.style.display =
            val && val.value === "oui" ? "block" : "none";
        };
        for (const r of document.querySelectorAll('input[name="atex"]')) {
          r.addEventListener("change", refreshAtex);
        }
        refreshAtex();

        // ATEX - Gestion des sections Gaz et Poussières
        const atexGaz = document.getElementById("atex_gaz");
        const atexPoussieres = document.getElementById("atex_poussieres");
        const atexSectionGaz = document.getElementById("atex_section_gaz");
        const atexSectionPoussieres = document.getElementById(
          "atex_section_poussieres"
        );

        function refreshAtexSections() {
          if (atexGaz && atexSectionGaz) {
            atexSectionGaz.style.display = atexGaz.checked ? "block" : "none";
          }
          if (atexPoussieres && atexSectionPoussieres) {
            atexSectionPoussieres.style.display = atexPoussieres.checked
              ? "block"
              : "none";
          }
        }

        if (atexGaz) atexGaz.addEventListener("change", refreshAtexSections);
        if (atexPoussieres)
          atexPoussieres.addEventListener("change", refreshAtexSections);
        refreshAtexSections();

        // ========== FREIN À INTÉGRER - NOUVELLE LOGIQUE ==========
        const freinDetails = document.getElementById("frein_details");
        const freinTensionCA = document.getElementById("frein_tension_ca");
        const freinTensionCC = document.getElementById("frein_tension_cc");
        const freinTensionAutre = document.getElementById(
          "frein_tension_autre_input"
        );

        function refreshFrein() {
          const sel = document.querySelector('input[name="has_frein"]:checked');
          const val = sel ? sel.value : "non";
          if (val === "oui") {
            if (freinDetails) freinDetails.style.display = "block";
          } else {
            if (freinDetails) freinDetails.style.display = "none";
            for (const i of document.querySelectorAll('input[name="frein_type"]')) {
              i.checked = false;
            }
            for (const i of document.querySelectorAll('input[name="frein_tension"]')) {
              i.checked = false;
            }
            if (freinTensionCA) freinTensionCA.style.display = "none";
            if (freinTensionCC) freinTensionCC.style.display = "none";
            if (freinTensionAutre) freinTensionAutre.style.display = "none";
          }
        }

        function refreshFreinType() {
          const sel = document.querySelector(
            'input[name="frein_type"]:checked'
          );
          const val = sel ? sel.value : "";
          for (const i of document.querySelectorAll('input[name="frein_tension"]')) {
            i.checked = false;
          }
          if (freinTensionAutre) freinTensionAutre.style.display = "none";
          if (val === "ca") {
            if (freinTensionCA) freinTensionCA.style.display = "flex";
            if (freinTensionCC) freinTensionCC.style.display = "none";
          } else if (val === "cc") {
            if (freinTensionCA) freinTensionCA.style.display = "none";
            if (freinTensionCC) freinTensionCC.style.display = "flex";
            const dc24 = document.querySelector(
              'input[name="frein_tension"][value="24"]'
            );
            if (dc24) dc24.checked = true;
          } else {
            if (freinTensionCA) freinTensionCA.style.display = "none";
            if (freinTensionCC) freinTensionCC.style.display = "none";
          }
        }

        function refreshFreinTension() {
          const sel = document.querySelector(
            'input[name="frein_tension"]:checked'
          );
          const val = sel ? sel.value : "";
          if (freinTensionAutre) {
            freinTensionAutre.style.display =
              val === "autre_ca" || val === "autre_cc" ? "block" : "none";
          }
        }

        for (const r of document.querySelectorAll('input[name="has_frein"]')) {
          r.addEventListener("change", refreshFrein);
        }
        for (const r of document.querySelectorAll('input[name="frein_type"]')) {
          r.addEventListener("change", refreshFreinType);
        }
        for (const r of document.querySelectorAll('input[name="frein_tension"]')) {
          r.addEventListener("change", refreshFreinTension);
        }

        refreshFrein();
        refreshFreinType();
        refreshFreinTension();
      });

      /* ===== Indicateur "Plaque signalétique" ===== */
      document.addEventListener("DOMContentLoaded", () => {
        // Injecte un badge sur chaque question marquée
        for (const q of document.querySelectorAll('.question[data-from-plaque="true"]')) {
          if (!q.querySelector(".badge-plaque")) {
            const b = document.createElement("span");
            b.className = "badge-plaque";
            b.title =
              "Cette info se lit directement sur la plaque signalétique du moteur";
            b.innerHTML = "<i>📇</i> Plaque";
            const strong = q.querySelector("strong");
            (strong || q).appendChild(b);
          }
        }

        // Toggles
        const hasPlate = document.getElementById("hasPlate");
        const filterPlate = document.getElementById("filterPlate");

        const applyModes = () => {
          document.body.classList.toggle("mode-plaque", !!hasPlate.checked);
          filterPlate.disabled = !hasPlate.checked;
          document.body.classList.toggle(
            "only-plaque",
            !!hasPlate.checked && !!filterPlate.checked
          );
        };

        if (hasPlate && filterPlate) {
          hasPlate.addEventListener("change", applyModes);
          filterPlate.addEventListener("change", applyModes);
          applyModes();
        }
      });
    </script>

    <div class="container">
      <div class="form-moteur-header">
        <h1 style="color: white;">⚡ Questions Essentielles</h1>
        <p>Pour la vente d'un moteur asynchrone triphasé</p>
      </div>

      <form method="POST" action="" enctype="multipart/form-data" id="formMoteur">
        <?php wp_nonce_field('cenov_moteur_form', 'cenov_moteur_nonce'); ?>

        <?php if (!empty($result)) : ?>
        <div style="margin: 20px; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: 500; text-align: center; background: #f8d7da; border: 2px solid #dc3545; color: #721c24;">
            <?php echo $result; ?>
        </div>
        <?php endif; ?>

      <div class="content">
        <!-- Barre d'options plaque -->
        <div class="plaque-bar" id="plaqueBar">
          <span>📇 Champs disponibles sur la plaque signalétique :</span>
          <label><input type="checkbox" id="hasPlate" /> J'ai la plaque</label>
          <label title="Masquer les autres champs">
            <input type="checkbox" id="filterPlate" disabled /> N'afficher que
            ces champs
          </label>
          <span class="badge-plaque"><i>📇</i> Indicateur</span>
        </div>

        <!-- 1. APPLICATION -->
        <div class="section">
          <div class="category-title">⚙️ CARACTÉRISTIQUES DE L'APPLICATION</div>
          <h2 style="color: #c92a2a; margin-bottom: 10px; font-size: 0.95em">
            Informations obligatoires <span class="required">*</span>
          </h2>

          <div class="question" data-from-plaque="true">
            <strong
              >🔋 Quelle est la puissance requise ?
              <span class="required">*</span></strong
            >
            <span class="annotation">en kW</span>
            <div class="info-box">
              <em>Puissance mécanique nécessaire pour entraîner la machine.</em>
            </div>
            <div class="answer-field">
              <input
                type="number"
                name="puissance_kw"
                step="0.1"
                placeholder="Entrez la puissance en kW..."
                required
              />
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <strong
              >🔄 Quelle est la vitesse de rotation ?
              <span class="required">*</span></strong
            >
            <span class="annotation">en tr/min</span>
            <div class="info-box">
              <em>La vitesse dépend du nombre de pôles.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label class="radio-item"
                ><input type="radio" name="vitesse" value="2900" /><span
                  >2 pôles → ~3000 tr/min (2900 réels)</span
                ></label
              >
              <label class="radio-item"
                ><input type="radio" name="vitesse" value="1450" /><span
                  >4 pôles → ~1500 tr/min (1450 réels)</span
                ></label
              >
              <label class="radio-item"
                ><input type="radio" name="vitesse" value="960" /><span
                  >6 pôles → ~1000 tr/min (960 réels)</span
                ></label
              >
              <label class="radio-item"
                ><input type="radio" name="vitesse" value="720" /><span
                  >8 pôles → ~750 tr/min (720 réels)</span
                ></label
              >
              <label class="radio-item"
                ><input type="radio" name="vitesse" value="autre" /><span
                  >Autre vitesse (à préciser)</span
                ></label
              >
            </div>

            <div
              class="answer-field"
              id="vitesse_autre_input"
              style="display: none; margin-top: 8px"
            >
              <input
                type="number"
                name="vitesse_autre_rpm"
                placeholder="Entrez la vitesse en tr/min..."
              />
            </div>
          </div>
        </div>

        <!-- 2. ALIMENTATION -->
        <div class="section">
          <div class="category-title">⚡ ALIMENTATION ÉLECTRIQUE</div>

          <h2 style="color: #c92a2a; margin-bottom: 10px; font-size: 0.95em">
            Informations obligatoires <span class="required">*</span>
          </h2>

          <div class="question" data-from-plaque="true">
            <label for="tension"
              ><strong
                >⚡ Quelle est votre tension d'alimentation ?
                <span class="required">*</span></strong
              ></label
            ><span class="annotation">en V</span>
            <div class="info-box">
              <em
                >Le moteur doit être compatible avec la tension disponible.</em
              >
            </div>
            <div class="answer-field">
              <select
                id="tension"
                name="tension"
                required
                onchange="document.getElementById('tension_autre').style.display = (this.value==='autre') ? 'block' : 'none';"
              >
                <option value="">-- Sélectionnez --</option>
                <option value="230/400">230/400 V</option>
                <option value="400/690">400/690 V</option>
                <option value="autre">Autre (à préciser)</option>
              </select>
            </div>
            <div
              class="answer-field"
              id="tension_autre"
              style="display: none; margin-top: 10px"
            >
              <input type="text" name="tension_autre" placeholder="Précisez la tension..." />
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <label for="frequence"
              ><strong
                >📊 Quelle est la fréquence du réseau ?
                <span class="required">*</span></strong
              ></label
            ><span class="annotation">en Hz</span>
            <div class="answer-field">
              <select id="frequence" name="frequence" required>
                <option value="">-- Sélectionnez --</option>
                <option value="50" selected>50 Hz</option>
                <option value="60">60 Hz</option>
              </select>
            </div>
          </div>
        </div>

        <!-- 3. INSTALLATION TECHNIQUE -->
        <div class="section">
          <div class="category-title">🔧 INSTALLATION TECHNIQUE</div>

          <h2 style="color: #c92a2a; margin-bottom: 10px; font-size: 0.95em">
            Informations obligatoires <span class="required">*</span>
          </h2>

          <div class="question" data-from-plaque="true">
            <strong
              >🔧 Quel type de montage ? <span class="required">*</span></strong
            >
            <div class="info-box">
              <em
                >Le mode de fixation détermine l'installation sur la
                machine.</em
              >
            </div>

            <div style="margin-top: 10px">
              <button
                type="button"
                onclick="openMontagePopup()"
                style="
                  background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                  color: #fff;
                  border: none;
                  padding: 8px 16px;
                  border-radius: 8px;
                  font-size: 0.95em;
                  font-weight: 600;
                  cursor: pointer;
                  box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
                "
              >
                📸 Voir les types en images
              </button>
            </div>

            <div class="radio-group inline-one-line" style="margin-top: 10px">
              <label
                ><input type="radio" name="montage" value="B3" /><span
                  >B3</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B5" /><span
                  >B5</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B14" /><span
                  >B14</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B35" /><span
                  >B35</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B34" /><span
                  >B34</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B6" /><span
                  >B6</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B7" /><span
                  >B7</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="B8" /><span
                  >B8</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V5" /><span
                  >V5</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V58" /><span
                  >V58</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V3" /><span
                  >V3</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V6" /><span
                  >V6</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V1" /><span
                  >V1</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V15" /><span
                  >V15</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V19" /><span
                  >V19</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V18" /><span
                  >V18</span
                ></label
              >
              <label
                ><input type="radio" name="montage" value="V69" /><span
                  >V69</span
                ></label
              >
            </div>
          </div>

          <h2 style="color: #4338ca; margin: 15px 0 10px; font-size: 0.95em">
            Informations complémentaires
          </h2>

          <div class="question" data-from-plaque="true">
            <label for="taille_carcasse"><strong>📏 Contraintes d'encombrement ?</strong></label>
            <div class="info-box">
              <em>Dimensions max autorisées pour l'intégration.</em>
            </div>
            <div style="margin-top: 12px">
              <button
                onclick="openCarcassePopup()"
                style="
                  background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                  color: #fff;
                  border: none;
                  padding: 10px 20px;
                  border-radius: 8px;
                  font-size: 0.95em;
                  font-weight: 600;
                  cursor: pointer;
                  display: inline-flex;
                  gap: 8px;
                  align-items: center;
                  transition: 0.3s;
                  box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
                "
              >
                <span style="font-size: 1.2em">📐</span
                ><span>Comprendre la taille de carcasse - Hauteur d'axe</span>
              </button>
            </div>
            <div class="answer-field">
              <select
                id="taille_carcasse"
                name="taille_carcasse"
                onchange="document.getElementById('taille_carcasse_autre').style.display = (this.value==='autre') ? 'block' : 'none';"
              >
                <option value="">-- Taille de carcasse --</option>
                <option>56</option>
                <option>63</option>
                <option>71</option>
                <option>80</option>
                <option>90S</option>
                <option>90L</option>
                <option>100L</option>
                <option>112M</option>
                <option>132S</option>
                <option>132M</option>
                <option>160M</option>
                <option>160L</option>
                <option>180M</option>
                <option>180L</option>
                <option>200L</option>
                <option>225S</option>
                <option>225M</option>
                <option>250M</option>
                <option>280S</option>
                <option>280M</option>
                <option>315S</option>
                <option>315M</option>
                <option>315L</option>
                <option value="autre">Autre (à préciser)</option>
              </select>
            </div>
            <div
              class="answer-field"
              id="taille_carcasse_autre"
              style="display: none; margin-top: 10px"
            >
              <input
                type="text"
                name="taille_carcasse_autre"
                placeholder="Précisez la taille de la carcasse..."
              />
            </div>
          </div>

          <div class="question">
            <strong>🏗️ Matière de la carcasse ?</strong>
            <div class="info-box">
              <em>Impacte poids, robustesse et dissipation thermique.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="matiere" value="alu" /><span
                  >Aluminium</span
                ></label
              >
              <label
                ><input type="radio" name="matiere" value="fonte" /><span
                  >Fonte</span
                ></label
              >
              <label
                ><input type="radio" name="matiere" value="acier" /><span
                  >Acier</span
                ></label
              >
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <strong>❄️ Mode de refroidissement</strong>
            <div class="info-box">
              <em>Détermine l'évacuation de la chaleur.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input
                  type="radio"
                  name="refroidissement"
                  value="IC411"
                  checked
                /><span
                  ><strong style="color: #4338ca"
                    >✓ Standard : IC411 (TEFC, auto-ventilé)</strong
                  ></span
                ></label
              >
              <label
                ><input
                  type="radio"
                  name="refroidissement"
                  value="IC416"
                /><span>IC416 (ventilation forcée)</span></label
              >
              <label
                ><input
                  type="radio"
                  name="refroidissement"
                  value="autre"
                /><span>Autre (à préciser)</span></label
              >
            </div>
            <div
              class="answer-field"
              id="refroidissement_autre"
              style="display: none; margin-top: 10px"
            >
              <input
                type="text"
                name="refroidissement_autre"
                placeholder="Précisez le mode de refroidissement..."
              />
            </div>
          </div>
        </div>

        <div class="section-divider"></div>

        <!-- 5. CONDITIONS D'UTILISATION -->
        <div class="section">
          <div class="category-title">⏱️ CONDITIONS D'UTILISATION</div>

          <h2 style="color: #c92a2a; margin-bottom: 10px; font-size: 0.95em">
            Informations obligatoires <span class="required">*</span>
          </h2>

          <div class="question" data-from-plaque="true">
            <strong
              >⏱️ Régime de fonctionnement ?
              <span class="required">*</span></strong
            >
            <div class="info-box">
              <em>Influe sur le dimensionnement thermique.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="regime" value="S1" checked /><span
                  ><strong style="color: #4338ca"
                    >✓ Standard : S1 (continu)</strong
                  ></span
                ></label
              >
              <label
                ><input type="radio" name="regime" value="S2" /><span
                  >S2 (temporaire)</span
                ></label
              >
              <label
                ><input type="radio" name="regime" value="S3-S10" /><span
                  >S3 à S10 (intermittent)</span
                ></label
              >
            </div>
          </div>
        </div>

        <!-- 6. ENVIRONNEMENT -->
        <div class="section">
          <div class="category-title">🌍 ENVIRONNEMENT D'INSTALLATION</div>

          <h2 style="color: #c92a2a; margin-bottom: 10px; font-size: 0.95em">
            Informations obligatoires <span class="required">*</span>
          </h2>

          <div class="question" data-from-plaque="true">
            <strong
              >🛡️ Indice de protection requis ?
              <span class="required">*</span></strong
            >
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="ip" value="IP55" checked /><span
                  ><strong style="color: #4338ca"
                    >✓ Standard : IP55
                  </strong></span
                ></label
              >
              <label
                ><input type="radio" name="ip" value="IP56" /><span
                  ><strong style="color: #4338ca">IP56</strong> – Forte
                  projection d'eau</span
                ></label
              >
              <label
                ><input type="radio" name="ip" value="IP65" /><span
                  ><strong style="color: #4338ca">IP65/IP66</strong> – Jets
                  puissants / lavage</span
                ></label
              >
              <label
                ><input type="radio" name="ip" value="IP67" /><span
                  ><strong style="color: #4338ca">IP67/IP68</strong> –
                  Immersion</span
                ></label
              >
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <strong>🌡️ Température ambiante ?</strong
            ><span class="annotation">min/max</span>
            <div class="info-box">
              <em
                >Températures extrêmes → isolation / refroidissement adapté.</em
              >
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input
                  type="radio"
                  name="temperature"
                  value="standard"
                  checked
                /><span
                  ><strong style="color: #4338ca"
                    >✓ Standard : -20°C à +40°C</strong
                  ></span
                ></label
              >
              <label
                ><input
                  type="radio"
                  name="temperature"
                  value="personnalise"
                /><span>✏️ Plage personnalisée</span></label
              >
            </div>
            <div
              class="answer-field"
              id="temp_personnalise_input"
              style="display: none; margin-top: 15px"
            >
              <div class="two-col">
                <div>
                  <label
                    style="
                      display: block;
                      margin-bottom: 5px;
                      color: #333;
                      font-weight: 500;
                    "
                    >Min (°C) :
                    <input type="number" name="temp_min" placeholder="Ex: -25" />
                  </label>
                </div>
                <div>
                  <label
                    style="
                      display: block;
                      margin-bottom: 5px;
                      color: #333;
                      font-weight: 500;
                    "
                    >Max (°C) :
                    <input type="number" name="temp_max" placeholder="Ex: +45" />
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <strong>⛰️ Altitude d'installation ?</strong>
            <div class="info-box">
              <em>&gt; 1000 m : air moins dense → possible déclassement.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input
                  type="radio"
                  name="altitude"
                  value="0-1000"
                  checked
                /><span
                  ><strong style="color: #4338ca"
                    >✓ Standard : 0 à 1000 m</strong
                  ></span
                ></label
              >
              <label
                ><input
                  type="radio"
                  name="altitude"
                  value="personnalise"
                /><span>✏️ Altitude personnalisée</span></label
              >
            </div>
            <div
              class="answer-field"
              id="alt_personnalise_input"
              style="display: none; margin-top: 15px"
            >
              <label
                style="
                  display: block;
                  margin-bottom: 5px;
                  color: #333;
                  font-weight: 500;
                "
                >Altitude précise (m) :
                <input type="number" name="altitude_custom" placeholder="Ex : 1500" />
              </label>
            </div>
          </div>

          <div class="question">
            <strong>☁️ Atmosphère agressive ?</strong>
            <div class="info-box">
              <em>Saline, chimique, humide, poussiéreuse…</em>
            </div>
            <div class="checkbox-group inline-one-line">
              <label
                ><input type="checkbox" name="atmos_saline" value="1" /><span
                  >Saline (bord de mer, offshore)</span
                ></label
              >
              <label
                ><input type="checkbox" name="atmos_humide" value="1" /><span
                  >Humide : &gt;95% HR</span
                ></label
              >
              <label
                ><input type="checkbox" name="atmos_chimique" value="1" /><span
                  >Chimique (vapeurs corrosives)</span
                ></label
              >
              <label
                ><input type="checkbox" name="atmos_poussiere" value="1" /><span
                  >Poussiéreuse</span
                ></label
              >
            </div>
            <div style="margin-top: 8px">
              <button
                onclick="openHumiditePopup()"
                style="
                  background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                  color: #fff;
                  border: none;
                  padding: 8px 16px;
                  border-radius: 6px;
                  font-size: 0.9em;
                  font-weight: 600;
                  cursor: pointer;
                  display: inline-flex;
                  gap: 6px;
                  align-items: center;
                  transition: 0.3s;
                  box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
                "
              >
                <span style="font-size: 1.1em">💧</span
                ><span>Comprendre l'humidité relative (HR)</span>
              </button>
            </div>
          </div>

          <div class="question">
            <strong>💥 Zone ATEX ?</strong>
            <div class="info-box">
              <em>En zone ATEX, moteur certifié requis.</em>
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="atex" value="non" checked /><span
                  >Non</span
                ></label
              >
              <label
                ><input type="radio" name="atex" value="oui" /><span
                  >Oui</span
                ></label
              >
            </div>

            <div
              id="atex_details"
              style="
                display: none;
                margin-top: 20px;
                padding: 20px;
                background: #fff9e6;
                border-left: 4px solid #ff9800;
                border-radius: 8px;
              "
            >
              <div style="margin-bottom: 20px">
                <strong
                  style="color: #1e3c72; display: block; margin-bottom: 10px"
                  >🔥 Type d'atmosphère explosive</strong
                >
                <div
                  class="info-box"
                  style="
                    background: #fff;
                    border-left: 3px solid #ff9800;
                    margin-bottom: 10px;
                  "
                >
                  <em>Sélection multiple (Gaz et/ou Poussières)</em>
                </div>
                <div class="checkbox-group inline-one-line">
                  <label
                    ><input
                      type="checkbox"
                      name="atex_type_gaz"
                      value="1"
                      id="atex_gaz"
                    /><span>Gaz (Zone 1 ou 2)</span></label
                  >
                  <label
                    ><input
                      type="checkbox"
                      name="atex_type_poussieres"
                      value="1"
                      id="atex_poussieres"
                    /><span>Poussières (Zone 21 ou 22)</span></label
                  >
                </div>
              </div>

              <!-- Section GAZ -->
              <div
                id="atex_section_gaz"
                style="
                  display: none;
                  padding: 15px;
                  background: #e3f2fd;
                  border-radius: 8px;
                  margin-bottom: 20px;
                  border-left: 4px solid #2196f3;
                "
              >
                <h3
                  style="color: #1565c0; margin-bottom: 15px; font-size: 1.1em"
                >
                  ⚡ Configuration pour atmosphère GAZ
                </h3>

                <div style="margin-bottom: 15px">
                  <strong
                    style="color: #1e3c72; display: block; margin-bottom: 10px"
                    >📍 Zone de classification (Gaz)</strong
                  >
                  <div
                    class="info-box"
                    style="
                      background: #fff;
                      border-left: 3px solid #2196f3;
                      margin-bottom: 10px;
                    "
                  >
                    <strong>Zone 1 = 2G</strong> | <strong>Zone 2 = 3G</strong>
                  </div>
                  <div class="radio-group inline-one-line">
                    <label
                      ><input
                        type="radio"
                        name="atex_zone_gaz"
                        value="1"
                      /><span>Zone 1 (2G)</span></label
                    >
                    <label
                      ><input
                        type="radio"
                        name="atex_zone_gaz"
                        value="2"
                      /><span>Zone 2 (3G)</span></label
                    >
                  </div>
                </div>

                <div style="margin-bottom: 15px">
                  <label for="atex_groupe_gaz"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >⚗️ Groupe de gaz</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_groupe_gaz" name="atex_groupe_gaz">
                      <option value="">-- Sélectionnez --</option>
                      <option value="IIA">IIA (propane, butane...)</option>
                      <option value="IIB">IIB (éthylène...)</option>
                      <option value="IIC">IIC (hydrogène, acétylène...)</option>
                    </select>
                  </div>
                </div>

                <div style="margin-bottom: 15px">
                  <label for="atex_temp_gaz"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >🌡️ Classe de température (T)</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_temp_gaz" name="atex_temp_gaz">
                      <option value="">-- Sélectionnez --</option>
                      <option value="T1">T1 (≤ 450°C)</option>
                      <option value="T2">T2 (≤ 300°C)</option>
                      <option value="T3">T3 (≤ 200°C)</option>
                      <option value="T4">T4 (≤ 135°C)</option>
                      <option value="T5">T5 (≤ 100°C)</option>
                      <option value="T6">T6 (≤ 85°C)</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label for="atex_protection_gaz"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >🛡️ Type de protection (Gaz)</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_protection_gaz" name="atex_protection_gaz">
                      <option value="">-- Sélectionnez --</option>
                      <option value="Ex d">
                        Ex d (enveloppe antidéflagrante)
                      </option>
                      <option value="Ex e">Ex e (sécurité augmentée)</option>
                      <option value="Ex de">Ex de (combinaison d + e)</option>
                      <option value="Ex n">Ex n (non étincelant)</option>
                      <option value="Ex p">Ex p (surpression interne)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Section POUSSIÈRES -->
              <div
                id="atex_section_poussieres"
                style="
                  display: none;
                  padding: 15px;
                  background: #fff3e0;
                  border-radius: 8px;
                  margin-bottom: 20px;
                  border-left: 4px solid #ff9800;
                "
              >
                <h3
                  style="color: #e65100; margin-bottom: 15px; font-size: 1.1em"
                >
                  💨 Configuration pour atmosphère POUSSIÈRES
                </h3>

                <div style="margin-bottom: 15px">
                  <strong
                    style="color: #1e3c72; display: block; margin-bottom: 10px"
                    >📍 Zone de classification (Poussières)</strong
                  >
                  <div
                    class="info-box"
                    style="
                      background: #fff;
                      border-left: 3px solid #ff9800;
                      margin-bottom: 10px;
                    "
                  >
                    <strong>Zone 21</strong> | <strong>Zone 22</strong>
                  </div>
                  <div class="radio-group inline-one-line">
                    <label
                      ><input
                        type="radio"
                        name="atex_zone_poussieres"
                        value="21"
                      /><span>Zone 21</span></label
                    >
                    <label
                      ><input
                        type="radio"
                        name="atex_zone_poussieres"
                        value="22"
                      /><span>Zone 22</span></label
                    >
                  </div>
                </div>

                <div style="margin-bottom: 15px">
                  <label for="atex_type_poussieres"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >⚗️ Type de poussières</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_type_poussieres" name="atex_type_poussieres">
                      <option value="">-- Sélectionnez --</option>
                      <option value="IIIB">
                        IIIB (poussières conductrices)
                      </option>
                      <option value="IIIC">
                        IIIC (poussières non conductrices)
                      </option>
                    </select>
                  </div>
                </div>

                <div style="margin-bottom: 15px">
                  <label for="atex_temp_poussieres"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >🌡️ Température maximale de surface</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_temp_poussieres" name="atex_temp_poussieres">
                      <option value="">-- Sélectionnez --</option>
                      <option value="T1">T1 (≤ 450°C)</option>
                      <option value="T2">T2 (≤ 300°C)</option>
                      <option value="T3">T3 (≤ 200°C)</option>
                      <option value="T4">T4 (≤ 135°C)</option>
                      <option value="T5">T5 (≤ 100°C)</option>
                      <option value="T6">T6 (≤ 85°C)</option>
                    </select>
                  </div>
                </div>

                <div>
                  <label for="atex_protection_poussieres"
                    ><strong
                      style="color: #1e3c72; display: block; margin-bottom: 10px"
                      >🛡️ Type de protection (Poussières)</strong
                    ></label
                  >
                  <div class="answer-field">
                    <select id="atex_protection_poussieres" name="atex_protection_poussieres">
                      <option value="">-- Sélectionnez --</option>
                      <option value="Ex t">
                        Ex t (protection contre poussières)
                      </option>
                      <option value="Ex p">Ex p (surpression interne)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div
                style="
                  background: #e3f2fd;
                  padding: 12px;
                  border-radius: 6px;
                  border-left: 3px solid #2196f3;
                "
              >
                <strong
                  style="
                    color: #1565c0;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    margin-bottom: 8px;
                  "
                >
                  <span style="font-size: 1.2em">📚</span>
                  <span>Besoin d'aide ?</span>
                </strong>
                <p style="margin: 0; font-size: 0.9em; color: #333">
                  <a
                    href="https://www.cenov-distribution.fr/moteur-atex-guide-de-selection/"
                    target="_blank"
                    rel="noopener noreferrer"
                    style="
                      color: #1565c0;
                      text-decoration: none;
                      font-weight: 600;
                      border-bottom: 2px solid #2196f3;
                      transition: all 0.3s ease;
                    "
                  >
                    Consultez notre article détaillé sur ATEX
                  </a>
                  pour comprendre les zones, classifications et exigences.
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="section-divider"></div>

        <!-- 7. PERFORMANCES ÉNERGÉTIQUES -->
        <div class="section">
          <div class="category-title">♻️ PERFORMANCES ÉNERGÉTIQUES</div>

          <div class="question" data-from-plaque="true">
            <strong>♻️ Exigence de rendement ?</strong>
            <div class="info-box">
              <em
                >IE3 minimum en Europe pour la plupart des moteurs ; IE4 réduit
                encore la consommation.</em
              >
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="rendement" value="IE2" /><span
                  >IE2</span
                ></label
              >
              <label
                ><input type="radio" name="rendement" value="IE3" /><span
                  ><span class="highlight">IE3</span> (mini Europe)</span
                ></label
              >
              <label
                ><input type="radio" name="rendement" value="IE4" /><span
                  >IE4 (super premium)</span
                ></label
              >
              <label
                ><input type="radio" name="rendement" value="IE5" /><span
                  >IE5 (ultra premium)</span
                ></label
              >
            </div>
            <div style="margin-top: 12px">
              <button
                onclick="openIE4Popup()"
                style="
                  background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
                  color: #fff;
                  border: none;
                  padding: 10px 20px;
                  border-radius: 8px;
                  font-size: 0.95em;
                  font-weight: 600;
                  cursor: pointer;
                  display: inline-flex;
                  gap: 8px;
                  align-items: center;
                  transition: 0.3s;
                  box-shadow: 0 2px 8px rgba(74, 144, 226, 0.3);
                "
              >
                <span style="font-size: 1.2em">♻️</span
                ><span
                  >Comprendre les indices énergétiques (IE) – plage de
                  puissance</span
                >
              </button>
            </div>
          </div>

          <div class="question" data-from-plaque="true">
            <strong>🔥 Classe d'isolation thermique ?</strong>
            <div class="info-box">
              <em
                >Température max des bobinages : B (130°C), F (155°C), H
                (180°C).</em
              >
            </div>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="isolation" value="B" /><span
                  >Classe B</span
                ></label
              >
              <label
                ><input type="radio" name="isolation" value="F" checked /><span
                  >Classe F</span
                ></label
              >
              <label
                ><input type="radio" name="isolation" value="H" /><span
                  >Classe H</span
                ></label
              >
            </div>
          </div>
        </div>

        <div class="section-divider"></div>

        <!-- 9 & 10. TABLEAU OPTIONS + NORMES -->
        <div class="section">
          <div class="category-title">📦 OPTIONS / 📜 NORMES</div>

          <div class="options-grid">
            <!-- Équipements électriques -->
            <div class="options-card">
              <h3>Équipements électriques</h3>
              <ul class="options-list">
                <li>
                  <input type="checkbox" id="rechauf" name="rechaufage" value="1" /><label for="rechauf"
                    >Résistances de réchauffage</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="ptc" name="sonde_thermique_ptc" value="1" /><label for="ptc"
                    >Sondes thermiques PTC/PT100</label
                  >
                </li>
              </ul>
            </div>

            <!-- Équipements mécaniques -->
            <div class="options-card">
              <h3>Équipements mécaniques</h3>
              <ul class="options-list">
                <li style="display: block; width: 100%">
                  <strong>Frein à intégrer ?</strong>
                  <div
                    class="radio-group inline-one-line"
                    style="margin-top: 8px"
                  >
                    <label
                      ><input
                        type="radio"
                        name="has_frein"
                        value="non"
                        checked
                      />Non</label
                    >
                    <label
                      ><input
                        type="radio"
                        name="has_frein"
                        value="oui"
                      />Oui</label
                    >
                  </div>

                  <div
                    id="frein_details"
                    class="frein-details"
                    style="display: none"
                  >
                    <div
                      style="
                        font-weight: 600;
                        margin-bottom: 6px;
                        color: #1e3c72;
                      "
                    >
                      Type de frein
                    </div>
                    <div
                      class="radio-group inline-one-line"
                      style="margin-top: 0"
                    >
                      <label
                        ><input
                          type="radio"
                          name="frein_type"
                          value="cc"
                        /><span>Courant continu (DC)</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_type"
                          value="ca"
                        /><span>Courant alternatif (AC)</span></label
                      >
                    </div>

                    <div
                      style="
                        font-weight: 600;
                        margin: 10px 0 6px;
                        color: #1e3c72;
                      "
                    >
                      Tension des freins
                    </div>

                    <div
                      id="frein_tension_ca"
                      class="radio-group inline-one-line"
                      style="display: none; margin-top: 0"
                    >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="230"
                        /><span>230 V</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="400"
                        /><span>400 V</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="autre_ca"
                        /><span>Autre (à préciser)</span></label
                      >
                    </div>

                    <div
                      id="frein_tension_cc"
                      class="radio-group inline-one-line"
                      style="display: none; margin-top: 0"
                    >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="24"
                          checked
                        /><span>24 V DC</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="48"
                        /><span>48 V DC</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="110"
                        /><span>110 V DC</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="180"
                        /><span>180 V DC</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="205"
                        /><span>205 V DC</span></label
                      >
                      <label
                        ><input
                          type="radio"
                          name="frein_tension"
                          value="autre_cc"
                        /><span>Autre (à préciser)</span></label
                      >
                    </div>

                    <div
                      class="answer-field"
                      id="frein_tension_autre_input"
                      style="display: none; margin-top: 10px"
                    >
                      <input type="text" name="frein_tension_autre" placeholder="Précisez la tension…" />
                    </div>
                  </div>
                </li>

                <li>
                  <div class="options-inline-input">
                    <input type="checkbox" id="codeurInc" name="codeur_incremental" value="1" /><label
                      for="codeurInc"
                      >Codeur incrémental (résolution :
                    </label>
                    <input type="text" name="codeur_incremental_resolution" placeholder="_______" aria-label="Résolution du codeur incrémental" /><span>)</span>
                  </div>
                </li>
                <li>
                  <input type="checkbox" id="codeurAbs" name="codeur_absolu" value="1" /><label for="codeurAbs"
                    >Codeur absolu</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="ventForcee" name="ventilation_forcee" value="1" /><label
                    for="ventForcee"
                    >Ventilation forcée indépendante</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="roulRenf" name="roulements_renforces" value="1" /><label for="roulRenf"
                    >Roulements renforcés / isolés</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="roulnu" name="roulements_nu" value="1" /><label for="roulnu"
                    >Roulements NU (poulie/courroie)</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="graissage" name="graissage_permanent" value="1" /><label for="graissage"
                    >Graissage permanent</label
                  >
                </li>

                <li>
                  <div class="options-inline-input">
                    <input type="checkbox" id="autresAcc" name="autres_accessoires" value="1" /><label
                      for="autresAcc"
                      >Autres accessoires :</label
                    >
                    <input type="text" name="autres_accessoires_details" placeholder="________________" aria-label="Précisez les autres accessoires" />
                  </div>
                </li>
              </ul>
            </div>

            <!-- Protection & revêtement -->
            <div class="options-card">
              <h3>Protection et revêtement</h3>
              <ul class="options-list">
                <li>
                  <input type="checkbox" id="tropical" name="traitement_tropical" value="1" /><label for="tropical"
                    >Traitement tropicalisation</label
                  >
                </li>
                <li>
                  <div class="options-inline-input">
                    <input type="checkbox" id="ral" name="couleur_ral" value="1" /><label for="ral"
                      >Couleur spécifique RAL :</label
                    >
                    <input type="text" name="couleur_ral_code" placeholder="________" aria-label="Code couleur RAL" />
                  </div>
                </li>
              </ul>
            </div>

            <!-- Normes & certifications -->
            <div class="options-card">
              <h3>Normes et certifications</h3>
              <ul class="options-list">
                <li>
                  <input type="checkbox" id="CE" name="certification_ce" value="1" checked /><label for="CE"
                    >Certification CE</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="UL" name="certification_ul" value="1" /><label for="UL"
                    >Certification UL/CSA</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="EAC" name="certification_eac" value="1" /><label for="EAC"
                    >Certification EAC (Russie)</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="CCC" name="certification_ccc" value="1" /><label for="CCC"
                    >Certification CCC (Chine)</label
                  >
                </li>
                <li>
                  <input type="checkbox" id="marine" name="certification_marine" value="1" /><label for="marine"
                    >Marine (DNV, ABS, Lloyd's…)</label
                  >
                </li>
                <li>
                  <div class="options-inline-input">
                    <input type="checkbox" id="normeAutre" name="certification_autre" value="1" /><label
                      for="normeAutre"
                      >Autre :</label
                    >
                    <input type="text" name="certification_autre_details" placeholder="______________________" aria-label="Précisez l'autre norme ou certification" />
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- ========= VOUS CONNAÎTRE DAVANTAGE ========= -->
        <div class="section">
          <div class="category-title">👤 VOUS CONNAÎTRE DAVANTAGE</div>

          <div class="question">
            <strong>🏢 Société <span class="required">*</span></strong>
            <div class="answer-field">
              <input type="text" name="societe" placeholder="Nom de votre société" required />
            </div>
          </div>

          <div class="two-col">
            <div class="question">
              <strong>🙍‍♂️ Nom & Prénom <span class="required">*</span></strong>
              <div class="answer-field">
                <input type="text" name="nom_prenom" placeholder="Ex : Jean Dupont" required />
              </div>
            </div>
            <div class="question">
              <strong>📧 Email <span class="required">*</span></strong>
              <div class="answer-field">
                <input type="email" name="email" placeholder="nom@domaine.com" required />
              </div>
            </div>
          </div>

          <div class="two-col">
            <div class="question">
              <strong>📱 Téléphone</strong>
              <div class="answer-field">
                <input type="tel" name="telephone" placeholder="+33 ..." />
              </div>
            </div>
            <div class="question">
              <strong>📍 Ville / Pays</strong>
              <div class="answer-field">
                <input type="text" name="ville_pays" placeholder="Ex : Lyon, France" />
              </div>
            </div>
          </div>

          <div class="question">
            <label for="fonction"><strong>🧑‍💼 Fonction</strong></label>
            <div class="answer-field">
              <select id="fonction" name="fonction">
                <option value="">-- Sélectionnez --</option>
                <option>Maintenance</option>
                <option>Bureau d'études</option>
                <option>Achat</option>
                <option>Production / Exploitation</option>
                <option>Direction</option>
              </select>
            </div>
          </div>

          <div class="question">
            <label for="budget"><strong>🧾 Budget estimatif</strong></label>
            <div class="answer-field">
              <select id="budget" name="budget">
                <option value="">-- Sélectionnez --</option>
                <option>&lt; 1 000 €</option>
                <option>1 000 – 5 000 €</option>
                <option>5 000 – 20 000 €</option>
                <option>&gt; 20 000 €</option>
              </select>
            </div>
          </div>

          <div class="question">
            <strong>🔢 Quantité prévue</strong>
            <div class="answer-field">
              <input type="number" name="quantite" min="1" step="1" placeholder="Ex : 3" />
            </div>
          </div>

          <div class="question">
            <strong>📅 Délai souhaité</strong>
            <div class="radio-group inline-one-line">
              <label
                ><input type="radio" name="delai" value="2 jours" /><span>
                  &gt; de 2 jours ouvrés</span
                ></label
              >
              <label
                ><input type="radio" name="delai" value="1 semaine" /><span>
                  &gt; 1 semaine</span
                ></label
              >
              <label
                ><input type="radio" name="delai" value="1-2 semaines" /><span
                  >1–2 semaines</span
                ></label
              >
              <label
                ><input type="radio" name="delai" value="2-4 semaines" /><span
                  >2–4 semaines</span
                ></label
              >
              <label
                ><input type="radio" name="delai" value="> 1 mois" /><span>
                  &gt; 1 mois</span
                ></label
              >
            </div>
          </div>

          <div class="question">
            <strong>📝 Brève description du besoin</strong>
            <div class="answer-field">
              <textarea
                name="description_besoin"
                placeholder="Décrivez votre projet, contraintes, normes, documents utiles…"
              ></textarea>
            </div>
          </div>

          <div class="question">
            <label for="fichier_plaque"
              ><strong
                >📎 Joindre un fichier (plaque signalétique par exemple)</strong
              ></label
            >
            <div class="answer-field"><input type="file" id="fichier_plaque" name="fichier_plaque" aria-label="Joindre un fichier (plaque signalétique par exemple)" /></div>
          </div>
        </div>

        <!-- Boutons -->
        <div class="button-group">
          <button type="button" class="btn btn-secondary" onclick="window.print()">
            🖨️ Imprimer
          </button>
          <button type="submit" name="submit_moteur" value="1" class="btn btn-primary">
            📧 Envoyer ma demande
          </button>
        </div>
      </div>

      </form>

      <div class="form-moteur-footer">
        <p>📋 Document technique - Moteurs asynchrones triphasés</p>
        <p>Guide de questionnaire pour la sélection optimale</p>
      </div>
    </div>
  </body>
</html>
        <?php
        
        return ob_get_clean();
    }
}

// Appel de la fonction pour l'extension Insert PHP Code Snippet (xyz-ips)
// Utilisé via le shortcode: [xyz-ips snippet="FormMoteur"]
echo cenovFormulaireMoteurAsyncDisplay();
?>
