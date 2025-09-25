# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress/WooCommerce-based B2B e-commerce site for Cenov Distribution (https://www.cenov-distribution.fr/), specializing in pumps and motors for industrial clients. The project contains various PHP modules and utilities for document generation, product management, and customer interactions.

## Technology Stack

- **Backend**: PHP 7.4+ with WordPress/WooCommerce
- **Frontend**: Divi theme with internal CSS (no external frameworks)
- **JavaScript**: Vanilla JavaScript (no jQuery dependencies)
- **Database**: WordPress/WooCommerce standard tables with custom taxonomies

## Project Structure

### Core Modules

- **Articles/**: WordPress article management system with search and category filtering

  - `Articles.php` - Main article display functionality
  - `ArticleTest.php` - Testing version with latest articles detection
  - `README.md` - Comprehensive documentation for the articles system

- **Attributs/**: Product attribute management and technical documentation

  - `AttributsTAB.php` - Attribute tab display
  - `Documentation-Technique/` - Technical document download system
    - `Doc-Download.php` - Product documentation download interface
    - `Doc-Technique.php` - Technical documentation utilities

- **Email/**: Contact form and email functionality

  - `FX.php` - Main contact form processing with file uploads and security validation
  - `sendDocPHP.php` - Document sending functionality

- **DiagnosticPompe/**: Pump diagnostic tools

  - `DiagPompe.php` - Interactive pump diagnostic form

- **EconomieMoteur/**: Motor economy calculation tools

  - `economieMoteur.php` - Motor efficiency calculations

- **JauneCenov/**: Order management utilities

  - `checkCommande.php` - Order validation system
  - `Sauvegarde.php` - Data backup functionality

- **Validation-Commande/**: Order processing

  - `RecapDemandeDePrix.php` - Price quote summary
  - `ValidationDemandeDePrix.php` - Price quote validation

- **Word-Press/**: Local WordPress installation for development
  - Includes full WordPress 6.7+ setup via Composer

## Development Workflow

### No Build System

This project uses direct PHP files without a formal build system. Files are deployed directly to the WordPress environment.

### WordPress Integration

All modules are designed as WordPress snippet functions that can be integrated via:

1. Code Snippets plugin
2. Theme functions.php
3. Custom plugins

### Testing

Test files (prefixed with `Test` or `test`) are present in most directories for manual testing of functionality.

## Code Architecture

### Function Structure

All PHP modules follow the WordPress snippet pattern:

```php
<?php
if (!function_exists('module_name_display')) {
    function module_name_display() {
        ob_start();
        // Module logic here
        return ob_get_clean();
    }
}
echo module_name_display();
?>
```

### CSS and JavaScript Integration

- Internal CSS embedded directly in PHP files (no external stylesheets)
- Vanilla JavaScript with no external dependencies
- Responsive design using CSS media queries
- Modern UI styling equivalent to Flowbite/Tailwind but in pure CSS

### WordPress/WooCommerce Integration

- Uses WooCommerce product taxonomies (pa_famille, pa_sous-famille, pa_documentation-technique)
- Leverages WordPress post system for articles
- Implements WordPress security functions (sanitize_text_field, wp_nonce_field)
- Uses WordPress caching system (wp_cache_get/wp_cache_set)

### Security Measures

- Input sanitization using WordPress functions
- File upload validation
- reCAPTCHA integration for forms
- CSRF protection with nonces

## Key Development Patterns

### Database Queries

Uses optimized SQL queries with caching for performance, particularly in `Doc-Download.php` for product data retrieval.

### Image Processing

Custom image extraction from Divi content blocks, prioritizing images with specific class names (cover1, contain).

### Form Processing

Comprehensive form validation with file upload handling, email sending, and security checks.

### Pagination

Custom pagination system for product listings and search results.

## Integration Notes

### Divi Theme Compatibility

- Detects Divi shortcodes in content (`[et_pb_`)
- Extracts images from Divi modules
- Maintains responsive design within Divi layouts

### WooCommerce Product Data

- Accesses custom product attributes through taxonomies
- Handles product documentation links
- Implements product search and filtering

### WordPress Hooks

The project doesn't use standard WordPress hooks extensively but integrates well with the WordPress ecosystem through proper function naming and security practices.

## File Naming Conventions

- Main functionality files: Descriptive names (e.g., `Articles.php`, `DiagPompe.php`)
- Test files: Prefixed with `Test` or `test` (e.g., `ArticleTest.php`, `testATT.php`)
- Configuration: Uses standard WordPress naming where applicable

## Customization Guidelines

### Adding New Modules

1. Follow the established function structure pattern
2. Include internal CSS for styling
3. Use vanilla JavaScript for interactivity
4. Implement proper WordPress security measures
5. Add caching where appropriate for performance

### Styling

- Maintain consistent color scheme ( #0066cc and #e31206 for primary, #6b7280 for secondary)
- Use responsive breakpoints (mobile-first approach)
- Include hover and focus effects

### Database Access

- Prefer WordPress/WooCommerce built-in functions where possible
- Use direct SQL only when necessary for performance
- Implement proper caching for expensive queries

## File Modification Guidelines

### Troubleshooting Edit Issues

If file modifications fail with errors like "File has been unexpectedly modified" or similar:

1. **Use Absolute Paths**: Always use complete absolute paths when modifying files, especially on Windows systems
2. **Use MultiEdit Tool**: For multiple changes in the same file, prefer the MultiEdit tool over multiple Edit calls
3. **Re-read Before Edit**: If an edit fails, always re-read the file before attempting the edit again
4. **Use Backslashes on Windows**: File paths should use backslashes (`\`) on Windows systems

**Example correct path format**: `C:\Users\EwanSenergous\OneDrive - jll.spear\Bureau\Projet\sendTheDoc\Documentation-Technique\Doc-Technique.php`
