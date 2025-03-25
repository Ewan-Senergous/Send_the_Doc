<?php
// Préparation des données pour le JavaScript
$simulateurData = array(
    'puissancesMoteur' => array(0.12, 0.18, 0.20, 0.25, 0.37, 0.4, 0.55, 0.75,
        1.1, 1.5, 2.2, 3, 4, 5.5, 7.5, 11, 15, 18.5, 22,
        30, 37, 45, 55, 75, 90, 110, 132, 160, 200, 250,
        315, 355, 400, 450, 500, 1000),
    'rendements' => array(
        "IE1" => array(
            0.12 => 0.50, 0.18 => 0.55, 0.20 => 0.58, 0.25 => 0.60, 0.37 => 0.63, 0.4 => 0.64, 0.55 => 0.66, 0.75 => 0.68,
            1.1 => 0.73, 1.5 => 0.75, 2.2 => 0.78, 3 => 0.80, 4 => 0.83, 5.5 => 0.85, 7.5 => 0.86,
            11 => 0.87, 15 => 0.88, 18.5 => 0.89, 22 => 0.89, 30 => 0.90, 37 => 0.90, 45 => 0.91,
            55 => 0.91, 75 => 0.92, 90 => 0.93, 110 => 0.93, 132 => 0.93, 160 => 0.94, 200 => 0.94,
            250 => 0.94, 315 => 0.95, 355 => 0.95, 400 => 0.95, 450 => 0.95, 500 => 0.95, 1000 => 0.95
        ),
        "IE2" => array(
            0.12 => 0.55, 0.18 => 0.60, 0.20 => 0.63, 0.25 => 0.65, 0.37 => 0.69, 0.4 => 0.70, 0.55 => 0.73, 0.75 => 0.75,
            1.1 => 0.79, 1.5 => 0.81, 2.2 => 0.83, 3 => 0.85, 4 => 0.86, 5.5 => 0.87, 7.5 => 0.88,
            11 => 0.89, 15 => 0.90, 18.5 => 0.91, 22 => 0.91, 30 => 0.92, 37 => 0.92, 45 => 0.93,
            55 => 0.93, 75 => 0.94, 90 => 0.94, 110 => 0.94, 132 => 0.95, 160 => 0.95, 200 => 0.95,
            250 => 0.95, 315 => 0.96, 355 => 0.96, 400 => 0.96, 450 => 0.96, 500 => 0.96, 1000 => 0.96
        ),
        "IE3" => array(
            0.12 => 0.60, 0.18 => 0.65, 0.20 => 0.68, 0.25 => 0.70, 0.37 => 0.75, 0.4 => 0.76, 0.55 => 0.78, 0.75 => 0.80,
            1.1 => 0.83, 1.5 => 0.85, 2.2 => 0.86, 3 => 0.88, 4 => 0.89, 5.5 => 0.90, 7.5 => 0.91,
            11 => 0.91, 15 => 0.92, 18.5 => 0.92, 22 => 0.93, 30 => 0.93, 37 => 0.94, 45 => 0.94,
            55 => 0.94, 75 => 0.95, 90 => 0.95, 110 => 0.95, 132 => 0.96, 160 => 0.96, 200 => 0.96,
            250 => 0.96, 315 => 0.96, 355 => 0.97, 400 => 0.97, 450 => 0.97, 500 => 0.97, 1000 => 0.97
        ),
        "IE4" => array(
            0.12 => 0.65, 0.18 => 0.70, 0.20 => 0.72, 0.25 => 0.74, 0.37 => 0.78, 0.4 => 0.79, 0.55 => 0.81, 0.75 => 0.83,
            1.1 => 0.86, 1.5 => 0.87, 2.2 => 0.89, 3 => 0.90, 4 => 0.91, 5.5 => 0.92, 7.5 => 0.92,
            11 => 0.93, 15 => 0.93, 18.5 => 0.94, 22 => 0.94, 30 => 0.94, 37 => 0.95, 45 => 0.95,
            55 => 0.95, 75 => 0.96, 90 => 0.96, 110 => 0.96, 132 => 0.96, 160 => 0.97, 200 => 0.97,
            250 => 0.97, 315 => 0.97, 355 => 0.97, 400 => 0.97, 450 => 0.97, 500 => 0.97, 1000 => 0.98
        ),
        "IE5" => array(
            0.12 => 0.69, 0.18 => 0.74, 0.20 => 0.76, 0.25 => 0.78, 0.37 => 0.82, 0.4 => 0.83, 0.55 => 0.85, 0.75 => 0.87,
            1.1 => 0.89, 1.5 => 0.90, 2.2 => 0.91, 3 => 0.92, 4 => 0.93, 5.5 => 0.94, 7.5 => 0.94,
            11 => 0.95, 15 => 0.95, 18.5 => 0.96, 22 => 0.96, 30 => 0.96, 37 => 0.96, 45 => 0.97,
            55 => 0.97, 75 => 0.97, 90 => 0.97, 110 => 0.97, 132 => 0.98, 160 => 0.98, 200 => 0.98,
            250 => 0.98, 315 => 0.98, 355 => 0.98, 400 => 0.98, 450 => 0.98, 500 => 0.98, 1000 => 0.99
        )
    ),
    'adjustPoleFactors' => array(
        2 => 1.01,
        4 => 1.0,
        6 => 0.99,
        8 => 0.98
    ),
    'coutMoteurs' => array(
        "IE2" => array(
            0.12 => 100, 0.18 => 120, 0.20 => 130, 0.25 => 150, 0.37 => 180, 0.4 => 190, 0.55 => 220, 0.75 => 250,
            1.1 => 300, 1.5 => 350, 2.2 => 450, 3 => 550, 4 => 650, 5.5 => 700, 7.5 => 900,
            11 => 1200, 15 => 1500, 18.5 => 1800, 22 => 2000, 30 => 2500, 37 => 3000, 45 => 3500,
            55 => 4500, 75 => 6000, 90 => 7000, 110 => 8500, 132 => 10000, 160 => 12000, 200 => 15000,
            250 => 18000, 315 => 22000, 355 => 25000, 400 => 28000, 450 => 32000, 500 => 35000, 1000 => 70000
        ),
        "IE3" => array(
            0.12 => 120, 0.18 => 140, 0.20 => 150, 0.25 => 180, 0.37 => 220, 0.4 => 230, 0.55 => 260, 0.75 => 300,
            1.1 => 350, 1.5 => 420, 2.2 => 550, 3 => 650, 4 => 800, 5.5 => 850, 7.5 => 1100,
            11 => 1500, 15 => 1800, 18.5 => 2200, 22 => 2500, 30 => 3200, 37 => 3800, 45 => 4200,
            55 => 5500, 75 => 7500, 90 => 8500, 110 => 10000, 132 => 12000, 160 => 15000, 200 => 18000,
            250 => 22000, 315 => 27000, 355 => 30000, 400 => 34000, 450 => 38000, 500 => 42000, 1000 => 85000
        ),
        "IE4" => array(
            0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 230, 0.37 => 280, 0.4 => 300, 0.55 => 350, 0.75 => 420,
            1.1 => 500, 1.5 => 600, 2.2 => 750, 3 => 900, 4 => 1100, 5.5 => 1200, 7.5 => 1500,
            11 => 2000, 15 => 2500, 18.5 => 3000, 22 => 3500, 30 => 4500, 37 => 5200, 45 => 6000,
            55 => 7500, 75 => 10000, 90 => 12000, 110 => 14000, 132 => 16000, 160 => 20000, 200 => 24000,
            250 => 30000, 315 => 36000, 355 => 40000, 400 => 45000, 450 => 50000, 500 => 55000, 1000 => 110000
        ),
        "IE5" => array(
            0.12 => 180, 0.18 => 220, 0.20 => 240, 0.25 => 280, 0.37 => 340, 0.4 => 360, 0.55 => 420, 0.75 => 500,
            1.1 => 600, 1.5 => 720, 2.2 => 900, 3 => 1100, 4 => 1300, 5.5 => 1500, 7.5 => 1800,
            11 => 2400, 15 => 3000, 18.5 => 3600, 22 => 4200, 30 => 5400, 37 => 6200, 45 => 7200,
            55 => 9000, 75 => 12000, 90 => 14000, 110 => 17000, 132 => 20000, 160 => 24000, 200 => 30000,
            250 => 36000, 315 => 44000, 355 => 48000, 400 => 54000, 450 => 60000, 500 => 66000, 1000 => 130000
        )
    ),
    'coutVSD' => array(
        0.12 => 150, 0.18 => 180, 0.20 => 200, 0.25 => 220, 0.37 => 250, 0.4 => 260, 0.55 => 300, 0.75 => 350,
        1.1 => 500, 1.5 => 550, 2.2 => 650, 3 => 750, 4 => 850, 5.5 => 900, 7.5 => 1200,
        11 => 1500, 15 => 1800, 18.5 => 2200, 22 => 2500, 30 => 3000, 37 => 3500, 45 => 4000,
        55 => 5000, 75 => 8000, 90 => 9500, 110 => 11000, 132 => 13000, 160 => 16000, 200 => 20000,
        250 => 25000, 315 => 30000, 355 => 34000, 400 => 38000, 450 => 42000, 500 => 45000, 1000 => 90000
    )
);

// Inclusion de Chart.js
wp_enqueue_script('chart-js', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js', array('jquery'), '3.7.1', true);

// Définir un identifiant unique pour le simulateur
$simulateurId = 'simulateur_' . uniqid();
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
    }

    .simulateur-input:focus,
    .simulateur-select:focus {
    outline: none;
    border-color: #000;
    box-shadow: 0 0 0 1px #000;
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
    
    .simulateur-section h3 {
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .simulateur-inputs {
        display: flex;
        flex-direction: column;
        gap: 1rem;
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
        font-weight: 500;
    }
    
    .simulateur-button {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        background-color: #e5e7eb;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: background-color 0.2s;
    }
    
    .simulateur-button:hover {
        background-color: #d1d5db;
    }
    
    .simulateur-accordion {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-in-out;
        border: 1px solid #e5e7eb;
        border-radius: 0.25rem;
    }
    
    .simulateur-accordion-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.25rem;
        padding: 0.5rem;
    }
    
    .simulateur-power-button {
        font-size: 0.75rem;
        padding: 0.25rem;
        border: none;
        border-radius: 0.25rem;
        background-color: #1e40af;
        color: #FFF;
        cursor: pointer;
        transition: background-color 0.2s;
        font-weight: bold;
    }
    
    .simulateur-power-button:hover {
        background-color: #1e3a8a;
    }
    
    .simulateur-power-button.active {
        background-color: #38bdf8;
        color: white;
    }
    
    .simulateur-select {
        padding: 0.5rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.25rem;
        background-color: white;
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
        justify-content: space-between;
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
        background-color: #f3f4f6;
        border-radius: 0.5rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .simulateur-result-row {
        display: grid;
        grid-template-columns: 60% 40%;
        gap: 0.5rem;
    }
    
    .simulateur-result-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .simulateur-result-value {
        font-size: 0.875rem;
        font-weight: 500;
        text-align: right;
    }
    
    .simulateur-result-value.positive {
        color: #16a34a;
    }
    
    .simulateur-chart-container {
        height: 16rem;
        margin-bottom: 1rem;
    }
    
    .simulateur-chart-container h4 {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .simulateur-analysis,
    .simulateur-savings,
    .simulateur-environmental {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .simulateur-analysis {
        background-color: #e0f2fe;
    }
    
    .simulateur-analysis h4,
    .simulateur-savings h4,
    .simulateur-environmental h4 {
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0;
        margin-bottom: 0.5rem;
    }
    
    .simulateur-analysis p {
        font-size: 0.875rem;
        margin: 0;
    }
    
    .simulateur-savings {
        background-color: #ecfdf5;
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
        background-color: #f9fafb;
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
    
    .simulateur-disclaimer {
        margin-top: 1rem;
        padding: 1rem;
        font-size: 0.875rem;
        color: #6b7280;
        text-align: center;
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
                <div class="simulateur-column">
                    <!-- Moteur actuel -->
                    <div class="simulateur-section">
                        <h3>Moteur actuel</h3>
                        
                        <div class="simulateur-inputs">
                            <!-- Puissance accordéon -->
                            <div class="simulateur-input-group">
                                <div class="simulateur-input-header">
                                    <label for="puissanceActuelle_<?php echo $simulateurId; ?>">Puissance du moteur actuel (kW)</label>
                                    <span class="simulateur-value" id="puissanceActuelleValue_<?php echo $simulateurId; ?>">11 kW</span>
                                </div>
                                <button
                                    id="toggleAccordionActuel_<?php echo $simulateurId; ?>"
                                    class="simulateur-button accordion-toggle"
                                    type="button"
                                >
                                    Sélectionner une puissance
                                    <span id="accordionIconActuel_<?php echo $simulateurId; ?>">▼</span>
                                </button>
                                <div
                                    id="accordionActuel_<?php echo $simulateurId; ?>"
                                    class="simulateur-accordion"
                                >
                                    <div class="simulateur-accordion-grid" id="puissancesActuel_<?php echo $simulateurId; ?>">
                                        <!-- Rempli dynamiquement par JavaScript -->
                                    </div>
                                </div>
                            </div>

                            
                            <!-- Nombre de pôles -->
                            <div class="simulateur-input-group">
                                <label for="polesActuel_<?php echo $simulateurId; ?>">Nombre de pôles (vitesse)</label>
                                <select id="polesActuel_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="2">2 pôles (3000 tr/min)</option>
                                    <option value="4" selected>4 pôles (1500 tr/min)</option>
                                    <option value="6">6 pôles (1000 tr/min)</option>
                                    <option value="8">8 pôles (750 tr/min)</option>
                                </select>
                            </div>
                            
                            <!-- Classe d'efficience -->
                            <div class="simulateur-input-group">
                                <label for="classeActuelle_<?php echo $simulateurId; ?>">Classe d'efficience</label>
                                <select id="classeActuelle_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="IE1">IE1 (Standard)</option>
                                    <option value="IE2" selected>IE2 (Haut rendement)</option>
                                    <option value="IE3">IE3 (Premium)</option>
                                    <option value="IE4">IE4 (Super Premium)</option>
                                    <option value="IE5">IE5 (Ultra Premium)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Moteur cible -->
                    <div class="simulateur-section">
                        <h3>Moteur cible</h3>
                        
                        <div class="simulateur-inputs">
                            <!-- Puissance accordéon cible -->
                            <div class="simulateur-input-group">
                                <div class="simulateur-input-header">
                                    <label for="puissanceCible_<?php echo $simulateurId; ?>">Puissance du moteur cible (kW)</label>
                                    <span class="simulateur-value" id="puissanceCibleValue_<?php echo $simulateurId; ?>">11 kW</span>
                                </div>
                                <button
                                    id="toggleAccordionCible_<?php echo $simulateurId; ?>"
                                    class="simulateur-button accordion-toggle"
                                    type="button"
                                >
                                    Sélectionner une puissance
                                    <span id="accordionIconCible_<?php echo $simulateurId; ?>">▼</span>
                                </button>
                                <div
                                    id="accordionCible_<?php echo $simulateurId; ?>"
                                    class="simulateur-accordion"
                                >
                                    <div class="simulateur-accordion-grid" id="puissancesCible_<?php echo $simulateurId; ?>">
                                        <!-- Rempli dynamiquement par JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Nombre de pôles cible -->
                            <div class="simulateur-input-group">
                                <label for="polesCible_<?php echo $simulateurId; ?>">Nombre de pôles (vitesse)</label>
                                <select id="polesCible_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="2">2 pôles (3000 tr/min)</option>
                                    <option value="4" selected>4 pôles (1500 tr/min)</option>
                                    <option value="6">6 pôles (1000 tr/min)</option>
                                    <option value="8">8 pôles (750 tr/min)</option>
                                </select>
                            </div>
                            
                            <!-- Classe d'efficience cible -->
                            <div class="simulateur-input-group">
                                <label for="classeCible_<?php echo $simulateurId; ?>">Classe d'efficience</label>
                                <select id="classeCible_<?php echo $simulateurId; ?>" class="simulateur-select">
                                    <option value="IE2">IE2 (Haut rendement)</option>
                                    <option value="IE3">IE3 (Premium)</option>
                                    <option value="IE4" selected>IE4 (Super Premium)</option>
                                    <option value="IE5">IE5 (Ultra Premium)</option>
                                </select>
                            </div>
                            
                            <!-- Variateur de vitesse -->
                            <div class="simulateur-input-group switch-group">
                                <label for="vitesseVariable_<?php echo $simulateurId; ?>">Variateur de vitesse</label>
                                <div class="switch-container">
                                    <input type="checkbox" id="vitesseVariable_<?php echo $simulateurId; ?>" class="switch-input">
                                    <label for="vitesseVariable_<?php echo $simulateurId; ?>" class="switch-label"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conditions d'exploitation -->
                    <div class="simulateur-section">
                        <h3>Conditions d'exploitation</h3>
                        
                        <div class="simulateur-inputs">
                            <div class="simulateur-input-group">
                                <label for="coutEnergie_<?php echo $simulateurId; ?>">Prix unitaire de l'électricité (€/kWh)</label>
                                <input
                                    id="coutEnergie_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="0.01"
                                    max="1"
                                    step="0.01"
                                    value="0.15"
                                    class="simulateur-input"
                                />
                            </div>
                            
                            <div class="simulateur-input-group">
                                <label for="joursFonctionnement_<?php echo $simulateurId; ?>">Combien de jours de fonctionnement par an ? (j)</label>
                                <input
                                    id="joursFonctionnement_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="1"
                                    max="365"
                                    value="250"
                                    class="simulateur-input"
                                />
                            </div>
                            
                            <div class="simulateur-input-group">
                                <label for="heuresFonctionnementParJour_<?php echo $simulateurId; ?>">Combien d'heures de fonctionnement par jour ? (h)</label>
                                <input
                                    id="heuresFonctionnementParJour_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="1"
                                    max="24"
                                    value="16"
                                    class="simulateur-input"
                                />
                            </div>
                            
                            <div class="simulateur-input-group">
                                <label for="chargeMoteur_<?php echo $simulateurId; ?>">Charge du moteur (%)</label>
                                <input
                                    id="chargeMoteur_<?php echo $simulateurId; ?>"
                                    type="number"
                                    min="10"
                                    max="100"
                                    value="75"
                                    class="simulateur-input"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- COLONNE 2 - Résultats -->
                <div class="simulateur-column">
                    <h3>Résultats</h3>
                    
                    <div class="simulateur-results">
                        <div class="simulateur-results-summary">
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label">Consommation annuelle actuelle:</div>
                                <div class="simulateur-result-value" id="consommationActuelle_<?php echo $simulateurId; ?>">0 kWh/an</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label">Consommation annuelle après optimisation:</div>
                                <div class="simulateur-result-value" id="consommationCible_<?php echo $simulateurId; ?>">0 kWh/an</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label">Économie annuelle:</div>
                                <div class="simulateur-result-value positive" id="economieAnnuelle_<?php echo $simulateurId; ?>">0 €/an</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label">Coût d'investissement:</div>
                                <div class="simulateur-result-value" id="coutInvestissement_<?php echo $simulateurId; ?>">0 €</div>
                            </div>
                            
                            <div class="simulateur-result-row">
                                <div class="simulateur-result-label">Retour sur investissement:</div>
                                <div class="simulateur-result-value" id="retourInvestissement_<?php echo $simulateurId; ?>">0 ans</div>
                            </div>
                        </div>
                        
                        <div class="simulateur-chart-container">
                            <h4>Évolution des coûts sur 10 ans</h4>
                            <canvas id="chartCouts_<?php echo $simulateurId; ?>"></canvas>
                        </div>
                        
                        <div class="simulateur-analysis">
                            <h4>Analyse</h4>
                            <p id="analyseText_<?php echo $simulateurId; ?>">Veuillez ajuster les paramètres pour obtenir une analyse.</p>
                        </div>
                        
                        <div class="simulateur-savings">
                            <h4>Économies estimées</h4>
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
                            <h4>Impact environnemental</h4>
                            <div class="simulateur-environmental-grid">
                                <div class="simulateur-environmental-row">
                                    <div class="simulateur-environmental-label">Réduction annuelle de CO2:</div>
                                    <div class="simulateur-environmental-value" id="reductionCO2_<?php echo $simulateurId; ?>">0 kg CO2/an</div>
                                </div>
                                <div class="simulateur-environmental-row">
                                    <div class="simulateur-environmental-label">Économie d'énergie annuelle:</div>
                                    <div class="simulateur-environmental-value" id="economieEnergie_<?php echo $simulateurId; ?>">0 kWh/an</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simulateur-disclaimer">
    <p>Note: Ce simulateur utilise des valeurs approximatives basées sur les normes d'efficacité IE1 à IE5 pour les moteurs électriques. Les résultats réels peuvent varier en fonction des spécifications exactes du moteur et des conditions d'utilisation. Pour une analyse détaillée, consultez un spécialiste.</p>
</div>
                <script>
document.addEventListener('DOMContentLoaded', function() {
    const simulateurId = '<?php echo $simulateurId; ?>';
    const simulateurData = <?php echo json_encode($simulateurData); ?>;
    
    // Éléments DOM pour l'accordéon actuel
    const toggleAccordionActuel = document.getElementById(`toggleAccordionActuel_${simulateurId}`);
    const accordionActuel = document.getElementById(`accordionActuel_${simulateurId}`);
    const accordionIconActuel = document.getElementById(`accordionIconActuel_${simulateurId}`);
    const puissancesActuelContainer = document.getElementById(`puissancesActuel_${simulateurId}`);
    const puissanceActuelleValue = document.getElementById(`puissanceActuelleValue_${simulateurId}`);
    
    // Éléments DOM pour l'accordéon cible
    const toggleAccordionCible = document.getElementById(`toggleAccordionCible_${simulateurId}`);
    const accordionCible = document.getElementById(`accordionCible_${simulateurId}`);
    const accordionIconCible = document.getElementById(`accordionIconCible_${simulateurId}`);
    const puissancesCibleContainer = document.getElementById(`puissancesCible_${simulateurId}`);
    const puissanceCibleValue = document.getElementById(`puissanceCibleValue_${simulateurId}`);
    
    // Valeurs par défaut
    let puissanceActuelle = 11;
    let puissanceCible = 11;
    
    // Fonction pour créer les boutons de puissance dans l'accordéon
    function createPowerButtons(container, puissances, currentValue, onSelectCallback) {
        container.innerHTML = '';
        
        puissances.forEach(puissance => {
            const button = document.createElement('button');
            button.className = 'simulateur-power-button';
            if (puissance === currentValue) {
                button.classList.add('active');
            }
            button.textContent = puissance + ' kW';
            button.dataset.value = puissance;
            
            button.addEventListener('click', function() {
                onSelectCallback(puissance);
                
                // Retirer la classe active de tous les boutons
                const allButtons = container.querySelectorAll('.simulateur-power-button');
                allButtons.forEach(btn => btn.classList.remove('active'));
                
                // Ajouter la classe active au bouton cliqué
                button.classList.add('active');
            });
            
            container.appendChild(button);
        });
    }
    
    // Fonctions de gestion des accordéons
    function toggleAccordion(accordion, icon) {
        const isExpanded = accordion.style.maxHeight;
        
        if (isExpanded) {
            accordion.style.maxHeight = null;
            icon.textContent = '▼';
        } else {
            accordion.style.maxHeight = accordion.scrollHeight + 'px';
            icon.textContent = '▲';
        }
    }
    
    // Initialiser l'accordéon moteur actuel
    toggleAccordionActuel.addEventListener('click', function() {
        toggleAccordion(accordionActuel, accordionIconActuel);
    });
    
    // Initialiser l'accordéon moteur cible
    toggleAccordionCible.addEventListener('click', function() {
        toggleAccordion(accordionCible, accordionIconCible);
    });
    
    // Fonction de sélection pour le moteur actuel
    function selectPuissanceActuelle(puissance) {
        puissanceActuelle = puissance;
        puissanceActuelleValue.textContent = puissance + ' kW';
        calculerResultats();
    }
    
    // Fonction de sélection pour le moteur cible
    function selectPuissanceCible(puissance) {
        puissanceCible = puissance;
        puissanceCibleValue.textContent = puissance + ' kW';
        calculerResultats();
    }
    
    // Créer les boutons de puissance
    createPowerButtons(puissancesActuelContainer, simulateurData.puissancesMoteur, puissanceActuelle, selectPuissanceActuelle);
    createPowerButtons(puissancesCibleContainer, simulateurData.puissancesMoteur, puissanceCible, selectPuissanceCible);

    // Modification de la génération d'analyse textuelle
function genererAnalyseTexte(economieAnnuelle, retourInvestissement, classeCible, puissanceCible, vitesseVariable) {
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
    
    // Ajouter les détails techniques
    if (economieAnnuelle > 0) {
        analyseTexte += ` L'investissement dans un moteur ${classeCible} de ${puissanceCible} kW`;
        analyseTexte += vitesseVariable ? ' avec variateur de vitesse' : '';
        analyseTexte += ` est une solution technique adaptée pour ce cas d'usage.`;
    }
    
    return `<span class="analyse-icon">${analyseIcon}</span><span class="analyse-text">${analyseTexte}</span>`;
}

    // Fonction pour calculer les résultats
    function calculerResultats() {
        // Récupérer toutes les valeurs des champs
        const classeActuelle = document.getElementById(`classeActuelle_${simulateurId}`).value;
        const classeCible = document.getElementById(`classeCible_${simulateurId}`).value;
        const polesActuel = parseInt(document.getElementById(`polesActuel_${simulateurId}`).value);
        const polesCible = parseInt(document.getElementById(`polesCible_${simulateurId}`).value);
        const vitesseVariable = document.getElementById(`vitesseVariable_${simulateurId}`).checked;
        const coutEnergie = parseFloat(document.getElementById(`coutEnergie_${simulateurId}`).value);
        const joursFonctionnement = parseInt(document.getElementById(`joursFonctionnement_${simulateurId}`).value);
        const heuresFonctionnementParJour = parseInt(document.getElementById(`heuresFonctionnementParJour_${simulateurId}`).value);
        const chargeMoteur = parseInt(document.getElementById(`chargeMoteur_${simulateurId}`).value) / 100;
        
        // Calculer les rendements ajustés
        const rendementActuel = simulateurData.rendements[classeActuelle][puissanceActuelle] * simulateurData.adjustPoleFactors[polesActuel];
        const rendementCible = simulateurData.rendements[classeCible][puissanceCible] * simulateurData.adjustPoleFactors[polesCible];
        
        // Calculer les heures de fonctionnement annuelles
        const heuresAnnuelles = joursFonctionnement * heuresFonctionnementParJour;
        
        // Calculer les consommations
        const puissanceUtile = puissanceActuelle * chargeMoteur;
        const consommationActuelle = puissanceUtile / rendementActuel * heuresAnnuelles;
        
        // Calculer la consommation avec le moteur cible
        let consommationCible = puissanceUtile / rendementCible * heuresAnnuelles;
        
        // Ajuster la consommation si un variateur est utilisé (exemple: réduction de 15%)
        if (vitesseVariable) {
            consommationCible *= 0.85; // Réduction de 15% grâce au variateur
        }
        
        // Économie d'énergie annuelle
        const economieEnergie = consommationActuelle - consommationCible;
        
        // Économie financière annuelle
        const economieAnnuelle = economieEnergie * coutEnergie;
        
        // Coût d'investissement
        let coutInvestissement = simulateurData.coutMoteurs[classeCible][puissanceCible];
        if (vitesseVariable) {
            coutInvestissement += simulateurData.coutVSD[puissanceCible];
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
        
        
        
        document.getElementById(`analyseText_${simulateurId}`).innerHTML = genererAnalyseTexte(
    economieAnnuelle,
    retourInvestissement,
    classeCible,
    puissanceCible,
    vitesseVariable
);
        
        // Mettre à jour le graphique
        updateChart(economieAnnuelle, coutInvestissement);
    }
    
    // Fonction pour mettre à jour le graphique
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
    
    // Ajouter des écouteurs d'événements pour les autres champs
    const champs = [
        'polesActuel', 'classeActuelle', 'polesCible', 'classeCible',
        'coutEnergie', 'joursFonctionnement', 'heuresFonctionnementParJour', 'chargeMoteur'
    ];
    
    champs.forEach(champ => {
        const element = document.getElementById(`${champ}_${simulateurId}`);
        if (element) {
            element.addEventListener('change', calculerResultats);
        }
    });
    
    // Gérer le variateur de vitesse
    const vitesseVariable = document.getElementById(`vitesseVariable_${simulateurId}`);
    if (vitesseVariable) {
        vitesseVariable.addEventListener('change', calculerResultats);
    }
    
    // Calculer les résultats initiaux
    calculerResultats();
});
</script>