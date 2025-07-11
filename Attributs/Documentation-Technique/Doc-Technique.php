// Ajouter un onglet Documentation technique
if (!function_exists('ajouter_onglet_documentation_technique')) {
    function ajouter_onglet_documentation_technique($tabs) {
        global $product;
        
        // Vérifier si le produit a l'attribut documentation-technique
        $documentation_url = $product->get_attribute('documentation-technique');
        
        if (!empty($documentation_url)) {
            $tabs['documentation_technique'] = array(
                'title'    => 'Documentation technique',
                'priority' => 25,
                'callback' => 'contenu_onglet_documentation_technique'
            );
        }
        
        return $tabs;
    }
    add_filter('woocommerce_product_tabs', 'ajouter_onglet_documentation_technique');
}

// Contenu de l'onglet Documentation technique
if (!function_exists('contenu_onglet_documentation_technique')) {
    function contenu_onglet_documentation_technique() {
        global $product;
        
        $documentation_url = $product->get_attribute('documentation-technique');
        
        if (!empty($documentation_url)) {
            echo '<p><a href="' . esc_url($documentation_url) . '" target="_blank" class="btn-documentation">📄 Télécharger la fiche technique</a></p>';
        }
    }
}