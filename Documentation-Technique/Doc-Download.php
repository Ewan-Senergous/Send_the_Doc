<?php

if (!function_exists('doc_download_display')) {
    function doc_download_display() {
        ob_start();
        
        // Debug: Vérifier WooCommerce
        if (!function_exists('wc_get_products')) {
            echo '<div style="color: red; padding: 20px; border: 1px solid red;">❌ WooCommerce n\'est pas activé ou chargé.</div>';
            return ob_get_clean();
        }
        
        // FONCTION DESACTIVÉE : Initialisation trop lourde - à faire manuellement si nécessaire
        // init_popularity_meta_fields();
        
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
            
            // Cache de 2 heures pour réduire la charge
            $cache_key = 'products_with_docs_optimized_v2';
            $cached_result = wp_cache_get($cache_key);
            
            if (false !== $cached_result) {
                return $cached_result;
            }
            
            // Requête SQL simplifiée pour éviter les problèmes de performance
            $sql = "
                SELECT DISTINCT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_name as slug,
                    p.post_date as post_date,
                    p.menu_order as menu_order,
                    p.comment_count as comment_count,
                    
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
                
                ORDER BY 
                    p.comment_count DESC,           -- Tri par commentaires (popularité native)
                    p.menu_order ASC,               -- Puis par ordre manuel admin
                    p.post_date DESC                -- Enfin par récence
            ";
            
            $results = $wpdb->get_results($sql, ARRAY_A);
            
            // Optimisation : traitement par batch et cache des termes
            $all_product_ids = array_column($results, 'id');
            
            // Pré-charger toutes les relations taxonomiques d'un coup
            $taxonomy_data = [];
            $taxonomies = ['pa_famille', 'pa_sous-famille', 'pa_sous-sous-famille', 'pa_vue-eclatee', 'pa_manuel-dutilisation', 'pa_datasheet', 'pa_manuel-de-reparation', 'pa_reference-fabriquant', 'pwb-brand'];
            
            foreach ($taxonomies as $taxonomy) {
                if (taxonomy_exists($taxonomy)) {
                    $taxonomy_data[$taxonomy] = [];
                    // Requête groupée pour tous les produits
                    $terms_relationships = $wpdb->get_results($wpdb->prepare("
                        SELECT tr.object_id, t.name 
                        FROM {$wpdb->term_relationships} tr
                        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                        WHERE tt.taxonomy = %s AND tr.object_id IN (" . implode(',', array_map('intval', $all_product_ids)) . ")
                    ", $taxonomy));
                    
                    foreach ($terms_relationships as $rel) {
                        if (!isset($taxonomy_data[$taxonomy][$rel->object_id])) {
                            $taxonomy_data[$taxonomy][$rel->object_id] = [];
                        }
                        $taxonomy_data[$taxonomy][$rel->object_id][] = $rel->name;
                    }
                }
            }
            
            // Formater les résultats avec les données pré-chargées
            $products_with_docs = array();
            foreach ($results as $row) {
                if (!empty($row['documentation_url']) && 
                    filter_var($row['documentation_url'], FILTER_VALIDATE_URL)) {
                    
                    $product_id = $row['id'];
                    
                    // Score de popularité simplifié basé sur WordPress natif
                    $popularity_score = (
                        intval($row['comment_count'] ?? 0) * 10 +       // Commentaires = facteur principal
                        (100 - intval($row['menu_order'] ?? 100)) * 2 + // Ordre manuel = priorité admin
                        max(0, 30 - ((strtotime('now') - strtotime($row['post_date'])) / (86400 * 30))) // Bonus récence
                    );
                    
                    // Utiliser les données pré-chargées
                    $famille = $taxonomy_data['pa_famille'][$product_id] ?? [];
                    $sous_famille = $taxonomy_data['pa_sous-famille'][$product_id] ?? [];
                    $sous_sous_famille = $taxonomy_data['pa_sous-sous-famille'][$product_id] ?? [];
                    $reference_fabriquant = $taxonomy_data['pa_reference-fabriquant'][$product_id] ?? [];
                    $brand = $taxonomy_data['pwb-brand'][$product_id] ?? [];
                    
                    // Traitement des documents avec URL validation
                    $vue_eclatee = [];
                    $vue_urls = $taxonomy_data['pa_vue-eclatee'][$product_id] ?? [];
                    foreach ($vue_urls as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $vue_eclatee[] = [
                                'url' => $url,
                                'friendly_name' => extract_friendly_name_from_url($url)
                            ];
                        }
                    }
                    
                    $manuel_utilisation = [];
                    $manuel_urls = $taxonomy_data['pa_manuel-dutilisation'][$product_id] ?? [];
                    foreach ($manuel_urls as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $manuel_utilisation[] = [
                                'url' => $url,
                                'friendly_name' => extract_friendly_name_from_url($url)
                            ];
                        }
                    }
                    
                    $datasheet = [];
                    $datasheet_urls = $taxonomy_data['pa_datasheet'][$product_id] ?? [];
                    foreach ($datasheet_urls as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $datasheet[] = [
                                'url' => $url,
                                'friendly_name' => extract_friendly_name_from_url($url)
                            ];
                        }
                    }
                    
                    $manuel_reparation = [];
                    $reparation_urls = $taxonomy_data['pa_manuel-de-reparation'][$product_id] ?? [];
                    foreach ($reparation_urls as $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $manuel_reparation[] = [
                                'url' => $url,
                                'friendly_name' => extract_friendly_name_from_url($url)
                            ];
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
                        'permalink' => get_permalink($product_id),
                        'popularity_score' => $popularity_score,
                        'is_featured' => ($row['comment_count'] >= 3) // Marquer comme featured si 3+ commentaires
                    );
                }
            }
            
            // Tri final par score de popularité (déjà trié par SQL mais on s'assure)
            usort($products_with_docs, function($a, $b) {
                return $b['popularity_score'] <=> $a['popularity_score'];
            });
            
            // Cache pendant 2 heures 
            wp_cache_set($cache_key, $products_with_docs, '', 7200);
            
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
        

        
        // OPTIMISÉ : Groupement simplifié des documents par type
        function group_documents_by_type_optimized($products) {
            $grouped = [
                'vue_eclatee' => [],
                'datasheet' => [],
                'manuel_utilisation' => [],
                'manuel_reparation' => []
            ];
            
            // Limiter le traitement aux 50 premiers produits pour éviter la surcharge
            $limited_products = array_slice($products, 0, 50);
            
            foreach ($limited_products as $product) {
                $item_data = [
                    'product' => $product,
                    'popularity_score' => $product['popularity_score'],
                    'is_featured' => $product['is_featured']
                ];
                
                // Grouper efficacement en une seule passe
                if (!empty($product['vue_eclatee'])) {
                    $grouped['vue_eclatee'][] = array_merge($item_data, ['docs' => $product['vue_eclatee']]);
                }
                if (!empty($product['datasheet'])) {
                    $grouped['datasheet'][] = array_merge($item_data, ['docs' => $product['datasheet']]);
                }
                if (!empty($product['manuel_utilisation'])) {
                    $grouped['manuel_utilisation'][] = array_merge($item_data, ['docs' => $product['manuel_utilisation']]);
                }
                if (!empty($product['manuel_reparation'])) {
                    $grouped['manuel_reparation'][] = array_merge($item_data, ['docs' => $product['manuel_reparation']]);
                }
            }
            
            // Tri simplifié par popularité uniquement
            foreach ($grouped as &$items) {
                usort($items, function($a, $b) {
                    return $b['popularity_score'] <=> $a['popularity_score'];
                });
            }
            
            return $grouped;
        }
        
        // Grouper les documents par type avec tri par popularité (version optimisée)
        $documents_by_type = group_documents_by_type_optimized($products_with_docs);
        
        // Extraire les 5 plus populaires de chaque type pour l'accordéon
        $popular_docs_preview = [];
        foreach ($documents_by_type as $type => $items) {
            $popular_docs_preview[$type] = [
                'count' => count($items),
                'top_5' => array_slice($items, 0, 5),
                'remaining' => max(0, count($items) - 5)
            ];
        }
        
        // Calculer les compteurs pour chaque type de document (mis à jour avec les nouveaux groupes)
        $doc_type_counts = [
            'vue_eclatee' => $popular_docs_preview['vue_eclatee']['count'],
            'manuel_utilisation' => $popular_docs_preview['manuel_utilisation']['count'],
            'datasheet' => $popular_docs_preview['datasheet']['count'],
            'manuel_reparation' => $popular_docs_preview['manuel_reparation']['count']
        ];
        
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
                
                /* NOUVEAU : Styles pour l'accordéon de types de documents */
                .doc-types-accordion {
                    flex: 1;
                    max-width: 600px;
                    margin: 0 20px;
                }
                
                .doc-types-tabs {
                    display: flex;
                    background: white;
                    border: 2px solid #0066cc;
                    border-radius: 8px;
                    overflow: hidden;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                
                .doc-type-tab {
                    flex: 1;
                    padding: 12px 8px;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    border-right: 1px solid #e5e7eb;
                    background: white;
                    position: relative;
                }
                
                .doc-type-tab:last-child {
                    border-right: none;
                }
                
                .doc-type-tab:hover {
                    background: #f3f4f6;
                }
                
                .doc-type-tab.active {
                    background: #0066cc;
                    color: white;
                }
                
                .doc-type-tab.active .doc-type-arrow {
                    transform: rotate(180deg);
                }
                
                .doc-type-label {
                    display: block;
                    font-weight: bold;
                    font-size: 0.85em;
                    margin-bottom: 2px;
                }
                
                .doc-type-count {
                    display: block;
                    font-size: 0.75em;
                    opacity: 0.8;
                }
                
                .doc-type-arrow {
                    position: absolute;
                    top: 50%;
                    right: 5px;
                    transform: translateY(-50%);
                    font-size: 0.7em;
                    transition: transform 0.3s ease;
                }
                
                .doc-type-content {
                    position: absolute;
                    top: 100%;
                    left: 0;
                    right: 0;
                    background: white;
                    border: 1px solid #0066cc;
                    border-top: none;
                    border-radius: 0 0 8px 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    z-index: 1000;
                    max-height: 400px;
                    overflow-y: auto;
                }
                
                .popular-docs-list {
                    padding: 15px;
                }
                
                .popular-doc-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    margin-bottom: 8px;
                    transition: all 0.2s ease;
                    background: white;
                }
                
                .popular-doc-item:hover {
                    background: #f8f9fa;
                    border-color: #0066cc;
                    transform: translateX(2px);
                }
                
                .popular-doc-item.featured {
                    border-left: 4px solid #fbbf24;
                    background: #fffbeb;
                }
                
                .doc-item-info {
                    flex: 1;
                    min-width: 0;
                }
                
                .doc-item-title {
                    margin-bottom: 4px;
                }
                
                .doc-item-title a {
                    color: #0066cc;
                    text-decoration: none;
                    font-weight: 500;
                    font-size: 0.9em;
                }
                
                .doc-item-title a:hover {
                    text-decoration: underline;
                }
                
                .featured-badge {
                    display: inline-block;
                    background: #fbbf24;
                    color: #92400e;
                    padding: 2px 6px;
                    border-radius: 12px;
                    font-size: 0.7em;
                    font-weight: bold;
                    margin-left: 8px;
                }
                
                .doc-item-meta {
                    font-size: 0.75em;
                    color: #6b7280;
                }
                
                .doc-brand, .doc-reference {
                    display: inline-block;
                    margin-right: 10px;
                }
                
                .doc-brand {
                    font-weight: 500;
                    color: #374151;
                }
                
                .doc-item-actions {
                    display: flex;
                    gap: 5px;
                    align-items: center;
                }
                
                .doc-quick-download {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 32px;
                    height: 32px;
                    background: #0066cc;
                    color: white;
                    border-radius: 6px;
                    text-decoration: none;
                    transition: all 0.2s ease;
                    font-size: 0.8em;
                }
                
                .doc-quick-download:hover {
                    background: #0052a3;
                    transform: scale(1.1);
                    color: white;
                    text-decoration: none;
                }
                
                .see-more-docs {
                    text-align: center;
                    padding: 15px;
                    border-top: 1px solid #e5e7eb;
                    background: #f8f9fa;
                }
                
                .see-more-btn {
                    background: #0066cc;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 500;
                    font-size: 0.85em;
                    transition: all 0.2s ease;
                    display: inline-flex;
                    align-items: center;
                }
                
                .see-more-btn:hover {
                    background: #0052a3;
                    transform: translateY(-1px);
                }
                
                .no-docs-message {
                    text-align: center;
                    padding: 20px;
                    color: #6b7280;
                    font-style: italic;
                }
                
                /* Assurer que le results-header est en position relative pour le positionnement absolu */
                .results-header {
                    position: relative;
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                }
                
              
                @media (max-width: 768px) {
                                        .doc-types-accordion {
                        margin: 10px 0;
                        max-width: 100%;
                    }
                    
                    .doc-types-tabs {
                        flex-wrap: wrap; 
                    }
                    
                    .doc-type-tab {
                        flex: 0 0 calc(50% - 1px); /* 2 onglets par ligne */
                        border-right: 1px solid #e5e7eb;
                        border-bottom: 1px solid #e5e7eb;
                        padding: 15px 8px; /* Plus de padding vertical pour mobile */
                    }
                    
                    .doc-type-tab:nth-child(2n) {
                        border-right: none; /* Retirer bordure droite du 2ème et 4ème */
                    }
                    
                    .doc-type-tab:nth-child(3), 
                    .doc-type-tab:nth-child(4) {
                        border-bottom: none; /* Retirer bordure bas de la 2ème ligne */
                    }
                    
                    .doc-type-label {
                        font-size: 0.8em; /* Légèrement plus petit en mobile */
                    }
                    
                    .doc-type-count {
                        font-size: 0.7em;
                    }
                    
                    .doc-type-arrow {
                        right: 8px;
                        font-size: 0.6em;
                    }
                    
                    .popular-doc-item {
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 10px;
                        padding: 12px;
                    }
                    
                    .doc-item-actions {
                        align-self: stretch;
                        justify-content: center;
                    }
                    
                    .results-header {
                        flex-direction: column;
                        gap: 15px;
                    }
                    
                    .doc-type-content {
                        /* Assurer que le contenu s'affiche correctement sous les 2 lignes */
                        top: calc(100% + 1px);
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
            
            <div class="results-container">
                <div class="results-header">
                    <div class="results-count">
                        <?php echo $total_products; ?> documentation(s) trouvée(s)
                    </div>
                    
                    <!-- NOUVEAU : Interface Accordéon Centrée -->
                    <div class="doc-types-accordion">
                        <div class="doc-types-tabs">
                            <div class="doc-type-tab" data-type="vue_eclatee">
                                <span class="doc-type-label">Vue éclatée</span>
                                <span class="doc-type-count">(<?php echo $popular_docs_preview['vue_eclatee']['count']; ?>)</span>
                                <span class="doc-type-arrow">▼</span>
                            </div>
                            <div class="doc-type-tab" data-type="datasheet">
                                <span class="doc-type-label">Datasheet</span>
                                <span class="doc-type-count">(<?php echo $popular_docs_preview['datasheet']['count']; ?>)</span>
                                <span class="doc-type-arrow">▼</span>
                            </div>
                            <div class="doc-type-tab" data-type="manuel_utilisation">
                                <span class="doc-type-label">Manuel utilisation</span>
                                <span class="doc-type-count">(<?php echo $popular_docs_preview['manuel_utilisation']['count']; ?>)</span>
                                <span class="doc-type-arrow">▼</span>
                            </div>
                            <div class="doc-type-tab" data-type="manuel_reparation">
                                <span class="doc-type-label">Manuel réparation</span>
                                <span class="doc-type-count">(<?php echo $popular_docs_preview['manuel_reparation']['count']; ?>)</span>
                                <span class="doc-type-arrow">▼</span>
                            </div>
                        </div>
                        
                        <!-- Contenus des accordéons -->
                        <?php foreach ($popular_docs_preview as $type => $data): ?>
                        <div class="doc-type-content" id="content-<?php echo $type; ?>" style="display: none;">
                            <?php if ($data['count'] > 0): ?>
                                <div class="popular-docs-list">
                                    <?php foreach ($data['top_5'] as $index => $item): ?>
                                        <div class="popular-doc-item <?php echo $item['is_featured'] ? 'featured' : ''; ?>">
                                            <div class="doc-item-info">
                                                <div class="doc-item-title">
                                                    <a href="<?php echo esc_url($item['product']['permalink']); ?>" target="_blank">
                                                        <?php echo esc_html($item['product']['name']); ?>
                                                    </a>
                                                    <?php if ($item['is_featured']): ?>
                                                        <span class="featured-badge">★ Populaire</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="doc-item-meta">
                                                    <?php if (!empty($item['product']['brand'])): ?>
                                                        <span class="doc-brand"><?php echo esc_html(implode(', ', $item['product']['brand'])); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['product']['reference_fabriquant'])): ?>
                                                        <span class="doc-reference"><?php echo esc_html(implode(', ', $item['product']['reference_fabriquant'])); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="doc-item-actions">
                                                <?php foreach ($item['docs'] as $doc): ?>
                                                    <a href="<?php echo esc_url($doc['url']); ?>" 
                                                       class="doc-quick-download <?php echo $type; ?>-link" 
                                                       target="_blank"
                                                       title="<?php echo esc_attr($doc['friendly_name']); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/>
                                                        </svg>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <?php if ($data['remaining'] > 0): ?>
                                <div class="see-more-docs">
                                    <button class="see-more-btn" onclick="filterByDocType('<?php echo $type; ?>')">
                                        Voir les <?php echo $data['remaining']; ?> autres
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 5px;">
                                            <path d="M9 18l6-6-6-6"/>
                                        </svg>
                                    </button>
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-docs-message">
                                    Aucun document de ce type trouvé.
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
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
                
                // NOUVEAU : Gestion de l'accordéon de types de documents
                const docTypeTabs = document.querySelectorAll('.doc-type-tab');
                const docTypeContents = document.querySelectorAll('.doc-type-content');
                let currentOpenTab = null;
                
                docTypeTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const type = this.dataset.type;
                        const content = document.getElementById('content-' + type);
                        const arrow = this.querySelector('.doc-type-arrow');
                        
                        // Si ce tab est déjà ouvert, le fermer
                        if (currentOpenTab === this) {
                            this.classList.remove('active');
                            content.style.display = 'none';
                            currentOpenTab = null;
                            return;
                        }
                        
                        // Fermer tous les autres tabs
                        docTypeTabs.forEach(otherTab => {
                            otherTab.classList.remove('active');
                        });
                        docTypeContents.forEach(otherContent => {
                            otherContent.style.display = 'none';
                        });
                        
                        // Ouvrir le tab sélectionné
                        this.classList.add('active');
                        content.style.display = 'block';
                        currentOpenTab = this;
                    });
                });
                
                // Fermer l'accordéon en cliquant ailleurs
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.doc-types-accordion')) {
                        docTypeTabs.forEach(tab => {
                            tab.classList.remove('active');
                        });
                        docTypeContents.forEach(content => {
                            content.style.display = 'none';
                        });
                        currentOpenTab = null;
                    }
                });
            });
            
            // NOUVEAU : Fonction pour filtrer par type de document
            function filterByDocType(docType) {
                // Créer les paramètres pour filtrer par type de document
                const urlParams = new URLSearchParams(window.location.search);
                
                // Conserver les filtres existants
                const currentParams = {};
                urlParams.forEach((value, key) => {
                    if (key !== 'doc_types[]') { // Retirer les anciens types de documents
                        currentParams[key] = value;
                    }
                });
                
                // Ajouter le nouveau type de document
                currentParams['doc_types[]'] = docType;
                
                                 // Rediriger vers la nouvelle URL
                 const newUrl = window.location.pathname + '?' + new URLSearchParams(currentParams).toString();
                 window.location.href = newUrl;
             }
             
             // TRACKING DÉSACTIVÉ pour éviter les problèmes de performance
             // Le tracking pourra être réactivé plus tard après optimisation
             /*
             function trackDownload(productId, docType) {
                 // Code de tracking simplifié à implémenter plus tard
                 console.log('Téléchargement:', productId, docType);
             }
             */
            </script>
        </div>
        <?php
        
        return ob_get_clean();
    }
}

// Appel de la fonction pour l'affichage
echo doc_download_display();
?>