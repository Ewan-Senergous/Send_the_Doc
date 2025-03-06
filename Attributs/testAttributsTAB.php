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

// Fonction pour obtenir les tensions nominales disponibles dans les attributs
if (!function_exists('getVoltageOptions')) {
    function getVoltageOptions() {
        if (!function_exists('is_product') || !is_product()) {
            return array();
        }
        
        $product = wc_get_product(get_the_ID());
        if (!$product) {
            return array();
        }
        
        $voltageOptions = array();
        $attributes = $product->get_attributes();
        
        // Chercher les attributs de tension nominale pour chaque couplage
        for ($couplingNumber = 1; $couplingNumber <= 4; $couplingNumber++) {
            $searchPattern = 'n°' . $couplingNumber;
            $voltageAttrKey = null;
            
            foreach ($attributes as $attributeKey => $attribute) {
                $attributeName = wc_attribute_label($attribute->get_name());
                
                // Vérifier si c'est un attribut de tension nominale pour ce couplage
                if (strpos($attributeName, $searchPattern) !== false && 
                    (strpos(strtolower($attributeName), 'tension') !== false || 
                     strpos(strtolower($attributeName), 'voltage') !== false)) {
                    $voltageAttrKey = $attributeKey;
                    break;
                }
            }
            
            // Si un attribut de tension a été trouvé pour ce couplage
            if ($voltageAttrKey) {
                $voltageAttr = $attributes[$voltageAttrKey];
                
                if ($voltageAttr->is_taxonomy()) {
                    $values = wc_get_product_terms($product->get_id(), $voltageAttr->get_name(), ['fields' => 'names']);
                } else {
                    $values = $voltageAttr->get_options();
                }
                
                if (!empty($values)) {
                    // Ajouter une option spécifique à ce couplage
                    $voltageValue = is_array($values) ? $values[0] : $values;
                    $voltageOptions[$couplingNumber] = array(
                        'value' => $voltageValue,
                        'label' => $voltageValue . ' ' . ($couplingNumber > 1 ? '(Couplage ' . $couplingNumber . ')' : '')
                    );
                }
            }
        }
        
        return $voltageOptions;
    }
}

// Fonction pour afficher tous les tableaux de couplage disponibles avec des onglets par tension
if (!function_exists('displayProductCouplingAttributesWithTabs')) {
    function displayProductCouplingAttributesWithTabs() {
        ob_start();
        
        $voltageOptions = getVoltageOptions();
        $uniqueId = 'voltage-tabs-' . uniqid();
        
        // Si aucune option de tension n'est trouvée, afficher tous les tableaux sans onglets
        if (empty($voltageOptions)) {
            echo '<div class="attributes-hidden-outside-tab" style="display: none;">';
            echo displayAllProductCouplingAttributes();
            echo '</div>';
            return ob_get_clean();
        }
        
        // Générer les onglets de tension - initialement masqués
        ?>
        <div class="attributes-hidden-outside-tab" id="<?php echo esc_attr($uniqueId); ?>" style="display: none;">
            <div class="voltage-tabs">
                <?php 
                $first = true;
                foreach ($voltageOptions as $couplingNumber => $option) : 
                    $tabClass = $first ? 'tab active' : 'tab';
                    $first = false;
                ?>
                    <div class="<?php echo $tabClass; ?>" data-coupling="<?php echo $couplingNumber; ?>">
                        <?php echo esc_html($option['value']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="voltage-tabs-content">
                <?php 
                $first = true;
                foreach ($voltageOptions as $couplingNumber => $option) : 
                    $contentClass = $first ? 'tab-content active' : 'tab-content';
                    $first = false;
                ?>
                    <div class="<?php echo $contentClass; ?>" id="tab-content-<?php echo $couplingNumber; ?>-<?php echo $uniqueId; ?>">
                        <?php echo displayProductCouplingAttributes($couplingNumber); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
            /* Style général */
            .error-message {
                color: red;
            }
            .warning-message {
                color: orange;
            }
            .tech-specs-container {
                margin: 0;
            }
            .tech-specs-title {
                margin-bottom: 15px;
            }
            
            /* Styles des onglets */
            .voltage-tabs-container {
                margin: 20px 0;
            }
            
            .voltage-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 20px;
            }
            
            .voltage-tabs .tab {
                padding: 10px 16px;
                cursor: pointer;
                font-weight: 500;
                border-radius: 4px;
                transition: all 0.2s ease;
                background: #f3f3f3;
                border: 1px solid #ddd;
                color: #444;
                user-select: none;
            }
            
            .voltage-tabs .tab:hover {
                background: #f5e5e2;
                border-color: #de2f19;
            }
            
            .voltage-tabs .tab.active {
                background: #de2f19;
                color: white;
                border-color: #c42815;
                box-shadow: 0 2px 5px rgba(222, 47, 25, 0.3);
            }
            
            .voltage-tabs-content .tab-content {
                display: none;
            }
            
            .voltage-tabs-content .tab-content.active {
                display: block;
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .voltage-tabs {
                    flex-direction: column;
                    gap: 5px;
                }
                
                .voltage-tabs .tab {
                    width: 100%;
                    text-align: center;
                }
            }

            /* Style pour l'instance active dans un onglet */
            .attributes-hidden-outside-tab.in-tab-active {
                display: block !important;
            }
        </style>
        <?php
        
        // Ajouter le script dans le footer pour détecter si on est dans un onglet
        add_action('wp_footer', function() use ($uniqueId) {
            ?>
            <script>
            (function() {
                // Fonction pour déterminer si un élément est visible et dans un onglet
                function checkIfInVisibleTab() {
                    var $ = jQuery;
                    if (!$) return;
                    
                    $('.attributes-hidden-outside-tab').each(function() {
                        var $this = $(this);
                        var isInTab = $this.parents('.et_pb_tab_content, .et_pb_all_tabs, .tab-pane, [role="tabpanel"]').length > 0;
                        var parentTab = $this.parents('.et_pb_tab_content, .et_pb_all_tabs, .tab-pane, [role="tabpanel"]').first();
                        
                        // Vérifier si l'élément est dans un onglet ET si l'onglet parent est visible
                        if (isInTab && parentTab.is(':visible')) {
                            $this.addClass('in-tab-active').show();
                            
                            // Initialiser les onglets internes si on est dans un onglet visible
                            initVoltageTabs($this);
                        } else if (!isInTab) {
                            // Si pas dans un onglet, garder caché
                            $this.removeClass('in-tab-active').hide();
                        } else {
                            // Dans un onglet mais pas visible
                            $this.removeClass('in-tab-active');
                        }
                    });
                }
                
                // Initialiser les onglets de tension
                function initVoltageTabs($container) {
                    var $ = jQuery;
                    
                    $container.find('.voltage-tabs .tab').off('click').on('click', function() {
                        var $this = $(this);
                        var container = $this.closest('.attributes-hidden-outside-tab');
                        var couplingNumber = $this.data('coupling');
                        
                        // Mettre à jour les classes actives
                        container.find('.voltage-tabs .tab').removeClass('active');
                        container.find('.voltage-tabs-content .tab-content').removeClass('active');
                        $this.addClass('active');
                        container.find('#tab-content-' + couplingNumber + '-' + container.attr('id')).addClass('active');
                    });
                }
                
                // Fonction pour s'assurer que jQuery est chargé
                function ensureJQuery(callback) {
                    if (typeof jQuery !== 'undefined') {
                        callback(jQuery);
                    } else {
                        setTimeout(function() {
                            ensureJQuery(callback);
                        }, 50);
                    }
                }
                
                // Configurer les vérifications à différents moments
                ensureJQuery(function($) {
                    // Première vérification au chargement
                    $(document).ready(checkIfInVisibleTab);
                    
                    // Vérifications différées
                    [100, 500, 1000, 2000].forEach(function(delay) {
                        setTimeout(checkIfInVisibleTab, delay);
                    });
                    
                    // Vérification au chargement complet de la page
                    $(window).on('load', checkIfInVisibleTab);
                    
                    // Vérification lors des clics sur différents types d'onglets
                    $(document).on('click', '[role="tab"], .tab, .nav-tab, .et_pb_tabs_controls li', function() {
                        setTimeout(checkIfInVisibleTab, 50);
                    });
                    
                    // Vérification lors des événements standards d'onglets
                    var tabEvents = [
                        'shown.bs.tab',       // Bootstrap
                        'tabsactivate',       // jQuery UI
                        'tab_activated',      // WordPress
                        'et_pb_tab_active',   // Divi
                        'elementor/tabs/show' // Elementor
                    ];
                    
                    tabEvents.forEach(function(event) {
                        $(document).on(event, function() {
                            setTimeout(checkIfInVisibleTab, 50);
                        });
                    });
                    
                    // Vérification sur les modifications de taille d'écran
                    $(window).on('resize', function() {
                        setTimeout(checkIfInVisibleTab, 100);
                    });
                    
                    // Observer les mutations du DOM si disponible
                    if (typeof MutationObserver !== 'undefined') {
                        var observer = new MutationObserver(function() {
                            checkIfInVisibleTab();
                        });
                        
                        setTimeout(function() {
                            if (document.body) {
                                observer.observe(document.body, {
                                    childList: true,
                                    subtree: true,
                                    attributes: true,
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

// Fonction originale pour tous les tableaux (maintenue pour compatibilité)
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

// Utilisation - Afficher les tableaux avec onglets de tension
echo displayProductCouplingAttributesWithTabs();
?>