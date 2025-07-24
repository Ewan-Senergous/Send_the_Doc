// Ajouter un onglet Documentation technique
if (!function_exists('ajouter_onglet_documentation_technique')) {
    function ajouter_onglet_documentation_technique($tabs) {
        global $product;
        
        // Vérifier si le produit a au moins un type de documentation
        $documentation_url = $product->get_attribute('documentation-technique');
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

// Contenu de l'onglet Documentation technique
if (!function_exists('contenu_onglet_documentation_technique')) {
    function contenu_onglet_documentation_technique() {
        global $product;
        
        $documentation_url = $product->get_attribute('documentation-technique');
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
            
            .doc-header h3 {
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
                font-size: 0.9em;
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
        echo '<h3>Documentation disponible</h3>';
        echo '<p>Téléchargez les documents techniques pour ce produit</p>';
        echo '</div>';
        
        echo '<div class="download-links">';
        
        // Documentation technique principale
        if (!empty($documentation_url) && filter_var($documentation_url, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($documentation_url) . '" target="_blank" class="download-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo 'Documentation technique';
            echo '</a>';
        }
        
        // Vue éclatée
        if (!empty($vue_eclatee) && filter_var($vue_eclatee, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($vue_eclatee) . '" target="_blank" class="download-link vue-eclatee-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo 'Vue éclatée';
            echo '</a>';
        }
        
        // Manuel d'utilisation
        if (!empty($manuel_utilisation) && filter_var($manuel_utilisation, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($manuel_utilisation) . '" target="_blank" class="download-link manuel-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo 'Manuel d\'utilisation';
            echo '</a>';
        }
        
        // Datasheet
        if (!empty($datasheet) && filter_var($datasheet, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($datasheet) . '" target="_blank" class="download-link datasheet-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo 'Datasheet';
            echo '</a>';
        }
        
        // Manuel de réparation
        if (!empty($manuel_reparation) && filter_var($manuel_reparation, FILTER_VALIDATE_URL)) {
            $has_documentation = true;
            echo '<a href="' . esc_url($manuel_reparation) . '" target="_blank" class="download-link repair-link">';
            echo '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="download-icon"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>';
            echo 'Manuel de réparation';
            echo '</a>';
        }
        
        echo '</div>';
        
        // Si aucune documentation n'est disponible
        if (!$has_documentation) {
            echo '<div class="no-documentation">';
            echo '<p>Aucune documentation technique disponible pour ce produit.</p>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}