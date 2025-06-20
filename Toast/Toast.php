<?php
function cenov_toast_system() {
    // Ne pas ajouter sur les pages d'admin, checkout ou compte
    if (is_admin() || is_checkout() || is_account_page()) {
        return;
    }
    ?>
    
    <!-- Conteneur Toast -->
    <div id="cenov-toast-container"></div>

    <style>
    /* Toast Container - Position bottom-right */
    #cenov-toast-container {
        position: fixed;
        bottom: 70px;
        right: 0px;
        z-index: 999999;
        pointer-events: none;
        max-width: 100vw;
        padding: 0 16px;
    }

    /* Toast Success - Style Sonner Rich Colors */
    .cenov-toast {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-left: 4px solid #10b981;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 8px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        pointer-events: auto;
        min-width: 300px;
        max-width: 500px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.21, 1.02, 0.73, 1);
        cursor: pointer;
    }

    .cenov-toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .cenov-toast.hide {
        transform: translateY(20px);
        opacity: 0;
    }

    .cenov-toast:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .cenov-toast:hover .cenov-toast-content {
        color: #1e40af;
    }

    /* Icône Success */
    .cenov-toast-icon {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        background: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cenov-toast-icon svg {
        width: 12px;
        height: 12px;
        stroke: white;
        stroke-width: 2;
        fill: none;
    }

    /* Texte du toast */
    .cenov-toast-content {
        flex: 1;
        color: #2563eb;
        font-weight: 500;
        text-decoration: underline;
        transition: all 0.2s;
    }

    /* Bouton fermer */
    .cenov-toast-close {
        flex-shrink: 0;
        background: none;
        border: none;
        cursor: pointer;
        color: #6b7280;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .cenov-toast-close:hover {
        background: #f3f4f6;
        color: #374151;
    }

    .cenov-toast-close svg {
        width: 16px;
        height: 16px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    /* Responsive */
    @media (max-width: 640px) {
        #cenov-toast-container {
            bottom: 60px;
            right: 0px;
            padding: 0 12px;
        }
        
        .cenov-toast {
            min-width: 280px;
            padding: 14px;
        }
    }
    </style>

    <script type="text/javascript">
    // Système Toast Sonner-like pour WordPress
    window.CenovToast = {
        container: null,
        
        init() {
            this.container = document.getElementById('cenov-toast-container');
        },
        
        success(message, duration = 5000) {
            if (!this.container) this.init();
            
            const toast = document.createElement('div');
            toast.className = 'cenov-toast';
            
            toast.innerHTML = `
                <div class="cenov-toast-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </div>
                <div class="cenov-toast-content">${message}</div>
                <button class="cenov-toast-close" onclick="CenovToast.remove(this.parentElement)">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                </button>
            `;
            
            // Ajouter la redirection vers le panier au clic
            toast.addEventListener('click', (e) => {
                // Ne pas rediriger si on clique sur le bouton fermer
                if (e.target.closest('.cenov-toast-close')) {
                    return;
                }
                window.location.href = 'https://www.cenov-distribution.fr/panier/';
            });
            
            this.container.appendChild(toast);
            
            // Animation d'entrée
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });
            
            // Auto-remove après duration
            setTimeout(() => {
                this.remove(toast);
            }, duration);
            
            return toast;
        },
        
        remove(toast) {
            if (!toast || !toast.parentElement) return;
            
            toast.classList.add('hide');
            
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            }, 300);
        }
    };

    // Initialiser au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        CenovToast.init();
    });
    </script>
    
    <?php
}
add_action('wp_footer', 'cenov_toast_system');

// Fonction pour déclencher le toast via AJAX (optionnel)
function cenov_show_toast() {
    $message = sanitize_text_field($_POST['message'] ?? 'Produit ajouté ! Voir le panier');
    
    wp_send_json_success([
        'message' => $message,
        'script' => "CenovToast.success('{$message}');"
    ]);
}
add_action('wp_ajax_cenov_show_toast', 'cenov_show_toast');
add_action('wp_ajax_nopriv_cenov_show_toast', 'cenov_show_toast');
?>
