<?php
// Fonctions pour les attributs de roulements
if (!function_exists('displayBearingAttributes')) {
    function displayBearingAttributes() {
        ob_start();
        
        if (!function_exists('is_product') || !is_product() || !($product = wc_get_product(get_the_ID()))) {
            echo '<p class="error-message">Produit non trouvé ou page invalide.</p>';
            return ob_get_clean();
        }
        
        if (empty($product->get_attributes())) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attributs.</p>';
            return ob_get_clean();
        }
        
        $bearingAttributes = getBearingAttributes($product);
        
        if (empty($bearingAttributes)) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attribut de roulement.</p>';
            return ob_get_clean();
        }
        
        echo '<div class="bearing-attributes attributes-hidden-outside-tab" style="display: none;">';
        renderBearingTable($bearingAttributes);
        echo '</div>';
        
        return ob_get_clean();
    }
}

if (!function_exists('getBearingAttributes')) {
    function getBearingAttributes($product) {
        $attributes = $product->get_attributes();
        $bearingAttributes = [];
        
        // Définition des attributs par leurs identifiants
        $attributeKeys = [
            'cote_accouplement' => [
                'roulement-cote-acccouplement' => 'Roulement côté accouplement',
                'joint-roulement-ca' => 'Joint roulement côté accouplement',
                'att0000116' => 'Intervalle de graissage roulement côté accouplement',
                'att0000118' => 'Quantité de graissage roulement côté accouplement'
            ],
            'cote_oppose' => [
                'roulement-cote-opa' => 'Roulement côté opposé accouplement',
                'joint-roulement-cote-opa' => 'Joint roulement côté opposé accouplement',
                'att0000115' => 'Intervalle de graissage roulement côté opposé accouplement',
                'att0000117' => 'Quantité de graissage roulement côté opposé accouplement'
            ],
            'general' => [
                'graisse-des-roulements' => 'Graisse des roulements'
            ]
        ];
        
        foreach ($attributes as $attributeKey => $attribute) {
            $taxonomyName = $attribute->get_name();
            
            // Récupérer l'ID de l'attribut en nettoyant le nom de la taxonomie
            $attributeId = strtolower(preg_replace('/^pa_/', '', $taxonomyName));
            
            foreach ($attributeKeys as $side => $sideAttributes) {
                foreach ($sideAttributes as $attrId => $attrLabel) {
                    if ($attributeId === $attrId) {
                        $value = $attribute->is_taxonomy()
                            ? implode(', ', wc_get_product_terms($product->get_id(), $taxonomyName, ['fields' => 'names']))
                            : implode(', ', $attribute->get_options());
                        
                        $bearingAttributes[$side][$attrLabel] = $value;
                        break;
                    }
                }
            }
        }
        
        return $bearingAttributes;
    }
}

if (!function_exists('renderBearingTable')) {
    function renderBearingTable($bearingAttributes) {
        echo '<div class="tech-specs-container">';
        echo '<h3 class="tech-specs-title">Caractéristiques des roulements</h3>';
        
        // Tableau pour Desktop
        echo '<table class="product-table-3 desktop-table">';
        
        // En-tête du tableau desktop
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1" style="width:33.33%"></th>';
        echo '<th class="product-header-1" style="width:33.33%">Côté accouplement</th>';
        echo '<th class="product-header-1" style="width:33.33%">Côté opposé accouplement</th>';
        echo '</tr>';
        
        // Définir les données à afficher dans l'ordre
        $rowsToDisplay = [
            ['key' => 'Roulement', 'label' => 'Roulement'],
            ['key' => 'Joint roulement', 'label' => 'Joint roulement'],
            ['key' => 'Intervalle de graissage roulement', 'label' => 'Intervalle de graissage roulement'],
            ['key' => 'Quantité de graissage roulement', 'label' => 'Quantité de graissage roulement'],
            ['key' => 'Graisse des roulements', 'label' => 'Graisse des roulements']
        ];
        
        // Corps du tableau desktop
        $rowIndex = 0;
        foreach ($rowsToDisplay as $row) {
            $rowClass = ($rowIndex % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
            $cote_accouplement_key = $row['key'] . ' côté accouplement';
            $cote_oppose_key = $row['key'] . ' côté opposé accouplement';
            
            echo '<tr class="' . $rowClass . '">';
            echo '<td class="product-cell-1">' . esc_html($row['label']) . '</td>';
            
            if ($row['key'] === 'Graisse des roulements') {
                $value = isset($bearingAttributes['general']['Graisse des roulements'])
                    ? $bearingAttributes['general']['Graisse des roulements']
                    : '-';
                echo '<td class="product-cell-1" colspan="2" style="text-align: center !important;"><strong>' . esc_html($value) . '</strong></td>';
            } else {
                $cote_accouplement_value = isset($bearingAttributes['cote_accouplement'][$cote_accouplement_key])
                    ? $bearingAttributes['cote_accouplement'][$cote_accouplement_key]
                    : '-';
                $cote_oppose_value = isset($bearingAttributes['cote_oppose'][$cote_oppose_key])
                    ? $bearingAttributes['cote_oppose'][$cote_oppose_key]
                    : '-';
                
                echo '<td class="product-cell-1"><strong>' . esc_html($cote_accouplement_value) . '</strong></td>';
                echo '<td class="product-cell-1"><strong>' . esc_html($cote_oppose_value) . '</strong></td>';
            }
            
            echo '</tr>';
            $rowIndex++;
        }
        
        echo '</table>';
        
        // Nouveau tableau pour Mobile - Version compacte du tableau desktop
        echo '<table class="product-table-3 mobile-table bearing-mobile-table">';
        
        // En-tête du tableau mobile avec taille réduite
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1" style="width:30%"></th>';
        echo '<th class="product-header-1" style="width:35%">Côté accouplement</th>';
        echo '<th class="product-header-1" style="width:35%">Côté opposé</th>';
        echo '</tr>';
        
        // Corps du tableau mobile - format compact
        $rowIndex = 0;
        foreach ($rowsToDisplay as $row) {
            $rowClass = ($rowIndex % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
            $cote_accouplement_key = $row['key'] . ' côté accouplement';
            $cote_oppose_key = $row['key'] . ' côté opposé accouplement';
            
            echo '<tr class="' . $rowClass . '">';
            
            // Utilisation du nom complet au lieu d'un label abrégé
            echo '<td class="product-cell-1">' . esc_html($row['label']) . '</td>';
            
            if ($row['key'] === 'Graisse des roulements') {
                $value = isset($bearingAttributes['general']['Graisse des roulements'])
                    ? $bearingAttributes['general']['Graisse des roulements']
                    : '-';
                echo '<td class="product-cell-1" colspan="2" style="text-align: center !important;"><strong>' . esc_html($value) . '</strong></td>';
            } else {
                $cote_accouplement_value = isset($bearingAttributes['cote_accouplement'][$cote_accouplement_key])
                    ? $bearingAttributes['cote_accouplement'][$cote_accouplement_key]
                    : '-';
                $cote_oppose_value = isset($bearingAttributes['cote_oppose'][$cote_oppose_key])
                    ? $bearingAttributes['cote_oppose'][$cote_oppose_key]
                    : '-';
                
                echo '<td class="product-cell-1"><strong>' . esc_html($cote_accouplement_value) . '</strong></td>';
                echo '<td class="product-cell-1"><strong>' . esc_html($cote_oppose_value) . '</strong></td>';
            }
            
            echo '</tr>';
            $rowIndex++;
        }
        
        echo '</table>';
        echo '</div>';
    }
}

// Fonctions pour les attributs de couplage
if (!function_exists('displayProductCouplingAttributes')) {
    function displayProductCouplingAttributes($couplingNumber) {
        ob_start();
        
        if (!function_exists('is_product') || !is_product() || !($product = wc_get_product(get_the_ID()))) {
            echo '<p class="error-message">Produit non trouvé ou page invalide.</p>';
            return ob_get_clean();
        }
        
        if (empty($product->get_attributes())) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attributs.</p>';
            return ob_get_clean();
        }
        
        $filteredAttributes = filterAttributesByCoupling($product, $couplingNumber);
        
        if (empty($filteredAttributes)) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attribut pour le couplage n°' . $couplingNumber . '.</p>';
            return ob_get_clean();
        }
        
        renderAttributesTable($filteredAttributes, $product, $couplingNumber);
        
        return ob_get_clean();
    }
}

if (!function_exists('filterAttributesByCoupling')) {
    function filterAttributesByCoupling($product, $couplingNumber) {
        $attributes = $product->get_attributes();
        $filteredAttributes = [];
        $searchPattern = 'n°' . $couplingNumber;
        
        foreach ($attributes as $attributeKey => $attribute) {
            $attributeName = wc_attribute_label($attribute->get_name());
            if (strpos($attributeName, $searchPattern) !== false) {
                $filteredAttributes[$attributeKey] = $attribute;
            }
        }
        
        return $filteredAttributes;
    }
}

if (!function_exists('renderAttributesTable')) {
    function renderAttributesTable($filteredAttributes, $product, $couplingNumber) {
        $searchStr = ' (couplage n°' . $couplingNumber . ')';
        $attributesArray = array_values($filteredAttributes);
        $totalAttributes = count($attributesArray);
        
        echo '<div class="tech-specs-container">';
        echo '<h3 class="tech-specs-title">Caractéristiques techniques (couplage n°' . $couplingNumber . ')</h3>';
        
        // Tableau pour Desktop
        echo '<table class="product-table-2 desktop-table">';
        
        // En-tête du tableau desktop
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1 header-name">Nom</th>';
        echo '<th class="product-header-1 header-value">Valeur</th>';
        echo '<th class="product-header-1 header-name-2">Nom</th>';
        echo '<th class="product-header-1 header-value-2">Valeur</th>';
        echo '</tr>';
        
        // Corps du tableau desktop
        for ($i = 0; $i < $totalAttributes; $i += 2) {
            $attribute1 = $attributesArray[$i];
            $originalName1 = wc_attribute_label($attribute1->get_name());
            $name1 = str_replace($searchStr, '', $originalName1);
            $name1 = str_replace('(couplage n°' . $couplingNumber . ')', '', $name1);
            
            $value1 = $attribute1->is_taxonomy()
                ? implode(', ', wc_get_product_terms($product->get_id(), $attribute1->get_name(), ['fields' => 'names']))
                : implode(', ', $attribute1->get_options());
            
            // Ajouter l'unité tr/min pour la vitesse de rotation
            if (strpos($name1, 'Vitesse de rotation') !== false && strpos($value1, 'tr/min') === false) {
                $value1 .= ' tr/min';
            }
            
            $rowClass = ($i / 2 % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
            echo '<tr class="' . $rowClass . '">';
            echo '<td class="product-cell-1">' . esc_html($name1) . '</td>';
            echo '<td class="product-cell-1"><strong>' . esc_html($value1) . '</strong></td>';
            
            // Deuxième attribut
            if ($i + 1 < $totalAttributes) {
                $attribute2 = $attributesArray[$i + 1];
                $originalName2 = wc_attribute_label($attribute2->get_name());
                $name2 = str_replace($searchStr, '', $originalName2);
                $name2 = str_replace('(couplage n°' . $couplingNumber . ')', '', $name2);
                
                $value2 = $attribute2->is_taxonomy()
                    ? implode(', ', wc_get_product_terms($product->get_id(), $attribute2->get_name(), ['fields' => 'names']))
                    : implode(', ', $attribute2->get_options());
                
                // Ajouter l'unité tr/min pour la vitesse de rotation
                if (strpos($name2, 'Vitesse de rotation') !== false && strpos($value2, 'tr/min') === false) {
                    $value2 .= ' tr/min';
                }

                // Ajouter l'unité Hz pour la fréquence
if (strpos($name1, 'Fréquence') !== false && strpos($value1, 'Hz') === false) {
    $value1 .= ' Hz';
}

// Et pour le deuxième attribut également
if (isset($name2) && strpos($name2, 'Fréquence') !== false && strpos($value2, 'Hz') === false) {
    $value2 .= ' Hz';
}
                
                echo '<td class="product-cell-1">' . esc_html($name2) . '</td>';
                echo '<td class="product-cell-1"><strong>' . esc_html($value2) . '</strong></td>';
            } else {
                // Cellules vides si nombre impair d'attributs
                echo '<td class="product-cell-1"></td>';
                echo '<td class="product-cell-1"></td>';
            }
            
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Tableau pour Mobile (complètement séparé)
        echo '<table class="product-table-2 mobile-table">';
        
        // En-tête du tableau mobile
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1 header-name">Nom</th>';
        echo '<th class="product-header-1 header-value">Valeur</th>';
        echo '</tr>';
        
        // Corps du tableau mobile - un attribut par ligne
        for ($i = 0; $i < $totalAttributes; $i++) {
            $attribute = $attributesArray[$i];
            $originalName = wc_attribute_label($attribute->get_name());
            $name = str_replace($searchStr, '', $originalName);
            $name = str_replace('(couplage n°' . $couplingNumber . ')', '', $name);
            
            $value = $attribute->is_taxonomy()
                ? implode(', ', wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']))
                : implode(', ', $attribute->get_options());
            
            // Ajouter l'unité tr/min pour la vitesse de rotation
            if (strpos($name, 'Vitesse de rotation') !== false && strpos($value, 'tr/min') === false) {
                $value .= ' tr/min';
            }
            
            // Alternance des couleurs en mobile basée sur l'index (pair/impair)
            $rowClass = ($i % 2 === 0) ? 'mobile-row-1' : 'mobile-row alternate-1';
            echo '<tr class="' . $rowClass . '">';
            echo '<td class="product-cell-1">' . esc_html($name) . '</td>';
            echo '<td class="product-cell-1"><strong>' . esc_html($value) . '</strong></td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
    }
}

// Fonctions pour les couplages spécifiques (1-10 au lieu de 1-4)
for ($i = 1; $i <= 10; $i++) {
    if (!function_exists('displayProductN' . $i . 'Attributes')) {
        eval('function displayProductN' . $i . 'Attributes() { return displayProductCouplingAttributes(' . $i . '); }');
    }
}

if (!function_exists('getVoltageOptions')) {
    function getVoltageOptions() {
        if (!function_exists('is_product') || !is_product() || !($product = wc_get_product(get_the_ID()))) {
            return [];
        }
        
        $voltageOptions = [];
        $attributes = $product->get_attributes();
        $frequencyValues = []; // Stocker les fréquences par couplage
        
        // Chercher d'abord les fréquences par couplage
        foreach ($attributes as $attributeKey => $attribute) {
            $attributeName = wc_attribute_label($attribute->get_name());
            
            for ($couplingNumber = 1; $couplingNumber <= 10; $couplingNumber++) {
                $searchPattern = '(couplage n°' . $couplingNumber . ')';
                
                if (strpos($attributeName, $searchPattern) !== false &&
                    (strpos(strtolower($attributeName), 'fréquence') !== false ||
                     strpos(strtolower($attributeName), 'frequence') !== false)) {
                    
                    $values = $attribute->is_taxonomy()
                        ? wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names'])
                        : $attribute->get_options();
                    
                    if (!empty($values)) {
                        $frequencyValue = is_array($values) ? $values[0] : $values;
                        // Ajouter Hz si nécessaire
                        if (strpos($frequencyValue, 'Hz') === false) {
                            $frequencyValue .= ' Hz';
                        }
                        $frequencyValues[$couplingNumber] = $frequencyValue;
                    }
                }
            }
        }
        
        // Chercher une fréquence globale (backup)
        $globalFrequency = '';
        foreach ($attributes as $attributeKey => $attribute) {
            $attributeName = wc_attribute_label($attribute->get_name());
            if ((strpos(strtolower($attributeName), 'fréquence') !== false || 
                 strpos(strtolower($attributeName), 'frequence') !== false) &&
                 strpos($attributeName, 'couplage') === false) {
                
                $values = $attribute->is_taxonomy()
                    ? wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names'])
                    : $attribute->get_options();
                
                if (!empty($values)) {
                    $frequencyValue = is_array($values) ? $values[0] : $values;
                    // Ajouter Hz si nécessaire
                    if (strpos($frequencyValue, 'Hz') === false) {
                        $frequencyValue .= ' Hz';
                    }
                    $globalFrequency = $frequencyValue;
                    break;
                }
            }
        }
        
        // Ensuite chercher les tensions par couplage
        foreach ($attributes as $attributeKey => $attribute) {
            $attributeName = wc_attribute_label($attribute->get_name());
            
            for ($couplingNumber = 1; $couplingNumber <= 10; $couplingNumber++) {
                $searchPattern = 'n°' . $couplingNumber;
                
                if (strpos($attributeName, $searchPattern) !== false &&
                    (strpos(strtolower($attributeName), 'tension') !== false ||
                     strpos(strtolower($attributeName), 'voltage') !== false)) {
                    
                    $values = $attribute->is_taxonomy()
                        ? wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names'])
                        : $attribute->get_options();
                    
                    if (!empty($values)) {
                        $voltageValue = is_array($values) ? $values[0] : $values;
                        
                        // Utiliser la fréquence spécifique au couplage si disponible, sinon la fréquence globale
                        $frequencyToUse = isset($frequencyValues[$couplingNumber]) ? 
                                          $frequencyValues[$couplingNumber] : 
                                          $globalFrequency;
                        
                        // Combiner tension et fréquence
                        $displayValue = $voltageValue . ($frequencyToUse ? ' - ' . $frequencyToUse : '');
                        $voltageOptions[$couplingNumber] = [
                            'value' => $displayValue,
                            'label' => $displayValue . ' ' . ($couplingNumber > 1 ? '(Couplage ' . $couplingNumber . ')' : '')
                        ];
                        break;
                    }
                }
            }
        }
        
        return $voltageOptions;
    }
}

if (!function_exists('displayProductCouplingAttributesWithTabs')) {
    function displayProductCouplingAttributesWithTabs() {
        ob_start();
        
        $voltageOptions = getVoltageOptions();
        $uniqueId = 'voltage-tabs-' . uniqid();
        
        if (empty($voltageOptions)) {
            echo '<div class="attributes-hidden-outside-tab" style="display: none;">';
            echo displayAllProductCouplingAttributes();
            echo '</div>';
            return ob_get_clean();
        }
        
        ?>
        <div class="attributes-hidden-outside-tab" id="<?php echo esc_attr($uniqueId); ?>" style="display: none;">
            <div class="voltage-tabs">
                <?php
                $first = true;
                foreach ($voltageOptions as $couplingNumber => $option) :
                    echo '<div class="tab ' . ($first ? 'active' : '') . '" data-coupling="' . $couplingNumber . '">'
                         . esc_html($option['value']) . '</div>';
                    $first = false;
                endforeach;
                ?>
            </div>
            
            <div class="voltage-tabs-content">
                <?php
                $first = true;
                foreach ($voltageOptions as $couplingNumber => $option) :
                    echo '<div class="tab-content ' . ($first ? 'active' : '') . '" id="tab-content-' . $couplingNumber
                         . '-' . $uniqueId . '">' . displayProductCouplingAttributes($couplingNumber) . '</div>';
                    $first = false;
                endforeach;
                ?>
            </div>
        </div>

<style>
    .error-message { color: red; }
    .warning-message { color: orange; }
    .tech-specs-container { margin: 0; margin-bottom: 1.5rem; }
    .tech-specs-title { margin-bottom: 1rem; margin-top: 1rem; }
    
    /* Styles pour le tableau responsive */
    .product-table-2, .product-table-3 {
        width: 100%;
        border-collapse: collapse;
    }
    
    .product-table-2 th, .product-table-2 td,
    .product-table-3 th, .product-table-3 td {
        padding: 0.5rem;
        border: 1px solid #ddd;
    }
    
    /* Classes d'affichage conditionnel */
    .mobile-table {
        display: none;
    }
    
    /* Styles des onglets */
    .voltage-tabs {
        display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 1.5rem; margin-top: 1.5rem;
    }
    
    .voltage-tabs .tab {
        padding: 10px 16px; cursor: pointer; font-weight: 500; border-radius: 4px;
        transition: all 0.2s ease; background: #f3f3f3; border: 1px solid #ddd;
        color: #444; user-select: none;
    }
    
    .voltage-tabs .tab:hover {
        background: #f5e5e2; border-color: #de2f19;
    }
    
    .voltage-tabs .tab.active {
        background: #de2f19; color: white; border-color: #c42815;
        box-shadow: 0 2px 5px rgba(222, 47, 25, 0.3);
    }
    
    .voltage-tabs-content .tab-content { display: none; }
    .voltage-tabs-content .tab-content.active { display: block; }
    .attributes-hidden-outside-tab.in-tab-active { display: block !important; }
    
    /* Nouveaux styles de tableaux */
    .product-table-1 {
      height: auto !important;
      width: 100% !important;
      max-width: 27rem !important;
      border-collapse: collapse !important;
      border: 1px solid #000000 !important;
      margin-top: 1rem !important;
    }
    .product-table-2 {
      height: auto !important;
      width: 100% !important;
      border-collapse: collapse !important;
      border: 1px solid #000000 !important;
      margin-top: 1rem !important;
    }
    .product-table-3 {
      height: auto !important;
      width: 100% !important;
      max-width: 55rem !important;
      border-collapse: collapse !important;
      border: 1px solid #000000 !important;
      margin-top: 1rem !important;
    }
    .product-row-1 {
      height: auto !important;
    }
    .product-header-1 {
      height: auto !important;
      width: 25% !important;
      text-align: left !important;
      background-color: #123750 !important;
      padding: 0.5rem !important;
      color: #FFFFFF !important;
      opacity: 0.9 !important;
      border: 1px solid #000000 !important;
    }
    .product-cell-1 {
      height: auto !important;
      width: 25% !important;
      text-align: left !important;
      padding: 0.5rem !important;
      border: 1px solid #000000 !important;
    }
    .product-row-1:not(.alternate) .product-cell-1 {
      background-color: #FFFFFF !important;
    }
    .product-row.alternate-1 .product-cell-1 {
      background-color: #f5f5f5 !important;
    }
    .product-cell-1:nth-child(2) {
      font-weight: bold;
    }
    .mobile-row-1 .product-cell-1 {
      background-color: #FFFFFF !important;
    }
    .mobile-row.alternate-1 .product-cell-1 {
      background-color: #f5f5f5 !important;
    }
    .mobile-table {
      display: none;
    }
    .cta-section-1 {
     margin-top: 1rem !important;
     margin-bottom: 1rem !important;
    }
    
    /* Media queries */
    @media (max-width: 768px) {
        /* Styles pour les onglets */
        .voltage-tabs { flex-direction: column; gap: 5px; }
        .voltage-tabs .tab { width: 100%; text-align: center; }
        
        /* Styles pour les tableaux */
        .desktop-table {
            display: none !important;
        }
        
        .mobile-table {
            display: table !important;
        }
        
        /* Styles spécifiques pour le tableau de roulements en mobile */
        .bearing-mobile-table {
            font-size: 0.9rem !important;
        }
        
        .bearing-mobile-table .product-cell-1 {
            padding: 6px !important;
            font-size: 0.8rem !important;
        }

        .bearing-mobile-table .product-header-1 {
            font-size: 0.8rem !important;
        }
        
        /* Styles spécifiques pour le tableau des caractéristiques complémentaires en mobile */
        .complementary-table .product-cell-1 {
            padding: 5px !important;
            font-size: 0.8rem !important;
        }

        .complementary-table .product-header-1 {
            font-size: 0.8rem !important;
        }
        
        .product-table-2 th, .product-table-2 td,
        .product-table-3 th, .product-table-3 td {
            padding: 0.5rem;
            text-align: left;
        }
        
        .product-table-2 th:first-child, .product-table-2 td:first-child,
        .product-table-3 th:first-child, .product-table-3 td:first-child {
            width: 50% !important;
        }
        
        .product-table-2 th:last-child, .product-table-2 td:last-child,
        .product-table-3 th:last-child, .product-table-3 td:last-child {
            width: 50% !important;
        }
        
        .tech-specs-title { font-size: xx-large !important; }
    }
</style>
        <?php
        
        add_action('wp_footer', function() use ($uniqueId) {
            ?>
            <script>
            (function() {
                function ensureJQuery(callback) {
                    if (typeof jQuery !== 'undefined') {
                        callback(jQuery);
                    } else {
                        setTimeout(() => ensureJQuery(callback), 50);
                    }
                }
                
                function checkIfInVisibleTab() {
                    var $ = jQuery;
                    if (!$) return;
                    
                    $('.attributes-hidden-outside-tab').each(function() {
                        var $this = $(this);
                        var parentTab = $this.parents('.et_pb_tab_content, .et_pb_all_tabs, .tab-pane, [role="tabpanel"]').first();
                        var isVisible = parentTab.length > 0 && parentTab.is(':visible');
                        
                        $this.toggleClass('in-tab-active', isVisible);
                        if (isVisible) {
                            $this.show();
                            initVoltageTabs($this);
                        } else if (parentTab.length === 0) {
                            $this.hide();
                        }
                    });
                }
                
                function initVoltageTabs($container) {
                    var $ = jQuery;
                    
                    $container.find('.voltage-tabs .tab').off('click').on('click', function() {
                        var $this = $(this);
                        var container = $this.closest('.attributes-hidden-outside-tab');
                        var couplingNumber = $this.data('coupling');
                        
                        container.find('.voltage-tabs .tab, .voltage-tabs-content .tab-content').removeClass('active');
                        $this.addClass('active');
                        container.find('#tab-content-' + couplingNumber + '-' + container.attr('id')).addClass('active');
                    });
                }
                
                ensureJQuery(function($) {
                    $(document).ready(checkIfInVisibleTab);
                    
                    [100, 500, 1000, 2000].forEach(delay => setTimeout(checkIfInVisibleTab, delay));
                    
                    $(window).on('load', checkIfInVisibleTab);
                    
                    $(document).on('click', '[role="tab"], .tab, .nav-tab, .et_pb_tabs_controls li', function() {
                        setTimeout(checkIfInVisibleTab, 50);
                    });
                    
                    ['shown.bs.tab', 'tabsactivate', 'tab_activated', 'et_pb_tab_active', 'elementor/tabs/show']
                        .forEach(event => $(document).on(event, () => setTimeout(checkIfInVisibleTab, 50)));
                    
                    $(window).on('resize', () => setTimeout(checkIfInVisibleTab, 100));
                    
                    if (typeof MutationObserver !== 'undefined') {
                        setTimeout(function() {
                            if (document.body) {
                                new MutationObserver(checkIfInVisibleTab).observe(document.body, {
                                    childList: true, subtree: true, attributes: true,
                                    attributeFilter: ['style', 'class']
                                });
                            }
                        }, 500);
                    }
                });
            })();
            </script>
            <?php
        }, 99);
        
        return ob_get_clean();
    }
}

if (!function_exists('displayAllProductCouplingAttributes')) {
    function displayAllProductCouplingAttributes() {
        ob_start();
        
        $output = '';
        for ($i = 1; $i <= 10; $i++) {
            $couplingOutput = displayProductCouplingAttributes($i);
            if (strpos($couplingOutput, 'warning-message') === false && strpos($couplingOutput, 'error-message') === false) {
                $output .= $couplingOutput;
            }
        }
        
        echo empty($output)
            ? '<p class="warning-message">Aucun attribut de couplage trouvé pour ce produit.</p>'
            : $output;
        
        return ob_get_clean();
    }
}

// Modification de la fonction pour les attributs complémentaires
if (!function_exists('getComplementaryAttributes')) {
    function getComplementaryAttributes($product) {
        $attributes = $product->get_attributes();
        $complementaryAttributes = [];
        
        // Liste des attributs à exclure du tableau complémentaire (par ID)
        $excludedAttributeIds = [
            'reference-fabriquant',
            'indice-energetique',
            'taille-carcasse',
            'norme-moteurs',
            'frequence',
            'nombre-de-poles',
            'indice-de-protection-ip',
            'vitesse-rotation',
            'puissance-utile-nominale',
            'type-de-montage',
            'att0000122',
            'puissance',
            'tension-50hz',
            'nombre-de-phases',
            'famille',
            'sous-sous-famille'
        ];
        
        foreach ($attributes as $attributeKey => $attribute) {
            $taxonomyName = $attribute->get_name();
            $attributeId = strtolower(preg_replace('/^pa_/', '', $taxonomyName));
            $attributeName = wc_attribute_label($attribute->get_name());
            
            // Exclure les attributs de roulement, de couplage et ceux de la liste d'exclusion par ID
            if (strpos($attributeName, 'n°') === false && 
                strpos(strtolower($attributeName), 'roulement') === false &&
                !in_array($attributeId, $excludedAttributeIds)) {
                
                $value = $attribute->is_taxonomy()
                    ? implode(', ', wc_get_product_terms($product->get_id(), $taxonomyName, ['fields' => 'names']))
                    : implode(', ', $attribute->get_options());
                
                // Ajouter l'unité tr/min pour la vitesse de rotation
                if (strpos($attributeName, 'Vitesse de rotation') !== false && strpos($value, 'tr/min') === false) {
                    $value .= ' tr/min';
                }
                
                $complementaryAttributes[$attributeName] = $value;
            }
        }
        
        return $complementaryAttributes;
    }
}

// Fonction pour afficher le tableau des attributs complémentaires
if (!function_exists('displayComplementaryAttributes')) {
    function displayComplementaryAttributes() {
        ob_start();
        
        if (!function_exists('is_product') || !is_product() || !($product = wc_get_product(get_the_ID()))) {
            echo '<p class="error-message">Produit non trouvé ou page invalide.</p>';
            return ob_get_clean();
        }
        
        if (empty($product->get_attributes())) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attributs.</p>';
            return ob_get_clean();
        }
        
        $complementaryAttributes = getComplementaryAttributes($product);
        
        if (empty($complementaryAttributes)) {
            echo '<p class="warning-message">Ce produit n\'a pas d\'attribut complémentaire.</p>';
            return ob_get_clean();
        }
        
        echo '<div class="complementary-attributes attributes-hidden-outside-tab" style="display: none;">';
        renderComplementaryTable($complementaryAttributes);
        echo '</div>';
        
        return ob_get_clean();
    }
}

// Fonction pour afficher le tableau des attributs complémentaires
if (!function_exists('renderComplementaryTable')) {
    function renderComplementaryTable($complementaryAttributes) {
        $attributesArray = [];
        foreach ($complementaryAttributes as $name => $value) {
            $attributesArray[] = ['name' => $name, 'value' => $value];
        }
        $totalAttributes = count($attributesArray);
        
        echo '<div class="tech-specs-container">';
        echo '<h3 class="tech-specs-title">Caractéristiques complémentaires</h3>';
        
        // Tableau pour Desktop
        echo '<table class="product-table-2 desktop-table complementary-table">';
        
        // En-tête du tableau desktop
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1 header-name">Nom</th>';
        echo '<th class="product-header-1 header-value">Valeur</th>';
        echo '<th class="product-header-1 header-name-2">Nom</th>';
        echo '<th class="product-header-1 header-value-2">Valeur</th>';
        echo '</tr>';
        
        // Corps du tableau desktop
        for ($i = 0; $i < $totalAttributes; $i += 2) {
            $rowClass = ($i / 2 % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
            echo '<tr class="' . $rowClass . '">';
            
            echo '<td class="product-cell-1">' . esc_html($attributesArray[$i]['name']) . '</td>';
            echo '<td class="product-cell-1"><strong>' . esc_html($attributesArray[$i]['value']) . '</strong></td>';
            
            // Deuxième attribut
            if ($i + 1 < $totalAttributes) {
                echo '<td class="product-cell-1">' . esc_html($attributesArray[$i + 1]['name']) . '</td>';
                echo '<td class="product-cell-1"><strong>' . esc_html($attributesArray[$i + 1]['value']) . '</strong></td>';
            } else {
                // Cellules vides si nombre impair d'attributs
                echo '<td class="product-cell-1"></td>';
                echo '<td class="product-cell-1"></td>';
            }
            
            echo '</tr>';
        }
        
        echo '</table>';
        
        // Tableau pour Mobile (complètement séparé)
        echo '<table class="product-table-2 mobile-table complementary-table">';
        
        // En-tête du tableau mobile
        echo '<tr class="product-row-1">';
        echo '<th class="product-header-1 header-name">Nom</th>';
        echo '<th class="product-header-1 header-value">Valeur</th>';
        echo '</tr>';
        
        // Corps du tableau mobile - un attribut par ligne
        for ($i = 0; $i < $totalAttributes; $i++) {
            // Alternance des couleurs en mobile basée sur l'index (pair/impair)
            $rowClass = ($i % 2 === 0) ? 'mobile-row-1' : 'mobile-row alternate-1';
            echo '<tr class="' . $rowClass . '">';
            echo '<td class="product-cell-1">' . esc_html($attributesArray[$i]['name']) . '</td>';
            echo '<td class="product-cell-1"><strong>' . esc_html($attributesArray[$i]['value']) . '</strong></td>';
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
    }
}

// Fonction principale qui combine les deux affichages (issu du fichier 1)
if (!function_exists('displayProductInfoWithBearings')) {
    function displayProductInfoWithBearings() {
        ob_start();
        
        // Afficher d'abord les informations de roulement
        echo displayBearingAttributes();
        
        // Ensuite afficher les informations de couplage
        echo displayProductCouplingAttributesWithTabs();
        
        // Enfin afficher les attributs complémentaires
        echo displayComplementaryAttributes();
        
        return ob_get_clean();
    }
}

// Point d'entrée principal - Utilise la fonction combinée
echo displayProductInfoWithBearings();
?>