<?php
if (!function_exists('displayProductN1Attributes')) {
    function displayProductN1Attributes() {
        ob_start();
        $output = '';
        $isValid = true;
        
        if (!function_exists('is_product') || !is_product()) {
            $output = '<p class="error-message">Ce snippet doit être utilisé sur une page produit.</p>';
            $isValid = false;
        } else {
            // Récupérer le produit de manière fiable
            $product = wc_get_product(get_the_ID());
            
            if (!$product) {
                $output = '<p class="error-message">Produit non trouvé.</p>';
                $isValid = false;
            } elseif (empty($product->get_attributes())) {
                $output = '<p class="warning-message">Ce produit n\'a pas d\'attributs.</p>';
                $isValid = false;
            }
        }
        
        if ($isValid) {
            // Récupérer les attributs
            $attributes = $product->get_attributes();
            
            // Filtrer les attributs dont le nom contient "n°1"
            $n1Attributes = array();
            
            foreach ($attributes as $attributeKey => $attribute) {
                // Récupérer le nom de l'attribut
                $attributeName = wc_attribute_label($attribute->get_name());
                
                // Vérifier si le nom contient "n°1"
                if (strpos($attributeName, 'n°1') !== false) {
                    $n1Attributes[$attributeKey] = $attribute;
                }
            }
            
            // Si aucun attribut ne correspond au critère
            if (empty($n1Attributes)) {
                $output = '<p class="warning-message">Ce produit n\'a pas d\'attribut dont le nom contient "n°1".</p>';
            } else {
                // Afficher le tableau des attributs filtrés avec les nouvelles classes
                $output = '<div class="tech-specs-container">';
                $output .= '<h3 class="tech-specs-title">Caractéristiques techniques</h3>';
                $output .= '<table class="product-table-2">';
                $output .= '<tr class="product-row-1">';
                $output .= '<th class="product-header-1">Nom</th>';
                $output .= '<th class="product-header-1">Valeur</th>';
                $output .= '</tr>';
                
                $rowCount = 0;
                foreach ($n1Attributes as $attribute) {
                    // Récupérer le nom
                    $originalName = wc_attribute_label($attribute->get_name());
                    $name = str_replace(' (couplage n°1)', '', $originalName);
                    $name = str_replace(' (couplage n°1)', '', $name);
                    
                    // Récupérer la valeur
                    if ($attribute->is_taxonomy()) {
                        $values = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                        $value = implode(', ', $values);
                    } else {
                        $values = $attribute->get_options();
                        $value = implode(', ', $values);
                    }
                    
                    // Alterner les lignes selon leur index
                    $rowClass = ($rowCount % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
                    
                    $output .= '<tr class="' . $rowClass . '">';
                    $output .= '<td class="product-cell-1">' . esc_html($name) . '</td>';
                    $output .= '<td class="product-cell-1"><strong>' . esc_html($value) . '</strong></td>';
                    $output .= '</tr>';
                    
                    $rowCount++;
                }
                
                $output .= '</table>';
                $output .= '</div>';
            }
        }
        
        // Afficher le contenu
        echo $output;
        
        ?>
        <style>
            .error-message {
                color: red;
            }
            .warning-message {
                color: orange;
            }
            .tech-specs-container {
                margin: 20px 0;
            }
            .tech-specs-title {
                margin-bottom: 15px;
            }
        </style>
        <?php
        
        return ob_get_clean();
    }
}

// Exécution de la fonction
echo displayProductN1Attributes();
?>