<?php

if (!function_exists('doc_download_display')) {
    function doc_download_display() {
        ob_start();
        
        // Debug: Vérifier WooCommerce
        if (!function_exists('wc_get_products')) {
            echo '<div style="color: red; padding: 20px; border: 1px solid red;">❌ WooCommerce n\'est pas activé ou chargé.</div>';
            return ob_get_clean();
        }
        
        // Récupération des paramètres de recherche et pagination
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $selected_famille = isset($_GET['famille']) ? sanitize_text_field($_GET['famille']) : '';
        $selected_sous_famille = isset($_GET['sous_famille']) ? sanitize_text_field($_GET['sous_famille']) : '';
        $selected_sous_sous_famille = isset($_GET['sous_sous_famille']) ? sanitize_text_field($_GET['sous_sous_famille']) : '';
        
        // Nouveaux attributs de documentation
        $selected_vue_eclatee = isset($_GET['vue_eclatee']) ? sanitize_text_field($_GET['vue_eclatee']) : '';
        $selected_manuel_utilisation = isset($_GET['manuel_utilisation']) ? sanitize_text_field($_GET['manuel_utilisation']) : '';
        $selected_datasheet = isset($_GET['datasheet']) ? sanitize_text_field($_GET['datasheet']) : '';
        $selected_manuel_reparation = isset($_GET['manuel_reparation']) ? sanitize_text_field($_GET['manuel_reparation']) : '';
        
        // Référence fabriquant
        $selected_reference_fabriquant = isset($_GET['reference_fabriquant']) ? sanitize_text_field($_GET['reference_fabriquant']) : '';
        
        // Catégorie WordPress
        $selected_categorie_wp = isset($_GET['categorie_wp']) ? sanitize_text_field($_GET['categorie_wp']) : '';
        
        // Marque (brand)
        $selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
        
        // Paramètres de pagination
        $page = isset($_GET['doc_page']) ? max(1, intval($_GET['doc_page'])) : 1;
        $per_page = 2; // Limiter à 12 produits par page
        
        // SOLUTION CORRIGÉE : Récupération via taxonomies WooCommerce
        function get_products_with_documentation_optimized() {
            global $wpdb;
            
            // Cache de 30 minutes
            $cache_key = 'products_with_docs_taxonomies_v1';
            $cached_result = wp_cache_get($cache_key);
            
            if (false !== $cached_result) {
                return $cached_result;
            }
            
            // Requête SQL corrigée pour les taxonomies WooCommerce
            $sql = "
                SELECT DISTINCT 
                    p.ID as id,
                    p.post_title as name,
                    p.post_name as slug,
                    
                    -- Documentation depuis taxonomie pa_documentation-technique
                    MAX(CASE WHEN tt_doc.taxonomy = 'pa_documentation-technique' THEN t_doc.name END) as documentation_url,
                    
                    -- Famille depuis taxonomie pa_famille  
                    MAX(CASE WHEN tt_famille.taxonomy = 'pa_famille' THEN t_famille.name END) as famille,
                    
                    -- Sous-famille depuis taxonomie pa_sous-famille
                    MAX(CASE WHEN tt_sous_famille.taxonomy = 'pa_sous-famille' THEN t_sous_famille.name END) as sous_famille,
                    
                    -- Sous-sous-famille depuis taxonomie pa_sous-sous-famille
                    MAX(CASE WHEN tt_sous_sous_famille.taxonomy = 'pa_sous-sous-famille' THEN t_sous_sous_famille.name END) as sous_sous_famille
                    
                FROM {$wpdb->posts} p
                
                -- Documentation technique (OBLIGATOIRE)
                INNER JOIN {$wpdb->term_relationships} tr_doc ON p.ID = tr_doc.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt_doc ON tr_doc.term_taxonomy_id = tt_doc.term_taxonomy_id 
                    AND tt_doc.taxonomy = 'pa_documentation-technique'
                INNER JOIN {$wpdb->terms} t_doc ON tt_doc.term_id = t_doc.term_id
                
                -- Famille (OPTIONNEL)
                LEFT JOIN {$wpdb->term_relationships} tr_famille ON p.ID = tr_famille.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt_famille ON tr_famille.term_taxonomy_id = tt_famille.term_taxonomy_id 
                    AND tt_famille.taxonomy = 'pa_famille'
                LEFT JOIN {$wpdb->terms} t_famille ON tt_famille.term_id = t_famille.term_id
                
                -- Sous-famille (OPTIONNEL)
                LEFT JOIN {$wpdb->term_relationships} tr_sous_famille ON p.ID = tr_sous_famille.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt_sous_famille ON tr_sous_famille.term_taxonomy_id = tt_sous_famille.term_taxonomy_id 
                    AND tt_sous_famille.taxonomy = 'pa_sous-famille'
                LEFT JOIN {$wpdb->terms} t_sous_famille ON tt_sous_famille.term_id = t_sous_famille.term_id
                
                -- Sous-sous-famille (OPTIONNEL)
                LEFT JOIN {$wpdb->term_relationships} tr_sous_sous_famille ON p.ID = tr_sous_sous_famille.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt_sous_sous_famille ON tr_sous_sous_famille.term_taxonomy_id = tt_sous_sous_famille.term_taxonomy_id 
                    AND tt_sous_sous_famille.taxonomy = 'pa_sous-sous-famille'
                LEFT JOIN {$wpdb->terms} t_sous_sous_famille ON tt_sous_sous_famille.term_id = t_sous_sous_famille.term_id
                
                WHERE p.post_type = 'product' 
                AND p.post_status IN ('publish', 'draft')
                AND t_doc.name IS NOT NULL 
                AND t_doc.name != ''
                AND t_doc.name != 'N/A'
                AND t_doc.name NOT LIKE '%non%'
                
                GROUP BY p.ID, p.post_title, p.post_name
                ORDER BY p.post_title ASC
            ";
            
            $results = $wpdb->get_results($sql, ARRAY_A);
            
            // Formater les résultats avec récupération des nouveaux attributs
            $products_with_docs = array();
            foreach ($results as $row) {
                if (!empty($row['documentation_url']) && 
                    filter_var($row['documentation_url'], FILTER_VALIDATE_URL)) {
                    
                    $product_id = $row['id'];
                    
                    // Récupération optimisée des nouveaux attributs
                    $vue_eclatee = '';
                    $manuel_utilisation = '';
                    $datasheet = '';
                    $manuel_reparation = '';
                    $reference_fabriquant = '';
                    $categorie_wp = '';
                    $brand = '';
                    
                    // Récupération avec vérification d'existence des taxonomies
                    if (taxonomy_exists('pa_vue-eclatee')) {
                        $terms = get_the_terms($product_id, 'pa_vue-eclatee');
                        if ($terms && !is_wp_error($terms)) {
                            $vue_eclatee = $terms[0]->name;
                        }
                    }
                    
                    if (taxonomy_exists('pa_manuel-dutilisation')) {
                        $terms = get_the_terms($product_id, 'pa_manuel-dutilisation');
                        if ($terms && !is_wp_error($terms)) {
                            $manuel_utilisation = $terms[0]->name;
                        }
                    }
                    
                    if (taxonomy_exists('pa_datasheet')) {
                        $terms = get_the_terms($product_id, 'pa_datasheet');
                        if ($terms && !is_wp_error($terms)) {
                            $datasheet = $terms[0]->name;
                        }
                    }
                    
                    if (taxonomy_exists('pa_manuel-de-reparation')) {
                        $terms = get_the_terms($product_id, 'pa_manuel-de-reparation');
                        if ($terms && !is_wp_error($terms)) {
                            $manuel_reparation = $terms[0]->name;
                        }
                    }
                    
                    if (taxonomy_exists('pa_reference-fabriquant')) {
                        $terms = get_the_terms($product_id, 'pa_reference-fabriquant');
                        if ($terms && !is_wp_error($terms)) {
                            $reference_fabriquant = $terms[0]->name;
                        }
                    }
                    
                    // Catégorie WordPress (WooCommerce) - TOUTES les catégories
                    $terms = get_the_terms($product_id, 'product_cat');
                    $categories_wp_array = [];
                    if ($terms && !is_wp_error($terms)) {
                        foreach ($terms as $term) {
                            $categories_wp_array[] = $term->name;
                        }
                    }
                    $categorie_wp = $categories_wp_array;
                    
                    // Marque (Brand) - Taxonomie pwb-brand
                    $terms = get_the_terms($product_id, 'pwb-brand');
                    if ($terms && !is_wp_error($terms)) {
                        $brand = $terms[0]->name;
                    }
                    
                    $products_with_docs[] = array(
                        'id' => $product_id,
                        'name' => $row['name'],
                        'documentation_url' => $row['documentation_url'],
                        'famille' => $row['famille'] ?? '',
                        'sous_famille' => $row['sous_famille'] ?? '',
                        'sous_sous_famille' => $row['sous_sous_famille'] ?? '',
                        'vue_eclatee' => $vue_eclatee,
                        'manuel_utilisation' => $manuel_utilisation,
                        'datasheet' => $datasheet,
                        'manuel_reparation' => $manuel_reparation,
                        'reference_fabriquant' => $reference_fabriquant,
                        'categorie_wp' => $categorie_wp,
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
                
                // Recherche dans la famille
                if (!empty($product['famille']) && stripos($product['famille'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans la sous-famille
                if (!empty($product['sous_famille']) && stripos($product['sous_famille'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans la sous-sous-famille
                if (!empty($product['sous_sous_famille']) && stripos($product['sous_sous_famille'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans la référence fabriquant
                if (!empty($product['reference_fabriquant']) && stripos($product['reference_fabriquant'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans les catégories WordPress
                if (!empty($product['categorie_wp']) && is_array($product['categorie_wp'])) {
                    foreach ($product['categorie_wp'] as $categorie) {
                        if (stripos($categorie, $search_query) !== false) {
                            return true;
                        }
                    }
                }
                
                // Recherche dans la marque
                if (!empty($product['brand']) && stripos($product['brand'], $search_query) !== false) {
                    return true;
                }
                
                // Recherche dans les types de documentation (optionnel)
                if (!empty($product['vue_eclatee']) && stripos($product['vue_eclatee'], $search_query) !== false) {
                    return true;
                }
                
                if (!empty($product['manuel_utilisation']) && stripos($product['manuel_utilisation'], $search_query) !== false) {
                    return true;
                }
                
                if (!empty($product['datasheet']) && stripos($product['datasheet'], $search_query) !== false) {
                    return true;
                }
                
                if (!empty($product['manuel_reparation']) && stripos($product['manuel_reparation'], $search_query) !== false) {
                    return true;
                }
                
                return false;
            });
        }
        
        if (!empty($selected_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_famille) {
                return $product['famille'] === $selected_famille;
            });
        }
        
        if (!empty($selected_sous_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_sous_famille) {
                return $product['sous_famille'] === $selected_sous_famille;
            });
        }
        
        if (!empty($selected_sous_sous_famille)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_sous_sous_famille) {
                return $product['sous_sous_famille'] === $selected_sous_sous_famille;
            });
        }
        
        // Filtres pour les nouveaux types de documentation
        if (!empty($selected_vue_eclatee)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_vue_eclatee) {
                return $product['vue_eclatee'] === $selected_vue_eclatee;
            });
        }
        
        if (!empty($selected_manuel_utilisation)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_manuel_utilisation) {
                return $product['manuel_utilisation'] === $selected_manuel_utilisation;
            });
        }
        
        if (!empty($selected_datasheet)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_datasheet) {
                return $product['datasheet'] === $selected_datasheet;
            });
        }
        
        if (!empty($selected_manuel_reparation)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_manuel_reparation) {
                return $product['manuel_reparation'] === $selected_manuel_reparation;
            });
        }
        
        if (!empty($selected_reference_fabriquant)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_reference_fabriquant) {
                return $product['reference_fabriquant'] === $selected_reference_fabriquant;
            });
        }
        
        if (!empty($selected_categorie_wp)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_categorie_wp) {
                return is_array($product['categorie_wp']) && in_array($selected_categorie_wp, $product['categorie_wp']);
            });
        }
        
        if (!empty($selected_brand)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($selected_brand) {
                return $product['brand'] === $selected_brand;
            });
        }

        // Récupérer les valeurs uniques pour les filtres (limiter à 5 par catégorie)
        $familles = array_unique(array_column($products_with_docs, 'famille'));
        $sous_familles = array_unique(array_column($products_with_docs, 'sous_famille'));
        $sous_sous_familles = array_unique(array_column($products_with_docs, 'sous_sous_famille'));
        
        // Nouveaux types de documentation
        $vues_eclatees = array_unique(array_column($products_with_docs, 'vue_eclatee'));
        $manuels_utilisation = array_unique(array_column($products_with_docs, 'manuel_utilisation'));
        $datasheets = array_unique(array_column($products_with_docs, 'datasheet'));
        $manuels_reparation = array_unique(array_column($products_with_docs, 'manuel_reparation'));
        
        // Références fabriquant - TOUTES les valeurs disponibles
        $references_fabriquant = array_column($products_with_docs, 'reference_fabriquant');
        $references_fabriquant = array_filter($references_fabriquant); // Enlever les valeurs vides
        $references_fabriquant = array_unique($references_fabriquant);
        sort($references_fabriquant); // Tri alphabétique
        
        // Catégories WordPress - TOUTES les valeurs disponibles
        $all_categories = [];
        foreach ($products_with_docs as $product) {
            if (is_array($product['categorie_wp'])) {
                $all_categories = array_merge($all_categories, $product['categorie_wp']);
            }
        }
        $all_categories = array_filter($all_categories); // Enlever les valeurs vides
        $categories_wp = array_unique($all_categories);
        sort($categories_wp); // Tri alphabétique
        
        // Marques - TOUTES les valeurs disponibles
        $brands = array_column($products_with_docs, 'brand');
        $brands = array_filter($brands); // Enlever les valeurs vides
        $brands = array_unique($brands);
        sort($brands); // Tri alphabétique

        // Nettoyer les valeurs vides - TOUTES les valeurs disponibles
        $familles = array_filter($familles);
        sort($familles); // Tri alphabétique
        
        $sous_familles = array_filter($sous_familles);
        sort($sous_familles); // Tri alphabétique
        
        $sous_sous_familles = array_filter($sous_sous_familles);
        sort($sous_sous_familles); // Tri alphabétique
        
        // Nettoyer les nouveaux attributs - TOUTES les valeurs disponibles
        $vues_eclatees = array_filter($vues_eclatees, function($value) {
            return !empty($value);
        });
        sort($vues_eclatees); // Tri alphabétique
        
        $manuels_utilisation = array_filter($manuels_utilisation, function($value) {
            return !empty($value);
        });
        sort($manuels_utilisation); // Tri alphabétique
        
        $datasheets = array_filter($datasheets, function($value) {
            return !empty($value);
        });
        sort($datasheets); // Tri alphabétique
        
        $manuels_reparation = array_filter($manuels_reparation, function($value) {
            return !empty($value);
        });
        sort($manuels_reparation); // Tri alphabétique
        
        // Créer une liste combinée pour l'auto-complétion du champ de recherche principal
        $all_search_values = [];
        
        // Ajouter tous les noms de produits
        foreach ($products_with_docs as $product) {
            if (!empty($product['name'])) {
                $all_search_values[] = $product['name'];
            }
        }
        
        // Ajouter toutes les valeurs des autres champs
        $all_search_values = array_merge($all_search_values, $familles);
        $all_search_values = array_merge($all_search_values, $sous_familles);
        $all_search_values = array_merge($all_search_values, $sous_sous_familles);
        $all_search_values = array_merge($all_search_values, $vues_eclatees);
        $all_search_values = array_merge($all_search_values, $manuels_utilisation);
        $all_search_values = array_merge($all_search_values, $datasheets);
        $all_search_values = array_merge($all_search_values, $manuels_reparation);
        $all_search_values = array_merge($all_search_values, $references_fabriquant);
        $all_search_values = array_merge($all_search_values, $categories_wp);
        $all_search_values = array_merge($all_search_values, $brands);
        
        // Nettoyer, dédupliquer et trier
        $all_search_values = array_filter($all_search_values, function($value) {
            return !empty($value) && trim($value) !== '';
        });
        $all_search_values = array_unique($all_search_values);
        sort($all_search_values); // Tri alphabétique
        
        // Pagination sur les produits filtrés
        $total_products = count($filtered_products);
        $start_index = ($page - 1) * $per_page;
        $current_page_products = array_slice($filtered_products, $start_index, $per_page);
        
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
                    right: 60px; /* Laisser de la place pour le bouton */
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
                    display: block;
                    margin-bottom: 5px;
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
                
                .pagination-button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin: 0 5px;
                    background: #0066cc;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: background 0.3s;
                }
                
                .pagination-button:first-child {
                    padding-left: 12px;
                }
                
                .pagination-button:last-child {
                    padding-right: 12px;
                }
                
                .pagination-button:hover {
                    background: #0052a3;
                    color: white;
                    text-decoration: none;
                }
                
                .pagination-button.disabled {
                    background: #ccc;
                    color: #333;
                    cursor: not-allowed;
                    pointer-events: none;
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
                    
                    .pagination-button {
                        margin: 5px;
                        padding: 8px 16px;
                        font-size: 0.9em;
                    }
                    
                    .pagination-button:first-child {
                        padding-left: 10px;
                    }
                    
                    .pagination-button:last-child {
                        padding-right: 10px;
                    }
                    .select-with-search .search-icon {
                        top: 57%;
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
                <input type="hidden" name="vue_eclatee" value="<?php echo esc_attr($selected_vue_eclatee); ?>">
                <input type="hidden" name="manuel_utilisation" value="<?php echo esc_attr($selected_manuel_utilisation); ?>">
                <input type="hidden" name="datasheet" value="<?php echo esc_attr($selected_datasheet); ?>">
                <input type="hidden" name="manuel_reparation" value="<?php echo esc_attr($selected_manuel_reparation); ?>">
                <input type="hidden" name="reference_fabriquant" value="<?php echo esc_attr($selected_reference_fabriquant); ?>">
                <input type="hidden" name="categorie_wp" value="<?php echo esc_attr($selected_categorie_wp); ?>">
                <input type="hidden" name="brand" value="<?php echo esc_attr($selected_brand); ?>">
            </form>
            
            <div class="filters-container">
                <form method="GET" class="filters-row">
                    <input type="hidden" name="search" value="<?php echo esc_attr($search_query); ?>">
                    
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
                        <label for="filter-vue-eclatee">Vue éclatée :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-vue-eclatee" 
                                   class="select-search-input" 
                                   placeholder="Toutes les vues éclatées" 
                                   value="<?php echo esc_attr($selected_vue_eclatee); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="vue_eclatee" id="vue_eclatee_hidden" value="<?php echo esc_attr($selected_vue_eclatee); ?>" />
                            <div id="vue-eclatee-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les vues éclatées</div>
                                <?php foreach ($vues_eclatees as $vue): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($vue); ?>"><?php echo esc_html($vue); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-manuel-utilisation">Manuel d'utilisation :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-manuel-utilisation" 
                                   class="select-search-input" 
                                   placeholder="Tous les manuels d'utilisation" 
                                   value="<?php echo esc_attr($selected_manuel_utilisation); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="manuel_utilisation" id="manuel_utilisation_hidden" value="<?php echo esc_attr($selected_manuel_utilisation); ?>" />
                            <div id="manuel-utilisation-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Tous les manuels d'utilisation</div>
                                <?php foreach ($manuels_utilisation as $manuel): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($manuel); ?>"><?php echo esc_html($manuel); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-datasheet">Datasheet :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-datasheet" 
                                   class="select-search-input" 
                                   placeholder="Toutes les datasheets" 
                                   value="<?php echo esc_attr($selected_datasheet); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="datasheet" id="datasheet_hidden" value="<?php echo esc_attr($selected_datasheet); ?>" />
                            <div id="datasheet-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les datasheets</div>
                                <?php foreach ($datasheets as $datasheet): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($datasheet); ?>"><?php echo esc_html($datasheet); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-manuel-reparation">Manuel de réparation :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-manuel-reparation" 
                                   class="select-search-input" 
                                   placeholder="Tous les manuels de réparation" 
                                   value="<?php echo esc_attr($selected_manuel_reparation); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="manuel_reparation" id="manuel_reparation_hidden" value="<?php echo esc_attr($selected_manuel_reparation); ?>" />
                            <div id="manuel-reparation-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Tous les manuels de réparation</div>
                                <?php foreach ($manuels_reparation as $manuel): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($manuel); ?>"><?php echo esc_html($manuel); ?></div>
                                <?php endforeach; ?>
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
                    
                    <div class="filter-group">
                        <label for="filter-categorie-wp">Catégorie produit :</label>
                        <div class="select-with-search">
                            <div class="search-icon">
                                <svg width="14" height="14" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   id="filter-categorie-wp" 
                                   class="select-search-input" 
                                   placeholder="Toutes les catégories" 
                                   value="<?php echo esc_attr($selected_categorie_wp); ?>"
                                   autocomplete="off" />
                            <input type="hidden" name="categorie_wp" id="categorie_wp_hidden" value="<?php echo esc_attr($selected_categorie_wp); ?>" />
                            <div id="category-dropdown" class="select-dropdown">
                                <div class="select-option" data-value="">Toutes les catégories</div>
                                <?php foreach ($categories_wp as $categorie): ?>
                                    <div class="select-option" data-value="<?php echo esc_attr($categorie); ?>"><?php echo esc_html($categorie); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
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
                    
                    <div class="filter-actions">
                        <a href="?" class="btn-reset">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw" style="vertical-align: middle; margin-right: 5px;">
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
                    <?php if ($total_products > $per_page): ?>
                    <div class="pagination-info">
                        Page <?php echo $page; ?> sur <?php echo ceil($total_products / $per_page); ?> 
                        (<?php echo count($current_page_products); ?> affichés)
                    </div>
                    <?php endif; ?>
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
                                <?php if (!empty($product['famille'])): ?>
                                    <span class="category-tag famille">Famille : <?php echo esc_html($product['famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_famille'])): ?>
                                    <span class="category-tag sous-famille">Sous-famille : <?php echo esc_html($product['sous_famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_sous_famille'])): ?>
                                    <span class="category-tag sous-sous-famille">Sous-sous-famille : <?php echo esc_html($product['sous_sous_famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['reference_fabriquant'])): ?>
                                    <span class="category-tag reference-fabriquant">Réf : <?php echo esc_html($product['reference_fabriquant']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['categorie_wp']) && is_array($product['categorie_wp'])): ?>
                                    <?php foreach ($product['categorie_wp'] as $categorie): ?>
                                        <span class="category-tag categorie-wp">Catégorie : <?php echo esc_html($categorie); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($product['brand'])): ?>
                                    <span class="category-tag brand">Marque : <?php echo esc_html($product['brand']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="download-links">
                                <a href="<?php echo esc_url($product['documentation_url']); ?>" 
                                   class="download-link" 
                                   target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Documentation
                                </a>
                                
                                <?php if (!empty($product['vue_eclatee']) && filter_var($product['vue_eclatee'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['vue_eclatee']); ?>" 
                                   class="download-link vue-eclatee-link" 
                                   target="_blank" title="Vue éclatée">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Vue éclatée
                                </a>
                                <?php elseif (!empty($product['vue_eclatee'])): ?>
                                <span class="download-link vue-eclatee-link disabled" title="Vue éclatée disponible: <?php echo esc_attr($product['vue_eclatee']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Vue éclatée
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_utilisation']) && filter_var($product['manuel_utilisation'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['manuel_utilisation']); ?>" 
                                   class="download-link manuel-link" 
                                   target="_blank" title="Manuel d'utilisation">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Manuel utilisation
                                </a>
                                <?php elseif (!empty($product['manuel_utilisation'])): ?>
                                <span class="download-link manuel-link disabled" title="Manuel d'utilisation disponible: <?php echo esc_attr($product['manuel_utilisation']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Manuel utilisation
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_reparation']) && filter_var($product['manuel_reparation'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['manuel_reparation']); ?>" 
                                   class="download-link repair-link" 
                                   target="_blank" title="Manuel de réparation">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Manuel réparation
                                </a>
                                <?php elseif (!empty($product['manuel_reparation'])): ?>
                                <span class="download-link repair-link disabled" title="Manuel de réparation disponible: <?php echo esc_attr($product['manuel_reparation']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Manuel réparation
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['datasheet']) && filter_var($product['datasheet'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['datasheet']); ?>" 
                                   class="download-link datasheet-link" 
                                   target="_blank" title="Datasheet">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Datasheet
                                </a>
                                <?php elseif (!empty($product['datasheet'])): ?>
                                <span class="download-link datasheet-link disabled" title="Datasheet disponible: <?php echo esc_attr($product['datasheet']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                    Datasheet
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_products > $per_page): ?>
                <div class="pagination-container">
                    <?php 
                    $total_pages = ceil($total_products / $per_page);
                    $current_params = $_GET;
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <?php 
                        $current_params['doc_page'] = $page - 1; 
                        ?>
                        <a href="?<?php echo http_build_query($current_params); ?>" class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left-icon lucide-chevron-left" style="vertical-align: middle;"><path d="m15 18-6-6 6-6"/></svg>
                            Précédent
                        </a>
                    <?php else: ?>
                        <span class="pagination-button disabled">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left-icon lucide-chevron-left" style="vertical-align: middle;"><path d="m15 18-6-6 6-6"/></svg>
                            Précédent
                        </span>
                    <?php endif; ?>
                    
                    <span class="pagination-button disabled">Page <?php echo $page; ?> / <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <?php 
                        $current_params['doc_page'] = $page + 1; 
                        ?>
                        <a href="?<?php echo http_build_query($current_params); ?>" class="pagination-button">
                            Suivant
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right" style="vertical-align: middle;"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    <?php else: ?>
                        <span class="pagination-button disabled">
                            Suivant
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right" style="vertical-align: middle;"><path d="m9 18 6-6-6-6"/></svg>
                        </span>
                    <?php endif; ?>
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
                    { inputId: 'filter-famille', hiddenId: 'famille_hidden', dropdownId: 'famille-dropdown' },
                    { inputId: 'filter-sous-famille', hiddenId: 'sous_famille_hidden', dropdownId: 'sous-famille-dropdown' },
                    { inputId: 'filter-sous-sous-famille', hiddenId: 'sous_sous_famille_hidden', dropdownId: 'sous-sous-famille-dropdown' },
                    { inputId: 'filter-vue-eclatee', hiddenId: 'vue_eclatee_hidden', dropdownId: 'vue-eclatee-dropdown' },
                    { inputId: 'filter-manuel-utilisation', hiddenId: 'manuel_utilisation_hidden', dropdownId: 'manuel-utilisation-dropdown' },
                    { inputId: 'filter-datasheet', hiddenId: 'datasheet_hidden', dropdownId: 'datasheet-dropdown' },
                    { inputId: 'filter-manuel-reparation', hiddenId: 'manuel_reparation_hidden', dropdownId: 'manuel-reparation-dropdown' },
                    { inputId: 'filter-reference-fabriquant', hiddenId: 'reference_fabriquant_hidden', dropdownId: 'reference-fabriquant-dropdown' },
                    { inputId: 'filter-categorie-wp', hiddenId: 'categorie_wp_hidden', dropdownId: 'category-dropdown' },
                    { inputId: 'filter-brand', hiddenId: 'brand_hidden', dropdownId: 'brand-dropdown' }
                ];
                
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
                        
                        // Soumettre le formulaire automatiquement sauf pour la recherche principale
                        if (!config.isMainSearch) {
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