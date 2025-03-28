<?php
// Préparation des données pour le JavaScript
$simulateurData = [
    'puissancesMoteur' => [0.12, 0.18, 0.20, 0.25, 0.37, 0.4, 0.55, 0.75, 1.1, 1.5, 2.2, 3, 4, 5.5, 7.5, 11, 15, 18.5, 22,
        30, 37, 45, 55, 75, 90, 110, 132, 160, 200, 250, 315, 355, 400, 450, 500, 1000],
    'rendements' => [
        "IE1" => [0.12 => 0.50, 0.18 => 0.55, 0.20 => 0.58, 0.25 => 0.60, 0.37 => 0.63, 0.4 => 0.64, 0.55 => 0.66, 0.75 => 0.68,
            1.1 => 0.73, 1.5 => 0.75, 2.2 => 0.78, 3 => 0.80, 4 => 0.83, 5.5 => 0.85, 7.5 => 0.86, 11 => 0.87, 15 => 0.88,
            18.5 => 0.89, 22 => 0.89, 30 => 0.90, 37 => 0.90, 45 => 0.91, 55 => 0.91, 75 => 0.92, 90 => 0.93, 110 => 0.93,
            132 => 0.93, 160 => 0.94, 200 => 0.94, 250 => 0.94, 315 => 0.95, 355 => 0.95, 400 => 0.95, 450 => 0.95, 500 => 0.95, 1000 => 0.95
        ],
        "IE2" => [0.12 => 0.55, 0.18 => 0.60, 0.20 => 0.63, 0.25 => 0.65, 0.37 => 0.69, 0.4 => 0.70, 0.55 => 0.73, 0.75 => 0.75,
            1.1 => 0.79, 1.5 => 0.81, 2.2 => 0.83, 3 => 0.85, 4 => 0.86, 5.5 => 0.87, 7.5 => 0.88, 11 => 0.89, 15 => 0.90,
            18.5 => 0.91, 22 => 0.91, 30 => 0.92, 37 => 0.92, 45 => 0.93, 55 => 0.93, 75 => 0.94, 90 => 0.94, 110 => 0.94,
            132 => 0.95, 160 => 0.95, 200 => 0.95, 250 => 0.95, 315 => 0.96, 355 => 0.96, 400 => 0.96, 450 => 0.96, 500 => 0.96, 1000 => 0.96
        ],
        "IE3" => [0.12 => 0.60, 0.18 => 0.65, 0.20 => 0.68, 0.25 => 0.70, 0.37 => 0.75, 0.4 => 0.76, 0.55 => 0.78, 0.75 => 0.80,
            1.1 => 0.83, 1.5 => 0.85, 2.2 => 0.86, 3 => 0.88, 4 => 0.89, 5.5 => 0.90, 7.5 => 0.91, 11 => 0.91, 15 => 0.92,
            18.5 => 0.92, 22 => 0.93, 30 => 0.93, 37 => 0.94, 45 => 0.94, 55 => 0.94, 75 => 0.95, 90 => 0.95, 110 => 0.95,
            132 => 0.96, 160 => 0.96, 200 => 0.96, 250 => 0.96, 315 => 0.96, 355 => 0.97, 400 => 0.97, 450 => 0.97, 500 => 0.97, 1000 => 0.97
        ],
        "IE4" => [0.12 => 0.65, 0.18 => 0.70, 0.20 => 0.72, 0.25 => 0.74, 0.37 => 0.78, 0.4 => 0.79, 0.55 => 0.81, 0.75 => 0.83,
            1.1 => 0.86, 1.5 => 0.87, 2.2 => 0.89, 3 => 0.90, 4 => 0.91, 5.5 => 0.92, 7.5 => 0.92, 11 => 0.93, 15 => 0.93,
            18.5 => 0.94, 22 => 0.94, 30 => 0.94, 37 => 0.95, 45 => 0.95, 55 => 0.95, 75 => 0.96, 90 => 0.96, 110 => 0.96,
            132 => 0.96, 160 => 0.97, 200 => 0.97, 250 => 0.97, 315 => 0.97, 355 => 0.97, 400 => 0.97, 450 => 0.97, 500 => 0.97, 1000 => 0.98
        ],
        "IE5" => [0.12 => 0.69, 0.18 => 0.74, 0.20 => 0.76, 0.25 => 0.78, 0.37 => 0.82, 0.4 => 0.83, 0.55 => 0.85, 0.75 => 0.87,
            1.1 => 0.89, 1.5 => 0.90, 2.2 => 0.91, 3 => 0.92, 4 => 0.93, 5.5 => 0.94, 7.5 => 0.94, 11 => 0.95, 15 => 0.95,
            18.5 => 0.96, 22 => 0.96, 30 => 0.96, 37 => 0.96, 45 => 0.97, 55 => 0.97, 75 => 0.97, 90 => 0.97, 110 => 0.97,
            132 => 0.98, 160 => 0.98, 200 => 0.98, 250 => 0.98, 315 => 0.98, 355 => 0.98, 400 => 0.98, 450 => 0.98, 500 => 0.98, 1000 => 0.99
        ]
    ],
    'adjustPoleFactors' => [2 => 1.01, 4 => 1.0, 6 => 0.99, 8 => 0.98],
    'coutMoteurs' => [
        "IE2" => [0.12 => 100, 0.18 => 120, 0.20 => 130, 0.25 => 150, 0.37 => 180, 0.4 => 190, 0.55 => 220, 0.75 => 250,
            1.1 => 300, 1.5 => 350, 2.2 => 450, 3 => 550, 4 => 650, 5.5 => 700, 7.5 => 900, 11 => 1200, 15 => 1500, 18.5 => 1800,
            22 => 2000, 30 => 2500, 37 => 3000, 45 => 3500, 55 => 4500, 75 => 6000, 90 => 7000, 110 => 8500, 132 => 10000,
            160 => 12000, 200 => 15000, 250 => 18000, 315 => 22000, 355 => 25000, 400 => 28000, 450 => 32000, 500 => 35000, 1000 => 70000
        ],
        "IE3" => [0.12 => 120, 0.18 => 140, 0.20 => 150, 0.25 => 180, 0.37 => 220, 0.4 => 230, 0.55 => 260, 0.75 => 300,
            1.1 => 350, 1.5 => 420, 2.2 => 550, 3 => 650, 4 => 800, 5.5 => 850, 7.5 => 1100, 11 => 1500, 15 => 1800, 18.5 => 2200,
            22 => 2500, 30 => 3200, 37 => 3800, 45 => 4200, 55 => 5500, 75 => 7500, 90 => 8500, 110 => 10000, 132 => 12000,
            160 => 15000, 200 => 18000, 250 => 22000, 315 => 27000, 355 => 30000, 400 => 34000, 450 => 38000, 500 => 42000, 1000 => 85000
        ],
        "IE4" => [0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 230, 0.37 => 280, 0.4 => 300, 0.55 => 350, 0.75 => 420,
            1.1 => 500, 1.5 => 600, 2.2 => 750, 3 => 900, 4 => 1100, 5.5 => 1200, 7.5 => 1500, 11 => 2000, 15 => 2500, 18.5 => 3000,
            22 => 3500, 30 => 4500, 37 => 5200, 45 => 6000, 55 => 7500, 75 => 10000, 90 => 12000, 110 => 14000, 132 => 16000,
            160 => 20000, 200 => 24000, 250 => 30000, 315 => 36000, 355 => 40000, 400 => 45000, 450 => 50000, 500 => 55000, 1000 => 110000
        ],
        "IE5" => [0.12 => 180, 0.18 => 220, 0.20 => 240, 0.25 => 280, 0.37 => 340, 0.4 => 360, 0.55 => 420, 0.75 => 500,
            1.1 => 600, 1.5 => 720, 2.2 => 900, 3 => 1100, 4 => 1300, 5.5 => 1500, 7.5 => 1800, 11 => 2400, 15 => 3000, 18.5 => 3600,
            22 => 4200, 30 => 5400, 37 => 6200, 45 => 7200, 55 => 9000, 75 => 12000, 90 => 14000, 110 => 17000, 132 => 20000,
            160 => 24000, 200 => 30000, 250 => 36000, 315 => 44000, 355 => 48000, 400 => 54000, 450 => 60000, 500 => 66000, 1000 => 130000
        ]
    ],
    'coutVSD' => [0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 220, 0.37 => 250, 0.4 => 260, 0.55 => 300, 0.75 => 350,
        1.1 => 500, 1.5 => 550, 2.2 => 650, 3 => 750, 4 => 850, 5.5 => 900, 7.5 => 1200, 11 => 1500, 15 => 1800, 18.5 => 2200,
        22 => 2500, 30 => 3000, 37 => 3500, 45 => 4000, 55 => 5000, 75 => 8000, 90 => 9500, 110 => 11000, 132 => 13000,
        160 => 16000, 200 => 20000, 250 => 25000, 315 => 30000, 355 => 34000, 400 => 38000, 450 => 42000, 500 => 45000, 1000 => 90000
    ]
];

// Inclusion de Chart.js
wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js', array('jquery'), '3.7.1', true);

// Définir un identifiant unique pour le simulateur
$simulateurId = 'simulateur_' . uniqid();

// Fonction pour créer une icône SVG
function getSvgIcon($type) {
    $icons = [
        'moteur' => '<svg fill="#000000" height="24px" width="24px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 491.52 491.52"><path d="M471.04,163.84h-32.768c-8.192,0-16.384,6.144-20.48,14.336l-18.432,81.92l-14.336-40.96 c0-8.192-8.192-14.336-16.384-14.336h-40.96v-20.48c0-12.288-8.192-20.48-20.48-20.48H81.92c-10.24,0-20.48,8.192-20.48,20.48 v163.84c0,2.048,0,6.144,2.048,8.192l28.672,61.44c4.096,8.192,10.24,12.288,18.432,12.288H358.4 c8.192,0,14.336-4.096,18.432-10.24l18.432-30.72h6.144l18.432,49.152c4.096,8.192,10.24,12.288,18.432,12.288h32.768 c12.288,0,20.48-8.192,20.48-20.48V184.32C491.52,172.032,483.328,163.84,471.04,163.84z M458.752,421.888l-24.576-61.44 c-4.096-6.144-10.24-12.288-20.48-12.288h-32.768c-8.192,0-14.336,4.096-18.432,10.24l-16.384,30.72H124.928L102.4,344.064V204.8 h184.32v20.48c0,12.288,8.192,20.48,20.48,20.48h47.104l8.192,26.624c2.048,8.192,10.24,14.336,18.432,14.336h32.768 c8.192,0,16.384-6.144,20.48-14.336l24.576-81.92V421.888z"></path><path d="M81.92,266.24H20.48C8.192,266.24,0,274.432,0,286.72c0,12.288,10.24,20.48,20.48,20.48h61.44 c12.288,0,20.48-8.192,20.48-20.48C102.4,274.432,94.208,266.24,81.92,266.24z"></path><path d="M20.48,225.28C8.192,225.28,0,233.472,0,245.76v81.92c0,12.288,8.192,20.48,20.48,20.48c12.288,0,20.48-8.192,20.48-20.48 v-81.92C40.96,233.472,32.768,225.28,20.48,225.28z"></path><path d="M245.76,102.4h-81.92c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h81.92 c12.288,0,20.48-8.192,20.48-20.48v-61.44C266.24,110.592,258.048,102.4,245.76,102.4z M225.28,163.84h-40.96v-20.48h40.96V163.84 z"></path><path d="M286.72,40.96H122.88c-10.24,0-20.48,8.192-20.48,20.48v61.44c0,12.288,10.24,20.48,20.48,20.48h163.84 c12.288,0,20.48-8.192,20.48-20.48V61.44C307.2,49.152,299.008,40.96,286.72,40.96z M266.24,102.4H143.36V81.92h122.88V102.4z"></path></svg>',
        'poles' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="M12 2v2"/><path d="M12 22v-2"/><path d="m17 20.66-1-1.73"/><path d="M11 10.27 7 3.34"/><path d="m20.66 17-1.73-1"/><path d="m3.34 7 1.73 1"/><path d="M14 12h8"/><path d="M2 12h2"/><path d="m20.66 7-1.73 1"/><path d="m3.34 17 1.73-1"/><path d="m17 3.34-1 1.73"/><path d="m11 13.73-4 6.93"/></svg>',
        'classe' => '<svg width="24px" height="24px" viewBox="0 0 16 16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="si-glyph si-glyph-chart-column" fill="#000000"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g transform="translate(0.000000, 1.000000)" fill="#434343"><path d="M16,13.031 L0.984,13.031 L0.984,0.016 L0.027,0.016 L0,13.95 L0.027,13.95 L0.027,13.979 L16,13.95 L16,13.031 Z" class="si-glyph-fill"></path><path d="M4.958,7.021 L2.016,7.021 L2.016,11.985 L4.958,11.985 L4.958,7.021 L4.958,7.021 Z" class="si-glyph-fill"></path><path d="M9.969,5.047 L7.016,5.047 L7.016,11.969 L9.969,11.969 L9.969,5.047 L9.969,5.047 Z" class="si-glyph-fill"></path><path d="M14.953,3.031 L12,3.031 L12,11.978 L14.953,11.978 L14.953,3.031 L14.953,3.031 Z" class="si-glyph-fill"></path></g></g></svg>',
        'variateur' => '<svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12M22 12C22 6.47715 17.5228 2 12 2M22 12H19.5M2 12C2 6.47715 6.47715 2 12 2M2 12H4.5M12 2V4.5M19.0784 5L13.4999 10.5M19.0784 19.0784L18.8745 18.8745C18.1827 18.1827 17.8368 17.8368 17.4331 17.5894C17.0753 17.3701 16.6851 17.2085 16.2769 17.1105C15.8166 17 15.3274 17 14.349 17L9.65096 17C8.6726 17 8.18342 17 7.72307 17.1106C7.31493 17.2086 6.92475 17.3702 6.56686 17.5895C6.1632 17.8369 5.8173 18.1828 5.12549 18.8746L4.92163 19.0784M4.92163 5L6.65808 6.73645M14 12C14 13.1046 13.1046 14 12 14C10.8954 14 10 13.1046 10 12C10 10.8954 10.8954 10 12 10C13.1046 10 14 10.8954 14 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>',
        'prix' => '<svg fill="#000000" width="24px" height="24px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M18.605 2.022v0zM18.605 2.022l-2.256 11.856 8.174 0.027-11.127 16.072 2.257-13.043-8.174-0.029zM18.606 0.023c-0.054 0-0.108 0.002-0.161 0.006-0.353 0.028-0.587 0.147-0.864 0.333-0.154 0.102-0.295 0.228-0.419 0.373-0.037 0.043-0.071 0.088-0.103 0.134l-11.207 14.832c-0.442 0.607-0.508 1.407-0.168 2.076s1.026 1.093 1.779 1.099l5.773 0.042-1.815 10.694c-0.172 0.919 0.318 1.835 1.18 2.204 0.257 0.11 0.527 0.163 0.793 0.163 0.629 0 1.145-0.294 1.533-0.825l11.22-16.072c0.442-0.607 0.507-1.408 0.168-2.076-0.34-0.669-1.026-1.093-1.779-1.098l-5.773-0.01 1.796-9.402c0.038-0.151 0.057-0.308 0.057-0.47 0-1.082-0.861-1.964-1.939-1.999-0.024-0.001-0.047-0.001-0.071-0.001v0z"></path></svg>',
        'jours' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>',
        'heures' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
    ];
    return $icons[$type] ?? '';
}

// Fonction pour générer un champ de formulaire
function createInputField($id, $type, $label, $icon, $options = []) {
    global $simulateurId;
    $fieldId = "{$id}_{$simulateurId}";
    $html = '<div class="simulateur-input-group' . (isset($options['switch']) ? ' switch-group' : '') . '">';
    
    // Label
    $html .= '<label for="' . $fieldId . '" class="text-bold-black">' . getSvgIcon($icon) . $label . '</label>';
    
    // Input field
    if ($type === 'select') {
        $html .= '<select id="' . $fieldId . '" class="simulateur-select">';
        foreach ($options['options'] as $value => $text) {
            $selected = isset($options['default']) && $options['default'] == $value ? ' selected' : '';
            $html .= '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
        }
        $html .= '</select>';
    } elseif ($type === 'number') {
        $html .= '<input id="' . $fieldId . '" type="number" min="' . ($options['min'] ?? 0) . '" max="' . ($options['max'] ?? 100) . '"
                 step="' . ($options['step'] ?? 1) . '" value="' . ($options['default'] ?? 0) . '" class="simulateur-input">';
    } elseif ($type === 'switch') {
        $html .= '<div class="switch-container">
                    <input type="checkbox" id="' . $fieldId . '" class="switch-input"' . (isset($options['checked']) && $options['checked'] ? ' checked' : '') . '>
                    <label for="' . $fieldId . '" class="switch-label" aria-label="' . $label . '"></label>
                </div>';
    } elseif ($type === 'puissance-grid') {
        $html .= '<div class="simulateur-input-header">
                    <label for="' . $fieldId . '" class="text-bold-black">' . getSvgIcon($icon) . $label . '</label>
                    <span class="simulateur-value text-bold-black" id="' . $fieldId . 'Value">' . $options['default'] . ' kW</span>
                </div>
                <div class="simulateur-category-selector">
                    <select id="' . $fieldId . 'Category" class="simulateur-select">';
        
        foreach ($options['categories'] as $value => $text) {
            $selected = isset($options['defaultCategory']) && $options['defaultCategory'] == $value ? ' selected' : '';
            $html .= '<option value="' . $value . '"' . $selected . '>' . $text . '</option>';
        }
        
        $html .= '</select>
                 <div class="simulateur-puissance-grid" id="' . $fieldId . 'Grid"></div>
                 </div>';
    }
    
    $html .= '</div>';
    return $html;
}

// Définition des catégories de puissance
$puissanceCategories = [
    'micro' => 'Micro-moteurs (0.1 kW - 0.75 kW)',
    'petit' => 'Petits moteurs (1.1 kW - 11 kW)',
    'moyen' => 'Moteurs moyens (15 kW - 75 kW)',
    'grand' => 'Grands moteurs (90 kW - 1000 kW)'
];

// Options pour les pôles
$poleOptions = [
    '2' => '2 pôles (3000 tr/min)',
    '4' => '4 pôles (1500 tr/min)',
    '6' => '6 pôles (1000 tr/min)',
    '8' => '8 pôles (750 tr/min)'
];

// Options pour les classes d'efficience
$classeOptionsActuelle = [
    'IE1' => 'IE1 (Standard)',
    'IE2' => 'IE2 (Haut rendement)',
    'IE3' => 'IE3 (Premium)',
    'IE4' => 'IE4 (Super Premium)',
    'IE5' => 'IE5 (Ultra Premium)'
];

$classeOptionsCible = [
    'IE2' => 'IE2 (Haut rendement)',
    'IE3' => 'IE3 (Premium)',
    'IE4' => 'IE4 (Super Premium)',
    'IE5' => 'IE5 (Ultra Premium)'
];
?>

<style>
.simulateur-economie-energie {
    margin: 2rem auto;
    max-width: 1200px;
    color: #333;
}
.simulateur-card {
    background-color: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 0.25rem 1rem rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.simulateur-header {
    background-color: #2563eb;
    color: white;
    padding: 1.5rem;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
}
.simulateur-header h2 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: #fff;
}
.simulateur-content {
    padding: 1.5rem;
}
.simulateur-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(2, 1fr);
}
.simulateur-full-width {
    grid-column: 1 / -1;
}
.simulateur-input:focus, .simulateur-select:focus {
    outline: none;
    border-color: #000;
    box-shadow: 0 0 0 1px #000;
    color: #000;
}
.analyse-icon {
    font-size: 1.25rem;
    margin-right: 0.5rem;
    vertical-align: middle;
}
.analyse-text {
    vertical-align: middle;
}
.simulateur-disclaimer {
    margin-top: 1rem;
    padding: 1rem;
    font-size: 0.875rem;
    color: #6b7280;
    text-align: center;
    border-top: 1px solid #e5e7eb;
}
.simulateur-conditions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}
.simulateur-results-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
.simulateur-analysis-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}
.simulateur-results-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.simulateur-results-header {
    margin-bottom: 1rem;
}
.simulateur-results-columns {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .simulateur-results-columns, .simulateur-conditions-grid {
        grid-template-columns: 1fr;
    }
}
@media (min-width: 992px) {
    .simulateur-results-grid {
        grid-template-columns: 1fr 1fr;
    }
    .simulateur-analysis-grid {
        grid-template-columns: 1fr;
    }
    .simulateur-results-columns {
        grid-template-columns: 1fr;
    }
}
@media (min-width: 1200px) {
    .simulateur-analysis-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (min-width: 768px) {
    .simulateur-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.simulateur-section {
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.simulateur-section-special {
    margin-top: -1.5rem;
    margin-bottom: 0rem;
}
.simulateur-section-last {
    margin-bottom: 0;
}
.simulateur-section h3 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    font-size: 1.25rem;
    font-weight: bold;
    color: #000;
}
.simulateur-inputs {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.simulateur-chart-fullwidth {
    grid-column: 1 / -1;
    margin-bottom: 1.5rem;
    height: 20rem;
    position: relative;
}
.simulateur-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.simulateur-input-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.simulateur-value {
    font-size: 0.875rem;
    font-weight: bold;
}
.simulateur-puissance-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    margin-top: 10px;
    padding-left: 4px;
    padding-right: 4px;
}
.simulateur-puissance-btn {
    background-color: #1e40af;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 0;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s;
    text-align: center;
    font-weight: bold;
}
.simulateur-puissance-btn:hover {
    background-color: #1e3a8a;
}
.simulateur-puissance-btn.selected {
    background-color: #38bdf8;
    font-weight: bold;
    color: #000;
}
.simulateur-select {
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    background-color: #FFF;
    width: 100%;
}
.simulateur-input {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.25rem;
    width: 100%;
}
.switch-group {
    flex-direction: row;
    align-items: center;
}
.switch-container {
    position: relative;
    display: inline-block;
    width: 3.5rem;
    height: 1.75rem;
}
.switch-input {
    opacity: 0;
    width: 0;
    height: 0;
}
.switch-label {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    border-radius: 1.75rem;
    cursor: pointer;
    transition: 0.3s;
}
.switch-label:before {
    position: absolute;
    content: "";
    height: 1.25rem;
    width: 1.25rem;
    left: 0.25rem;
    bottom: 0.25rem;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}
.switch-input:checked + .switch-label {
    background-color: #2563eb;
}
.switch-input:checked + .switch-label:before {
    transform: translateX(1.75rem);
}
.simulateur-results {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}
.simulateur-results-summary {
    background-color: #fff;
    border: 1px solid #000;
    border-radius: 0.5rem;
    padding: 1rem;
    display: flex;
    flex-direction: row;
    gap: 0.75rem;
    text-align: center;
    max-width: 690px;
    margin: 0 auto;
}
.simulateur-result-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}
.simulateur-result-label {
    font-size: 0.875rem;
    color: #6b7280;
}
.simulateur-result-value {
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
    margin-bottom: 0.75rem;
}
.simulateur-result-value.positive {
    color: #16a34a;
}
.simulateur-analysis, .simulateur-savings, .simulateur-environmental {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}
.simulateur-analysis {
    background-color: #e0f2fe;
}
.simulateur-analysis h4, .simulateur-savings h4, .simulateur-environmental h4 {
    font-size: 0.875rem;
    font-weight: bold;
    margin-top: 0;
    margin-bottom: 0.5rem;
}
.simulateur-analysis p {
    font-size: 0.875rem;
    margin: 0;
}
.simulateur-savings {
    background-color: #ecfdf5;
    border: 1px solid #000;
    display: flex;
    flex-direction: column;
    text-align: center;
    width: 30rem;
    max-width: 690px;
    margin: 0 auto;
}
.simulateur-savings-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}
.simulateur-savings-label {
    font-size: 0.75rem;
    color: #6b7280;
}
.simulateur-savings-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #16a34a;
}
.simulateur-environmental {
    background-color: #fff;
    border: 1px solid #000;
    display: flex;
    flex-direction: column;
    text-align: center;
    width: 30rem;
    max-width: 690px;
    margin: 0 auto;
}
.simulateur-environmental-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.simulateur-environmental-row {
    display: flex;
    justify-content: space-between;
}
.simulateur-environmental-label {
    font-size: 0.875rem;
    color: #6b7280;
}
.simulateur-environmental-value {
    font-size: 0.875rem;
    font-weight: 500;
}
.text-bold {
    font-weight: bold;
}
.text-bold-black {
    font-weight: bold;
    color: #000;
}
.text-black {
    color: #000;
}
.simulateur-input-header label svg, .simulateur-input-group label svg {
    vertical-align: middle;
    margin-right: 0.3rem;
}
.simulateur-input-group label {
    display: flex;
    align-items: center;
    margin-left: 0.1rem;
}
.simulateur-input-group label svg.variateur-icon {
    margin-left: 0.5rem;
    margin-right: 0;
}
.simulateur-input-group.switch-group label {
    margin-bottom: 0;
}
</style>

<div class="simulateur-economie-energie">
    <div class="simulateur-card">
        <div class="simulateur-header">
            <h2>Simulateur d'Économies d'Énergie pour Moteurs</h2>
        </div>
        
        <div class="simulateur-content">
            <div class="simulateur-grid">
                <!-- COLONNE 1 - Paramètres d'entrée -->
                <div class="simulateur-section">
                    <h3 class="text-bold-black">Moteur actuel</h3>
                    <div class="simulateur-inputs">
                        <?php
                        // Puissance actuelle
                        echo createInputField('puissanceActuelle', 'puissance-grid', 'Puissance du moteur actuel (kW)', 'moteur', [
                            'default' => '11 kW',
                            'defaultCategory' => 'petit',
                            'categories' => $puissanceCategories
                        ]);
                        
                        // Nombre de pôles actuel
                        echo createInputField('polesActuel', 'select', 'Nombre de pôles (vitesse)', 'poles', [
                            'options' => $poleOptions,
                            'default' => '4'
                        ]);
                        
                        // Classe d'efficience actuelle
                        echo createInputField('classeActuelle', 'select', 'Classe d\'efficience', 'classe', [
                            'options' => $classeOptionsActuelle,
                            'default' => 'IE2'
                        ]);
                        
                        // Efficacité du moteur actuel
                        echo createInputField('efficaciteMoteurActuel', 'number', 'Efficacité du moteur (%)', 'moteur', [
                            'min' => '10',
                            'max' => '100',
                            'default' => '89'
                        ]);
                        
                        // Variateur de vitesse actuel
                        echo createInputField('vitesseVariableActuel', 'switch', 'Variateur de vitesse', 'variateur');
                        ?>
                    </div>
                </div>
                
                <!-- Moteur cible -->
                <div class="simulateur-section">
                    <h3>Moteur cible</h3>
                    <div class="simulateur-inputs">
                        <?php
                        // Puissance cible
                        echo createInputField('puissanceCible', 'puissance-grid', 'Puissance du moteur cible (kW)', 'moteur', [
                            'default' => '11 kW',
                            'defaultCategory' => 'petit',
                            'categories' => $puissanceCategories
                        ]);
                        
                        // Nombre de pôles cible
                        echo createInputField('polesCible', 'select', 'Nombre de pôles (vitesse)', 'poles', [
                            'options' => $poleOptions,
                            'default' => '4'
                        ]);
                        
                        // Classe d'efficience cible
                        echo createInputField('classeCible', 'select', 'Classe d\'efficience', 'classe', [
                            'options' => $classeOptionsCible,
                            'default' => 'IE4'
                        ]);
                        
                        // Efficacité du moteur cible
                        echo createInputField('efficaciteMoteurCible', 'number', 'Efficacité du moteur (%)', 'moteur', [
                            'min' => '10',
                            'max' => '100',
                            'default' => '93'
                        ]);
                        
                        // Variateur de vitesse cible
                        echo createInputField('vitesseVariableCible', 'switch', 'Variateur de vitesse', 'variateur');
                        ?>
                    </div>
                </div>
                
                <!-- Conditions d'exploitation -->
                <div class="simulateur-full-width">
                    <div class="simulateur-section simulateur-section-special">
                        <h3>Conditions d'exploitation</h3>
                        <div class="simulateur-inputs">
                            <div class="simulateur-conditions-grid">
                                <?php
                                // Coût de l'électricité
                                echo createInputField('coutEnergie', 'number', 'Prix unitaire de l\'électricité (€/kWh)', 'prix', [
                                    'min' => '0.01',
                                    'max' => '1',
                                    'step' => '0.01',
                                    'default' => '0.15'
                                ]);
                                
                                // Jours de fonctionnement
                                echo createInputField('joursFonctionnement', 'number', 'Combien de jours de fonctionnement par an ? (J)', 'jours', [
                                    'min' => '1',
                                    'max' => '365',
                                    'default' => '250'
                                ]);
                                
                                // Heures de fonctionnement
                                echo createInputField('heuresFonctionnementParJour', 'number', 'Combien d\'heures de fonctionnement par jour ? (H)', 'heures', [
                                    'min' => '1',
                                    'max' => '24',
                                    'default' => '16'
                                ]);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Résultats -->
                <div class="simulateur-full-width">
                    <div class="simulateur-section simulateur-section-last">
                        <h3 class="text-bold-black simulateur-results-header">Résultats :</h3>
                        <div class="simulateur-results-container">
                            <div class="simulateur-results-summary">
                                <div class="simulateur-result-row">
                                    <div class="simulateur-result-label">Consommation annuelle actuelle :</div>
                                    <div class="simulateur-result-value text-bold-black" id="consommationActuelle_<?php echo $simulateurId; ?>">0 kWh/an</div>
                                </div>
                                
                                <div class="simulateur-result-row">
                                    <div class="simulateur-result-label">Consommation annuelle cible :</div>
                                    <div class="simulateur-result-value text-bold-black" id="consommationCible_<?php echo $simulateurId; ?>">0 kWh/an</div>
                                </div>
                                
                                <div class="simulateur-result-row">
                                    <div class="simulateur-result-label">Économie annuelle :</div>
                                    <div class="simulateur-result-value positive text-bold-black" id="economieAnnuelle_<?php echo $simulateurId; ?>">0 €/an</div>
                                </div>
                                
                                <div class="simulateur-result-row">
                                    <div class="simulateur-result-label">Coût investissement :</div>
                                    <div class="simulateur-result-value text-bold-black" id="coutInvestissement_<?php echo $simulateurId; ?>">0 €</div>
                                </div>
                                
                                <div class="simulateur-result-row">
                                    <div class="simulateur-result-label">Retour sur investissement :</div>
                                    <div class="simulateur-result-value text-bold-black" id="retourInvestissement_<?php echo $simulateurId; ?>">0 ans</div>
                                </div>
                            </div>
                            
                            <div class="simulateur-chart-fullwidth">
                                <h4 class="text-bold-black">Évolution des coûts sur 10 ans</h4>
                                <canvas id="chartCouts_<?php echo $simulateurId; ?>"></canvas>
                            </div>
                            
                            <div class="simulateur-results-columns">
                                <div class="simulateur-analysis">
                                    <h4 class="text-bold-black">Analyse</h4>
                                    <p id="analyseText_<?php echo $simulateurId; ?>">Veuillez ajuster les paramètres pour obtenir une analyse.</p>
                                </div>
                                
                                <div class="simulateur-savings">
                                    <h4 class="text-bold-black">Économies estimées :</h4>
                                    <div class="simulateur-savings-grid">
                                        <div class="simulateur-savings-item">
                                            <div class="simulateur-savings-label">Sur 5 ans</div>
                                            <div class="simulateur-savings-value" id="economie5Ans_<?php echo $simulateurId; ?>">0 €</div>
                                        </div>
                                        <div class="simulateur-savings-item">
                                            <div class="simulateur-savings-label">Sur 10 ans</div>
                                            <div class="simulateur-savings-value" id="economie10Ans_<?php echo $simulateurId; ?>">0 €</div>
                                        </div>
                                        <div class="simulateur-savings-item">
                                            <div class="simulateur-savings-label">Sur 15 ans</div>
                                            <div class="simulateur-savings-value" id="economie15Ans_<?php echo $simulateurId; ?>">0 €</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="simulateur-environmental">
                                    <h4 class="text-bold-black">Impact environnemental :</h4>
                                    <div class="simulateur-environmental-grid">
                                        <div class="simulateur-environmental-row">
                                            <div class="simulateur-environmental-label">Réduction annuelle de CO2 :</div>
                                            <div class="simulateur-environmental-value text-bold-black" id="reductionCO2_<?php echo $simulateurId; ?>">0 kg CO2/an</div>
                                        </div>
                                        <div class="simulateur-environmental-row">
                                            <div class="simulateur-environmental-label">Économie d'énergie annuelle :</div>
                                            <div class="simulateur-environmental-value text-bold-black" id="economieEnergie_<?php echo $simulateurId; ?>">0 kWh/an</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simulateur-disclaimer">
                    <p>Note: Ce simulateur utilise des valeurs approximatives basées sur les normes d'efficacité IE1 à IE5 pour les moteurs électriques. Les résultats réels peuvent varier en fonction des spécifications exactes du moteur et des conditions d'utilisation. Pour une analyse détaillée, consultez un spécialiste.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const simulateurId = '<?php echo $simulateurId; ?>';
    const simulateurData = <?php echo json_encode($simulateurData); ?>;
    
    // Définition des catégories de puissance
    const puissanceCategories = {
        micro: [0.12, 0.18, 0.20, 0.25, 0.37, 0.4, 0.55, 0.75],
        petit: [1.1, 1.5, 2.2, 3, 4, 5.5, 7.5, 11],
        moyen: [15, 18.5, 22, 30, 37, 45, 55, 75],
        grand: [90, 110, 132, 160, 200, 250, 315, 355, 400, 450, 500, 1000]
    };
    
    // Valeurs par défaut
    let puissanceActuelle = 11;
    let puissanceCible = 11;

    // Utilitaires
    function findClosestPower(targetPower, rendements, classe) {
        if (rendements[classe][targetPower] !== undefined) {
            return targetPower;
        }
        const powers = Object.keys(rendements[classe]).map(Number);
        let closestPower = powers.reduce((prev, curr) =>
            Math.abs(curr - targetPower) < Math.abs(prev - targetPower) ? curr : prev
        );
        return closestPower;
    }
    
    // Génération des boutons de puissance
    function generatePuissanceButtons(category, containerId, defaultValue) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        
        puissanceCategories[category].forEach(puissance => {
            const button = document.createElement('button');
            button.className = 'simulateur-puissance-btn';
            
            if (puissance === parseFloat(defaultValue)) {
                button.classList.add('selected');
            }
            
            button.textContent = `${parseFloat(puissance).toFixed(puissance % 1 === 0 ? 0 : 2)} kW`;
            button.dataset.value = puissance;
            
            button.addEventListener('click', function() {
                document.querySelectorAll(`#${containerId} .simulateur-puissance-btn`).forEach(btn => {
                    btn.classList.remove('selected');
                });
                
                this.classList.add('selected');
                
                if (containerId.includes('Actuelle')) {
                    selectPuissanceActuelle(puissance);
                } else {
                    selectPuissanceCible(puissance);
                }
            });
            
            container.appendChild(button);
        });
    }
    
    // Sélection de puissance
    function selectPuissanceActuelle(puissance) {
        puissanceActuelle = parseFloat(puissance);
        document.getElementById(`puissanceActuelleValue_${simulateurId}`).textContent = puissance + ' kW';
        calculerResultats();
    }

    function selectPuissanceCible(puissance) {
        puissanceCible = parseFloat(puissance);
        document.getElementById(`puissanceCibleValue_${simulateurId}`).textContent = puissance + ' kW';
        calculerResultats();
    }
    
    // Initialisation des grilles de puissance
    const categorySelectActuel = document.getElementById(`puissanceActuelleCategory_${simulateurId}`);
    generatePuissanceButtons(categorySelectActuel.value, `puissanceActuelleGrid_${simulateurId}`, puissanceActuelle);
    
    categorySelectActuel.addEventListener('change', function() {
        generatePuissanceButtons(this.value, `puissanceActuelleGrid_${simulateurId}`, puissanceActuelle);
    });
    
    const categorySelectCible = document.getElementById(`puissanceCibleCategory_${simulateurId}`);
    generatePuissanceButtons(categorySelectCible.value, `puissanceCibleGrid_${simulateurId}`, puissanceCible);
    
    categorySelectCible.addEventListener('change', function() {
        generatePuissanceButtons(this.value, `puissanceCibleGrid_${simulateurId}`, puissanceCible);
    });
    
    // Génération de l'analyse textuelle
    function genererAnalyseTexte(economieAnnuelle, retourInvestissement, classeCible, puissanceCible, vitesseVariableCible) {
        let analyseTexte = '';
        let analyseIcon = '';
        
        if (economieAnnuelle <= 0) {
            analyseIcon = '❌';
            analyseTexte = "Aucune économie significative n'est générée avec cette configuration.";
        } else if (retourInvestissement <= 2) {
            analyseIcon = '🔥';
            analyseTexte = `Investissement très rentable à court terme. Économies importantes et retour sur investissement rapide de ${retourInvestissement.toFixed(1)} ans.`;
        } else if (retourInvestissement <= 5) {
            analyseIcon = '✅';
            analyseTexte = `Bon investissement. Rentabilité atteinte en moins de 5 ans (${retourInvestissement.toFixed(1)} ans).`;
        } else if (retourInvestissement <= 10) {
            analyseIcon = '⚠️';
            analyseTexte = `Rentabilité à moyen terme. Économies modérées sur ${retourInvestissement.toFixed(1)} ans.`;
        } else {
            analyseIcon = '🔍';
            analyseTexte = `Rentabilité à long terme. Envisager d'autres options ou attendre une hausse du coût de l'énergie.`;
        }
        
        if (economieAnnuelle > 0) {
            analyseTexte += ` L'investissement dans un moteur ${classeCible} de ${puissanceCible} kW`;
            analyseTexte += vitesseVariableCible ? ' avec variateur de vitesse' : '';
            analyseTexte += ` est une solution technique adaptée pour ce cas d'usage.`;
        }
        
        return `<span class="analyse-icon">${analyseIcon}</span><span class="analyse-text">${analyseTexte}</span>`;
    }
    
    // Calcul des résultats
    function calculerResultats() {
        // Récupération des valeurs
        const classeActuelle = document.getElementById(`classeActuelle_${simulateurId}`).value;
        const classeCible = document.getElementById(`classeCible_${simulateurId}`).value;
        const polesActuel = parseInt(document.getElementById(`polesActuel_${simulateurId}`).value);
        const polesCible = parseInt(document.getElementById(`polesCible_${simulateurId}`).value);
        const vitesseVariableActuel = document.getElementById(`vitesseVariableActuel_${simulateurId}`).checked;
        const vitesseVariableCible = document.getElementById(`vitesseVariableCible_${simulateurId}`).checked;
        const coutEnergie = parseFloat(document.getElementById(`coutEnergie_${simulateurId}`).value);
        const joursFonctionnement = parseInt(document.getElementById(`joursFonctionnement_${simulateurId}`).value);
        const heuresFonctionnementParJour = parseInt(document.getElementById(`heuresFonctionnementParJour_${simulateurId}`).value);
        const efficaciteMoteurActuel = parseInt(document.getElementById(`efficaciteMoteurActuel_${simulateurId}`).value) / 100;
        const efficaciteMoteurCible = parseInt(document.getElementById(`efficaciteMoteurCible_${simulateurId}`).value) / 100;
        
        // Trouver les puissances ajustées
        const puissanceActuelleAjustee = findClosestPower(puissanceActuelle, simulateurData.rendements, classeActuelle);
        const puissanceCibleAjustee = findClosestPower(puissanceCible, simulateurData.rendements, classeCible);
        
        // Calculer les rendements ajustés
        const rendementActuel = simulateurData.rendements[classeActuelle][puissanceActuelleAjustee] * simulateurData.adjustPoleFactors[polesActuel];
        const rendementCible = simulateurData.rendements[classeCible][puissanceCibleAjustee] * simulateurData.adjustPoleFactors[polesCible];
        
        // Calculer les heures de fonctionnement annuelles
        const heuresAnnuelles = joursFonctionnement * heuresFonctionnementParJour;
        
        // Calculer les consommations
        const puissanceUtileActuelle = puissanceActuelleAjustee * efficaciteMoteurActuel;
        let consommationActuelle = puissanceUtileActuelle / rendementActuel * heuresAnnuelles;
        if (vitesseVariableActuel) {
            consommationActuelle *= 0.85; // Réduction de 15% grâce au variateur
        }

        const puissanceUtileCible = puissanceCibleAjustee * efficaciteMoteurCible;
        let consommationCible = puissanceUtileCible / rendementCible * heuresAnnuelles;
        if (vitesseVariableCible) {
            consommationCible *= 0.85; // Réduction de 15% grâce au variateur
        }
        
        // Économie d'énergie annuelle
        const economieEnergie = consommationActuelle - consommationCible;
        
        // Économie financière annuelle
        const economieAnnuelle = economieEnergie * coutEnergie;
        
        // Coût d'investissement
        let coutInvestissement = simulateurData.coutMoteurs[classeCible][puissanceCible];
        if (vitesseVariableCible && !vitesseVariableActuel) {
            if (simulateurData.coutVSD[puissanceCible] !== undefined) {
                coutInvestissement += simulateurData.coutVSD[puissanceCible];
            } else {
                // Utiliser une valeur par défaut ou la plus proche
                const puissances = Object.keys(simulateurData.coutVSD).map(Number);
                const puissanceProche = puissances.reduce((a, b) => {
                    return Math.abs(b - puissanceCible) < Math.abs(a - puissanceCible) ? b : a;
                });
                coutInvestissement += simulateurData.coutVSD[puissanceProche];
            }
        }
        
        // Retour sur investissement
        const retourInvestissement = economieAnnuelle > 0 ? coutInvestissement / economieAnnuelle : 0;
        
        // Économies sur plusieurs années
        const economie5Ans = economieAnnuelle * 5;
        const economie10Ans = economieAnnuelle * 10;
        const economie15Ans = economieAnnuelle * 15;
        
        // Impact environnemental (kg CO2 par kWh - moyenne européenne ~0.275)
        const facteurCO2 = 0.275;
        const reductionCO2 = economieEnergie * facteurCO2;
        
        // Mettre à jour les affichages
        document.getElementById(`consommationActuelle_${simulateurId}`).textContent = Math.round(consommationActuelle).toLocaleString() + ' kWh/an';
        document.getElementById(`consommationCible_${simulateurId}`).textContent = Math.round(consommationCible).toLocaleString() + ' kWh/an';
        document.getElementById(`economieAnnuelle_${simulateurId}`).textContent = Math.round(economieAnnuelle).toLocaleString() + ' €/an';
        document.getElementById(`coutInvestissement_${simulateurId}`).textContent = Math.round(coutInvestissement).toLocaleString() + ' €';
        document.getElementById(`retourInvestissement_${simulateurId}`).textContent = retourInvestissement.toFixed(1) + ' ans';
        document.getElementById(`economie5Ans_${simulateurId}`).textContent = Math.round(economie5Ans).toLocaleString() + ' €';
        document.getElementById(`economie10Ans_${simulateurId}`).textContent = Math.round(economie10Ans).toLocaleString() + ' €';
        document.getElementById(`economie15Ans_${simulateurId}`).textContent = Math.round(economie15Ans).toLocaleString() + ' €';
        document.getElementById(`reductionCO2_${simulateurId}`).textContent = Math.round(reductionCO2).toLocaleString() + ' kg CO2/an';
        document.getElementById(`economieEnergie_${simulateurId}`).textContent = Math.round(economieEnergie).toLocaleString() + ' kWh/an';
        
        // Mise à jour de la couleur du texte d'économie
        const economieAnnuelleElement = document.getElementById(`economieAnnuelle_${simulateurId}`);
        economieAnnuelleElement.style.color = economieAnnuelle < 0 ? '#e31206' : economieAnnuelle === 0 ? '#000000' : '#16a34a';
        
        // Mettre à jour l'analyse textuelle
        document.getElementById(`analyseText_${simulateurId}`).innerHTML = genererAnalyseTexte(
            economieAnnuelle,
            retourInvestissement,
            classeCible,
            puissanceCible,
            vitesseVariableCible
        );
        
        // Mettre à jour le graphique
        updateChart(economieAnnuelle, coutInvestissement);
    }
    
    // Mise à jour du graphique
    function updateChart(economieAnnuelle, coutInvestissement) {
        const ctx = document.getElementById(`chartCouts_${simulateurId}`);
        
        // Détruire le graphique existant s'il existe
        if (window.coutChart) {
            window.coutChart.destroy();
        }
        
        // Calculer les données pour le graphique
        const labels = Array.from({length: 11}, (_, i) => i);
        const dataActuel = labels.map(annee => annee * 0); // Pas de coût d'investissement pour le moteur actuel
        const dataCible = labels.map(annee => annee === 0 ? coutInvestissement : coutInvestissement - (annee * economieAnnuelle));
        
        // Créer le nouveau graphique
        window.coutChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Coût cumulé actuel',
                        data: dataActuel,
                        borderColor: '#e31206',
                        backgroundColor: 'rgba(227, 18, 6, 0.1)',
                        fill: true
                    },
                    {
                        label: 'Coût cumulé optimisé',
                        data: dataCible,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Années'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Coût (€)'
                        }
                    }
                }
            }
        });
    }
    
    // Attacher les écouteurs d'événements
    function attachEventListeners() {
        // Champs à surveiller
        const champs = [
            'polesActuel', 'classeActuelle', 'polesCible', 'classeCible',
            'coutEnergie', 'joursFonctionnement', 'heuresFonctionnementParJour',
            'efficaciteMoteurActuel', 'efficaciteMoteurCible'
        ];
        
        champs.forEach(champ => {
            const element = document.getElementById(`${champ}_${simulateurId}`);
            if (element) {
                element.addEventListener('change', calculerResultats);
            }
        });
        
        // Variateurs de vitesse
        const vitesseVariableActuelElement = document.getElementById(`vitesseVariableActuel_${simulateurId}`);
        if (vitesseVariableActuelElement) {
            vitesseVariableActuelElement.addEventListener('change', calculerResultats);
        }
        
        const vitesseVariableCibleElement = document.getElementById(`vitesseVariableCible_${simulateurId}`);
        if (vitesseVariableCibleElement) {
            vitesseVariableCibleElement.addEventListener('change', calculerResultats);
        }
    }
    
    // Initialisation
    attachEventListeners();
    calculerResultats();
});
</script>