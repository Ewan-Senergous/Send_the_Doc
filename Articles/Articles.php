<?php

// Fonction pour extraire l'image principale du contenu HTML
if (!function_exists('get_principal_image_from_content')) {
    function get_principal_image_from_content($post_id) {
        $post_content = get_post_field('post_content', $post_id);
        $post_title = get_the_title($post_id);
        
        // Debug : Affichage direct dans la page (comme DiagPompe.php)
        echo '<div style="background:#f0f0f0;padding:15px;margin:10px 0;border-radius:8px;font-size:12px;border-left:4px solid #2196F3;">';
        echo '<h4 style="margin:0 0 10px 0;color:#333;">🔍 DEBUG IMAGE PRINCIPALE</h4>';
        echo '<p><strong>Post ID:</strong> ' . $post_id . '</p>';
        echo '<p><strong>Post Title:</strong> ' . htmlspecialchars($post_title) . '</p>';
        echo '<p><strong>Content length:</strong> ' . strlen($post_content) . ' caractères</p>';
        
        // Détecter si c'est du contenu Divi
        $is_divi = strpos($post_content, '[et_pb_') !== false;
        echo '<p><strong>Type de contenu:</strong> ' . ($is_divi ? 'Divi Builder' : 'HTML standard') . '</p>';
        
        if ($is_divi) {
            echo '<p><strong>🔍 Recherche dans les shortcodes Divi...</strong></p>';
            
            // Recherche des shortcodes et_pb_image avec alt ou title_text contenant "principal"
            preg_match_all('/\[et_pb_image[^\]]*\]/i', $post_content, $divi_images);
            echo '<p><strong>Nombre de shortcodes et_pb_image trouvés:</strong> ' . count($divi_images[0]) . '</p>';
            
            if (count($divi_images[0]) > 0) {
                echo '<p><strong>Shortcodes Divi détectés:</strong></p>';
                echo '<ol style="margin:0;padding-left:20px;">';
                foreach ($divi_images[0] as $index => $shortcode) {
                    echo '<li style="margin:5px 0;"><code style="background:#fff;padding:2px 4px;border-radius:2px;font-size:10px;">' . htmlspecialchars($shortcode) . '</code></li>';
                }
                echo '</ol>';
                
                // Analyser chaque shortcode pour trouver l'image principale
                foreach ($divi_images[0] as $shortcode) {
                    // Extraire les attributs du shortcode
                    preg_match('/src="([^"]+)"/', $shortcode, $src_match);
                    preg_match('/alt="([^"]*principal[^"]*)"/', $shortcode, $alt_match);
                    preg_match('/title_text="([^"]*principal[^"]*)"/', $shortcode, $title_match);
                    
                    if ($src_match && ($alt_match || $title_match)) {
                        $image_url = $src_match[1];
                        $match_type = $alt_match ? 'alt' : 'title_text';
                        $match_value = $alt_match ? $alt_match[1] : $title_match[1];
                        
                        echo '<p style="color:green;"><strong>✅ Trouvé image Divi avec ' . $match_type . ' principal:</strong></p>';
                        echo '<p style="color:green;"><strong>URL:</strong> ' . htmlspecialchars($image_url) . '</p>';
                        echo '<p style="color:green;"><strong>' . ucfirst($match_type) . ':</strong> ' . htmlspecialchars($match_value) . '</p>';
                        echo '</div>';
                        return $image_url;
                    }
                }
                
                // Fallback : première image Divi trouvée
                if (isset($divi_images[0][0])) {
                    preg_match('/src="([^"]+)"/', $divi_images[0][0], $first_src);
                    if ($first_src) {
                        echo '<p style="color:orange;"><strong>⚠️ Fallback première image Divi:</strong> ' . htmlspecialchars($first_src[1]) . '</p>';
                        echo '</div>';
                        return $first_src[1];
                    }
                }
            }
        } else {
            // Traitement HTML standard (ancien code)
            echo '<p><strong>Content preview:</strong></p>';
            echo '<pre style="background:#fff;padding:10px;border-radius:4px;max-height:200px;overflow:auto;font-size:11px;">';
            echo htmlspecialchars(substr($post_content, 0, 800)) . '...';
            echo '</pre>';
            
            // Recherche toutes les images dans le contenu
            preg_match_all('/<img[^>]*>/i', $post_content, $all_images);
            echo '<p><strong>Nombre d\'images trouvées:</strong> ' . count($all_images[0]) . '</p>';
            
            if (count($all_images[0]) > 0) {
                echo '<p><strong>Images détectées:</strong></p>';
                echo '<ol style="margin:0;padding-left:20px;">';
                foreach ($all_images[0] as $index => $img) {
                    echo '<li style="margin:5px 0;"><code style="background:#fff;padding:2px 4px;border-radius:2px;font-size:10px;">' . htmlspecialchars($img) . '</code></li>';
                }
                echo '</ol>';
            }
            
            // Recherche d'une image avec alt contenant "principal"
            if (preg_match('/<img[^>]+alt=["\'][^"\']*principal[^"\']*["\'][^>]*src=["\']([^"\']+)["\'][^>]*>/i', $post_content, $matches)) {
                echo '<p style="color:green;"><strong>✅ Trouvé image avec alt principal:</strong> ' . htmlspecialchars($matches[1]) . '</p>';
                echo '</div>';
                return $matches[1];
            }
            
            // Recherche d'une image avec title contenant "principal"
            if (preg_match('/<img[^>]+title=["\'][^"\']*principal[^"\']*["\'][^>]*src=["\']([^"\']+)["\'][^>]*>/i', $post_content, $matches)) {
                echo '<p style="color:green;"><strong>✅ Trouvé image avec title principal:</strong> ' . htmlspecialchars($matches[1]) . '</p>';
                echo '</div>';
                return $matches[1];
            }
            
            // Recherche d'une image avec src contenant "principal" et alt
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]+alt=["\'][^"\']*principal[^"\']*["\'][^>]*>/i', $post_content, $matches)) {
                echo '<p style="color:green;"><strong>✅ Trouvé image src+alt principal:</strong> ' . htmlspecialchars($matches[1]) . '</p>';
                echo '</div>';
                return $matches[1];
            }
            
            // Recherche d'une image avec src contenant "principal" et title
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]+title=["\'][^"\']*principal[^"\']*["\'][^>]*>/i', $post_content, $matches)) {
                echo '<p style="color:green;"><strong>✅ Trouvé image src+title principal:</strong> ' . htmlspecialchars($matches[1]) . '</p>';
                echo '</div>';
                return $matches[1];
            }
            
            // Fallback : première image du contenu
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post_content, $matches)) {
                echo '<p style="color:orange;"><strong>⚠️ Fallback première image:</strong> ' . htmlspecialchars($matches[1]) . '</p>';
                echo '</div>';
                return $matches[1];
            }
        }
        
        echo '<p style="color:red;"><strong>❌ Aucune image trouvée dans le contenu</strong></p>';
        echo '</div>';
        return false;
    }
}

if (!function_exists('articles_page_display')) {
    function articles_page_display() {
        ob_start();
        
        // Récupération des paramètres de recherche et filtres
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $selected_categories = isset($_GET['categories']) ? array_map('sanitize_text_field', $_GET['categories']) : array();

        // Récupération des catégories d'articles
        $categories = get_categories(array(
            'taxonomy' => 'category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        // Arguments pour la requête des articles par thèmes
        $theme_args = array(
            'post_type' => 'post',
            'posts_per_page' => 6,
            'post_status' => 'publish',
            'meta_query' => array(),
            'tax_query' => array()
        );

        // Ajout de la recherche si présente
        if (!empty($search_query)) {
            $theme_args['s'] = $search_query;
        }

        // Ajout des filtres de catégories si sélectionnées
        if (!empty($selected_categories)) {
            $theme_args['tax_query'][] = array(
                'taxonomy' => 'category',
                'field' => 'slug',
                'terms' => $selected_categories,
                'operator' => 'IN'
            );
        }

        // Requête pour les articles par thèmes
        $theme_articles = new WP_Query($theme_args);

        // Requête pour les derniers articles publiés
        $latest_args = array(
            'post_type' => 'post',
            'posts_per_page' => 6,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $latest_articles = new WP_Query($latest_args);
        ?>

        <style>
        .articles-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .articles-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .articles-title {
            font-size: 1.875rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        
        .search-form {
            max-width: 28rem;
            margin: 0 auto 2rem auto;
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
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #f9fafb;
            font-size: 0.875rem;
            color: #1f2937;
        }
        
        .search-input:focus {
            outline: none;
            border: 3px solid #3b82f6;
        }
        
        .search-button {
            position: absolute;
            right: 0.5rem;
            bottom: 0.5rem;
            background-color: #1d4ed8;
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
            background-color: #1e40af;
        }
        
        .search-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px #93c5fd;
        }
        
        .main-layout {
            display: flex;
            gap: 2rem;
            flex-direction: column;
        }
        
        @media (min-width: 1024px) {
            .main-layout {
                flex-direction: row;
            }
            .sidebar {
                width: 25%;
            }
            .content {
                width: 75%;
            }
        }
        
        .dropdown-container {
            position: relative;
        }
        
        .dropdown-button {
            width: 100%;
            background-color: #1d4ed8;
            color: white;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }
        
        .dropdown-button:hover {
            background-color: #1e40af;
        }
        
        .dropdown-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px #93c5fd;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-top: 0.5rem;
            z-index: 10;
            display: none;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-list {
            padding: 0.75rem;
            margin: 0;
            list-style: none;
        }
        
        .dropdown-item {
            margin-bottom: 0.25rem;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .checkbox-container:hover {
            background-color: #f3f4f6;
        }
        
        .checkbox-input {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            accent-color: #3b82f6;
            list-style: none;
        }
        
        .checkbox-input:focus {
            outline: none;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #1f2937;
            cursor: pointer;
        }
        
        .category-count {
            color: #6b7280;
            font-weight: normal;
        }
        
        .section {
            margin-bottom: 3rem;
        }
        
        .section-header {
            display: flex;
            margin-bottom: 1.5rem;
        }
        
        .section-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .articles-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        .article-card {
            max-width: 24rem;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .article-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .article-image {
            width: 100%;
            height: 12rem;
            object-fit: cover;
        }
        
        .article-content {
            padding: 1.25rem;
        }
        
        .article-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-decoration: none;
            display: block;
        }
        
        .article-title:hover {
            color: #1d4ed8;
        }
        
        .article-excerpt {
            color: #6b7280;
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }
        
        .article-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .read-more-button {
            display: inline-flex;
            align-items: center;
            background-color: #1d4ed8;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .read-more-button:hover {
            background-color: #1e40af;
            color: white;
        }
        
        .read-more-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px #93c5fd;
        }
        
        .read-more-icon {
            margin-left: 0.5rem;
            margin-top: 0.2rem;
            width: 0.875rem;
            height: 0.875rem;
        }
        
        .article-date {
            display: flex;
            align-items: center;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .date-icon {
            margin-right: 0.25rem;
            width: 1rem;
            height: 1rem;
        }
        
        .see-more-container {
            text-align: center;
        }
        
        .see-more-button {
            background-color: #1d4ed8;
            color: white;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            margin: 0.5rem;
            transition: all 0.2s;
        }
        
        .see-more-button:hover {
            background-color: #1e40af;
        }
        
        .see-more-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px #93c5fd;
        }
        
        .no-articles {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .hidden-input {
            display: none;
        }
        
        /* Supprimer les puces des listes */
        .dropdown-list {
            list-style-type: none;
        }
        
        .dropdown-item {
            list-style-type: none;
        }
        
        /* Supprimer les puces des checkboxes */
        .checkbox-container::before {
            display: none;
        }
        
        .dropdown-list li::before {
            display: none;
        }
        
        .dropdown-list li {
            list-style: none;
        }
        </style>

        <div class="articles-container">
            <!-- En-tête de la page -->
            <div class="articles-header">
                <h1 class="articles-title">
                    Découvrez l'ensemble de la documentation technique que vous pouvez télécharger.
                </h1>
            </div>

            <!-- Formulaire de recherche -->
            <form method="GET" class="search-form">
                <div class="search-container">
                    <div class="search-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" name="search" value="<?php echo esc_attr($search_query); ?>" class="search-input" placeholder="Rechercher un article, une référence..." />
                    <button type="submit" class="search-button">Rechercher</button>
                </div>
                
                <!-- Champs cachés pour maintenir les filtres de catégories -->
                <?php foreach ($selected_categories as $cat): ?>
                    <input type="hidden" name="categories[]" value="<?php echo esc_attr($cat); ?>" class="hidden-input">
                <?php endforeach; ?>
            </form>

            <div class="main-layout">
                <!-- Sidebar avec filtres -->
                <div class="sidebar">
                    <div class="dropdown-container">
                        <button type="button" class="dropdown-button" onclick="toggleDropdown()">
                            Filtrer par catégorie
                            <svg width="10" height="6" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>
                        
                        <!-- Dropdown menu -->
                        <div id="dropdownBgHover" class="dropdown-menu">
                            <form method="GET" id="categoryForm">
                                <input type="hidden" name="search" value="<?php echo esc_attr($search_query); ?>" class="hidden-input">
                                <ul class="dropdown-list">
                                    <?php foreach ($categories as $category): ?>
                                    <li class="dropdown-item">
                                        <div class="checkbox-container">
                                            <input
                                                id="checkbox-<?php echo $category->term_id; ?>"
                                                type="checkbox"
                                                name="categories[]"
                                                value="<?php echo esc_attr($category->slug); ?>"
                                                <?php echo in_array($category->slug, $selected_categories) ? 'checked' : ''; ?>
                                                onchange="document.getElementById('categoryForm').submit();"
                                                class="checkbox-input"
                                            >
                                            <label for="checkbox-<?php echo $category->term_id; ?>" class="checkbox-label">
                                                <?php echo esc_html($category->name); ?>
                                                <span class="category-count">(<?php echo $category->count; ?>)</span>
                                            </label>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="content">
                    <!-- Section Thèmes articles -->
                    <div class="section">
                        <div class="section-header">
                            <span class="section-icon">📋</span>
                            <h2 class="section-title">Thèmes articles</h2>
                        </div>

                        <?php if ($theme_articles->have_posts()): ?>
                        <div class="articles-grid">
                            <?php
                            $count = 0;
                            while ($theme_articles->have_posts() && $count < 2):
                                $theme_articles->the_post();
                                $count++;
                                $featured_image = get_principal_image_from_content(get_the_ID());
                                if (!$featured_image) {
                                    $featured_image = 'https://www.cenov-distribution.fr/wp-content/uploads/2024/01/default-article.jpg';
                                }
                                // Debug front-end
                                echo '<script>console.log("🔍 DEBUG THÈMES - Article ID: ' . get_the_ID() . '");';
                                echo 'console.log("🔍 DEBUG THÈMES - Article Title: ' . addslashes(get_the_title()) . '");';
                                echo 'console.log("🔍 DEBUG THÈMES - Image trouvée: ' . addslashes($featured_image) . '");</script>';
                            ?>
                            <div class="article-card">
                                <a href="<?php the_permalink(); ?>">
                                    <img class="article-image" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>
                                <div class="article-content">
                                    <a href="<?php the_permalink(); ?>" class="article-title"><?php the_title(); ?></a>
                                    <p class="article-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                    </p>
                                    <div class="article-footer">
                                        <a href="<?php the_permalink(); ?>" class="read-more-button">
                                            Lire la suite
                                            <svg class="read-more-icon" fill="none" viewBox="0 0 14 10">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                            </svg>
                                        </a>
                                        
                                        <div class="article-date">
                                            <svg class="date-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M8 2v4"/>
                                                <path d="M16 2v4"/>
                                                <rect width="18" height="18" x="3" y="4" rx="2"/>
                                                <path d="M3 10h18"/>
                                            </svg>
                                            <?php echo get_the_date('j F Y'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($theme_articles->found_posts > 2): ?>
                        <div class="see-more-container">
                            <button type="button" onclick="loadMoreThemeArticles()" class="see-more-button">
                                Voir plus
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="no-articles">
                            <p>Aucun article trouvé pour les critères sélectionnés.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Section Derniers Articles Publiés -->
                    <div class="section">
                        <div class="section-header">
                            <span class="section-icon">📋</span>
                            <h2 class="section-title">Derniers Articles Publiés</h2>
                        </div>

                        <?php if ($latest_articles->have_posts()): ?>
                        <div class="articles-grid">
                            <?php
                            $count = 0;
                            while ($latest_articles->have_posts() && $count < 2):
                                $latest_articles->the_post();
                                $count++;
                                $featured_image = get_principal_image_from_content(get_the_ID());
                                if (!$featured_image) {
                                    $featured_image = 'https://www.cenov-distribution.fr/wp-content/uploads/2024/01/default-article.jpg';
                                }
                                // Debug front-end
                                echo '<script>console.log("📰 DEBUG DERNIERS - Article ID: ' . get_the_ID() . '");';
                                echo 'console.log("📰 DEBUG DERNIERS - Article Title: ' . addslashes(get_the_title()) . '");';
                                echo 'console.log("📰 DEBUG DERNIERS - Image trouvée: ' . addslashes($featured_image) . '");</script>';
                            ?>
                            <div class="article-card">
                                <a href="<?php the_permalink(); ?>">
                                    <img class="article-image" src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>
                                <div class="article-content">
                                    <a href="<?php the_permalink(); ?>" class="article-title"><?php the_title(); ?></a>
                                    <p class="article-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                    </p>
                                    <div class="article-footer">
                                        <a href="<?php the_permalink(); ?>" class="read-more-button">
                                            Lire la suite
                                            <svg class="read-more-icon" fill="none" viewBox="0 0 14 10">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                                            </svg>
                                        </a>
                                        
                                        <div class="article-date">
                                            <svg class="date-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M8 2v4"/>
                                                <path d="M16 2v4"/>
                                                <rect width="18" height="18" x="3" y="4" rx="2"/>
                                                <path d="M3 10h18"/>
                                            </svg>
                                            <?php echo get_the_date('j F Y'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($latest_articles->found_posts > 2): ?>
                        <div class="see-more-container">
                            <button type="button" onclick="loadMoreLatestArticles()" class="see-more-button">
                                Voir plus
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="no-articles">
                            <p>Aucun article récent trouvé.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownBgHover');
            dropdown.classList.toggle('show');
        }

        // Fermer le dropdown si on clique ailleurs
        document.addEventListener('click', function(event) {
            const button = document.querySelector('.dropdown-button');
            const dropdown = document.getElementById('dropdownBgHover');
            
            if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Fonctions pour charger plus d'articles (à implémenter avec AJAX si nécessaire)
        function loadMoreThemeArticles() {
            console.log('Chargement de plus d\'articles par thèmes...');
            // Implémentation AJAX à ajouter
        }

        function loadMoreLatestArticles() {
            console.log('Chargement de plus d\'articles récents...');
            // Implémentation AJAX à ajouter
        }
        </script>

        <?php
        // Réinitialiser les données de post
        wp_reset_postdata();
        
        return ob_get_clean();
    }
}

// Appel de la fonction pour l'affichage
echo articles_page_display();
?>
