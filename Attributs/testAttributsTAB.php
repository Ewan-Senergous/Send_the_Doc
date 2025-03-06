<?php
if (!function_exists('displayProductCouplingAttributes')) {
    function displayProductCouplingAttributes($couplingNumber) {
        ob_start();
        $output = '';
        
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
        echo '<table class="product-table-2">';
        echo '<tr class="product-row-1"><th class="product-header-1">Nom</th><th class="product-header-1">Valeur</th>';
        echo '<th class="product-header-1">Nom</th><th class="product-header-1">Valeur</th></tr>';
        
        for ($i = 0; $i < $totalAttributes; $i += 2) {
            $rowClass = ($i / 2 % 2 === 0) ? 'product-row-1' : 'product-row alternate-1';
            echo '<tr class="' . $rowClass . '">';
            
            for ($j = 0; $j < 2; $j++) {
                $index = $i + $j;
                if ($index < $totalAttributes) {
                    $attribute = $attributesArray[$index];
                    $originalName = wc_attribute_label($attribute->get_name());
                    $name = str_replace($searchStr, '', $originalName);
                    $name = str_replace('(couplage n°' . $couplingNumber . ')', '', $name);
                    
                    $value = $attribute->is_taxonomy()
                        ? implode(', ', wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']))
                        : implode(', ', $attribute->get_options());
                    
                    echo '<td class="product-cell-1">' . esc_html($name) . '</td>';
                    echo '<td class="product-cell-1"><strong>' . esc_html($value) . '</strong></td>';
                } else {
                    echo '<td class="product-cell-1"></td><td class="product-cell-1"></td>';
                }
            }
            
            echo '</tr>';
        }
        
        echo '</table></div>';
    }
}

// Fonctions pour les couplages spécifiques (1-4)
for ($i = 1; $i <= 4; $i++) {
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
        
        foreach ($attributes as $attributeKey => $attribute) {
            $attributeName = wc_attribute_label($attribute->get_name());
            
            for ($couplingNumber = 1; $couplingNumber <= 4; $couplingNumber++) {
                $searchPattern = 'n°' . $couplingNumber;
                
                if (strpos($attributeName, $searchPattern) !== false &&
                    (strpos(strtolower($attributeName), 'tension') !== false ||
                     strpos(strtolower($attributeName), 'voltage') !== false)) {
                    
                    $values = $attribute->is_taxonomy()
                        ? wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names'])
                        : $attribute->get_options();
                    
                    if (!empty($values)) {
                        $voltageValue = is_array($values) ? $values[0] : $values;
                        $voltageOptions[$couplingNumber] = [
                            'value' => $voltageValue,
                            'label' => $voltageValue . ' ' . ($couplingNumber > 1 ? '(Couplage ' . $couplingNumber . ')' : '')
                        ];
                        break; // Une fois trouvé pour ce couplage, on passe au suivant
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
            .tech-specs-container { margin: 0; }
            .tech-specs-title { margin-bottom: 15px; }
            
            .voltage-tabs {
                display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;
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
            
            @media (max-width: 768px) {
                .voltage-tabs { flex-direction: column; gap: 5px; }
                .voltage-tabs .tab { width: 100%; text-align: center; }
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
        for ($i = 1; $i <= 4; $i++) {
            $couplingOutput = displayProductCouplingAttributes($i);
            if (strpos($couplingOutput, 'warning-message') === false && strpos($couplingOutput, 'error-message') === false) {
                $output .= $couplingOutput;
            }
        }
        
        echo empty($output)
            ? '<p class="warning-message">Aucun attribut de couplage trouvé pour ce produit.</p>'
            : $output;
        
        ?>
        <style>
            .error-message { color: red; }
            .warning-message { color: orange; }
            .tech-specs-container { margin: 20px 0; }
            .tech-specs-title { margin-bottom: 15px; }
        </style>
        <?php
        
        return ob_get_clean();
    }
}

echo displayProductCouplingAttributesWithTabs();
?>
