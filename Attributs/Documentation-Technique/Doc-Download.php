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
        
        // Paramètres de pagination
        $page = isset($_GET['doc_page']) ? max(1, intval($_GET['doc_page'])) : 1;
        $per_page = 12; // Limiter à 12 produits par page
        
        // Récupérer les produits avec LIMITATION pour éviter les bugs
        $products = wc_get_products(array(
            'status' => 'publish',
            'limit' => 100, // Limiter à 100 au lieu de -1 pour éviter les problèmes mémoire
            'page' => 1
        ));
        
        // Debug simplifié
        echo '<script>console.log("Debug: Nombre de produits récupérés:", ' . count($products) . ');</script>';

        // Filtrer les produits qui ont réellement une documentation
        $products_with_docs = array();
        
        foreach ($products as $product) {
            $product_id = $product->get_id();
            $product_name = $product->get_name();
            
            // Récupérer les attributs
            $documentation_url = $product->get_attribute('documentation-technique');
            $documentation_url_alt = $product->get_attribute('Documentation-technique');
            $famille = $product->get_attribute('famille');
            $famille_alt = $product->get_attribute('Famille');
            $sous_famille = $product->get_attribute('sous-famille');
            $sous_famille_alt = $product->get_attribute('Sous-Famille');
            $sous_sous_famille = $product->get_attribute('sous-sous-famille');
            $sous_sous_famille_alt = $product->get_attribute('Sous-sous-Famille');
            
            // Essayer les deux variantes de nom d'attribut
            $final_doc_url = !empty($documentation_url) ? $documentation_url : $documentation_url_alt;
            $final_famille = !empty($famille) ? $famille : $famille_alt;
            $final_sous_famille = !empty($sous_famille) ? $sous_famille : $sous_famille_alt;
            $final_sous_sous_famille = !empty($sous_sous_famille) ? $sous_sous_famille : $sous_sous_famille_alt;
            
            if (!empty($final_doc_url)) {
                $products_with_docs[] = array(
                    'id' => $product_id,
                    'name' => $product_name,
                    'documentation_url' => $final_doc_url,
                    'famille' => $final_famille,
                    'sous_famille' => $final_sous_famille,
                    'sous_sous_famille' => $final_sous_sous_famille,
                    'permalink' => $product->get_permalink()
                );
            }
        }
        
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

        // Récupérer les valeurs uniques pour les filtres (limiter à 5 par catégorie)
        $familles = array_unique(array_column($products_with_docs, 'famille'));
        $sous_familles = array_unique(array_column($products_with_docs, 'sous_famille'));
        $sous_sous_familles = array_unique(array_column($products_with_docs, 'sous_sous_famille'));

        // Nettoyer les valeurs vides et limiter à 5 éléments
        $familles = array_filter($familles);
        $familles = array_slice($familles, 0, 5);
        
        $sous_familles = array_filter($sous_familles);
        $sous_familles = array_slice($sous_familles, 0, 5);
        
        $sous_sous_familles = array_filter($sous_sous_familles);
        $sous_sous_familles = array_slice($sous_sous_familles, 0, 5);
        
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
                    background: linear-gradient(135deg, #0066cc, #004499);
                    color: white;
                    border-radius: 8px;
                }
                
                .doc-header h1 {
                    margin: 0 0 10px 0;
                    font-size: 2.5em;
                    font-weight: bold;
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
                    top: 50%;
                    transform: translateY(-50%);
                    pointer-events: none;
                    color: #6b7280;
                }
                
                .search-input {
                    width: 100%;
                    padding: 1rem 1rem 1rem 2.5rem;
                    border: 1px solid #d1d5db;
                    border-radius: 0.5rem;
                    background-color: #f9fafb;
                    font-size: 0.875rem;
                    color: #1f2937;
                }
                
                .search-input:focus {
                    outline: none;
                    border: 2px solid #93c5fd;
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
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s;
                }
                
                .search-button:hover {
                    background-color: #0052a3;
                }
                
                .filters-container {
                    background: #f8f9fa;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                    border: 1px solid #e9ecef;
                }
                
                .filters-row {
                    display: flex;
                    gap: 15px;
                    flex-wrap: wrap;
                    align-items: center;
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
                    border: 2px solid #ddd;
                    border-radius: 5px;
                    font-size: 14px;
                    background: white;
                    transition: border-color 0.3s;
                }
                
                .filter-group select:focus {
                    border-color: #0066cc;
                    outline: none;
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
                    background: #e31206;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    transition: background 0.3s;
                }
                
                .btn-reset:hover {
                    background: #b60f05;
                }
                
                .results-container {
                    margin-top: 20px;
                }
                
                .results-header {
                    background: #e9ecef;
                    padding: 15px;
                    border-radius: 5px 5px 0 0;
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
                    border: 1px solid #e9ecef;
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
                
                .download-link:before {
                    content: "📄 ";
                    margin-right: 5px;
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
                    <button type="submit" class="search-button">Rechercher</button>
                </div>
                
                <!-- Champs cachés pour maintenir les filtres -->
                <input type="hidden" name="famille" value="<?php echo esc_attr($selected_famille); ?>">
                <input type="hidden" name="sous_famille" value="<?php echo esc_attr($selected_sous_famille); ?>">
                <input type="hidden" name="sous_sous_famille" value="<?php echo esc_attr($selected_sous_sous_famille); ?>">
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
                    
                    <div class="filter-actions">
                        <a href="?" class="btn-reset">Réinitialiser</a>
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
                            </div>
                            
                            <a href="<?php echo esc_url($product['documentation_url']); ?>" 
                               class="download-link" 
                               target="_blank">
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
