<?php

// Fonction pour extraire l'image principale du contenu HTML
if (!function_exists('get_principal_image_from_content')) {
    function get_principal_image_from_content($post_id) {
        $post_content = get_post_field('post_content', $post_id);
        $post_title = get_the_title($post_id);
        
        // Détecter si c'est du contenu Divi
        $is_divi = strpos($post_content, '[et_pb_') !== false;
        
        if ($is_divi) {
            // PRIORITÉ 1 : Rechercher 'cover1' partout (HTML + Divi)
            
            // Recherche cover1 dans les balises HTML
            if (preg_match('/<img[^>]+(alt|title)="[^"]*cover1[^"]*"[^>]*src="([^"]+)"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[2],
                    'class' => 'cover1'
                ];
            }
            if (preg_match('/<img[^>]+src="([^"]+)"[^>]+(alt|title)="[^"]*cover1[^"]*"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => 'cover1'
                ];
            }
            
            // Recherche cover1 dans les shortcodes Divi
            preg_match_all('/\[et_pb_image[^\]]*\]/i', $post_content, $divi_images);
            if (count($divi_images[0]) > 0) {
                foreach ($divi_images[0] as $shortcode) {
                    preg_match('/src="([^"]+)"/', $shortcode, $src_match);
                    preg_match('/alt="([^"]*)"/', $shortcode, $alt_match);
                    preg_match('/title_text="([^"]*)"/', $shortcode, $title_match);
                    
                    if ($src_match && (
                        ($alt_match && stripos($alt_match[1], 'cover1') !== false) ||
                        ($title_match && stripos($title_match[1], 'cover1') !== false)
                    )) {
                        return [
                            'url' => $src_match[1],
                            'class' => 'cover1'
                        ];
                    }
                }
            }
            
            // PRIORITÉ 2 : Rechercher 'contain' partout (HTML + Divi)
            
            // Recherche contain dans les balises HTML
            if (preg_match('/<img[^>]+(alt|title)="[^"]*contain[^"]*"[^>]*src="([^"]+)"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[2],
                    'class' => 'contain'
                ];
            }
            if (preg_match('/<img[^>]+src="([^"]+)"[^>]+(alt|title)="[^"]*contain[^"]*"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => 'contain'
                ];
            }
            
            // Recherche contain dans les shortcodes Divi
            if (count($divi_images[0]) > 0) {
                foreach ($divi_images[0] as $shortcode) {
                    preg_match('/src="([^"]+)"/', $shortcode, $src_match);
                    preg_match('/alt="([^"]*)"/', $shortcode, $alt_match);
                    preg_match('/title_text="([^"]*)"/', $shortcode, $title_match);
                    
                    if ($src_match && (
                        ($alt_match && stripos($alt_match[1], 'contain') !== false) ||
                        ($title_match && stripos($title_match[1], 'contain') !== false)
                    )) {
                        return [
                            'url' => $src_match[1],
                            'class' => 'contain'
                        ];
                    }
                }
            }
            
            // PRIORITÉ 3 : Rechercher 'principal' partout (HTML + Divi)
            
            // Recherche principal dans les balises HTML
            if (preg_match('/<img[^>]+(alt|title)="[^"]*principal[^"]*"[^>]*src="([^"]+)"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[2],
                    'class' => ''
                ];
            }
            if (preg_match('/<img[^>]+src="([^"]+)"[^>]+(alt|title)="[^"]*principal[^"]*"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // Recherche principal dans les shortcodes Divi
            if (count($divi_images[0]) > 0) {
                foreach ($divi_images[0] as $shortcode) {
                    preg_match('/src="([^"]+)"/', $shortcode, $src_match);
                    preg_match('/alt="([^"]*)"/', $shortcode, $alt_match);
                    preg_match('/title_text="([^"]*)"/', $shortcode, $title_match);
                    
                    if ($src_match && (
                        ($alt_match && stripos($alt_match[1], 'principal') !== false) ||
                        ($title_match && stripos($title_match[1], 'principal') !== false)
                    )) {
                        return [
                            'url' => $src_match[1],
                            'class' => ''
                        ];
                    }
                }
            }
            
            // PRIORITÉ 4 : Première image HTML normale trouvée
            if (preg_match('/<img[^>]+src="([^"]+)"[^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // PRIORITÉ 5 : Première image Divi shortcode trouvée
            if (count($divi_images[0]) > 0 && isset($divi_images[0][0])) {
                preg_match('/src="([^"]+)"/', $divi_images[0][0], $first_src);
                if ($first_src) {
                    return [
                        'url' => $first_src[1],
                        'class' => ''
                    ];
                }
            }
        } else {
            // Traitement HTML standard (ancien code)
            
            // Recherche image avec alt ou title contenant 'cover1'
            if (preg_match('/<img[^>]+(alt|title)=["\\\']([^"\\\']*cover1[^"\\\']*)["\\\'][^>]*src=["\\\']([^"\\\']+)["\\\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[3],
                    'class' => 'cover1'
                ];
            }
            
            // Recherche image avec alt ou title contenant 'contain'
            if (preg_match('/<img[^>]+(alt|title)=["\\\']([^"\\\']*contain[^"\\\']*)["\\\'][^>]*src=["\\\']([^"\\\']+)["\\\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[3],
                    'class' => 'contain'
                ];
            }
            
            // Recherche d'une image avec alt contenant "principal"
            if (preg_match('/<img[^>]+alt=["\'][^"\']*principal[^"\']*["\'][^>]*src=["\']([^"\']+)["\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // Recherche d'une image avec title contenant "principal"
            if (preg_match('/<img[^>]+title=["\'][^"\']*principal[^"\']*["\'][^>]*src=["\']([^"\']+)["\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // Recherche d'une image avec src contenant "principal" et alt
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]+alt=["\'][^"\']*principal[^"\']*["\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // Recherche d'une image avec src contenant "principal" et title
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]+title=["\'][^"\']*principal[^"\']*["\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
            
            // Fallback : première image du contenu
            if (preg_match('/<img[^>]+src=["\\\']([^"\\\']+)["\\\'][^>]*>/i', $post_content, $matches)) {
                return [
                    'url' => $matches[1],
                    'class' => ''
                ];
            }
        }
        
        return false;
    }
}

// Fonction pour extraire un extrait intelligent du contenu
if (!function_exists('get_smart_excerpt')) {
    function get_smart_excerpt($post_id) {
        $post_content = get_post_field('post_content', $post_id);
        
        // Détecter si c'est du contenu Divi
        $is_divi = strpos($post_content, '[et_pb_') !== false;
        
        if ($is_divi) {
            // Extraire le premier shortcode et_pb_text
            preg_match('/\[et_pb_text[^\]]*\](.*?)\[\/et_pb_text\]/s', $post_content, $text_match);
            if ($text_match) {
                $raw_text = $text_match[1];
            } else {
                // Fallback : chercher n'importe quel texte entre shortcodes
                $raw_text = strip_shortcodes($post_content);
            }
        } else {
            // HTML standard : extraire le premier paragraphe
            preg_match('/<p[^>]*>(.*?)<\/p>/s', $post_content, $p_match);
            if ($p_match) {
                $raw_text = $p_match[1];
            } else {
                $raw_text = $post_content;
            }
        }
        
        // Nettoyer le HTML et les shortcodes
        $clean_text = strip_tags($raw_text);
        $clean_text = strip_shortcodes($clean_text);
        $clean_text = html_entity_decode($clean_text);
        $clean_text = trim($clean_text);
        
        // Paramètres de longueur
        $min_length = 150;
        $ideal_length = 180;
        $max_length = 220;
        
        // Si le texte est déjà court, le retourner tel quel avec ...
        if (strlen($clean_text) <= $min_length) {
            return $clean_text . '...';
        }
        
        // Si le texte est dans la plage idéale, chercher le meilleur point de coupure
        if (strlen($clean_text) <= $max_length) {
            // Chercher le dernier point ou virgule
            $last_period = strrpos(substr($clean_text, 0, $max_length), '. ');
            $last_comma = strrpos(substr($clean_text, 0, $max_length), ', ');
            
            $best_cut = max($last_period, $last_comma);
            
            if ($best_cut && $best_cut >= $min_length) {
                return substr($clean_text, 0, $best_cut + 1) . '..';
            }
        }
        
        // Algorithme de coupure intelligente pour textes longs
        $target_pos = $ideal_length;
        
        // Points de coupure par ordre de priorité (chercher vers l'arrière depuis la position idéale)
        $cut_points = [
            '. ' => 2,  // Fin de phrase
            ', ' => 1,  // Virgule
            '; ' => 1,  // Point-virgule
            ': ' => 1,  // Deux-points
        ];
        
        $best_position = false;
        $best_priority = 0;
        
        // Chercher vers l'arrière depuis la position idéale
        for ($i = $target_pos; $i >= $min_length; $i--) {
            foreach ($cut_points as $delimiter => $priority) {
                if (substr($clean_text, $i, strlen($delimiter)) === $delimiter) {
                    if ($priority > $best_priority) {
                        $best_position = $i + strlen($delimiter);
                        $best_priority = $priority;
                    }
                }
            }
        }
        
        // Si aucun bon point trouvé vers l'arrière, chercher vers l'avant
        if (!$best_position) {
            for ($i = $target_pos; $i <= $max_length; $i++) {
                foreach ($cut_points as $delimiter => $priority) {
                    if (substr($clean_text, $i, strlen($delimiter)) === $delimiter) {
                        $best_position = $i + strlen($delimiter);
                        break 2;
                    }
                }
            }
        }
        
        // Fallback : couper au dernier espace avant la limite max
        if (!$best_position) {
            $best_position = strrpos(substr($clean_text, 0, $max_length), ' ');
            if (!$best_position || $best_position < $min_length) {
                $best_position = $max_length;
            }
        }
        
        $excerpt = substr($clean_text, 0, $best_position);
        
        // Nettoyer les espaces en fin et ajouter les points de suspension
        $excerpt = rtrim($excerpt, ' .,;:');
        
        return $excerpt . '...';
    }
}

if (!function_exists('articles_page_display')) {
    function articles_page_display() {
        ob_start();
        
        // Récupération des paramètres de recherche et filtres
        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $selected_categories = isset($_GET['categories']) ? array_map('sanitize_text_field', $_GET['categories']) : array();
        $sort_desc = isset($_GET['sort_desc']) ? false : true;
        
        // Paramètres de pagination
        $theme_page = isset($_GET['theme_page']) ? max(1, intval($_GET['theme_page'])) : 1;
        $per_page = 6;

        // Récupération des catégories d'articles (minimum 1 article)
        $categories = get_categories(array(
            'taxonomy' => 'category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC'
        ));

        // Filtrer les catégories avec au moins 1 article
        $categories = array_filter($categories, function($category) {
            return $category->count >= 1;
        });

        // Arguments pour la requête des articles par thèmes
        $theme_args = array(
            'post_type' => 'post',
            'posts_per_page' => $per_page * $theme_page,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => $sort_desc ? 'DESC' : 'ASC',
            'meta_query' => array(),
            'tax_query' => array(),
            'post__not_in' => get_posts(array(
                'post_type' => 'product',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'post_status' => 'any'
            ))
        );

        // Ajout de la recherche si présente
        if (!empty($search_query)) {
            $theme_args['s'] = $search_query;
            $theme_args['search_columns'] = ['post_title', 'post_content', 'post_excerpt'];
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
        
        // Calculer les compteurs de catégories basés sur les articles visibles
        $category_counts = array();
        
        // Compter dans les articles par thèmes
        if ($theme_articles->have_posts()) {
            while ($theme_articles->have_posts()) {
                $theme_articles->the_post();
                $post_categories = get_the_category();
                foreach ($post_categories as $cat) {
                    if (!isset($category_counts[$cat->slug])) {
                        $category_counts[$cat->slug] = 0;
                    }
                    $category_counts[$cat->slug]++;
                }
            }
            wp_reset_postdata();
        }
        
        wp_reset_postdata();
         
         // Filtrer les catégories pour ne montrer que celles avec des articles visibles
         $categories = array_filter($categories, function($category) use ($category_counts) {
             return isset($category_counts[$category->slug]) && $category_counts[$category->slug] > 0;
         });
         ?>

        <style>
        .articles-container {
            max-width: 1650px;
            margin: 0 auto;
        }
        
        .articles-header {
            text-align: center;
        }
        
        .articles-title {
            font-size: 1.875rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 0.7rem;
        }
        
        .search-form {
            max-width: 28rem;
            margin: 0 auto;
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
            border: 2px solid #93c5fd;
        }
        
        .search-button {
            position: absolute;
            right: 0.5rem;
            bottom: 0.5rem;
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
                width: 16%;
            }
            .content {
                width: 84%;
            }
        }
        
        .dropdown-container {
            position: relative;
        }
        
        .dropdown-button {
            width: 100%;
            background-color: #0066cc;
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
            background-color: #0052a3;
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
            height: auto !important;
            min-height: unset !important;
            padding-bottom: 0 !important;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-list {
            padding: 0.75rem;
            margin: 0;
            list-style: none;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
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
            accent-color: #3399ff;
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
        
        @media (min-width: 1024px) {
            .articles-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 2.8rem;
            }
        }
        
        .article-card {
            max-width: 42rem;
            display: flex;
            flex-direction: column;
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
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }
        
        .article-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 1rem;
            text-decoration: none;
            display: block;
        }
        
        .article-title:hover {
            color: #3399ff;
        }
        
        .article-title.search-match {
            color: #22c55e;
            font-weight: 900;
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
            margin-top: auto;
        }
        
        .read-more-button {
            display: inline-flex;
            align-items: center;
            background-color: #0066cc;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .read-more-button:hover {
            background-color: #0052a3;
            color: white;
        }
        
        .read-more-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px #93c5fd;
        }
        
        .read-more-icon {
            margin-left: 0.5rem;
            margin-top: 0.1rem;
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
            background-color: #0066cc;
            color: white;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            margin: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            font-weight: 700;
        }
        
        .see-more-button:hover {
            background-color: #0052a3;
            color: white;
            text-decoration: none;
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
        
        .article-image.cover1 {
            object-fit: cover;
        }
        
        .article-image.contain {
            object-fit: contain;
            background: #fff;
            padding: 1.5rem;
        }
        
        .article-separator {
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            margin: 0;
        }
        
        @media (max-width: 640px) {
            .search-icon {
                top: 54%;
            }
        }
        .reset-search-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 1.5rem auto 0 auto;
            margin-bottom: 1.5rem;
            padding: 0.6rem 1.4rem;
            background-color: #1f2937;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 0 0 0 rgba(0,0,0,0);
            max-width: 220px;
            width: 100%;
        }
        .reset-search-btn svg {
            margin-right: 0.6em;
            width: 1.5em;
            height: 1.5em;
        }
        .reset-search-btn:hover {
            background-color: #111827;
        }
        .reset-search-btn:focus {
            outline: none;
            box-shadow: 0 0 0 4px #6b7280;
        }
        .article-content {
            position: relative;
        }
        .search-badge {
            position: static;
            display: inline-block;
            margin: 0.7rem auto 0 auto;
            padding: 0.25rem 0.8rem;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #fff;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            letter-spacing: 0.01em;
        }
        .search-badge-bottom {
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-top: 1rem;
            text-align: center;
        }
        .search-badge-green {
            background: #22c55e;
        }
        .search-badge-black {
            background: #000000;
        }
        </style>

        <div class="articles-container">
            <!-- En-tête de la page -->
            <div class="articles-header">
                <h1 class="articles-title">
                    Parcourez nos articles, guides et actualités pour tout savoir sur nos produits et solutions.
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
                    <input type="search" name="search" value="<?php echo esc_attr($search_query); ?>" class="search-input" placeholder="Rechercher un article, une référence..." data-placeholder-mobile="Rechercher un article" data-placeholder-desktop="Rechercher un article, une référence..." />
                    <button type="submit" class="search-button">
                        <svg style="margin-right:0.4em;vertical-align:middle;" width="16" height="16" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                        Rechercher
                    </button>
                </div>
                
                <!-- Champs cachés pour maintenir les filtres de catégories -->
                <?php foreach ($selected_categories as $cat): ?>
                    <input type="hidden" name="categories[]" value="<?php echo esc_attr($cat); ?>" class="hidden-input">
                <?php endforeach; ?>
                <?php if (!$sort_desc): ?>
                    <input type="hidden" name="sort_desc" value="1" class="hidden-input">
                <?php endif; ?>
            </form>

            <!-- Bouton réinitialiser la recherche -->
            <div style="text-align:center;">
                <a href="/articles-ewan/" class="reset-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                    Réinitialiser la page
                </a>
            </div>

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
                                <input type="hidden" name="theme_page" value="<?php echo esc_attr($theme_page); ?>" class="hidden-input">
                                <ul class="dropdown-list">
                                    <!-- Option de tri -->
                                    <li class="dropdown-item">
                                        <div class="checkbox-container">
                                            <input
                                                id="sort-desc"
                                                type="checkbox"
                                                name="sort_desc"
                                                value="1"
                                                <?php echo !$sort_desc ? 'checked' : ''; ?>
                                                onchange="document.getElementById('categoryForm').submit();"
                                                class="checkbox-input"
                                            >
                                            <label for="sort-desc" class="checkbox-label">
                                                Du plus vieux au plus récent
                                            </label>
                                        </div>
                                    </li>
                                    
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
                                                <span class="category-count">(<?php echo isset($category_counts[$category->slug]) ? $category_counts[$category->slug] : 0; ?>)</span>
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
                            <h2 class="section-title">Liste des articles :</h2>
                        </div>

                        <?php if ($theme_articles->have_posts()): ?>
                        <div class="articles-grid">
                            <?php
                            $count = 0;
                            while ($theme_articles->have_posts()):
                                $theme_articles->the_post();
                                $count++;
                                
                                // Sauvegarder l'ID de l'article avant toute autre manipulation
                                $current_post_id = get_the_ID();
                                $current_post_date = get_the_date('j F Y', $current_post_id);
                                $current_post_title = get_the_title($current_post_id);
                                $current_post_permalink = get_permalink($current_post_id);
                                
                                $featured_image_data = get_principal_image_from_content($current_post_id);
                                if ($featured_image_data) {
                                    $featured_image = $featured_image_data['url'];
                                    $image_class = $featured_image_data['class'];
                                } else {
                                    $featured_image = 'https://www.cenov-distribution.fr/wp-content/uploads/2025/07/Defaut.svg_.png';
                                    $image_class = 'contain';
                                }
                            ?>
                            <?php 
// Détection pour badge
$hasTitleMatch = !empty($search_query) && stripos($current_post_title, $search_query) !== false;
$hasContentMatch = !empty($search_query) && stripos(get_the_content(), $search_query) !== false;
?>
                            <div class="article-card">
                                <a href="<?php echo esc_url($current_post_permalink); ?>">
                                    <img class="article-image <?php echo esc_attr($image_class); ?>" src="<?php echo esc_url($featured_image); ?>" alt="<?php echo ($featured_image_data) ? esc_attr($current_post_title) : 'Image par défaut'; ?>" />
                                </a>
                                <?php if ($image_class === 'contain') : ?>
                                    <div class="article-separator"></div>
                                <?php endif; ?>
                                <div class="article-content">
                                    <a href="<?php echo esc_url($current_post_permalink); ?>" class="article-title <?php echo $hasTitleMatch ? 'search-match' : ''; ?>"><?php echo esc_html($current_post_title); ?></a>
                                    <p class="article-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                    </p>
                                    <div class="article-footer">
                                        <a href="<?php echo esc_url($current_post_permalink); ?>" class="read-more-button">
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
                                            <?php echo esc_html($current_post_date); ?>
                                        </div>
                                    </div>
                                    <?php if ($hasTitleMatch): ?>
                                        <div class="search-badge search-badge-green search-badge-bottom">Mot trouvé dans le titre</div>
                                    <?php endif; ?>
                                    <?php if ($hasContentMatch && !$hasTitleMatch): ?>
                                        <div class="search-badge search-badge-black search-badge-bottom">Mot trouvé dans l'article</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <?php if ($theme_articles->found_posts > ($per_page * $theme_page)): ?>
                        <div class="see-more-container">
                            <a href="<?php 
                                $params = $_GET;
                                $params['theme_page'] = $theme_page + 1;
                                // Maintenir le tri si activé
                                if (!$sort_desc && !isset($params['sort_desc'])) {
                                    $params['sort_desc'] = '1';
                                }
                                echo '?' . http_build_query($params);
                            ?>" class="see-more-button">
                                Voir plus
                            </a>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="no-articles">
                            <p>Aucun article trouvé pour les critères sélectionnés.</p>
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

        // Placeholder adaptatif mobile/desktop pour le champ de recherche principal
        function adaptSearchPlaceholder() {
            const input = document.querySelector('.search-input');
            if (!input) return;
            if (window.innerWidth <= 640) {
                input.placeholder = input.getAttribute('data-placeholder-mobile');
            } else {
                input.placeholder = input.getAttribute('data-placeholder-desktop');
            }
        }
        window.addEventListener('resize', adaptSearchPlaceholder);
        window.addEventListener('DOMContentLoaded', adaptSearchPlaceholder);


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
