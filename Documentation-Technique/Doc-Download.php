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
        $selected_manuel_utilisation = isset($_GET['manuel_utilisation']) ? sanitize_text_field($_GET['manuel_utilisation']) : '';
        $selected_datasheet = isset($_GET['datasheet']) ? sanitize_text_field($_GET['datasheet']) : '';
        $selected_vue_eclatee = isset($_GET['vue_eclatee']) ? sanitize_text_field($_GET['vue_eclatee']) : '';
        $selected_manuel_reparation = isset($_GET['manuel_reparation']) ? sanitize_text_field($_GET['manuel_reparation']) : '';
        $selected_category = isset($_GET['product_category']) ? sanitize_text_field($_GET['product_category']) : '';
        $selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
        $selected_ref_fabricant = isset($_GET['ref_fabricant']) ? sanitize_text_field($_GET['ref_fabricant']) : '';
        
        // Paramètres de pagination ultra simplifiés
        $page = isset($_GET['doc_page']) ? max(1, intval($_GET['doc_page'])) : 1;
        $per_page = 5; // Limiter à 5 produits par page pour le debug
        
        // SOLUTION ULTRA SIMPLIFIÉE pour éviter les boucles infinies
        function get_products_with_documentation_optimized() {
            global $wpdb;
            
            echo '<div style="background: orange; padding: 10px; margin: 10px; border: 2px solid red;">';
            echo '<h3>🔴 DEBUG - Début de la fonction</h3>';
            echo '</div>';
            
            // DIAGNOSTIC 1: Vérifier les produits disponibles
            $diagnostic1 = $wpdb->get_results("
                SELECT COUNT(*) as total, p.post_status 
                FROM {$wpdb->posts} p 
                WHERE p.post_type = 'product' 
                GROUP BY p.post_status
            ", ARRAY_A);
            
            echo '<div style="background: cyan; padding: 10px; margin: 10px; border: 2px solid teal;">';
            echo '<h3>🔬 DIAGNOSTIC 1 - Produits disponibles</h3>';
            foreach ($diagnostic1 as $row) {
                echo '<p>Status: ' . $row['post_status'] . ' = ' . $row['total'] . ' produits</p>';
            }
            echo '</div>';
            
            // DIAGNOSTIC 2: Vérifier les taxonomies disponibles
            $diagnostic2 = $wpdb->get_results("
                SELECT DISTINCT taxonomy, COUNT(*) as count
                FROM {$wpdb->term_taxonomy} 
                WHERE taxonomy LIKE 'pa_%' OR taxonomy = 'product_cat'
                GROUP BY taxonomy
                ORDER BY taxonomy
            ", ARRAY_A);
            
            echo '<div style="background: pink; padding: 10px; margin: 10px; border: 2px solid magenta;">';
            echo '<h3>🔬 DIAGNOSTIC 2 - Taxonomies disponibles</h3>';
            foreach ($diagnostic2 as $row) {
                echo '<p>' . $row['taxonomy'] . ' = ' . $row['count'] . ' termes</p>';
            }
            echo '</div>';
            
            // DIAGNOSTIC 3: Vérifier spécifiquement pa_documentation-technique
            $diagnostic3 = $wpdb->get_results("
                SELECT t.name, t.slug, COUNT(tr.object_id) as product_count
                FROM {$wpdb->term_taxonomy} tt
                LEFT JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = 'pa_documentation-technique'
                GROUP BY t.term_id
                ORDER BY product_count DESC
                LIMIT 10
            ", ARRAY_A);
            
            echo '<div style="background: khaki; padding: 10px; margin: 10px; border: 2px solid olive;">';
            echo '<h3>🔬 DIAGNOSTIC 3 - Taxonomie pa_documentation-technique</h3>';
            if (empty($diagnostic3)) {
                echo '<p style="color: red;"><strong>⚠️ PROBLÈME: Taxonomie "pa_documentation-technique" introuvable !</strong></p>';
            } else {
                echo '<ul>';
                foreach ($diagnostic3 as $row) {
                    echo '<li>' . $row['name'] . ' (' . $row['slug'] . ') = ' . $row['product_count'] . ' produits</li>';
                }
                echo '</ul>';
            }
            echo '</div>';
            
            // PAS DE CACHE pour le debugging
            
            // Requête SQL EXACTEMENT comme Test-Doc-Download (SANS les nouveaux LEFT JOIN qui causent la boucle)
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
                LIMIT 10
            ";
            
            echo '<div style="background: yellow; padding: 10px; margin: 10px; border: 2px solid orange;">';
            echo '<h3>🔍 DEBUG - Execution de la requête SQL</h3>';
            echo '<p>SQL: ' . substr($sql, 0, 200) . '...</p>';
            echo '</div>';
            
            $results = $wpdb->get_results($sql, ARRAY_A);
            
            echo '<div style="background: lightgreen; padding: 10px; margin: 10px; border: 2px solid green;">';
            echo '<h3>👍 RESULTATS SQL</h3>';
            echo '<p><strong>Nombre de produits trouvés:</strong> ' . count($results) . '</p>';
            if (!empty($results)) {
                echo '<ul style="max-height: 200px; overflow-y: auto;">';
                foreach ($results as $row) {
                    echo '<li><strong>' . $row['name'] . '</strong><br>';
                    echo 'Doc: ' . substr($row['documentation_url'], 0, 60) . '...</li>';
                }
                echo '</ul>';
            } else {
                echo '<p style="color: red;">AUCUN RESULTAT TROUVE !</p>';
            }
            echo '</div>';
            
            // Formater les résultats TRES SIMPLEMENT
            $products_with_docs = array();
            foreach ($results as $row) {
                if (!empty($row['documentation_url'])) {
                    $products_with_docs[] = array(
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'documentation_url' => $row['documentation_url'],
                        'famille' => '',
                        'sous_famille' => '',
                        'sous_sous_famille' => '',
                        'manuel_utilisation' => '',
                        'datasheet' => '',
                        'vue_eclatee' => '',
                        'manuel_reparation' => '',
                        'product_category' => '',
                        'brand' => '',
                        'reference_fabricant' => '',
                        'permalink' => get_permalink($row['id'])
                    );
                }
            }
            
            // PAS DE CACHE pour le debugging
            
            echo '<div style="background: lightblue; padding: 10px; margin: 10px; border: 2px solid blue;">';
            echo '<h3>📊 PRODUITS FORMATES</h3>';
            echo '<p><strong>Nombre de produits formatés:</strong> ' . count($products_with_docs) . '</p>';
            echo '</div>';
            
            return $products_with_docs;
        }

        // Récupérer TOUS les produits avec documentation (optimisé)
        $products_with_docs = get_products_with_documentation_optimized();

        echo '<div style="background: purple; color: white; padding: 10px; margin: 10px; border: 2px solid purple;">';
        echo '<h3>🔍 DEBUG - Après récupération</h3>';
        echo '<p><strong>Produits récupérés:</strong> ' . count($products_with_docs) . '</p>';
        echo '<p><strong>Recherche:</strong> ' . ($search_query ?: 'Aucune') . '</p>';
        echo '</div>';
        
        // FILTRAGE ULTRA SIMPLIFIE - juste la recherche
        $filtered_products = $products_with_docs;
        
        if (!empty($search_query)) {
            $filtered_products = array_filter($filtered_products, function($product) use ($search_query) {
                return stripos($product['name'], $search_query) !== false;
            });
        }
        
        // TOUS LES AUTRES FILTRES DESACTIVES
        // if (!empty($selected_famille)) {
        // if (!empty($selected_sous_famille)) {
        // if (!empty($selected_sous_sous_famille)) {
        
        // TOUS LES NOUVEAUX FILTRES DESACTIVES pour éviter les problèmes
        // if (!empty($selected_manuel_utilisation)) {
        // if (!empty($selected_datasheet)) {
        // if (!empty($selected_vue_eclatee)) {
        // if (!empty($selected_manuel_reparation)) {
        // if (!empty($selected_category)) {
        // if (!empty($selected_brand)) {
        // if (!empty($selected_ref_fabricant)) {

        // FILTRES SIMPLIFIES - pas de traitement complexe pour éviter les boucles
        $familles = array(); // Désactivé
        $sous_familles = array(); // Désactivé
        $sous_sous_familles = array(); // Désactivé
        $product_categories = array(); // Désactivé
        $brands = array(); // Désactivé
        
        // TOUS les nouveaux filtres désactivés
        $manuel_utilisations = array();
        $datasheets = array();
        $vue_eclatees = array();
        $manuel_reparations = array();
        $references_fabricant = array();

        
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
                .category { border-left: 4px solid #e31206; }
                .brand { border-left: 4px solid #6f42c1; }
                .ref-fabricant { border-left: 4px solid #20c997; }
                
                .product-docs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin-bottom: 15px;
                }
                
                .doc-link {
                    display: inline-block;
                    padding: 6px 10px;
                    background: #f8f9fa;
                    color: #495057;
                    text-decoration: none;
                    border-radius: 15px;
                    font-size: 0.8em;
                    border: 1px solid #dee2e6;
                    transition: all 0.2s;
                }
                
                .doc-link:hover {
                    background: #e9ecef;
                    color: #495057;
                    text-decoration: none;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                }
                
                .manuel-util { border-left: 3px solid #17a2b8; }
                .datasheet { border-left: 3px solid #007bff; }
                .vue-eclatee { border-left: 3px solid #fd7e14; }
                .manuel-rep { border-left: 3px solid #dc3545; }
                
                .download-link {
                    display: inline-block;
                    background: #0066cc;
                    color: white;
                    padding: 10px 15px;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                    transition: background 0.3s;
                    margin-top: 10px;
                }
                
                .download-link:hover {
                    background: #0052a3;
                    color: white;
                    text-decoration: none;
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
                <input type="hidden" name="manuel_utilisation" value="<?php echo esc_attr($selected_manuel_utilisation); ?>">
                <input type="hidden" name="datasheet" value="<?php echo esc_attr($selected_datasheet); ?>">
                <input type="hidden" name="vue_eclatee" value="<?php echo esc_attr($selected_vue_eclatee); ?>">
                <input type="hidden" name="manuel_reparation" value="<?php echo esc_attr($selected_manuel_reparation); ?>">
                <input type="hidden" name="product_category" value="<?php echo esc_attr($selected_category); ?>">
                <input type="hidden" name="brand" value="<?php echo esc_attr($selected_brand); ?>">
                <input type="hidden" name="ref_fabricant" value="<?php echo esc_attr($selected_ref_fabricant); ?>">
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
                        <label for="filter-manuel-utilisation">Manuel d'utilisation</label>
                        <select id="filter-manuel-utilisation" name="manuel_utilisation" onchange="this.form.submit()">
                            <option value="">Tous manuels</option>
                            <?php foreach ($manuel_utilisations as $manuel): ?>
                                <option value="<?php echo esc_attr($manuel); ?>" <?php selected($selected_manuel_utilisation, $manuel); ?>><?php echo esc_html($manuel); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-datasheet">Datasheet</label>
                        <select id="filter-datasheet" name="datasheet" onchange="this.form.submit()">
                            <option value="">Tous datasheets</option>
                            <?php foreach ($datasheets as $datasheet): ?>
                                <option value="<?php echo esc_attr($datasheet); ?>" <?php selected($selected_datasheet, $datasheet); ?>><?php echo esc_html($datasheet); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-vue-eclatee">Vue éclatée</label>
                        <select id="filter-vue-eclatee" name="vue_eclatee" onchange="this.form.submit()">
                            <option value="">Toutes vues éclatées</option>
                            <?php foreach ($vue_eclatees as $vue_eclatee): ?>
                                <option value="<?php echo esc_attr($vue_eclatee); ?>" <?php selected($selected_vue_eclatee, $vue_eclatee); ?>><?php echo esc_html($vue_eclatee); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-manuel-reparation">Manuel de réparation</label>
                        <select id="filter-manuel-reparation" name="manuel_reparation" onchange="this.form.submit()">
                            <option value="">Tous manuels réparation</option>
                            <?php foreach ($manuel_reparations as $manuel_rep): ?>
                                <option value="<?php echo esc_attr($manuel_rep); ?>" <?php selected($selected_manuel_reparation, $manuel_rep); ?>><?php echo esc_html($manuel_rep); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-category">Catégorie WordPress</label>
                        <select id="filter-category" name="product_category" onchange="this.form.submit()">
                            <option value="">Toutes catégories</option>
                            <?php foreach ($product_categories as $category): ?>
                                <option value="<?php echo esc_attr($category); ?>" <?php selected($selected_category, $category); ?>><?php echo esc_html($category); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-brand">Brand</label>
                        <select id="filter-brand" name="brand" onchange="this.form.submit()">
                            <option value="">Toutes marques</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo esc_attr($brand); ?>" <?php selected($selected_brand, $brand); ?>><?php echo esc_html($brand); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="filter-ref-fabricant">Référence fabricant</label>
                        <select id="filter-ref-fabricant" name="ref_fabricant" onchange="this.form.submit()">
                            <option value="">Toutes références</option>
                            <?php foreach ($references_fabricant as $ref_fab): ?>
                                <option value="<?php echo esc_attr($ref_fab); ?>" <?php selected($selected_ref_fabricant, $ref_fab); ?>><?php echo esc_html($ref_fab); ?></option>
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
                                <?php if (!empty($product['product_category'])): ?>
                                    <span class="category-tag category"><?php echo esc_html($product['product_category']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['brand'])): ?>
                                    <span class="category-tag brand"><?php echo esc_html($product['brand']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($product['reference_fabricant'])): ?>
                                    <span class="category-tag ref-fabricant"><?php echo esc_html($product['reference_fabricant']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Nouvelles documentations disponibles -->
                            <div class="product-docs" style="margin-bottom: 15px;">
                                <?php if (!empty($product['manuel_utilisation'])): ?>
                                    <a href="<?php echo esc_url($product['manuel_utilisation']); ?>" class="doc-link manuel-util" target="_blank" title="Manuel d'utilisation">
                                        📖 Manuel
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($product['datasheet'])): ?>
                                    <a href="<?php echo esc_url($product['datasheet']); ?>" class="doc-link datasheet" target="_blank" title="Datasheet">
                                        📊 Datasheet
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($product['vue_eclatee'])): ?>
                                    <a href="<?php echo esc_url($product['vue_eclatee']); ?>" class="doc-link vue-eclatee" target="_blank" title="Vue éclatée">
                                        🔧 Vue éclatée
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($product['manuel_reparation'])): ?>
                                    <a href="<?php echo esc_url($product['manuel_reparation']); ?>" class="doc-link manuel-rep" target="_blank" title="Manuel de réparation">
                                        🔨 Réparation
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <a href="<?php echo esc_url($product['documentation_url']); ?>" 
                               class="download-link" 
                               target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download-icon lucide-download" style="vertical-align: middle; margin-right: 5px;"><path d="M12 15V3"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/></svg>
                                Télécharger la documentation
                            </a>
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