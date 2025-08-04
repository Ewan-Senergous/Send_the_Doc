<?php

if (!function_exists('doc_download_display')) {
    function doc_download_display() {
        ob_start();
        
        // Debug: Vérifier WooCommerce
        if (!function_exists('wc_get_products')) {
            echo '<div style="color: red; padding: 20px; border: 1px solid red;">❌ WooCommerce n\'est pas activé ou chargé.</div>';
            return ob_get_clean();
        }
        
        // NOUVELLE FONCTION : Extraire un nom friendly à partir d'une URL
        function extract_friendly_name_from_url($url) {
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                return '';
            }
            
            // Extraire le nom de fichier de l'URL
            $filename = basename(parse_url($url, PHP_URL_PATH));
            
            // Retirer l'extension
            $name = pathinfo($filename, PATHINFO_FILENAME);
            
            // Détecter le type de document
            $type = '';
            if (stripos($name, 'vue-eclatee') !== false || stripos($name, 'exploded') !== false) {
                $type = 'Vue éclatée';
            } elseif (stripos($name, 'datasheet') !== false) {
                $type = 'Datasheet';
            } elseif (stripos($name, 'manuel-utilisation') !== false || stripos($name, 'manual') !== false || stripos($name, 'user-guide') !== false) {
                $type = 'Manuel utilisation';
            } elseif (stripos($name, 'manuel-reparation') !== false || stripos($name, 'repair') !== false || stripos($name, 'maintenance') !== false) {
                $type = 'Manuel réparation';
            } else {
                $type = 'Documentation';
            }
            
            // Extraire la référence/modèle (généralement au début du nom)
            $reference = '';
            
            // Patterns pour extraire la référence
            if (preg_match('/^([A-Z0-9\-_]+)[\-_]/', $name, $matches)) {
                $reference = $matches[1];
                // Nettoyer la référence
                $reference = str_replace(['_', '-'], [' ', '-'], $reference);
                // Supprimer les doublons de tirets
                $reference = preg_replace('/-+/', '-', $reference);
                // Nettoyer les espaces
                $reference = trim($reference, '- ');
            }
            
            // Si on a trouvé une référence, créer le label complet
            if (!empty($reference)) {
                return $reference . ' - ' . $type;
            }
            
            // Sinon, essayer d'extraire des infos plus génériques
            $parts = explode('-', $name);
            if (count($parts) >= 2) {
                $first_parts = array_slice($parts, 0, 2);
                $reference = implode(' ', $first_parts);
                $reference = strtoupper($reference);
                return $reference . ' - ' . $type;
            }
            
            // En dernier recours, retourner juste le type
            return $type;
        }
        
        // Récupération des paramètres de recherche et pagination
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $selected_famille = isset($_GET['famille']) ? sanitize_text_field($_GET['famille']) : '';
        $selected_sous_famille = isset($_GET['sous_famille']) ? sanitize_text_field($_GET['sous_famille']) : '';
        $selected_sous_sous_famille = isset($_GET['sous_sous_famille']) ? sanitize_text_field($_GET['sous_sous_famille']) : '';
        
        // NOUVEAU : Types de documents multi-sélection
        $selected_doc_types = isset($_GET['doc_types']) && is_array($_GET['doc_types']) ? 
            array_map('sanitize_text_field', $_GET['doc_types']) : [];
        
        // Référence fabriquant
        $selected_reference_fabriquant = isset($_GET['reference_fabriquant']) ? sanitize_text_field($_GET['reference_fabriquant']) : '';
        
        // Marque (brand)
        $selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
        
        // Paramètres de pagination - NOUVEAU SYSTÈME VOIR PLUS
        $initial_display = 2; // Afficher 2 produits au début
        $load_more_count = 12; // Charger 12 produits supplémentaires à chaque clic
        $visible_count = isset($_GET['visible']) ? max($initial_display, intval($_GET['visible'])) : $initial_display;
        
        // SOLUTION CORRIGÉE : Récupération via taxonomies WooCommerce
        function get_products_with_documentation_optimized() {
            global $wpdb;
            
            // Cache de 30 minutes
            $cache_key = 'products_with_docs_taxonomies_v1';
            $cached_result = wp_cache_get($cache_key);
            
            if (false !== $cached_result) {
                return $cached_result;
            }
            
            // Requête SQL simplifiée - récupérer seulement les produits avec documentation
            $sql = "
                SELECT DISTINCT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_name as slug,
                    
                    -- Documentation depuis taxonomie pa_catalogue
                    t_doc.name as documentation_url
                    
                FROM {$wpdb->posts} p
                
                -- Catalogue (OBLIGATOIRE)
                INNER JOIN {$wpdb->term_relationships} tr_doc ON p.ID = tr_doc.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt_doc ON tr_doc.term_taxonomy_id = tt_doc.term_taxonomy_id 
                    AND tt_doc.taxonomy = 'pa_catalogue'
                INNER JOIN {$wpdb->terms} t_doc ON tt_doc.term_id = t_doc.term_id
                
                WHERE p.post_type = 'product' 
                AND p.post_status IN ('publish', 'draft')
                AND t_doc.name IS NOT NULL 
                AND t_doc.name != ''
                AND t_doc.name != 'N/A'
                AND t_doc.name NOT LIKE '%non%'
                
                ORDER BY p.post_title ASC
            ";
            
            $results = $wpdb->get_results($sql, ARRAY_A);
            
            // Formater les résultats avec récupération des nouveaux attributs
            $products_with_docs = array();
            foreach ($results as $row) {
                if (!empty($row['documentation_url']) && 
                    filter_var($row['documentation_url'], FILTER_VALIDATE_URL)) {
                    
                    $product_id = $row['id'];
                    
                    // Récupération de TOUS les attributs avec get_the_terms() - TOUTES les valeurs
                    $famille = [];
                    $sous_famille = [];
                    $sous_sous_famille = [];
                    $vue_eclatee = [];
                    $manuel_utilisation = [];
                    $datasheet = [];
                    $manuel_reparation = [];
                    $reference_fabriquant = [];
                    $brand = [];
                    
                    // Récupération des familles - TOUTES les valeurs
                    if (taxonomy_exists('pa_famille')) {
                        $terms = get_the_terms($product_id, 'pa_famille');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                $famille[] = $term->name;
                            }
                        }
                    }
                    
                    // Récupération des sous-familles - TOUTES les valeurs
                    if (taxonomy_exists('pa_sous-famille')) {
                        $terms = get_the_terms($product_id, 'pa_sous-famille');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                $sous_famille[] = $term->name;
                            }
                        }
                    }
                    
                    // Récupération des sous-sous-familles - TOUTES les valeurs
                    if (taxonomy_exists('pa_sous-sous-famille')) {
                        $terms = get_the_terms($product_id, 'pa_sous-sous-famille');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                $sous_sous_famille[] = $term->name;
                            }
                        }
                    }
                    
                    // Récupération avec vérification d'existence des taxonomies - TOUTES les valeurs
                    if (taxonomy_exists('pa_vue-eclatee')) {
                        $terms = get_the_terms($product_id, 'pa_vue-eclatee');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                if (filter_var($term->name, FILTER_VALIDATE_URL)) {
                                    $friendly_name = extract_friendly_name_from_url($term->name);
                                    $vue_eclatee[] = array(
                                        'url' => $term->name,
                                        'friendly_name' => $friendly_name
                                    );
                                }
                            }
                        }
                    }
                    
                    if (taxonomy_exists('pa_manuel-dutilisation')) {
                        $terms = get_the_terms($product_id, 'pa_manuel-dutilisation');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                if (filter_var($term->name, FILTER_VALIDATE_URL)) {
                                    $friendly_name = extract_friendly_name_from_url($term->name);
                                    $manuel_utilisation[] = array(
                                        'url' => $term->name,
                                        'friendly_name' => $friendly_name
                                    );
                                }
                            }
                        }
                    }
                    
                    if (taxonomy_exists('pa_datasheet')) {
                        $terms = get_the_terms($product_id, 'pa_datasheet');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                if (filter_var($term->name, FILTER_VALIDATE_URL)) {
                                    $friendly_name = extract_friendly_name_from_url($term->name);
                                    $datasheet[] = array(
                                        'url' => $term->name,
                                        'friendly_name' => $friendly_name
                                    );
                                }
                            }
                        }
                    }
                    
                    if (taxonomy_exists('pa_manuel-de-reparation')) {
                        $terms = get_the_terms($product_id, 'pa_manuel-de-reparation');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                if (filter_var($term->name, FILTER_VALIDATE_URL)) {
                                    $friendly_name = extract_friendly_name_from_url($term->name);
                                    $manuel_reparation[] = array(
                                        'url' => $term->name,
                                        'friendly_name' => $friendly_name
                                    );
                                }
                            }
                        }
                    }
                    
                    if (taxonomy_exists('pa_reference-fabriquant')) {
                        $terms = get_the_terms($product_id, 'pa_reference-fabriquant');
                        if ($terms && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                $reference_fabriquant[] = $term->name;
                            }
                        }
                    }
                    
                    // Marque (Brand) - Taxonomie pwb-brand - TOUTES les valeurs
                    $terms = get_the_terms($product_id, 'pwb-brand');
                    if ($terms && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $brand[] = $term->name;
                        }
                    }
                    
                    $products_with_docs[] = array(
                        'id' => $product_id,
                        'name' => $row['name'],
                        'documentation_url' => $row['documentation_url'],
                        'famille' => $famille,
                        'sous_famille' => $sous_famille,
                        'sous_sous_famille' => $sous_sous_famille,
                        'vue_eclatee' => $vue_eclatee,
                        'manuel_utilisation' => $manuel_utilisation,
                        'datasheet' => $datasheet,
                        'manuel_reparation' => $manuel_reparation,
                        'reference_fabriquant' => $reference_fabriquant,
                        'brand' => $brand,
                        'permalink' => get_permalink($product_id)
                    );
                }
            }
            
            // Cache pendant 30 minutes
            wp_cache_set($cache_key, $products_with_docs, '', 1800);
            
            return $products_with_docs;
        }

        // Récupérer TOUS les produits avec documentation (optimisé)
        $products_with_docs = get_products_with_documentation_optimized();

        // Appliquer les filtres de recherche et famille
        $filtered_products = $products_with_docs;
        
        if (!empty($search_query)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($search_query) {
                // Recherche dans le nom du produit
                if (stripos($product['name'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans la famille (array)
                if (!empty($product['famille']) && is_array($product['famille'])) {
                    foreach ($product['famille'] as $famille) {
                        if (stripos($famille, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans la sous-famille (array)
                if (!empty($product['sous_famille']) && is_array($product['sous_famille'])) {
                    foreach ($product['sous_famille'] as $sous_famille) {
                        if (stripos($sous_famille, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans la sous-sous-famille (array)
                if (!empty($product['sous_sous_famille']) && is_array($product['sous_sous_famille'])) {
                    foreach ($product['sous_sous_famille'] as $sous_sous_famille) {
                        if (stripos($sous_sous_famille, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans la référence fabriquant (array)
                if (!empty($product['reference_fabriquant']) && is_array($product['reference_fabriquant'])) {
                    foreach ($product['reference_fabriquant'] as $reference) {
                        if (stripos($reference, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans la marque (array)
                if (!empty($product['brand']) && is_array($product['brand'])) {
                    foreach ($product['brand'] as $brand) {
                        if (stripos($brand, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans les types de documentation (friendly_names)
                if (!empty($product['vue_eclatee']) && is_array($product['vue_eclatee'])) {
                    foreach ($product['vue_eclatee'] as $doc) {
                        if (isset($doc['friendly_name']) && stripos($doc['friendly_name'], $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                if (!empty($product['manuel_utilisation']) && is_array($product['manuel_utilisation'])) {
                    foreach ($product['manuel_utilisation'] as $doc) {
                        if (isset($doc['friendly_name']) && stripos($doc['friendly_name'], $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                if (!empty($product['datasheet']) && is_array($product['datasheet'])) {
                    foreach ($product['datasheet'] as $doc) {
                        if (isset($doc['friendly_name']) && stripos($doc['friendly_name'], $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                if (!empty($product['manuel_reparation']) && is_array($product['manuel_reparation'])) {
                    foreach ($product['manuel_reparation'] as $doc) {
                        if (isset($doc['friendly_name']) && stripos($doc['friendly_name'], $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                return false;
            });
        }
        
        if (!empty($selected_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_famille) {
                return is_array($product['famille']) && in_array($selected_famille, $product['famille']);
            });
        }
        
        if (!empty($selected_sous_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_sous_famille) {
                return is_array($product['sous_famille']) && in_array($selected_sous_famille, $product['sous_famille']);
            });
        }
        
        if (!empty($selected_sous_sous_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_sous_sous_famille) {
                return is_array($product['sous_sous_famille']) && in_array($selected_sous_sous_famille, $product['sous_sous_famille']);
            });
        }
        
        // NOUVEAU : Filtre pour les types de documents multi-sélection
        if (!empty($selected_doc_types)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_doc_types) {
                foreach ($selected_doc_types as $doc_type) {
                    switch ($doc_type) {
                        case 'vue_eclatee':
                            if (!empty($product['vue_eclatee']) && is_array($product['vue_eclatee'])) {
                                return true;
                            }
                            break;
                        case 'manuel_utilisation':
                            if (!empty($product['manuel_utilisation']) && is_array($product['manuel_utilisation'])) {
                                return true;
                            }
                            break;
                        case 'datasheet':
                            if (!empty($product['datasheet']) && is_array($product['datasheet'])) {
                                return true;
                            }
                            break;
                        case 'manuel_reparation':
                            if (!empty($product['manuel_reparation']) && is_array($product['manuel_reparation'])) {
                                return true;
                            }
                            break;
                    }
                }
                return false;
            });
        }
        
        if (!empty($selected_reference_fabriquant)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_reference_fabriquant) {
                return is_array($product['reference_fabriquant']) && in_array($selected_reference_fabriquant, $product['reference_fabriquant']);
            });
        }
        
        if (!empty($selected_brand)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_brand) {
                return is_array($product['brand']) && in_array($selected_brand, $product['brand']);
            });
        }

        // Récupérer les valeurs uniques pour les filtres depuis les arrays
        $familles = [];
        $sous_familles = [];
        $sous_sous_familles = [];
        $vues_eclatees = [];
        $manuels_utilisation = [];
        $datasheets = [];
        $manuels_reparation = [];
        $references_fabriquant = [];
        $brands = [];
        
        foreach ($products_with_docs as $product) {
            // Familles
            if (is_array($product['famille'])) {
                $familles = array_merge($familles, $product['famille']);
            }
            
            // Sous-familles  
            if (is_array($product['sous_famille'])) {
                $sous_familles = array_merge($sous_familles, $product['sous_famille']);
            }
            
            // Sous-sous-familles
            if (is_array($product['sous_sous_famille'])) {
                $sous_sous_familles = array_merge($sous_sous_familles, $product['sous_sous_famille']);
            }
            
            // Types de documentation
            if (is_array($product['vue_eclatee'])) {
                foreach ($product['vue_eclatee'] as $doc) {
                    if (isset($doc['friendly_name'])) {
                        $vues_eclatees[] = $doc['friendly_name'];
                    }
                }
            }
            
            if (is_array($product['manuel_utilisation'])) {
                foreach ($product['manuel_utilisation'] as $doc) {
                    if (isset($doc['friendly_name'])) {
                        $manuels_utilisation[] = $doc['friendly_name'];
                    }
                }
            }
            
            if (is_array($product['datasheet'])) {
                foreach ($product['datasheet'] as $doc) {
                    if (isset($doc['friendly_name'])) {
                        $datasheets[] = $doc['friendly_name'];
                    }
                }
            }
            
            if (is_array($product['manuel_reparation'])) {
                foreach ($product['manuel_reparation'] as $doc) {
                    if (isset($doc['friendly_name'])) {
                        $manuels_reparation[] = $doc['friendly_name'];
                    }
                }
            }
            
            // Références fabriquant
            if (is_array($product['reference_fabriquant'])) {
                $references_fabriquant = array_merge($references_fabriquant, $product['reference_fabriquant']);
            }
            
            // Marques
            if (is_array($product['brand'])) {
                $brands = array_merge($brands, $product['brand']);
            }
        }
        
        // Nettoyer et dédupliquer toutes les listes
        $familles = array_filter(array_unique($familles));
        $sous_familles = array_filter(array_unique($sous_familles));
        $sous_sous_familles = array_filter(array_unique($sous_sous_familles));
        $vues_eclatees = array_filter(array_unique($vues_eclatees));
        $manuels_utilisation = array_filter(array_unique($manuels_utilisation));
        $datasheets = array_filter(array_unique($datasheets));
        $manuels_reparation = array_filter(array_unique($manuels_reparation));
        $references_fabriquant = array_filter(array_unique($references_fabriquant));
        $brands = array_filter(array_unique($brands));
        
        // Tri alphabétique pour tous
        natcasesort($familles);
        natcasesort($sous_familles);
        natcasesort($sous_sous_familles);
        natcasesort($vues_eclatees);
        natcasesort($manuels_utilisation);
        natcasesort($datasheets);
        natcasesort($manuels_reparation);
        natcasesort($references_fabriquant);
        natcasesort($brands);
        
        // Réindexer
        $familles = array_values($familles);
        $sous_familles = array_values($sous_familles);
        $sous_sous_familles = array_values($sous_sous_familles);
        $vues_eclatees = array_values($vues_eclatees);
        $manuels_utilisation = array_values($manuels_utilisation);
        $datasheets = array_values($datasheets);
        $manuels_reparation = array_values($manuels_reparation);
        $references_fabriquant = array_values($references_fabriquant);
        $brands = array_values($brands);
        
        // Calculer les compteurs pour chaque type de document
        $doc_type_counts = [
            'vue_eclatee' => 0,
            'manuel_utilisation' => 0,
            'datasheet' => 0,
            'manuel_reparation' => 0
        ];
        
        foreach ($products_with_docs as $product) {
            if (!empty($product['vue_eclatee']) && is_array($product['vue_eclatee'])) {
                $doc_type_counts['vue_eclatee']++;
            }
            if (!empty($product['manuel_utilisation']) && is_array($product['manuel_utilisation'])) {
                $doc_type_counts['manuel_utilisation']++;
            }
            if (!empty($product['datasheet']) && is_array($product['datasheet'])) {
                $doc_type_counts['datasheet']++;
            }
            if (!empty($product['manuel_reparation']) && is_array($product['manuel_reparation'])) {
                $doc_type_counts['manuel_reparation']++;
            }
        }
        
        // Créer une liste combinée pour l'auto-complétion du champ de recherche principal
        $all_search_values = [];
        
        // Ajouter tous les noms de produits
        foreach ($products_with_docs as $product) {
            if (!empty($product['name'])) {
                $all_search_values[] = $product['name'];
            }
        }
        
        // Ajouter toutes les valeurs des autres champs (déjà nettoyées)
        $all_search_values = array_merge($all_search_values, $familles);
        $all_search_values = array_merge($all_search_values, $sous_familles);
        $all_search_values = array_merge($all_search_values, $sous_sous_familles);
        $all_search_values = array_merge($all_search_values, $vues_eclatees);
        $all_search_values = array_merge($all_search_values, $manuels_utilisation);
        $all_search_values = array_merge($all_search_values, $datasheets);
        $all_search_values = array_merge($all_search_values, $manuels_reparation);
        $all_search_values = array_merge($all_search_values, $references_fabriquant);
        $all_search_values = array_merge($all_search_values, $brands);
        
        // Nettoyer, dédupliquer et trier
        $all_search_values = array_filter(array_unique($all_search_values), function($value) {
            return !empty($value) && trim($value) !== '';
        });
        natcasesort($all_search_values);
        $all_search_values = array_values($all_search_values);
        
        // NOUVELLE FONCTIONNALITÉ : Analyser les documents uniques dans les produits filtrés
        $unique_documents = [
            'vue_eclatee' => [],
            'manuel_utilisation' => [],
            'datasheet' => [],
            'manuel_reparation' => [],
            'catalogue' => []
        ];
        
        foreach ($filtered_products as $product) {
            // Catalogue
            if (!empty($product['documentation_url']) && filter_var($product['documentation_url'], FILTER_VALIDATE_URL)) {
                $unique_documents['catalogue'][$product['documentation_url']] = 'Catalogue';
            }
            
            // Vue éclatée
            if (!empty($product['vue_eclatee']) && is_array($product['vue_eclatee'])) {
                foreach ($product['vue_eclatee'] as $doc) {
                    if (isset($doc['url']) && filter_var($doc['url'], FILTER_VALIDATE_URL)) {
                        $unique_documents['vue_eclatee'][$doc['url']] = $doc['friendly_name'];
                    }
                }
            }
            
            // Manuel utilisation
            if (!empty($product['manuel_utilisation']) && is_array($product['manuel_utilisation'])) {
                foreach ($product['manuel_utilisation'] as $doc) {
                    if (isset($doc['url']) && filter_var($doc['url'], FILTER_VALIDATE_URL)) {
                        $unique_documents['manuel_utilisation'][$doc['url']] = $doc['friendly_name'];
                    }
                }
            }
            
            // Datasheet
            if (!empty($product['datasheet']) && is_array($product['datasheet'])) {
                foreach ($product['datasheet'] as $doc) {
                    if (isset($doc['url']) && filter_var($doc['url'], FILTER_VALIDATE_URL)) {
                        $unique_documents['datasheet'][$doc['url']] = $doc['friendly_name'];
                    }
                }
            }
            
            // Manuel réparation
            if (!empty($product['manuel_reparation']) && is_array($product['manuel_reparation'])) {
                foreach ($product['manuel_reparation'] as $doc) {
                    if (isset($doc['url']) && filter_var($doc['url'], FILTER_VALIDATE_URL)) {
                        $unique_documents['manuel_reparation'][$doc['url']] = $doc['friendly_name'];
                    }
                }
            }
        }
        
        // Compter les documents uniques
        $unique_counts = [
            'catalogue' => count($unique_documents['catalogue']),
            'vue_eclatee' => count($unique_documents['vue_eclatee']),
            'manuel_utilisation' => count($unique_documents['manuel_utilisation']),
            'datasheet' => count($unique_documents['datasheet']),
            'manuel_reparation' => count($unique_documents['manuel_reparation'])
        ];
        
        // Pagination sur les produits filtrés - NOUVEAU SYSTÈME VOIR PLUS
        $total_products = count($filtered_products);
        $current_page_products = array_slice($filtered_products, 0, $visible_count);
        $has_more_products = $total_products > $visible_count;
        
        ?>
        <div class="documentation-center">
            <style>
                .documentation-center {
                    font-family: Arial, sans-serif;
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 30px 0px;
                }
                
                .doc-header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding: 20px;
                    background: #0066cc;
                    color: white;
                    border-radius: 8px;
                }
                
                .doc-header h1 {
                    margin: 0 0 2px 0;
                    font-size: 2.5em;
                    font-weight: bold;
                    color: white;
                }
                
                .doc-header p {
                    margin: 0;
                    font-size: 1.1em;
                    opacity: 0.9;
                }
                
                .search-form {
                    max-width: 28rem;
                    margin: 0 auto 30px auto;
                }
                
                .search-container {
                    position: relative;
                }
                
                .search-container .search-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 1px solid #6b7280;
                    border-top: none;
                    border-radius: 0 0 0.5rem 0.5rem;
                    max-height: 300px;
                    overflow-y: auto;
                    z-index: 1000;
                    display: none;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                }
                
                .search-dropdown .search-option {
                    padding: 10px 15px;
                    cursor: pointer;
                    border-bottom: 1px solid #e5e7eb;
                    transition: background-color 0.2s;
                    font-size: 0.875rem;
                }
                
                .search-dropdown .search-option:hover,
                .search-dropdown .search-option.selected {
                    background-color: #f3f4f6;
                }
                
                .search-dropdown .search-option:last-child {
                    border-bottom: none;
                }
                
                .search-icon {
                    position: absolute;
                    left: 0.95rem;
                    top: 59%;
                    transform: translateY(-50%);
                    pointer-events: none;
                    color: #6b7280;
                }
                
                .search-input {
                    width: 100%;
                    padding: 1rem 1rem 1rem 2.5rem;
                    border: 1px solid #6b7280;
                    border-radius: 0.5rem;
                    background-color: #f3f4f6;
                    font-size: 0.875rem;
                    color: #000000 !important;
                }
                
                .search-input:focus {
                    outline: none;
                    border: 2px solid #0066cc;
                }
                
                .search-input::placeholder {
                    color: #333 !important;
                }
                
                .search-button {
                    position: absolute;
                    right: 0.5rem;
                    top: 50%;
                    transform: translateY(-50%);
                    background-color: #0066cc;
                    color: white;
                    padding: 0.5rem 1rem;
                    border: none;
                    border-radius: 0.5rem;
                    font-size: 0.875rem;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                
                .search-button:hover {
                    background-color: #0052a3;
                }
                
                .search-button:focus {
                    outline: none;
                    box-shadow: 0 0 0 4px #93c5fd;
                }
                
                .select-with-search {
                    position: relative;
                }
                
                .select-with-search .search-icon {
                    position: absolute;
                    left: 0.8rem;
                    top: 59%;
                    transform: translateY(-50%);
                    pointer-events: none;
                    color: #6b7280;
                    z-index: 1;
                }
                
                .select-search-input {
                    width: 100% !important;
                    padding: 10px 10px 10px 2rem !important;
                    border: 1px solid #6b7280 !important;
                    border-radius: 5px !important;
                    font-size: 13px !important;
                    background: white !important;
                }
                
                .select-search-input:focus {
                    border-color: #0066cc;
                    outline: none;
                    border: 2px solid #0066cc !important;
                    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15) !important;
                }
                
                /* Filtre actif */
                .filter-active {
                    border: 3px solid #16a34a !important; 
                    background: #f0fdf4 !important; 
                }
                
                .filter-active:focus {
                    border: 3px solid #16a34a !important; 
                    background: #f0fdf4 !important;
                }
                
                .select-dropdown {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 1px solid #6b7280;
                    border-top: none;
                    border-radius: 0 0 5px 5px;
                    max-height: 200px;
                    overflow-y: auto;
                    z-index: 1000;
                    display: none;
                }
                
                .select-option {
                    padding: 10px;
                    cursor: pointer;
                    border-bottom: 1px solid #e5e7eb;
                    transition: background-color 0.2s;
                }
                
                .select-option:hover,
                .select-option.selected {
                    background-color: #f3f4f6;
                }
                
                .select-option:last-child {
                    border-bottom: none;
                }
                
                .filters-container {
                    background: #f3f4f6;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                    border: 1px solid #6b7280;
                }
                
                .filters-row {
                    display: flex;
                    gap: 15px;
                    flex-wrap: wrap;
                    align-items: flex-end;
                }
                
                .filter-group {
                    flex: 1;
                    min-width: 200px;
                }
                
                .filter-group label {
                    font-weight: bold;
                    color: #333;
                }
                
                .filter-group select {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #6b7280;
                    border-radius: 5px;
                    font-size: 13px;
                    background: white;
                }
                
                .filter-group select:focus {
                    border-color: #0066cc;
                    outline: none;
                    border: 2px solid #0066cc !important;
                    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15) !important;
                }
                
                .filter-actions {
                    display: flex;
                    gap: 10px;
                    align-items: end;
                    width: 100%;
                    justify-content: center;
                }
                
                .btn-filter {
                    background: #0066cc;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    transition: background 0.3s;
                }
                
                .btn-filter:hover {
                    background: #0052a3;
                }
                
                .btn-reset {
                    background: #000000;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    transition: background 0.3s;
                }
                
                .btn-reset:focus {
                    outline: none;
                    box-shadow: 0 0 0 4px #6b7280;
                }
                
                /* Styles pour les options de types de documents */
                .doc-type-option {
                    padding: 10px;
                    cursor: pointer;
                    border-bottom: 1px solid #e5e7eb;
                    transition: background-color 0.2s;
                }
                
                .doc-type-option:hover {
                    background-color: #f3f4f6;
                }
                
                .doc-type-option:last-child {
                    border-bottom: none;
                }
                
                .doc-checkbox-label {
                    display: flex;
                    align-items: center;
                    cursor: pointer;
                    font-size: 13px;
                    margin: 0;
                    width: 100%;
                }
                
                .doc-checkbox-label input[type="checkbox"] {
                    width: 16px;
                    height: 16px;
                    accent-color: #0066cc;
                    cursor: pointer;
                }
                
                .doc-count {
                    color: #6b7280;
                    font-size: 0.85em;
                    font-weight: normal;
                    margin-left: auto;
                }
                
                .doc-checkbox-label input[type="checkbox"]:checked ~ .doc-count {
                    color: #0066cc;
                    font-weight: bold;
                }
                
                /* Style pour l'input readonly */
                #filter-doc-types[readonly] {
                    cursor: pointer;
                }
                
                #filter-doc-types[readonly]:focus {
                    cursor: pointer;
                }
                
                .results-container {
                    margin-top: 30px;
                }
                
                .results-header {
                    background: #f3f4f6;
                    padding: 15px;
                    border-radius: 5px 5px 0 0;
                    border: 1px solid #6b7280;
                    border-bottom: 2px solid #0066cc;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .results-count {
                    font-weight: bold;
                    color: #0066cc;
                    font-size: 1.1em;
                }
                
                .pagination-info {
                    color: #333;
                    font-size: 0.9em;
                }
                
                .products-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                    gap: 20px;
                    margin-top: 30px;
                }
                
                .product-card {
                    background: white;
                    border: 1px solid #6b7280;
                    border-radius: 8px;
                    padding: 20px;
                    transition: box-shadow 0.3s, transform 0.2s;
                }
                
                .product-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
                    transform: translateY(-2px);
                }
                
                .product-title {
                    font-size: 1.2em;
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #333;
                }
                
                .product-title a {
                    color: #0066cc;
                    text-decoration: none;
                }
                
                .product-title a:hover {
                    text-decoration: underline;
                }
                
                .product-categories {
                    margin-bottom: 15px;
                    font-size: 0.9em;
                }
                
                .category-tag {
                    display: inline-block;
                    background: #f8f9fa;
                    color: #495057;
                    padding: 4px 8px;
                    border-radius: 12px;
                    margin: 4px 2px;
                    font-size: 0.85em;
                    border: 1px solid #e9ecef;
                }
                
                .famille { border-left: 4px solid #0066cc; }
                .sous-famille { border-left: 4px solid #28a745; }
                .sous-sous-famille { border-left: 4px solid #ffc107; }
                .reference-fabriquant { border-left: 4px solid #6f42c1; }
                .categorie-wp { border-left: 4px solid #e31206; }
                .brand { border-left: 4px solid #17a2b8; }
                
                .download-links {
                    margin-top: 15px;
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                }
                
                .download-link {
                    display: inline-block;
                    background: #0066cc;
                    color: white;
                    padding: 8px 12px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    font-size: 0.85em;
                    transition: background 0.3s;
                    flex: 1;
                    min-width: 120px;
                    text-align: center;
                }
                
                .download-link:hover {
                    background: #0052a3;
                    color: white;
                    text-decoration: none;
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
                
                /* Style pour les boutons désactivés */
                .download-link.disabled {
                    cursor: default;
                    pointer-events: none;
                    position: relative;
                }
                
                .pagination-container {
                    text-align: center;
                    margin-top: 30px;
                    padding: 0;
                }
                

                
                .no-results {
                    text-align: center;
                    padding: 40px;
                    color: #6c757d;
                    font-size: 1.1em;
                }
                
                @media (max-width: 768px) {
                    .filters-row {
                        flex-direction: column;
                    }
                    
                    .filter-group {
                        min-width: 100%;
                    }
                    
                    .products-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .results-header {
                        flex-direction: column;
                        gap: 10px;
                    }
                    
                    .pagination-container {
                        padding: 0 10px;
                    }
                    .select-with-search .search-icon {
                        top: 57%;
                    }
                }
                
                .load-more-button {
                    background-color: #0066cc;
                    color: white;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 0.5rem;
                    font-size: 0.875rem;
                    font-weight: bold;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .load-more-button:hover {
                    background-color: #0052a3;
                    color: white;
                    text-decoration: none;
                }
                
                .load-more-button:focus {
                    outline: none;
                    box-shadow: 0 0 0 4px #93c5fd;
                }
                
                /* Styles pour le résumé des documents uniques */
                .unique-docs-summary {
                    background: #f3f4f6;
                    border: 1px solid #6b7280;
                    border-radius: 12px;
                    padding: 20px;
                    margin: 25px 0;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                }
                
                .summary-title {
                    font-size: 1.1em;
                    font-weight: bold;
                    color: #1e293b;
                    margin-bottom: 15px;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .summary-title svg {
                    color: #0066cc;
                }
                
                .summary-help {
                    font-size: 0.85em;
                    color: #64748b;
                    margin-bottom: 15px;
                    text-align: center;
                    font-style: italic;
                }
                
                .summary-help strong {
                    color: #0066cc;
                    font-weight: 600;
                }
                
                .summary-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 12px;
                }
                
                .summary-card {
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 15px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    cursor: pointer;
                    position: relative;
                    overflow: hidden;
                }
                
                .summary-card::before {
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    bottom: 0;
                    width: 4px;
                }
                
                .summary-card.catalogue::before { background: #0066cc; }
                .summary-card.vue-eclatee::before { background: #7e22ce; }
                .summary-card.manuel-utilisation::before { background: #15803d; }
                .summary-card.datasheet::before { background: #111827; }
                .summary-card.manuel-reparation::before { background: #e31206; }
                
                .summary-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                    transform: translateY(-2px);
                }
                
                .summary-card:hover::before {
                    width: 6px;
                }
                
                .summary-info {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }
                
                .summary-label {
                    font-size: 0.9em;
                    color: #64748b;
                    font-weight: 500;
                }
                
                .summary-count {
                    font-size: 1.4em;
                    font-weight: bold;
                    color: #1e293b;
                }
                
                .summary-icon {
                    opacity: 0.7;
                }
                
                .summary-card:hover .summary-icon {
                    opacity: 1;
                }
                
                .summary-card.zero {
                    opacity: 0.5;
                    cursor: default;
                    pointer-events: none;
                }
                
                .summary-card.zero .summary-count {
                    color: #94a3b8;
                }
                
                .summary-card.active {
                    border-color: #0066cc;
                    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
                    transform: translateY(-2px);
                }
                
                .summary-card.active::before {
                    width: 6px;
                }
                
                .summary-card.active .summary-count {
                    color: #0066cc;
                }
                
                @media (max-width: 768px) {
                    .summary-grid {
                        grid-template-columns: repeat(2, 1fr);
                        gap: 8px;
                    }
                    
                    .summary-card {
                        padding: 12px;
                    }
                    
                    .summary-count {
                        font-size: 1.2em;
                    }
                    
                    .summary-label {
                        font-size: 0.8em;
                    }
                }
                
                /* Styles pour la grille de documents intégrée */
                .documents-grid {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #e2e8f0;
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                    gap: 15px;
                    animation: slideDown 0.3s ease-out;
                }
                
                @keyframes slideDown {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                
                .document-card {
                    background: white;
                    border: 1px solid #e2e8f0;
                    border-radius: 8px;
                    transition: all 0.2s ease;
                    position: relative;
                    overflow: hidden;
                }
                
                .document-card::before {
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    bottom: 0;
                    width: 4px;
                    background: var(--doc-color, #0066cc);
                }
                
                .document-card:hover {
                    box-shadow: 0 4px 12px rgba(0, 102, 204, 0.15);
                    transform: translateY(-2px);
                    border-color: #0066cc;
                }
                
                .document-link {
                    display: flex;
                    align-items: center;
                    padding: 15px;
                    text-decoration: none;
                    color: #1e293b;
                    transition: color 0.2s;
                }
                
                .document-link:hover {
                    color: #0066cc;
                }
                
                .document-icon {
                    font-size: 24px;
                    margin-right: 12px;
                    flex-shrink: 0;
                }
                
                .document-info {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                }
                
                .document-name {
                    font-weight: 500;
                    font-size: 0.95em;
                    line-height: 1.4;
                }
                
                .document-type {
                    font-size: 0.8em;
                    color: #64748b;
                    font-weight: 600;
                    text-transform: uppercase;
                }
                
                @media (max-width: 768px) {
                    .documents-grid {
                        grid-template-columns: 1fr;
                        gap: 10px;
                    }
                    
                    .document-link {
                        padding: 12px;
                    }
                    
                    .document-icon {
                        font-size: 20px;
                        margin-right: 10px;
                    }
                    
                    .document-name {
                        font-size: 0.85em;
                    }
                    
                    .document-type {
                        font-size: 0.75em;
                    }
                    
                    .document-name {
                        font-size: 0.9em;
                    }
                }
            </style>
            
            <div class="doc-header">
                <h1>Centre de Documentation Technique</h1>
                <p>Recherchez et téléchargez les documentations techniques par famille de produits</p>
            </div>
            
            <!-- Formulaire de recherche -->
            <form method="GET" class="search-form">
                <div class="search-container">
                    <div class="search-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" 
                           id="main-search" 
                           name="search" 
                           value="<?php echo esc_attr($search_query); ?>" 
                           class="search-input" 
                           placeholder="Rechercher un produit..." 
                           autocomplete="off" />
                    <div id="main-search-dropdown" class="search-dropdown">
                        <?php foreach ($all_search_values as $value): ?>
                            <div class="search-option" data-value="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="search-button">
                        <svg style="margin-right:0.4em;vertical-align:middle;" width="16" height="16" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                        Rechercher
                    </button>
                </div>
                
                <!-- Champs cachés pour maintenir les filtres -->
                <input type="hidden" name="famille" value="<?php echo esc_attr($selected_famille); ?>">
                <input type="hidden" name="sous_famille" value="<?php echo esc_attr($selected_sous_famille); ?>">
                <input type="hidden" name="sous_sous_famille" value="<?php echo esc_attr($selected_sous_sous_famille); ?>">
                <?php foreach ($selected_doc_types as $doc_type): ?>
                <input type="hidden" name="doc_types[]" value="<?php echo esc_attr($doc_type); ?>">
                <?php endforeach; ?>
                <input type="hidden" name="reference_fabriquant" value="<?php echo esc_attr($selected_reference_fabriquant); ?>">
                <input type="hidden" name="brand" value="<?php echo esc_attr($selected_brand); ?>">
            </form>
            
            <div class="filters-container">
                <form method="GET" class="filters-row">
                    <input type="hidden" name="search" value="<?php echo esc_attr($search_query); ?>">
                    
                    <div class="filter-group">
                        <label for="filter-brand">Marque :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-brand" 
                                   class="select-search-input" 
                                   placeholder="Toutes les marques" 
                                   value="<?php echo esc_attr($selected_brand); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="brand" id="brand_hidden" value="<?php echo esc_attr($selected_brand); ?>" />
                            <div id="brand-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les marques</div>
                                <?php foreach ($brands as $brand): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($brand); ?>"><?php echo esc_html($brand); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-famille">Famille :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-famille" 
                                   class="select-search-input" 
                                   placeholder="Toutes les familles" 
                                   value="<?php echo esc_attr($selected_famille); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="famille" id="famille_hidden" value="<?php echo esc_attr($selected_famille); ?>" />
                            <div id="famille-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les familles</div>
                                <?php foreach ($familles as $famille): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($famille); ?>"><?php echo esc_html($famille); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-sous-famille">Sous-famille :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-sous-famille" 
                                   class="select-search-input" 
                                   placeholder="Toutes les sous-familles" 
                                   value="<?php echo esc_attr($selected_sous_famille); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="sous_famille" id="sous_famille_hidden" value="<?php echo esc_attr($selected_sous_famille); ?>" />
                            <div id="sous-famille-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les sous-familles</div>
                                <?php foreach ($sous_familles as $sous_famille): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($sous_famille); ?>"><?php echo esc_html($sous_famille); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-sous-sous-famille">Sous-sous-famille :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-sous-sous-famille" 
                                   class="select-search-input" 
                                   placeholder="Toutes les sous-sous-familles" 
                                   value="<?php echo esc_attr($selected_sous_sous_famille); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="sous_sous_famille" id="sous_sous_famille_hidden" value="<?php echo esc_attr($selected_sous_sous_famille); ?>" />
                            <div id="sous-sous-famille-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les sous-sous-familles</div>
                                <?php foreach ($sous_sous_familles as $sous_sous_famille): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($sous_sous_famille); ?>"><?php echo esc_html($sous_sous_famille); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-doc-types">Types de documentation :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-doc-types" 
                                   class="select-search-input" 
                                   placeholder="<?php 
                                   if (empty($selected_doc_types)) {
                                       echo 'Tous les types de documents';
                                   } else {
                                       $doc_labels = [
                                           'vue_eclatee' => 'Vue éclatée',
                                           'manuel_utilisation' => 'Manuel utilisation', 
                                           'datasheet' => 'Datasheet',
                                           'manuel_reparation' => 'Manuel réparation'
                                       ];
                                       $selected_labels = [];
                                       foreach ($selected_doc_types as $type) {
                                           if (isset($doc_labels[$type])) {
                                               $selected_labels[] = $doc_labels[$type];
                                           }
                                       }
                                       echo count($selected_labels) . ' type' . (count($selected_labels) > 1 ? 's' : '') . ' sélectionné' . (count($selected_labels) > 1 ? 's' : '');
                                   }
                                   ?>"
                                   readonly
                                   autocomplete="off" />
                            <div id="doc-types-dropdown" class="select-dropdown">
                                <div class="doc-type-option" data-value="">
                                    <label class="doc-checkbox-label">
                                        <input type="checkbox" class="doc-clear-all" style="margin-right: 8px;">
                                        <strong>Tout désélectionner</strong>
                                    </label>
                                </div>
                                <div class="doc-type-option" data-value="vue_eclatee">
                                    <label class="doc-checkbox-label">
                                        <input type="checkbox" name="doc_types[]" value="vue_eclatee" 
                                               <?php echo in_array('vue_eclatee', $selected_doc_types) ? 'checked' : ''; ?>
                                               style="margin-right: 8px;">
                                        Vue éclatée <span class="doc-count">(<?php echo $doc_type_counts['vue_eclatee']; ?>)</span>
                                    </label>
                                </div>
                                <div class="doc-type-option" data-value="manuel_utilisation">
                                    <label class="doc-checkbox-label">
                                        <input type="checkbox" name="doc_types[]" value="manuel_utilisation" 
                                               <?php echo in_array('manuel_utilisation', $selected_doc_types) ? 'checked' : ''; ?>
                                               style="margin-right: 8px;">
                                        Manuel utilisation <span class="doc-count">(<?php echo $doc_type_counts['manuel_utilisation']; ?>)</span>
                                    </label>
                                </div>
                                <div class="doc-type-option" data-value="datasheet">
                                    <label class="doc-checkbox-label">
                                        <input type="checkbox" name="doc_types[]" value="datasheet" 
                                               <?php echo in_array('datasheet', $selected_doc_types) ? 'checked' : ''; ?>
                                               style="margin-right: 8px;">
                                        Datasheet <span class="doc-count">(<?php echo $doc_type_counts['datasheet']; ?>)</span>
                                    </label>
                                </div>
                                <div class="doc-type-option" data-value="manuel_reparation">
                                    <label class="doc-checkbox-label">
                                        <input type="checkbox" name="doc_types[]" value="manuel_reparation" 
                                               <?php echo in_array('manuel_reparation', $selected_doc_types) ? 'checked' : ''; ?>
                                               style="margin-right: 8px;">
                                        Manuel réparation <span class="doc-count">(<?php echo $doc_type_counts['manuel_reparation']; ?>)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-reference-fabriquant">Référence fabriquant :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-reference-fabriquant" 
                                   class="select-search-input" 
                                   placeholder="Toutes les références" 
                                   value="<?php echo esc_attr($selected_reference_fabriquant); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="reference_fabriquant" id="reference_fabriquant_hidden" value="<?php echo esc_attr($selected_reference_fabriquant); ?>" />
                            <div id="reference-fabriquant-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les références</div>
                                <?php foreach ($references_fabriquant as $reference): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($reference); ?>"><?php echo esc_html($reference); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <a href="?" class="btn-reset">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw" style="vertical-align: middle; margin-right: 5px;">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                <path d="M16 16h5v5"/>
                            </svg>
                            Réinitialiser
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Résumé des documents uniques -->
            <?php 
            // Afficher le résumé seulement s'il y a des filtres actifs ou des résultats
            $has_active_filters = !empty($search_query) || !empty($selected_famille) || !empty($selected_sous_famille) || 
                                 !empty($selected_sous_sous_famille) || !empty($selected_doc_types) || 
                                 !empty($selected_reference_fabriquant) || !empty($selected_brand);
                                 
            if ($total_products > 0): ?>
            <div class="unique-docs-summary">
                <div class="summary-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10,9 9,9 8,9"/>
                    </svg>
                    Documents uniques disponibles :<?php echo $has_active_filters ? ' dans cette sélection' : ''; ?>
                </div>
                
                <div class="summary-grid">
                    <!-- Catalogue -->
                    <div class="summary-card catalogue <?php echo $unique_counts['catalogue'] == 0 ? 'zero' : ''; ?>" 
                         data-doc-type="catalogue" 
                         data-count="<?php echo $unique_counts['catalogue']; ?>">
                        <div class="summary-info">
                            <div class="summary-label">Catalogues</div>
                            <div class="summary-count"><?php echo $unique_counts['catalogue']; ?></div>
                        </div>
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-icon lucide-book-open">
                                <path d="M12 7v14"/>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Vue éclatée -->
                    <div class="summary-card vue-eclatee <?php echo $unique_counts['vue_eclatee'] == 0 ? 'zero' : ''; ?>" 
                         data-doc-type="vue_eclatee" 
                         data-count="<?php echo $unique_counts['vue_eclatee']; ?>">
                        <div class="summary-info">
                            <div class="summary-label">Vues éclatées</div>
                            <div class="summary-count"><?php echo $unique_counts['vue_eclatee']; ?></div>
                        </div>
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye">
                                <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Manuel utilisation -->
                    <div class="summary-card manuel-utilisation <?php echo $unique_counts['manuel_utilisation'] == 0 ? 'zero' : ''; ?>" 
                         data-doc-type="manuel_utilisation" 
                         data-count="<?php echo $unique_counts['manuel_utilisation']; ?>">
                        <div class="summary-info">
                            <div class="summary-label">Manuels utilisation</div>
                            <div class="summary-count"><?php echo $unique_counts['manuel_utilisation']; ?></div>
                        </div>
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                <path d="M6 8h2"/>
                                <path d="M6 12h2"/>
                                <path d="M16 8h2"/>
                                <path d="M16 12h2"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Datasheet -->
                    <div class="summary-card datasheet <?php echo $unique_counts['datasheet'] == 0 ? 'zero' : ''; ?>" 
                         data-doc-type="datasheet" 
                         data-count="<?php echo $unique_counts['datasheet']; ?>">
                        <div class="summary-info">
                            <div class="summary-label">Datasheets</div>
                            <div class="summary-count"><?php echo $unique_counts['datasheet']; ?></div>
                        </div>
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="9" y1="13" x2="15" y2="13"/>
                                <line x1="9" y1="17" x2="15" y2="17"/>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Manuel réparation -->
                    <div class="summary-card manuel-reparation <?php echo $unique_counts['manuel_reparation'] == 0 ? 'zero' : ''; ?>" 
                         data-doc-type="manuel_reparation" 
                         data-count="<?php echo $unique_counts['manuel_reparation']; ?>">
                        <div class="summary-info">
                            <div class="summary-label">Manuels réparation</div>
                            <div class="summary-count"><?php echo $unique_counts['manuel_reparation']; ?></div>
                        </div>
                        <div class="summary-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des documents intégrée dans le summary -->
                <div id="documentsGrid" class="documents-grid" style="display: none;"></div>
            </div>
            
            <script>
                // Données des documents uniques pour JavaScript
                const uniqueDocuments = {
                    catalogue: <?php echo json_encode($unique_documents['catalogue']); ?>,
                    vue_eclatee: <?php echo json_encode($unique_documents['vue_eclatee']); ?>,
                    manuel_utilisation: <?php echo json_encode($unique_documents['manuel_utilisation']); ?>,
                    datasheet: <?php echo json_encode($unique_documents['datasheet']); ?>,
                    manuel_reparation: <?php echo json_encode($unique_documents['manuel_reparation']); ?>
                };
                
                // Fonction pour détecter le type de fichier
                function getFileType(url) {
                    const extension = url.split('.').pop().toLowerCase();
                    const fileTypes = {
                        'pdf': 'PDF',
                        'doc': 'DOC',
                        'docx': 'DOCX',
                        'xls': 'XLS',
                        'xlsx': 'XLSX',
                        'ppt': 'PPT',
                        'pptx': 'PPTX',
                        'jpg': 'JPG',
                        'jpeg': 'JPEG',
                        'png': 'PNG',
                        'gif': 'GIF',
                        'zip': 'ZIP',
                        'rar': 'RAR',
                        'txt': 'TXT',
                        'rtf': 'RTF'
                    };
                    return fileTypes[extension] || extension.toUpperCase();
                }
                
                // Fonction pour afficher la liste des documents
                function showDocumentsList(docType) {
                    const grid = document.getElementById('documentsGrid');
                    
                    // Définir les couleurs selon le type de document
                    const colors = {
                        'catalogue': '#0066cc',
                        'vue_eclatee': '#7e22ce', 
                        'manuel_utilisation': '#15803d',
                        'datasheet': '#111827',
                        'manuel_reparation': '#e31206'
                    };
                    
                    // Vider la grille
                    grid.innerHTML = '';
                    
                    // Ajouter les documents
                    const docs = uniqueDocuments[docType] || {};
                    Object.entries(docs).forEach(([url, name]) => {
                        const docCard = document.createElement('div');
                        docCard.className = 'document-card';
                        docCard.style.setProperty('--doc-color', colors[docType] || '#0066cc');
                        
                        const docLink = document.createElement('a');
                        docLink.href = url;
                        docLink.target = '_blank';
                        docLink.rel = 'noopener noreferrer';
                        docLink.className = 'document-link';
                        
                        const docIcon = document.createElement('div');
                        docIcon.className = 'document-icon';
                        docIcon.innerHTML = '📄';
                        
                        const docInfo = document.createElement('div');
                        docInfo.className = 'document-info';
                        
                        const docName = document.createElement('div');
                        docName.className = 'document-name';
                        docName.textContent = name;
                        
                        const docTypeElement = document.createElement('div');
                        docTypeElement.className = 'document-type';
                        docTypeElement.textContent = '(' + getFileType(url) + ')';
                        
                        docInfo.appendChild(docName);
                        docInfo.appendChild(docTypeElement);
                        
                        docLink.appendChild(docIcon);
                        docLink.appendChild(docInfo);
                        docCard.appendChild(docLink);
                        grid.appendChild(docCard);
                    });
                    
                    // Afficher la grille
                    grid.style.display = 'grid';
                }
                
                // Fonction pour cacher la liste des documents
                function hideDocumentsList() {
                    document.getElementById('documentsGrid').style.display = 'none';
                }
                
                // Ajouter les événements de clic aux cartes de résumé
                document.addEventListener('DOMContentLoaded', function() {
                    const summaryCards = document.querySelectorAll('.summary-card');
                    let activeCardType = null;
                    
                    summaryCards.forEach(card => {
                        const count = parseInt(card.dataset.count);
                        if (count > 0) {
                            card.addEventListener('click', function(e) {
                                const docType = this.dataset.docType;
                                
                                // Empêcher le comportement par défaut
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Gérer l'état actif des cartes
                                summaryCards.forEach(c => c.classList.remove('active'));
                                this.classList.add('active');
                                activeCardType = docType;
                                
                                // Afficher la liste des documents
                                showDocumentsList(docType);
                            });
                            
                            // Effets visuels pour les cartes cliquables
                            card.addEventListener('mousedown', function() {
                                this.style.transform = 'translateY(-1px) scale(0.98)';
                            });
                            
                            card.addEventListener('mouseup', function() {
                                this.style.transform = 'translateY(-2px) scale(1)';
                            });
                                
                                card.addEventListener('mouseleave', function() {
                                    this.style.transform = '';
                                });
                        }
                    });
                    
                    // Afficher les catalogues par défaut si disponibles
                    const catalogueCard = document.querySelector('.summary-card.catalogue');
                    if (catalogueCard && parseInt(catalogueCard.dataset.count) > 0) {
                        catalogueCard.classList.add('active');
                        activeCardType = 'catalogue';
                        showDocumentsList('catalogue');
                    }
                });
            </script>
            <?php endif; ?>
            
            <div class="results-container">
                <div class="results-header">
                    <div class="results-count">
                        <?php echo $total_products; ?> documentation(s) trouvée(s)
                    </div>
                    <div class="pagination-info">
                        <?php echo count($current_page_products); ?> affichés
                        <?php if ($has_more_products): ?>
                            sur <?php echo $total_products; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (!empty($current_page_products)): ?>
                <div class="products-grid">
                    <?php foreach ($current_page_products as $product): ?>
                        <div class="product-card">
                            <div class="product-title">
                                <a href="<?php echo esc_url($product['permalink']); ?>" target="_blank">
                                    <?php echo esc_html($product['name']); ?>
                                </a>
                            </div>
                            
                            <div class="product-categories">
                                <?php if (!empty($product['brand']) && is_array($product['brand'])): ?>
                                    <?php foreach ($product['brand'] as $brand): ?>
                                        <span class="category-tag brand">Marque : <?php echo esc_html($brand); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($product['famille']) && is_array($product['famille'])): ?>
                                    <?php foreach ($product['famille'] as $famille): ?>
                                        <span class="category-tag famille">Famille : <?php echo esc_html($famille); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_famille']) && is_array($product['sous_famille'])): ?>
                                    <?php foreach ($product['sous_famille'] as $sous_famille): ?>
                                        <span class="category-tag sous-famille">Sous-famille : <?php echo esc_html($sous_famille); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_sous_famille']) && is_array($product['sous_sous_famille'])): ?>
                                    <?php foreach ($product['sous_sous_famille'] as $sous_sous_famille): ?>
                                        <span class="category-tag sous-sous-famille">Sous-sous-famille : <?php echo esc_html($sous_sous_famille); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($product['reference_fabriquant']) && is_array($product['reference_fabriquant'])): ?>
                                    <?php foreach ($product['reference_fabriquant'] as $reference): ?>
                                        <span class="category-tag reference-fabriquant">Réf : <?php echo esc_html($reference); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </div>
                            
                            <div class="download-links">
                                <a href="<?php echo esc_url($product['documentation_url']); ?>" 
                                   class="download-link" 
                                   target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Catalogue
                                </a>
                                
                                <?php if (!empty($product['vue_eclatee']) && is_array($product['vue_eclatee'])): ?>
                                    <?php foreach ($product['vue_eclatee'] as $vue): ?>
                                        <?php if (filter_var($vue['url'], FILTER_VALIDATE_URL)): ?>
                                        <a href="<?php echo esc_url($vue['url']); ?>" 
                                           class="download-link vue-eclatee-link" 
                                           target="_blank" title="Vue éclatée">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($vue['friendly_name']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="download-link vue-eclatee-link disabled" title="Vue éclatée disponible: <?php echo esc_attr($vue['url']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($vue['friendly_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_utilisation']) && is_array($product['manuel_utilisation'])): ?>
                                    <?php foreach ($product['manuel_utilisation'] as $manuel): ?>
                                        <?php if (filter_var($manuel['url'], FILTER_VALIDATE_URL)): ?>
                                        <a href="<?php echo esc_url($manuel['url']); ?>" 
                                           class="download-link manuel-link" 
                                           target="_blank" title="Manuel d'utilisation">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($manuel['friendly_name']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="download-link manuel-link disabled" title="Manuel d'utilisation disponible: <?php echo esc_attr($manuel['url']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($manuel['friendly_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_reparation']) && is_array($product['manuel_reparation'])): ?>
                                    <?php foreach ($product['manuel_reparation'] as $manuel): ?>
                                        <?php if (filter_var($manuel['url'], FILTER_VALIDATE_URL)): ?>
                                        <a href="<?php echo esc_url($manuel['url']); ?>" 
                                           class="download-link repair-link" 
                                           target="_blank" title="Manuel de réparation">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($manuel['friendly_name']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="download-link repair-link disabled" title="Manuel de réparation disponible: <?php echo esc_attr($manuel['url']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($manuel['friendly_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['datasheet']) && is_array($product['datasheet'])): ?>
                                    <?php foreach ($product['datasheet'] as $datasheet): ?>
                                        <?php if (filter_var($datasheet['url'], FILTER_VALIDATE_URL)): ?>
                                        <a href="<?php echo esc_url($datasheet['url']); ?>" 
                                           class="download-link datasheet-link" 
                                           target="_blank" title="Datasheet">
                                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($datasheet['friendly_name']); ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="download-link datasheet-link disabled" title="Datasheet disponible: <?php echo esc_attr($datasheet['url']); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                            <?php echo esc_html($datasheet['friendly_name']); ?>
                                        </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                

                
                <!-- Bouton Voir Plus -->
                <?php if ($has_more_products): ?>
                <div class="pagination-container">
                    <?php 
                    $current_params = $_GET;
                    $current_params['visible'] = $visible_count + $load_more_count;
                    ?>
                    <a href="?<?php echo http_build_query($current_params); ?>" class="load-more-button">
                        Voir plus
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-plus-icon lucide-circle-plus">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M8 12h8"/>
                            <path d="M12 8v8"/>
                        </svg>
                    </a>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="no-results">
                    <p>Aucune documentation trouvée pour les critères sélectionnés.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Configuration des champs avec recherche
                const searchFields = [
                    { inputId: 'main-search', hiddenId: null, dropdownId: 'main-search-dropdown', isMainSearch: true },
                    { inputId: 'filter-brand', hiddenId: 'brand_hidden', dropdownId: 'brand-dropdown' },
                    { inputId: 'filter-famille', hiddenId: 'famille_hidden', dropdownId: 'famille-dropdown' },
                    { inputId: 'filter-sous-famille', hiddenId: 'sous_famille_hidden', dropdownId: 'sous-famille-dropdown' },
                    { inputId: 'filter-sous-sous-famille', hiddenId: 'sous_sous_famille_hidden', dropdownId: 'sous-sous-famille-dropdown' },
                    { inputId: 'filter-reference-fabriquant', hiddenId: 'reference_fabriquant_hidden', dropdownId: 'reference-fabriquant-dropdown' }
                ];
                
                // Fonction pour vérifier et appliquer les styles aux filtres actifs
                function updateActiveFilters() {
                    let activeCount = 0;
                    
                    // Récupérer les paramètres de l'URL pour vérifier les filtres réellement actifs
                    const urlParams = new URLSearchParams(window.location.search);
                    
                    searchFields.forEach(config => {
                        const input = document.getElementById(config.inputId);
                        const hidden = config.hiddenId ? document.getElementById(config.hiddenId) : null;
                        
                        if (input) {
                            // Vérifier si le filtre est actif selon l'URL (valeur soumise)
                            let hasValue = false;
                            
                            if (config.isMainSearch) {
                                // Pour la recherche principale, vérifier le paramètre 'search' dans l'URL
                                hasValue = urlParams.get('search') && urlParams.get('search').trim() !== '';
                            } else {
                                // Pour les autres filtres, vérifier le champ hidden
                                hasValue = hidden && hidden.value.trim() !== '';
                            }
                            
                            // Appliquer ou retirer la classe active
                            if (hasValue) {
                                input.classList.add('filter-active');
                                activeCount++;
                            } else {
                                input.classList.remove('filter-active');
                            }
                        }
                    });
                    
                    // Vérifier les checkboxes de types de documents
                    const checkedDocTypes = document.querySelectorAll('#doc-types-dropdown input[type="checkbox"]:checked:not(.doc-clear-all)');
                    if (checkedDocTypes.length > 0) {
                        activeCount += checkedDocTypes.length;
                        
                        // Appliquer le style actif au champ types de documents
                        const docTypesInput = document.getElementById('filter-doc-types');
                        if (docTypesInput) {
                            docTypesInput.classList.add('filter-active');
                        }
                    } else {
                        // Retirer le style actif
                        const docTypesInput = document.getElementById('filter-doc-types');
                        if (docTypesInput) {
                            docTypesInput.classList.remove('filter-active');
                        }
                    }
                    
                    // Mettre à jour le bouton Réinitialiser avec le compteur
                    const resetButton = document.querySelector('.btn-reset');
                    if (resetButton) {
                        const originalText = 'Réinitialiser';
                        if (activeCount > 0) {
                            resetButton.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw" style="vertical-align: middle; margin-right: 5px;">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                    <path d="M16 16h5v5"/>
                                </svg>
                                ${originalText} (${activeCount})
                            `;
                        } else {
                            resetButton.innerHTML = `
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw" style="vertical-align: middle; margin-right: 5px;">
                                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                    <path d="M16 16h5v5"/>
                                </svg>
                                ${originalText}
                            `;
                        }
                    }
                }
                
                // Appliquer les styles au chargement de la page
                updateActiveFilters();
                
                // Fonction pour initialiser un champ de recherche
                function initSearchField(config) {
                    const input = document.getElementById(config.inputId);
                    const hidden = config.hiddenId ? document.getElementById(config.hiddenId) : null;
                    const dropdown = document.getElementById(config.dropdownId);
                    
                    if (!input || !dropdown) return;
                    
                    const form = input.closest('form');
                    let selectedIndex = -1;
                    let isOpen = false;
                    
                    // Classe d'option selon le type de champ
                    const optionClass = config.isMainSearch ? '.search-option' : '.select-option';
                    
                    // Fonction pour filtrer les options
                    function filterOptions() {
                        const value = input.value.toLowerCase().trim();
                        const options = dropdown.querySelectorAll(optionClass);
                        let visibleCount = 0;
                        
                        options.forEach((option, index) => {
                            const text = option.textContent.toLowerCase();
                            if (text.includes(value) || option.dataset.value === '') {
                                option.style.display = 'block';
                                visibleCount++;
                            } else {
                                option.style.display = 'none';
                            }
                        });
                        
                        return visibleCount > 0;
                    }
                    
                    // Fonction pour ouvrir/fermer le dropdown
                    function toggleDropdown(show) {
                        isOpen = show;
                        dropdown.style.display = show ? 'block' : 'none';
                        selectedIndex = -1;
                        
                        if (show) {
                            filterOptions();
                        }
                    }
                    
                    // Fonction pour mettre à jour la sélection visuelle
                    function updateSelection() {
                        const visibleOptions = Array.from(dropdown.querySelectorAll(optionClass)).filter(opt => opt.style.display !== 'none');
                        
                        visibleOptions.forEach((option, index) => {
                            if (index === selectedIndex) {
                                option.classList.add('selected');
                            } else {
                                option.classList.remove('selected');
                            }
                        });
                    }
                    
                    // Fonction pour sélectionner une option
                    function selectOption(option) {
                        const value = option.dataset.value;
                        const text = value === '' ? '' : option.textContent;
                        
                        input.value = text;
                        if (hidden) {
                            hidden.value = value;
                        }
                        toggleDropdown(false);
                        
                        // Mettre à jour les styles des filtres actifs seulement pour les filtres (pas la recherche principale)
                        if (!config.isMainSearch) {
                            updateActiveFilters();
                            form.submit();
                        }
                    }
                    
                    // Events
                    input.addEventListener('focus', function() {
                        toggleDropdown(true);
                    });
                    
                    input.addEventListener('input', function() {
                        toggleDropdown(true);
                        selectedIndex = -1;
                        
                        // Si le champ est vide, vider aussi le champ hidden
                        if (this.value.trim() === '' && hidden) {
                            hidden.value = '';
                        }
                        
                        // Mettre à jour les styles des filtres actifs en temps réel seulement pour les filtres (pas la recherche principale)
                        if (!config.isMainSearch) {
                            updateActiveFilters();
                        }
                    });
                    
                    input.addEventListener('keydown', function(e) {
                        if (!isOpen) return;
                        
                        const visibleOptions = Array.from(dropdown.querySelectorAll(optionClass)).filter(opt => opt.style.display !== 'none');
                        
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            selectedIndex = Math.min(selectedIndex + 1, visibleOptions.length - 1);
                            updateSelection();
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            selectedIndex = Math.max(selectedIndex - 1, -1);
                            updateSelection();
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (selectedIndex >= 0 && visibleOptions[selectedIndex]) {
                                selectOption(visibleOptions[selectedIndex]);
                            }
                        } else if (e.key === 'Escape') {
                            toggleDropdown(false);
                            input.blur();
                        }
                    });
                    
                    // Clic sur les options
                    dropdown.addEventListener('click', function(e) {
                        if (e.target.classList.contains(config.isMainSearch ? 'search-option' : 'select-option')) {
                            selectOption(e.target);
                        }
                    });
                    
                    // Fermer le dropdown en cliquant ailleurs
                    document.addEventListener('click', function(e) {
                        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                            toggleDropdown(false);
                        }
                    });
                    
                    // Empêcher la fermeture lors du blur si on clique dans le dropdown
                    input.addEventListener('blur', function(e) {
                        setTimeout(() => {
                            if (!dropdown.contains(document.activeElement)) {
                                toggleDropdown(false);
                            }
                        }, 150);
                    });
                }
                
                // Initialiser tous les champs de recherche
                searchFields.forEach(initSearchField);
                
                // Gestion du filtre de types de documents
                const docTypesInput = document.getElementById('filter-doc-types');
                const docTypesDropdown = document.getElementById('doc-types-dropdown');
                const documentCheckboxes = document.querySelectorAll('#doc-types-dropdown input[type="checkbox"]:not(.doc-clear-all)');
                const clearAllCheckbox = document.querySelector('.doc-clear-all');
                const filtersForm = document.querySelector('.filters-row');
                let docDropdownOpen = false;
                
                // Fonction pour mettre à jour le texte de l'input
                function updateDocTypesInputText() {
                    const checkedBoxes = document.querySelectorAll('#doc-types-dropdown input[type="checkbox"]:checked:not(.doc-clear-all)');
                    const docLabels = {
                        'vue_eclatee': 'Vue éclatée',
                        'manuel_utilisation': 'Manuel utilisation',
                        'datasheet': 'Datasheet',
                        'manuel_reparation': 'Manuel réparation'
                    };
                    
                    if (checkedBoxes.length === 0) {
                        docTypesInput.placeholder = 'Tous les types de documents';
                        docTypesInput.value = '';
                    } else {
                        const selectedLabels = [];
                        checkedBoxes.forEach(cb => {
                            if (docLabels[cb.value]) {
                                selectedLabels.push(docLabels[cb.value]);
                            }
                        });
                        docTypesInput.value = checkedBoxes.length + ' type' + (checkedBoxes.length > 1 ? 's' : '') + ' sélectionné' + (checkedBoxes.length > 1 ? 's' : '');
                    }
                }
                
                // Ouvrir/fermer le dropdown
                if (docTypesInput && docTypesDropdown) {
                    docTypesInput.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Vérifier l'état actuel du dropdown
                        const isCurrentlyOpen = docTypesDropdown.style.display === 'block';
                        docDropdownOpen = !isCurrentlyOpen;
                        docTypesDropdown.style.display = docDropdownOpen ? 'block' : 'none';
                    });
                    
                    // Gestion du clavier pour l'accessibilité
                    docTypesInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            const isCurrentlyOpen = docTypesDropdown.style.display === 'block';
                            docDropdownOpen = !isCurrentlyOpen;
                            docTypesDropdown.style.display = docDropdownOpen ? 'block' : 'none';
                        } else if (e.key === 'Escape') {
                            docDropdownOpen = false;
                            docTypesDropdown.style.display = 'none';
                        }
                    });
                }
                
                // Gestion des checkboxes individuelles
                documentCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateDocTypesInputText();
                        updateActiveFilters();
                        
                        // Soumettre automatiquement le formulaire après un court délai
                        setTimeout(() => {
                            if (filtersForm) {
                                filtersForm.submit();
                            }
                        }, 150);
                    });
                });
                
                // Gestion du "Tout désélectionner"
                if (clearAllCheckbox) {
                    clearAllCheckbox.addEventListener('change', function() {
                        if (this.checked) {
                            documentCheckboxes.forEach(cb => {
                                if (cb.checked) {
                                    cb.checked = false;
                                }
                            });
                            this.checked = false; // Décocher le "tout désélectionner"
                            updateDocTypesInputText();
                            updateActiveFilters();
                            
                            setTimeout(() => {
                                if (filtersForm) {
                                    filtersForm.submit();
                                }
                            }, 150);
                        }
                    });
                }
                
                // Fermer le dropdown en cliquant ailleurs
                document.addEventListener('click', function(e) {
                    if (docTypesInput && docTypesDropdown && !docTypesInput.contains(e.target) && !docTypesDropdown.contains(e.target)) {
                        docDropdownOpen = false;
                        docTypesDropdown.style.display = 'none';
                    }
                });
                
                // Empêcher la fermeture lors du clic dans le dropdown
                if (docTypesDropdown) {
                    docTypesDropdown.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                }
                
                // Initialiser le texte au chargement
                updateDocTypesInputText();
                
                // S'assurer que le dropdown est fermé par défaut
                if (docTypesDropdown) {
                    docTypesDropdown.style.display = 'none';
                    docDropdownOpen = false;
                }
                
            });
            </script>
        </div>
        <?php
        
        return ob_get_clean();
    }
}

// Appel de la fonction pour l'affichage
echo doc_download_display();
?>