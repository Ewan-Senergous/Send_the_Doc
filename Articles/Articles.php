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

// Fonction pour extraire le texte entre le premier et deuxième H2
if (!function_exists('get_text_between_h2')) {
    function get_text_between_h2($post_id) {
        $post_content = get_post_field('post_content', $post_id);
        
        // Détecter si c'est du contenu Divi
        $is_divi = strpos($post_content, '[et_pb_') !== false;
        
        if ($is_divi) {
            // Pour Divi, chercher dans les shortcodes et_pb_text ET et_pb_code
            preg_match_all('/\[et_pb_text[^\]]*\](.*?)\[\/et_pb_text\]/s', $post_content, $all_texts);
            preg_match_all('/\[et_pb_code[^\]]*\](.*?)\[\/et_pb_code\]/s', $post_content, $all_codes);
            
            // Combiner le contenu des modules text et code
            $combined_content = array();
            if ($all_texts && isset($all_texts[1])) {
                $combined_content = array_merge($combined_content, $all_texts[1]);
            }
            if ($all_codes && isset($all_codes[1])) {
                $combined_content = array_merge($combined_content, $all_codes[1]);
            }
            
            if (!empty($combined_content)) {
                $combined_text = implode(' ', $combined_content);
                // Chercher les H2 dans le contenu combiné
                if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $combined_text, $h2_matches)) {
                    if (count($h2_matches[0]) >= 2) {
                        // Extraire le texte entre le premier et deuxième H2
                        $first_h2_pos = strpos($combined_text, $h2_matches[0][0]) + strlen($h2_matches[0][0]);
                        $second_h2_pos = strpos($combined_text, $h2_matches[0][1]);
                        
                        if ($first_h2_pos !== false && $second_h2_pos !== false && $second_h2_pos > $first_h2_pos) {
                            $text_between = substr($combined_text, $first_h2_pos, $second_h2_pos - $first_h2_pos);
                        } else {
                            $text_between = '';
                        }
                    } else {
                        // Fallback : texte après le premier H2
                        if (isset($h2_matches[0][0])) {
                            $first_h2_pos = strpos($combined_text, $h2_matches[0][0]) + strlen($h2_matches[0][0]);
                            $text_between = substr($combined_text, $first_h2_pos);
                        } else {
                            $text_between = '';
                        }
                    }
                } else {
                    // Pas de H2 trouvé, prendre le premier contenu disponible
                    $text_between = isset($combined_content[0]) ? $combined_content[0] : '';
                }
            } else {
                $text_between = '';
            }
        } else {
            // HTML standard
            if (preg_match_all('/<h2[^>]*>(.*?)<\/h2>/is', $post_content, $h2_matches)) {
                if (count($h2_matches[0]) >= 2) {
                    // Extraire le texte entre le premier et deuxième H2
                    $first_h2_pos = strpos($post_content, $h2_matches[0][0]) + strlen($h2_matches[0][0]);
                    $second_h2_pos = strpos($post_content, $h2_matches[0][1]);
                    
                    if ($first_h2_pos !== false && $second_h2_pos !== false && $second_h2_pos > $first_h2_pos) {
                        $text_between = substr($post_content, $first_h2_pos, $second_h2_pos - $first_h2_pos);
                    } else {
                        $text_between = '';
                    }
                } else {
                    // Fallback : texte après le premier H2
                    $first_h2_pos = strpos($post_content, $h2_matches[0][0]) + strlen($h2_matches[0][0]);
                    $text_between = substr($post_content, $first_h2_pos);
                }
            } else {
                // Pas de H2, prendre le début du contenu
                $text_between = $post_content;
            }
        }
        
        // Nettoyer le HTML et les shortcodes
        $clean_text = strip_tags($text_between);
        $clean_text = strip_shortcodes($clean_text);
        $clean_text = html_entity_decode($clean_text);
        $clean_text = trim($clean_text);
        
        // Limiter la longueur (environ 120 caractères)
        $max_length = 120;
        if (strlen($clean_text) > $max_length) {
            $clean_text = substr($clean_text, 0, $max_length);
            // Couper au dernier espace pour éviter de couper un mot
            $last_space = strrpos($clean_text, ' ');
            if ($last_space !== false && $last_space > $max_length * 0.8) {
                $clean_text = substr($clean_text, 0, $last_space);
            }
            $clean_text .= '...';
        }
        
        return $clean_text;
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

// Fonction pour tri personnalisé par pertinence de recherche
if (!function_exists('custom_search_orderby')) {
    function custom_search_orderby($orderby, $query) {
        global $wpdb;
        
        if (!is_admin() && $query->is_search()) {
            $search_term = $query->get('s');
            
            if (!empty($search_term)) {
                // Décoder les entités HTML
                $decoded_search = html_entity_decode($search_term, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $escaped_search = esc_sql($decoded_search);
                
                // Ordre de priorité :
                // 1. Titre contient le terme exact (badge vert)
                // 2. Contenu contient le terme exact (badge noir)  
                // 3. Tri par date selon préférence utilisateur
                $custom_orderby = "
                    CASE 
                        WHEN {$wpdb->posts}.post_title LIKE '%{$escaped_search}%' THEN 1
                        WHEN {$wpdb->posts}.post_content LIKE '%{$escaped_search}%' THEN 2
                        ELSE 3
                    END ASC,
                    {$wpdb->posts}.post_date DESC
                ";
                
                return $custom_orderby;
            }
        }
        
        return $orderby;
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

        // Génération des données d'autocomplétion
        $autocomplete_data = array();
        
        // Priorité 1 : Noms des catégories
        foreach ($categories as $category) {
            $autocomplete_data[] = array(
                'text' => $category->name,
                'category' => 'Catégories',
                'type' => 'category'
            );
        }
        
        // Priorité 2 : Titres d'articles
        $all_articles = get_posts(array(
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ));
        
        foreach ($all_articles as $article_id) {
            $title = get_the_title($article_id);
            if (!empty($title)) {
                $autocomplete_data[] = array(
                    'text' => $title,
                    'category' => 'Articles',
                    'url' => get_permalink($article_id),
                    'type' => 'article'
                );
            }
        }

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
            // Décoder les entités HTML dans la requête de recherche
            $decoded_search = html_entity_decode($search_query, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $theme_args['s'] = $decoded_search;
            $theme_args['search_columns'] = ['post_title', 'post_content', 'post_excerpt'];
            
            // Modifier l'ordre pour prioriser les résultats pertinents
            $theme_args['orderby'] = array(
                'relevance' => 'DESC',
                'date' => $sort_desc ? 'DESC' : 'ASC'
            );
            
            // Hook personnalisé pour le tri par pertinence
            add_filter('posts_orderby', 'custom_search_orderby', 10, 2);
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
        
        // Supprimer le filtre après la requête
        if (!empty($search_query)) {
            remove_filter('posts_orderby', 'custom_search_orderby', 10);
        }
        
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
        
        .search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border: 1px solid #6b7280;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 10;
            display: none;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .search-dropdown.show {
            display: block;
        }
        
        .search-dropdown-item {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .search-dropdown-item:hover,
        .search-dropdown-item.highlighted {
            background-color: #f3f4f6;
        }
        
        .search-dropdown-item:last-child {
            border-bottom: none;
        }
        
        .search-dropdown-category {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            margin-bottom: 0.25rem;
        }
        
        .search-dropdown-text {
            color: #1f2937;
            font-size: 0.875rem;
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
            font-weight: bold;
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
            list-style: none;
            background-color: white;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .checkbox-input:checked {
            background-color: white;
            border-color: #3399ff;
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='%233399ff' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
            background-size: 0.75rem;
            background-position: center;
            background-repeat: no-repeat;
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
            margin-bottom: 0.75rem;
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
        
        .article-h2-excerpt {
            color: #6b7280;
            margin-bottom: 0.75rem;
            line-height: 1.5;
            font-size: 0.875rem;
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
            margin-top: 30px;
            padding: 0;
        }
        
        .see-more-button {
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
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .see-more-button svg {
            margin-top: 0.1rem;
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
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 0 0 0 rgba(0,0,0,0);
            max-width: 220px;
            width: 100%;
        }
        .reset-search-btn svg {
            margin-right: 0.6em;
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
                    <input type="search" name="search" value="<?php echo esc_attr($search_query); ?>" class="search-input" placeholder="Rechercher un article, une référence..." data-placeholder-mobile="Rechercher un article" data-placeholder-desktop="Rechercher un article, une référence..." id="main-search" autocomplete="off" />
                    <div id="search-dropdown" class="search-dropdown"></div>
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
                <a href="/articles/" class="reset-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw-icon lucide-refresh-ccw"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
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
                                $h2_text_excerpt = get_text_between_h2($current_post_id);
                                if ($featured_image_data) {
                                    $featured_image = $featured_image_data['url'];
                                    $image_class = $featured_image_data['class'];
                                } else {
                                    $featured_image = 'https://www.cenov-distribution.fr/wp-content/uploads/2025/07/Defaut.svg_.png';
                                    $image_class = 'contain';
                                }
                            ?>
                            <?php 
// Détection pour badge - avec décodage des entités HTML
$decoded_search_for_badge = !empty($search_query) ? html_entity_decode($search_query, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
$hasTitleMatch = !empty($decoded_search_for_badge) && stripos($current_post_title, $decoded_search_for_badge) !== false;
$hasContentMatch = !empty($decoded_search_for_badge) && stripos(get_the_content(), $decoded_search_for_badge) !== false;

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
                                    <?php if (!empty($h2_text_excerpt)): ?>
                                    <p class="article-h2-excerpt">
                                        <?php echo esc_html($h2_text_excerpt); ?>
                                    </p>
                                    <?php endif; ?>
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <path d="M8 12h8"/>
                                    <path d="M12 8v8"/>
                                </svg>
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
        // Données d'autocomplétion
        const autocompleteData = <?php echo json_encode($autocomplete_data); ?>;
        
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

        // Autocomplétion
        const searchInput = document.getElementById('main-search');
        const searchDropdown = document.getElementById('search-dropdown');
        let currentFocus = -1;

        function showAutocomplete(results) {
            searchDropdown.innerHTML = '';
            if (results.length === 0) {
                searchDropdown.classList.remove('show');
                return;
            }

            results.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'search-dropdown-item';
                div.innerHTML = `
                    <div class="search-dropdown-category">${item.category}</div>
                    <div class="search-dropdown-text">${item.text}</div>
                `;
                div.addEventListener('click', function() {
                    if (item.type === 'article' && item.url) {
                        // Redirection directe vers l'article
                        window.location.href = item.url;
                    } else {
                        // Recherche normale pour les catégories
                        searchInput.value = item.text;
                        searchDropdown.classList.remove('show');
                        searchInput.form.submit();
                    }
                });
                searchDropdown.appendChild(div);
            });

            searchDropdown.classList.add('show');
        }

        function normalizeText(text) {
            // Créer un élément temporaire pour décoder les entités HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = text;
            return tempDiv.textContent || tempDiv.innerText || '';
        }

        function filterAutocomplete(query) {
            if (query.length < 2) {
                searchDropdown.classList.remove('show');
                return;
            }

            const normalizedQuery = normalizeText(query.toLowerCase());
            const filtered = autocompleteData.filter(item => {
                const normalizedText = normalizeText(item.text.toLowerCase());
                return normalizedText.includes(normalizedQuery);
            }).slice(0, 12); // Limiter à 12 résultats

            showAutocomplete(filtered);
        }

        searchInput.addEventListener('input', function() {
            currentFocus = -1;
            filterAutocomplete(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            const items = searchDropdown.querySelectorAll('.search-dropdown-item');
            
            if (e.key === 'ArrowDown') {
                currentFocus++;
                if (currentFocus >= items.length) currentFocus = 0;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'ArrowUp') {
                currentFocus--;
                if (currentFocus < 0) currentFocus = items.length - 1;
                addActive(items);
                e.preventDefault();
            } else if (e.key === 'Enter') {
                if (currentFocus > -1 && items[currentFocus]) {
                    items[currentFocus].click();
                    e.preventDefault();
                } else if (searchDropdown.classList.contains('show')) {
                    // Si pas de sélection mais dropdown ouvert, prendre le premier résultat
                    const firstItem = items[0];
                    if (firstItem) {
                        firstItem.click();
                        e.preventDefault();
                    }
                }
            } else if (e.key === 'Escape') {
                searchDropdown.classList.remove('show');
                currentFocus = -1;
            }
        });

        function addActive(items) {
            items.forEach(item => item.classList.remove('highlighted'));
            if (currentFocus >= 0 && items[currentFocus]) {
                items[currentFocus].classList.add('highlighted');
            }
        }

        // Fermer l'autocomplétion si on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !searchDropdown.contains(event.target)) {
                searchDropdown.classList.remove('show');
            }
        });

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
