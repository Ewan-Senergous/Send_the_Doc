<?php
if (!function_exists('displayProductCouplingAttributes')) {
    function displayProductCouplingAttributes($couplingNumber) {
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
            
            // Filtrer les attributs dont le nom contient le numéro de couplage spécifié
            $filteredAttributes = array();
            $searchPattern = 'n°' . $couplingNumber;
            
            foreach ($attributes as $attributeKey => $attribute) {
                // Récupérer le nom de l'attribut
                $attributeName = wc_attribute_label($attribute->get_name());
                
                // Vérifier si le nom contient le numéro de couplage
                if (strpos($attributeName, $searchPattern) !== false) {
                    $filteredAttributes[$attributeKey] = $attribute;
                }
            }
            
            // Si aucun attribut ne correspond au critère
            if (empty($filteredAttributes)) {
                $output = '<p class="warning-message">Ce produit n\'a pas d\'attribut dont le nom contient "' . $searchPattern . '".</p>';
            } else {
                // Afficher le tableau des attributs filtrés avec les nouvelles classes
                $output = '<div class="tech-specs-container">';
                $output .= '<h3 class="tech-specs-title">Caractéristiques techniques (couplage n°' . $couplingNumber . ')</h3>';
                $output .= '<table class="product-table-2">';
                $output .= '<tr class="product-row-1">';
                $output .= '<th class="product-header-1">Nom</th>';
                $output .= '<th class="product-header-1">Valeur</th>';
                $output .= '<th class="product-header-1">Nom</th>';
                $output .= '<th class="product-header-1">Valeur</th>';
                $output .= '</tr>';
                
                // Préparation des attributs
                $attributesArray = array_values($filteredAttributes);
                $totalAttributes = count($attributesArray);
                $rowCount = 0;
                
                // Parcourir les attributs par paires
                for ($i = 0; $i < $totalAttributes; $i += 2) {
                    $rowClass = ($rowCount % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
                    $output .= '<tr class="' . $rowClass . '">';
                    
                    // Premier attribut de la paire
                    $attribute1 = $attributesArray[$i];
                    $originalName1 = wc_attribute_label($attribute1->get_name());
                    $searchStr = ' (couplage n°' . $couplingNumber . ')';
                    $name1 = str_replace($searchStr, '', $originalName1);
                    $name1 = str_replace('(couplage n°' . $couplingNumber . ')', '', $name1);
                    
                    if ($attribute1->is_taxonomy()) {
                        $values1 = wc_get_product_terms($product->get_id(), $attribute1->get_name(), ['fields' => 'names']);
                        $value1 = implode(', ', $values1);
                    } else {
                        $values1 = $attribute1->get_options();
                        $value1 = implode(', ', $values1);
                    }
                    
                    $output .= '<td class="product-cell-1">' . esc_html($name1) . '</td>';
                    $output .= '<td class="product-cell-1"><strong>' . esc_html($value1) . '</strong></td>';
                    
                    // Deuxième attribut de la paire (s'il existe)
                    if ($i + 1 < $totalAttributes) {
                        $attribute2 = $attributesArray[$i + 1];
                        $originalName2 = wc_attribute_label($attribute2->get_name());
                        $name2 = str_replace($searchStr, '', $originalName2);
                        $name2 = str_replace('(couplage n°' . $couplingNumber . ')', '', $name2);
                        
                        if ($attribute2->is_taxonomy()) {
                            $values2 = wc_get_product_terms($product->get_id(), $attribute2->get_name(), ['fields' => 'names']);
                            $value2 = implode(', ', $values2);
                        } else {
                            $values2 = $attribute2->get_options();
                            $value2 = implode(', ', $values2);
                        }
                        
                        $output .= '<td class="product-cell-1">' . esc_html($name2) . '</td>';
                        $output .= '<td class="product-cell-1"><strong>' . esc_html($value2) . '</strong></td>';
                    } else {
                        // Cellules vides si nombre impair d'attributs
                        $output .= '<td class="product-cell-1"></td>';
                        $output .= '<td class="product-cell-1"></td>';
                    }
                    
                    $output .= '</tr>';
                    $rowCount++;
                }
                
                $output .= '</table>';
                $output .= '</div>';
            }
        }
        
        // Afficher le contenu
        echo $output;
        
        return ob_get_clean();
    }
}

// Fonction spécifique pour les attributs n°1 (pour maintenir la compatibilité)
if (!function_exists('displayProductN1Attributes')) {
    function displayProductN1Attributes() {
        return displayProductCouplingAttributes(1);
    }
}

// Fonction spécifique pour les attributs n°2
if (!function_exists('displayProductN2Attributes')) {
    function displayProductN2Attributes() {
        return displayProductCouplingAttributes(2);
    }
}

// Fonction spécifique pour les attributs n°3
if (!function_exists('displayProductN3Attributes')) {
    function displayProductN3Attributes() {
        return displayProductCouplingAttributes(3);
    }
}

// Fonction spécifique pour les attributs n°4
if (!function_exists('displayProductN4Attributes')) {
    function displayProductN4Attributes() {
        return displayProductCouplingAttributes(4);
    }
}

// Fonction pour afficher tous les tableaux de couplage disponibles
if (!function_exists('displayAllProductCouplingAttributes')) {
    function displayAllProductCouplingAttributes() {
        ob_start();
        
        $output = '';
        
        // Afficher les tableaux pour les couplages 1 à 4
        for ($i = 1; $i <= 4; $i++) {
            $couplingOutput = displayProductCouplingAttributes($i);
            if (strpos($couplingOutput, 'warning-message') === false && strpos($couplingOutput, 'error-message') === false) {
                $output .= $couplingOutput;
            }
        }
        
        if (empty($output)) {
            echo '<p class="warning-message">Aucun attribut de couplage trouvé pour ce produit.</p>';
        } else {
            echo $output;
        }
        
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

// Utilisation par défaut - Afficher tous les tableaux de couplage
echo displayAllProductCouplingAttributes();
?>