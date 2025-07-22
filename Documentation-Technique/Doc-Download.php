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
        $per_page = 12; // Limiter à 12 produits par page
        
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
            
            // Debug simple
            echo '<div style="background: lightgreen; padding: 10px; margin: 10px; border: 2px solid green;">';
            echo '<h3>📋 Produits avec documentation trouvés : ' . count($results) . '</h3>';
            echo '</div>';
            
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
                    
                    // Catégorie WordPress (WooCommerce)
                    $terms = get_the_terms($product_id, 'product_cat');
                    if ($terms && !is_wp_error($terms)) {
                        $categorie_wp = $terms[0]->name;
                    }
                    
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

        // Debug simplifié
        echo '<script>console.log("Debug: Produits avec docs (OPTIMISÉ):", ' . count($products_with_docs) . ');</script>';

        // Appliquer les filtres de recherche et famille
        $filtered_products = $products_with_docs;
        
        if (!empty($search_query)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($search_query) {
                return stripos($product['name'], $search_query) !== false;
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
                return $product['categorie_wp'] === $selected_categorie_wp;
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
        
        // Références fabriquant (beaucoup de valeurs - gestion spéciale)
        $references_fabriquant = array_column($products_with_docs, 'reference_fabriquant');
        $references_fabriquant = array_filter($references_fabriquant); // Enlever les valeurs vides
        
        // Compter les occurrences et garder seulement les 10 plus courantes
        $references_count = array_count_values($references_fabriquant);
        arsort($references_count); // Trier par nombre d'occurrences décroissant
        $references_fabriquant = array_slice(array_keys($references_count), 0, 10);
        
        // Catégories WordPress (beaucoup de valeurs - gestion spéciale)
        $categories_wp = array_column($products_with_docs, 'categorie_wp');
        $categories_wp = array_filter($categories_wp); // Enlever les valeurs vides
        
        // Compter les occurrences et garder seulement les 10 plus courantes
        $categories_count = array_count_values($categories_wp);
        arsort($categories_count); // Trier par nombre d'occurrences décroissant
        $categories_wp = array_slice(array_keys($categories_count), 0, 10);
        
        // Marques (47 valeurs - gestion spéciale)
        $brands = array_column($products_with_docs, 'brand');
        $brands = array_filter($brands); // Enlever les valeurs vides
        
        // Compter les occurrences et garder seulement les 10 plus courantes
        $brands_count = array_count_values($brands);
        arsort($brands_count); // Trier par nombre d'occurrences décroissant
        $brands = array_slice(array_keys($brands_count), 0, 10);

        // Nettoyer les valeurs vides et limiter à 5 éléments
        $familles = array_filter($familles);
        $familles = array_slice($familles, 0, 5);
        
        $sous_familles = array_filter($sous_familles);
        $sous_familles = array_slice($sous_familles, 0, 5);
        
        $sous_sous_familles = array_filter($sous_sous_familles);
        $sous_sous_familles = array_slice($sous_sous_familles, 0, 5);
        
        // Nettoyer et limiter les nouveaux attributs (garder toutes les valeurs non vides pour les filtres)
        $vues_eclatees = array_filter($vues_eclatees, function($value) {
            return !empty($value);
        });
        $vues_eclatees = array_slice($vues_eclatees, 0, 5);
        
        $manuels_utilisation = array_filter($manuels_utilisation, function($value) {
            return !empty($value);
        });
        $manuels_utilisation = array_slice($manuels_utilisation, 0, 5);
        
        $datasheets = array_filter($datasheets, function($value) {
            return !empty($value);
        });
        $datasheets = array_slice($datasheets, 0, 5);
        
        $manuels_reparation = array_filter($manuels_reparation, function($value) {
            return !empty($value);
        });
        $manuels_reparation = array_slice($manuels_reparation, 0, 5);
        
        // Pagination sur les produits filtrés
        $total_products = count($filtered_products);
        $start_index = ($page - 1) * $per_page;
        $current_page_products = array_slice($filtered_products, $start_index, $per_page);
        
        // Debug simplifié
        ?>
        <script>
            console.log("Debug: Produits avec docs:", <?php echo $total_products; ?>);
            console.log("Debug: Page actuelle:", <?php echo $page; ?>);
            console.log("Debug: Produits affichés:", <?php echo count($current_page_products); ?>);
        </script>
        <?php
        ?>
        <div class="documentation-center">
            <style>
                .documentation-center {
                    font-family: Arial, sans-serif;
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 20px;
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
                    margin: 0 0 10px 0;
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
                    color: #1f2937;
                }
                
                .search-input:focus {
                    outline: none;
                    border: 2px solid #0066cc;
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
                    font-size: 14px;
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
                    margin-top: 20px;
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
                    color: #6c757d;
                    font-size: 0.9em;
                }
                
                .products-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
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
                    margin: 2px;
                    font-size: 0.85em;
                    border: 1px solid #e9ecef;
                }
                
                .famille { border-left: 4px solid #0066cc; }
                .sous-famille { border-left: 4px solid #28a745; }
                .sous-sous-famille { border-left: 4px solid #ffc107; }
                .reference-fabriquant { border-left: 4px solid #6f42c1; background-color: #f8f9fc; }
                .categorie-wp { border-left: 4px solid #e31206; background-color: #fef2f2; }
                .brand { border-left: 4px solid #17a2b8; background-color: #e6f7ff; }
                
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
                    background: #6f42c1;
                }
                .vue-eclatee-link:hover {
                    background: #5a32a3;
                }
                
                .manuel-link {
                    background: #28a745;
                }
                .manuel-link:hover {
                    background: #218838;
                }
                
                .datasheet-link {
                    background: #ffc107;
                    color: #212529;
                }
                .datasheet-link:hover {
                    background: #e0a800;
                    color: #212529;
                }
                
                .repair-link {
                    background: #e31206;
                }
                .repair-link:hover {
                    background: #c10e04;
                }
                
                /* Style pour les boutons désactivés (valeurs non-URL) */
                .download-link.disabled {
                    opacity: 0.6;
                    cursor: default;
                    pointer-events: none;
                    position: relative;
                }
                
                .download-link.disabled::after {
                    content: " (non-URL)";
                    font-size: 0.7em;
                    opacity: 0.8;
                }
                

                
                .pagination-container {
                    text-align: center;
                    margin-top: 30px;
                    padding: 20px 0;
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
                
                .pagination-button:hover {
                    background: #0052a3;
                    color: white;
                    text-decoration: none;
                }
                
                .pagination-button.disabled {
                    background: #ccc;
                    color: #666;
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
                    <input type="search" name="search" value="<?php echo esc_attr($search_query); ?>" class="search-input" placeholder="Rechercher un produit..." />
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
                        <label for="filter-famille">Famille (max 5)</label>
                        <select id="filter-famille" name="famille" onchange="this.form.submit()">
                            <option value="">Toutes les familles</option>
                            <?php foreach ($familles as $famille): ?>
                                <option value="<?php echo esc_attr($famille); ?>" <?php selected($selected_famille, $famille); ?>><?php echo esc_html($famille); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-sous-famille">Sous-famille (max 5)</label>
                        <select id="filter-sous-famille" name="sous_famille" onchange="this.form.submit()">
                            <option value="">Toutes les sous-familles</option>
                            <?php foreach ($sous_familles as $sous_famille): ?>
                                <option value="<?php echo esc_attr($sous_famille); ?>" <?php selected($selected_sous_famille, $sous_famille); ?>><?php echo esc_html($sous_famille); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-sous-sous-famille">Sous-sous-famille (max 5)</label>
                        <select id="filter-sous-sous-famille" name="sous_sous_famille" onchange="this.form.submit()">
                            <option value="">Toutes les sous-sous-familles</option>
                            <?php foreach ($sous_sous_familles as $sous_sous_famille): ?>
                                <option value="<?php echo esc_attr($sous_sous_famille); ?>" <?php selected($selected_sous_sous_famille, $sous_sous_famille); ?>><?php echo esc_html($sous_sous_famille); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-vue-eclatee">Vue éclatée (max 5)</label>
                        <select id="filter-vue-eclatee" name="vue_eclatee" onchange="this.form.submit()">
                            <option value="">Toutes les vues éclatées</option>
                            <?php foreach ($vues_eclatees as $vue): ?>
                                <option value="<?php echo esc_attr($vue); ?>" <?php selected($selected_vue_eclatee, $vue); ?>><?php echo esc_html($vue); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-manuel-utilisation">Manuel d'utilisation (max 5)</label>
                        <select id="filter-manuel-utilisation" name="manuel_utilisation" onchange="this.form.submit()">
                            <option value="">Tous les manuels d'utilisation</option>
                            <?php foreach ($manuels_utilisation as $manuel): ?>
                                <option value="<?php echo esc_attr($manuel); ?>" <?php selected($selected_manuel_utilisation, $manuel); ?>><?php echo esc_html($manuel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-datasheet">Datasheet (max 5)</label>
                        <select id="filter-datasheet" name="datasheet" onchange="this.form.submit()">
                            <option value="">Toutes les datasheets</option>
                            <?php foreach ($datasheets as $datasheet): ?>
                                <option value="<?php echo esc_attr($datasheet); ?>" <?php selected($selected_datasheet, $datasheet); ?>><?php echo esc_html($datasheet); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-manuel-reparation">Manuel de réparation (max 5)</label>
                        <select id="filter-manuel-reparation" name="manuel_reparation" onchange="this.form.submit()">
                            <option value="">Tous les manuels de réparation</option>
                            <?php foreach ($manuels_reparation as $manuel): ?>
                                <option value="<?php echo esc_attr($manuel); ?>" <?php selected($selected_manuel_reparation, $manuel); ?>><?php echo esc_html($manuel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-reference-fabriquant">Référence fabriquant (top 10)</label>
                        <select id="filter-reference-fabriquant" name="reference_fabriquant" onchange="this.form.submit()">
                            <option value="">Toutes les références</option>
                            <?php foreach ($references_fabriquant as $reference): ?>
                                <option value="<?php echo esc_attr($reference); ?>" <?php selected($selected_reference_fabriquant, $reference); ?>><?php echo esc_html($reference); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-categorie-wp">Catégorie produit (top 10)</label>
                        <select id="filter-categorie-wp" name="categorie_wp" onchange="this.form.submit()">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories_wp as $categorie): ?>
                                <option value="<?php echo esc_attr($categorie); ?>" <?php selected($selected_categorie_wp, $categorie); ?>><?php echo esc_html($categorie); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-brand">Marque (top 10)</label>
                        <select id="filter-brand" name="brand" onchange="this.form.submit()">
                            <option value="">Toutes les marques</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo esc_attr($brand); ?>" <?php selected($selected_brand, $brand); ?>><?php echo esc_html($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
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
                                    <span class="category-tag famille"><?php echo esc_html($product['famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_famille'])): ?>
                                    <span class="category-tag sous-famille"><?php echo esc_html($product['sous_famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['sous_sous_famille'])): ?>
                                    <span class="category-tag sous-sous-famille"><?php echo esc_html($product['sous_sous_famille']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['reference_fabriquant'])): ?>
                                    <span class="category-tag reference-fabriquant"><?php echo esc_html($product['reference_fabriquant']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['categorie_wp'])): ?>
                                    <span class="category-tag categorie-wp"><?php echo esc_html($product['categorie_wp']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['brand'])): ?>
                                    <span class="category-tag brand"><?php echo esc_html($product['brand']); ?></span>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    Vue éclatée
                                </a>
                                <?php elseif (!empty($product['vue_eclatee'])): ?>
                                <span class="download-link vue-eclatee-link disabled" title="Vue éclatée disponible: <?php echo esc_attr($product['vue_eclatee']); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    Vue éclatée
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_utilisation']) && filter_var($product['manuel_utilisation'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['manuel_utilisation']); ?>" 
                                   class="download-link manuel-link" 
                                   target="_blank" title="Manuel d'utilisation">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    Manuel utilisation
                                </a>
                                <?php elseif (!empty($product['manuel_utilisation'])): ?>
                                <span class="download-link manuel-link disabled" title="Manuel d'utilisation disponible: <?php echo esc_attr($product['manuel_utilisation']); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    Manuel utilisation
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['datasheet']) && filter_var($product['datasheet'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['datasheet']); ?>" 
                                   class="download-link datasheet-link" 
                                   target="_blank" title="Datasheet">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
                                    Datasheet
                                </a>
                                <?php elseif (!empty($product['datasheet'])): ?>
                                <span class="download-link datasheet-link disabled" title="Datasheet disponible: <?php echo esc_attr($product['datasheet']); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
                                    Datasheet
                                </span>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['manuel_reparation']) && filter_var($product['manuel_reparation'], FILTER_VALIDATE_URL)): ?>
                                <a href="<?php echo esc_url($product['manuel_reparation']); ?>" 
                                   class="download-link repair-link" 
                                   target="_blank" title="Manuel de réparation">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                    Manuel réparation
                                </a>
                                <?php elseif (!empty($product['manuel_reparation'])): ?>
                                <span class="download-link repair-link disabled" title="Manuel de réparation disponible: <?php echo esc_attr($product['manuel_reparation']); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                    Manuel réparation
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
                        <a href="?<?php echo http_build_query($current_params); ?>" class="pagination-button">‹ Précédent</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">‹ Précédent</span>
                    <?php endif; ?>
                    
                    <span class="pagination-button disabled">Page <?php echo $page; ?> / <?php echo $total_pages; ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <?php 
                        $current_params['doc_page'] = $page + 1; 
                        ?>
                        <a href="?<?php echo http_build_query($current_params); ?>" class="pagination-button">Suivant ›</a>
                    <?php else: ?>
                        <span class="pagination-button disabled">Suivant ›</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="no-results">
                    <p>Aucune documentation trouvée pour les critères sélectionnés.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
}

// Appel de la fonction pour l'affichage
echo doc_download_display();
?>