# Page de Gestion des Articles - Snippet WordPress

## Description

Ce snippet crée une page complète de gestion des articles pour WordPress avec :

- Barre de recherche avec style moderne
- Filtres par catégories avec dropdown checkbox
- Affichage des articles par thèmes
- Section des derniers articles publiés
- Design responsive avec CSS interne (pas de dépendance Tailwind)

## Installation

### 1. Ajouter le snippet dans WordPress

1. Allez dans votre administration WordPress
2. Installez le plugin "Insert PHP Code Snippet" ou "Code Snippets"
3. Créez un nouveau snippet avec le nom "ArticlesPage"
4. Copiez le contenu COMPLET du fichier `Articles.php` dans le snippet
5. Activez le snippet

### 2. Utilisation du shortcode

Ajoutez ce shortcode dans n'importe quelle page ou article :

```
[xyz-ips snippet="ArticlesPage"]
```

## Structure du code

### Fonction WordPress appropriée

Le code utilise la structure recommandée pour les snippets WordPress :

```php
<?php
if (!function_exists('articles_page_display')) {
    function articles_page_display() {
        ob_start();

        // Code PHP ici
        ?>
        <!-- HTML et CSS ici -->
        <?php

        return ob_get_clean();
    }
}

echo articles_page_display();
?>
```

### CSS interne

- **Aucune dépendance externe** : Tout le CSS est inclus dans le fichier
- **Design moderne** : Styles équivalents à Flowbite/Tailwind mais en CSS pur
- **Responsive** : Media queries pour mobile, tablette et desktop
- **Animations** : Effets hover et transitions fluides

## Fonctionnalités

### Recherche

- Champ de recherche avec icône
- Recherche dans le titre et le contenu des articles
- Conservation des filtres lors de la recherche

### Filtres par catégories

- Dropdown avec checkboxes
- Affichage du nombre d'articles par catégorie
- Filtrage multiple possible
- Soumission automatique du formulaire

### Affichage des articles

- **Thèmes articles** : Articles filtrés selon les critères sélectionnés
- **Derniers articles publiés** : Articles les plus récents
- 2 articles affichés par défaut avec bouton "Voir plus"
- Cards avec image, titre, extrait et date

### Design

- CSS interne moderne et responsive
- Palette de couleurs cohérente (bleus #1d4ed8, gris #6b7280)
- Effets hover et animations
- Compatible tous navigateurs

## Personnalisation

### Modifier le nombre d'articles affichés

Dans le fichier PHP, modifiez les valeurs :

```php
'posts_per_page' => 6, // Nombre total d'articles récupérés
```

Et dans la boucle :

```php
while ($theme_articles->have_posts() && $count < 2): // Nombre affiché initialement
```

### Modifier les couleurs

Dans la section `<style>`, changez les couleurs :

```css
.search-button,
.dropdown-button,
.read-more-button,
.see-more-button {
  background-color: #1d4ed8; /* Couleur principale */
}

.search-button:hover,
.dropdown-button:hover,
.read-more-button:hover,
.see-more-button:hover {
  background-color: #1e40af; /* Couleur hover */
}
```

### Ajouter des catégories spécifiques

Pour filtrer uniquement certaines catégories :

```php
$categories = get_categories(array(
    'taxonomy' => 'category',
    'hide_empty' => true,
    'include' => array(1, 2, 3), // IDs des catégories à inclure
    'orderby' => 'name',
    'order' => 'ASC'
));
```

### Modifier les images par défaut

Changez l'URL de l'image par défaut :

```php
$featured_image = 'https://votre-site.com/wp-content/uploads/default-image.jpg';
```

## Intégration avec le site Cenov Distribution

### Articles de référence

Le code est configuré pour fonctionner avec les articles existants :

- [Pompes à becs : Guide complet](https://www.cenov-distribution.fr/pompes-a-becs-guide-complet/)
- [Variateur de vitesse : Guide complet](https://www.cenov-distribution.fr/variateur-de-vitesse-guide-complet/)

### Catégories supportées

- Moteurs
- Pompes
- Pièces Détachées
- Variateurs de vitesse
- Et toutes les autres catégories WordPress

## Avantages de cette version

### Autonome

- **Aucune dépendance** : Fonctionne sans Tailwind CSS ou autres frameworks
- **CSS interne** : Tout est inclus dans le fichier
- **Portable** : Peut être utilisé sur n'importe quel site WordPress

### Performance

- **CSS optimisé** : Seulement les styles nécessaires
- **Pas de chargement externe** : Pas de CDN ou fichiers externes
- **Léger** : Code minimal et efficace

### Compatibilité

- **Tous thèmes WordPress** : Fonctionne indépendamment du thème
- **Tous navigateurs** : CSS standard compatible partout
- **Responsive** : S'adapte à tous les écrans

## JavaScript inclus

### Fonctionnalités

- Toggle du dropdown de filtres
- Fermeture automatique du dropdown
- Fonctions "Voir plus" (prêtes pour AJAX)

### Pas de dépendances

- JavaScript vanilla (pas de jQuery requis)
- Compatible tous navigateurs modernes

## Améliorations possibles

### Chargement AJAX

Implémentez les fonctions `loadMoreThemeArticles()` et `loadMoreLatestArticles()` pour charger plus d'articles sans rechargement de page.

### Pagination

Ajoutez une pagination complète au lieu du simple bouton "Voir plus".

### Filtres avancés

Ajoutez des filtres par date, auteur, ou tags.

### Cache

Implémentez un système de cache pour améliorer les performances.

## Support

Pour toute question ou problème, vérifiez :

1. Que le plugin de snippets est actif
2. Que les articles ont des catégories assignées
3. Que les images à la une sont définies
4. Que le code complet a été copié (fonction + CSS + JavaScript)

## Compatibilité

- WordPress 5.0+
- PHP 7.4+
- Tous navigateurs modernes (Chrome, Firefox, Safari, Edge)
- **Aucune dépendance externe requise**
