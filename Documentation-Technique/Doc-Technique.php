// Ajouter un onglet Documentation technique
if (!function_exists('ajouter_onglet_documentation_technique')) {
    function ajouter_onglet_documentation_technique($tabs) {
        global $product;
        
        // Vérifier si le produit a au moins un type de documentation
        $documentation_url = $product->get_attribute('catalogue');
        $vue_eclatee = $product->get_attribute('vue-eclatee');
        $manuel_utilisation = $product->get_attribute('manuel-dutilisation');
        $datasheet = $product->get_attribute('datasheet');
        $manuel_reparation = $product->get_attribute('manuel-de-reparation');
        
        if (!empty($documentation_url) || !empty($vue_eclatee) || !empty($manuel_utilisation) || 
            !empty($datasheet) || !empty($manuel_reparation)) {
            $tabs['documentation_technique'] = array(
                'title'    => 'Documentation technique',
                'priority' => 25,
                'callback' => 'contenu_onglet_documentation_technique'
            );
        }
        
        return $tabs;
    }
    add_filter('woocommerce_product_tabs', 'ajouter_onglet_documentation_technique');
}

// Fonction pour extraire un titre intelligent depuis une URL de fichier
if (!function_exists('extraire_titre_document')) {
    function extraire_titre_document($url, $type_document) {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $type_document;
        }

        // Extraire le nom de fichier depuis l'URL
        $nom_fichier = basename(parse_url($url, PHP_URL_PATH));

        // Enlever l'extension
        $nom_sans_extension = preg_replace('/\.[^.]+$/', '', $nom_fichier);

        // Supprimer les dates en fin de nom (format jour.mois.année)
        $nom_sans_extension = preg_replace('/_Em-\d{2}\.\d{2}\.\d{4}$/', '', $nom_sans_extension);
        $nom_sans_extension = preg_replace('/_Em$/', '', $nom_sans_extension);

        // Garder les codes de langue car ils sont informatifs

        // Détecter si le type de document est déjà dans le nom de fichier
        $types_detectes = [
            'catalogue' => 'Catalogue',
            'datasheet' => 'Datasheet',
            'manuel' => 'Manuel',
            'vue-eclatee' => 'Vue éclatée',
            'reparation' => 'Manuel de réparation'
        ];

        $nom_nettoye = $nom_sans_extension;
        $type_detecte = null;

        foreach ($types_detectes as $pattern => $label) {
            if (stripos(strtolower($nom_sans_extension), $pattern) !== false) {
                // Retirer le type du nom pour garder seulement la partie principale
                $nom_nettoye = preg_replace('/[-_]?' . preg_quote($pattern, '/') . '[-_]?/i', '', $nom_sans_extension);
                $type_detecte = $label;
                break;
            }
        }

        // Nettoyer les caractères indésirables
        $nom_nettoye = preg_replace('/[-_]+/', '-', $nom_nettoye);
        $nom_nettoye = trim($nom_nettoye, '-_');
        $nom_nettoye = str_replace('_', ' ', $nom_nettoye);

        // Si on a détecté un type dans le nom, l'utiliser, sinon utiliser le type par défaut
        $type_final = $type_detecte ? $type_detecte : $type_document;

        // Retourner le format "Type - Nom" si on a un nom, sinon juste le type
        if (!empty($nom_nettoye) && strlen($nom_nettoye) > 2) {
            return $type_final . ' - ' . $nom_nettoye;
        }

        return $type_final;
    }
}

// Contenu de l'onglet Documentation technique
if (!function_exists('contenu_onglet_documentation_technique')) {
    function contenu_onglet_documentation_technique() {
        global $product;
        
        $documentation_url = $product->get_attribute('catalogue');
        $vue_eclatee = $product->get_attribute('vue-eclatee');
        $manuel_utilisation = $product->get_attribute('manuel-dutilisation');
        $datasheet = $product->get_attribute('datasheet');
        $manuel_reparation = $product->get_attribute('manuel-de-reparation');
        
        echo '<div class="documentation-technique-container">';
        echo '<style>
            
            .doc-header {
                margin-bottom: 20px;
                padding: 15px;
                background: #f3f4f6;
                border-left: 4px solid #0066cc;
                border-radius: 4px;
            }
            
            .doc-header h2 {
                margin: 0 0 8px 0;
                color: #0066cc;
                font-size: 1.3em;
            }
            
            .doc-header p {
                margin: 0;
                color: #6c757d;
                font-size: 0.95em;
            }
            
            .download-links {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 15px;
            }
            
            .download-link {
                display: inline-block;
                background: #0066cc;
                color: white;
                padding: 12px 20px;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                transition: all 0.3s;
                flex: 1;
                min-width: 150px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .download-link:hover {
                background: #0052a3;
                color: white;
                text-decoration: none;
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }
            
            /* Couleurs spécifiques pour chaque type de document */
            .vue-eclatee-link {
                background: #7e22ce;
            }
            .vue-eclatee-link:hover {
                background: #6b21a8;
            }
            
            .manuel-link {
                background: #15803d;
            }
            .manuel-link:hover {
                background: #166534;
            }
            
            .datasheet-link {
                background: #111827;
            }
            .datasheet-link:hover {
                background: #030712;
            }
            
            .repair-link {
                background: #e31206;
            }
            .repair-link:hover {
                background: #c10e04;
            }
            
            .download-icon {
                vertical-align: middle;
                margin-right: 8px;
            }
            
            .no-documentation {
                text-align: center;
                padding: 30px;
                color: #6c757d;
                font-style: italic;
                background: #f8f9fa;
                border-radius: 6px;
                border: 1px solid #e9ecef;
            }
            
            @media (max-width: 768px) {
                .download-links {
                    flex-direction: column;
                }
                
                .download-link {
                    min-width: 100%;
                    margin-bottom: 8px;
                }
            }
        </style>';
        
        $has_documentation = false;
        
        echo '<div class="doc-header">';
        echo '<h2>Documentation disponible – Téléchargement</h2>';
        echo '<p>Téléchargez les documents techniques pour ce produit</p>';
        echo '</div>';
        
        echo '<div class="download-links">';
        
        // Catalogue principal
        if (!empty($documentation_url) && filter_var($documentation_url, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($documentation_url) . '" target="_blank" class="download-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo extraire_titre_document($documentation_url, 'Catalogue');
            echo '</a>';
        }
        
        // Vue éclatée
        if (!empty($vue_eclatee) && filter_var($vue_eclatee, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($vue_eclatee) . '" target="_blank" class="download-link vue-eclatee-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo extraire_titre_document($vue_eclatee, 'Vue éclatée');
            echo '</a>';
        }
        
        // Manuel d'utilisation
        if (!empty($manuel_utilisation) && filter_var($manuel_utilisation, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($manuel_utilisation) . '" target="_blank" class="download-link manuel-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo extraire_titre_document($manuel_utilisation, 'Manuel d\'utilisation');
            echo '</a>';
        }
        
        // Manuel de réparation
        if (!empty($manuel_reparation) && filter_var($manuel_reparation, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($manuel_reparation) . '" target="_blank" class="download-link repair-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo extraire_titre_document($manuel_reparation, 'Manuel de réparation');
            echo '</a>';
        }
        
        // Datasheet
        if (!empty($datasheet) && filter_var($datasheet, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($datasheet) . '" target="_blank" class="download-link datasheet-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo extraire_titre_document($datasheet, 'Datasheet');
            echo '</a>';
        }
        
        echo '</div>';
        
        // Si aucune documentation n'est disponible
        if (!$has_documentation) {
            echo '<div class="no-documentation">';
            echo '<p>Aucune documentation disponible pour ce produit.</p>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}