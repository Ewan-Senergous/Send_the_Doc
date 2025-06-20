<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Diagnostic Pompe à Palettes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
            margin: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: calc(100vh - 20px);
        }

        .header {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .progress-bar {
            background: rgba(255,255,255,0.2);
            height: 6px;
            border-radius: 3px;
            margin-top: 20px;
            overflow: hidden;
        }

        .progress-fill {
            background: white;
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 10px;
        }

        .step-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e0e0e0;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background: #2196F3;
            transform: scale(1.2);
        }

        .form-content {
            padding: 20px;
        }

        .step {
            display: none;
            padding-bottom: 20px;
        }

        .step.active {
            display: block;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .step h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 1.3rem;
            border-bottom: 2px solid #2196F3;
            padding-bottom: 10px;
            line-height: 1.2;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-row.desktop-two-col {
            grid-template-columns: 1fr;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        input[type="text"], input[type="number"], input[type="date"], input[type="email"], input[type="tel"], select, textarea {
            width: 100%;
            padding: 15px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: white;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
            transform: scale(1.02);
        }

        .radio-group {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 50px;
            font-size: 1rem;
        }

        .radio-item:hover, .radio-item:active {
            background: #f5f5f5;
            transform: scale(1.02);
        }

        .radio-item input[type="radio"] {
            width: 20px;
            height: 20px;
            margin: 0;
        }

        .radio-item.selected {
            border-color: #2196F3;
            background: rgba(33, 150, 243, 0.1);
            transform: scale(1.02);
        }

        .component-diagnosis {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fafafa;
        }

        .component-diagnosis h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .diagnosis-options {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .diagnosis-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.95rem;
            min-height: 45px;
            transition: all 0.2s ease;
        }

        .diagnosis-option:hover, .diagnosis-option:active {
            background: #f0f0f0;
            transform: scale(1.02);
        }

        .diagnosis-option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .diagnosis-option.selected {
            border-color: #2196F3;
            background: rgba(33, 150, 243, 0.1);
            transform: scale(1.02);
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            position: sticky;
            bottom: 0;
            background: white;
            padding: 20px;
            margin: 0 -20px;
        }

        .btn {
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-height: 50px;
            touch-action: manipulation;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background: #2196F3;
            color: white;
        }

        .btn-primary:hover {
            background: #1976D2;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-success {
            background: #4CAF50;
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
        }

        .btn-success:hover {
            background: #45a049;
            transform: translateY(-2px);
        }

        .summary-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .summary-section h3 {
            color: #333;
            margin-bottom: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        .report-send-section {
            background: #e8f4fd;
            border: 2px solid #2196F3;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }

        .report-send-section h3 {
            color: #1976D2;
            margin-bottom: 20px;
            font-size: 1.2rem;
            text-align: center;
        }

        .honeypot-field {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            height: 0;
            width: 0;
            z-index: -1;
            overflow: hidden;
        }

        .cenov-gdpr-consent {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 5px;
        }

        .cenov-gdpr-consent input[type="checkbox"] {
            width: auto;
            margin-top: 4px;
        }

        .form-submit {
            text-align: center;
            margin-top: 20px;
        }

        #sendReportBtn {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        #sendReportBtn:hover {
            background: linear-gradient(135deg, #45a049, #4CAF50);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        #sendReportBtn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .success-message {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 500;
        }

        .error-message {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 500;
        }

        .measurement-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .measurement-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .measurement-card h4 {
            color: #555;
            margin-bottom: 15px;
            font-size: 1rem;
            text-transform: uppercase;
            font-weight: 600;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 8px;
        }

        /* Media Queries pour Tablette */
        @media (min-width: 768px) {
            body {
                padding: 20px;
            }
            
            .container {
                border-radius: 20px;
            }
            
            .header {
                padding: 30px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .form-content {
                padding: 30px;
            }
            
            .form-row.desktop-two-col {
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }
            
            .radio-group {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 15px;
            }
            
            .measurement-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .diagnosis-options {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .button-group {
                flex-direction: row;
                justify-content: space-between;
                margin: 30px -30px 0;
                padding: 20px 30px;
            }
            
            .btn {
                min-width: 140px;
            }
        }

        /* Media Queries pour Desktop */
        @media (min-width: 1024px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .form-content {
                padding: 40px;
            }
            
            .step h2 {
                font-size: 1.5rem;
            }
            
            .measurement-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
            
            .button-group {
                margin: 40px -40px 0;
                padding: 20px 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Diagnostic Pompe à Palettes</h1>
            <p>Fiche technique digitalisée</p>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="step-indicator" id="stepIndicator"></div>
        </div>

        <div class="form-content">
            <!-- Étape 1: Informations Client -->
            <div class="step active" id="step1">
                <h2>📋 Informations Client & Réception</h2>
                
                <div class="form-row desktop-two-col">
                    <div class="form-group">
                        <label for="gmaoClient">GMAO Client</label>
                        <input type="text" id="gmaoClient" name="gmaoClient" required>
                    </div>
                    <div class="form-group">
                        <label for="numeroDossier">N° de dossier</label>
                        <input type="text" id="numeroDossier" name="numeroDossier" required>
                    </div>
                </div>

                <div class="form-row desktop-two-col">
                    <div class="form-group">
                        <label for="constructeur">Constructeur</label>
                        <input type="text" id="constructeur" name="constructeur" required>
                    </div>
                    <div class="form-group">
                        <label for="type">Type</label>
                        <input type="text" id="type" name="type" required>
                    </div>
                </div>

                <div class="form-row desktop-two-col">
                    <div class="form-group">
                        <label for="numeroSerie">Numéro de série</label>
                        <input type="text" id="numeroSerie" name="numeroSerie" required>
                    </div>
                    <div class="form-group">
                        <label>Type de pompe à palettes</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" id="seches" name="typePompe" value="seches">
                                <label for="seches">Sèches</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="lubrifiees" name="typePompe" value="lubrifiees">
                                <label for="lubrifiees">Lubrifiées</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type de prestation</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="preventif" name="prestation" value="preventif">
                            <label for="preventif">Préventif</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="entretien" name="prestation" value="entretien">
                            <label for="entretien">Entretien</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="correctif" name="prestation" value="correctif">
                            <label for="correctif">Correctif</label>
                        </div>
                    </div>
                </div>

                <!-- Photos plaque signalétique -->
                <div class="form-group" style="margin-top: 30px;">
                    <label for="plaque_photos">📸 Photos de la plaque signalétique</label>
                    <input type="file" id="plaque_photos" name="plaque_photos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,.webp" style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px;">
                    <div id="plaque_preview" style="margin-top: 10px; color: #666; font-size: 0.9rem;"></div>
                    <small style="color: #666;">Formats acceptés : JPG, PNG, PDF, HEIC, WEBP (10 Mo max par fichier)</small>
                </div>
            </div>

            <!-- Étape 2: Contrôle Initial -->
            <div class="step" id="step2">
                <h2>🔍 Contrôle Initial</h2>
                
                <div class="form-group">
                    <label>État général</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="bon" name="etatGeneral" value="bon">
                            <label for="bon">Bon</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="acceptable" name="etatGeneral" value="acceptable">
                            <label for="acceptable">Acceptable</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="hs" name="etatGeneral" value="hs">
                            <label for="hs">Hors Service</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>État de la pompe</label>
                    <div class="radio-group">
                        <div class="radio-item">
                            <input type="radio" id="libre" name="etatPompe" value="libre">
                            <label for="libre">Libre</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="bloque" name="etatPompe" value="bloque">
                            <label for="bloque">Bloqué</label>
                        </div>
                        <div class="radio-item">
                            <input type="radio" id="autre" name="etatPompe" value="autre">
                            <label for="autre">Autre</label>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="autreDescription" style="display: none;">
                    <label for="autreDetail">Préciser l'état "Autre"</label>
                    <input type="text" id="autreDetail" name="autreDetail" placeholder="Décrivez l'état de la pompe...">
                </div>

                <div class="form-row desktop-two-col">
                    <div class="form-group">
                        <label for="dateDiagnostic">Date du diagnostic</label>
                        <input type="date" id="dateDiagnostic" name="dateDiagnostic" required>
                    </div>
                    <div class="form-group">
                        <label for="technicienNom">Nom du technicien</label>
                        <input type="text" id="technicienNom" name="technicienNom" required>
                    </div>
                </div>

                <!-- Photos état initial -->
                <div class="form-group" style="margin-top: 30px;">
                    <label for="etat_photos">📸 Photos de l'état initial</label>
                    <input type="file" id="etat_photos" name="etat_photos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,.webp" style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px;">
                    <div id="etat_preview" style="margin-top: 10px; color: #666; font-size: 0.9rem;"></div>
                    <small style="color: #666;">Photos de l'état avant intervention</small>
                </div>
            </div>

            <!-- Étape 3: Mesures Techniques -->
            <div class="step" id="step3">
                <h2>📊 Informations Techniques</h2>
                
                <div class="measurement-grid">
                    <div class="measurement-card">
                        <h4>Dépression (mbars)</h4>
                        <div class="form-row desktop-two-col">
                            <div class="form-group">
                                <label for="depressionEntree">Entrée</label>
                                <input type="number" id="depressionEntree" name="depressionEntree" step="0.1" inputmode="decimal">
                            </div>
                            <div class="form-group">
                                <label for="depressionSortie">Sortie</label>
                                <input type="number" id="depressionSortie" name="depressionSortie" step="0.1" inputmode="decimal">
                            </div>
                        </div>
                    </div>

                    <div class="measurement-card">
                        <h4>Pression (mbars)</h4>
                        <div class="form-row desktop-two-col">
                            <div class="form-group">
                                <label for="pressionEntree">Entrée</label>
                                <input type="number" id="pressionEntree" name="pressionEntree" step="0.1" inputmode="decimal">
                            </div>
                            <div class="form-group">
                                <label for="pressionSortie">Sortie</label>
                                <input type="number" id="pressionSortie" name="pressionSortie" step="0.1" inputmode="decimal">
                            </div>
                        </div>
                    </div>

                    <div class="measurement-card">
                        <h4>Température (°C)</h4>
                        <div class="form-group">
                            <label for="tempAspiration">Aspiration</label>
                            <input type="number" id="tempAspiration" name="tempAspiration" step="0.1" inputmode="decimal">
                        </div>
                        <div class="form-group">
                            <label for="tempCorpsPompe">Corps de pompe</label>
                            <input type="number" id="tempCorpsPompe" name="tempCorpsPompe" step="0.1" inputmode="decimal">
                        </div>
                        <div class="form-group">
                            <label for="tempEchappement">Échappement</label>
                            <input type="number" id="tempEchappement" name="tempEchappement" step="0.1" inputmode="decimal">
                        </div>
                    </div>

                    <div class="measurement-card">
                        <h4>Ampérage & Durée</h4>
                        <div class="form-group">
                            <label for="amperage">Ampérage (A)</label>
                            <input type="number" id="amperage" name="amperage" step="0.1" inputmode="decimal">
                        </div>
                        <div class="form-group">
                            <label for="dureeEssai">Durée essai sortie (min)</label>
                            <input type="number" id="dureeEssai" name="dureeEssai" step="1" inputmode="numeric">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="commentairesTechniques">Commentaires techniques</label>
                    <textarea id="commentairesTechniques" name="commentairesTechniques" rows="4" placeholder="Observations techniques..."></textarea>
                </div>

                <!-- Photos mesures techniques -->
                <div class="form-group" style="margin-top: 30px;">
                    <label for="mesures_photos">📸 Photos des mesures techniques</label>
                    <input type="file" id="mesures_photos" name="mesures_photos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,.webp" style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px;">
                    <div id="mesures_preview" style="margin-top: 10px; color: #666; font-size: 0.9rem;"></div>
                    <small style="color: #666;">Photos des instruments de mesure, valeurs relevées</small>
                </div>
            </div>

            <!-- Étape 4: Diagnostic Détaillé -->
            <div class="step" id="step4">
                <h2>🔧 Diagnostic Détaillé des Composants</h2>
                
                <!-- Flasques -->
                <div class="component-diagnosis">
                    <h3>Flasques (ACC/OP ACC)</h3>
                    <div class="form-row desktop-two-col">
                        <div class="form-group">
                            <label>ACC</label>
                            <div class="diagnosis-options">
                                <div class="diagnosis-option">
                                    <input type="radio" name="flasqueACC" value="bon">
                                    <span>Bon</span>
                                </div>
                                <div class="diagnosis-option">
                                    <input type="radio" name="flasqueACC" value="rebaguage">
                                    <span>Rebaguage</span>
                                </div>
                            </div>
                            <input type="text" placeholder="Diamètre" name="flasqueACCDiametre">
                            <input type="number" placeholder="Voile (mm)" name="flasqueACCVoile" step="0.01" inputmode="decimal">
                        </div>
                        <div class="form-group">
                            <label>OP ACC</label>
                            <div class="diagnosis-options">
                                <div class="diagnosis-option">
                                    <input type="radio" name="flasqueOPACC" value="bon">
                                    <span>Bon</span>
                                </div>
                                <div class="diagnosis-option">
                                    <input type="radio" name="flasqueOPACC" value="rebaguage">
                                    <span>Rebaguage</span>
                                </div>
                            </div>
                            <input type="text" placeholder="Diamètre" name="flasqueOPACCDiametre">
                            <input type="number" placeholder="Voile (mm)" name="flasqueOPACCVoile" step="0.01" inputmode="decimal">
                        </div>
                    </div>
                </div>

                <!-- Corps de pompe -->
                <div class="component-diagnosis">
                    <h3>Corps de pompe</h3>
                    <div class="diagnosis-options">
                        <div class="diagnosis-option">
                            <input type="radio" name="corpsPompe" value="bon">
                            <span>Bon</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="corpsPompe" value="realesage">
                            <span>Réalésage</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="corpsPompe" value="rectifier">
                            <span>Rectifier</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="corpsPompe" value="hs">
                            <span>HS</span>
                        </div>
                    </div>
                    <input type="text" placeholder="Diamètre" name="corpsPompeDiametre">
                </div>

                <!-- Autres composants -->
                <div class="component-diagnosis">
                    <h3>Déshuilleur</h3>
                    <div class="diagnosis-options">
                        <div class="diagnosis-option">
                            <input type="radio" name="deshuilleur" value="bon">
                            <span>Bon</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="deshuilleur" value="hs">
                            <span>HS</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="deshuilleur" value="kit">
                            <span>KIT</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="deshuilleur" value="stock">
                            <span>STOCK</span>
                        </div>
                    </div>
                    <div class="form-row desktop-two-col">
                        <input type="number" placeholder="Quantité" name="deshuileur_qte" inputmode="numeric">
                        <input type="text" placeholder="Référence" name="deshuileur_ref">
                    </div>
                </div>

                <div class="component-diagnosis">
                    <h3>Jeu de palettes</h3>
                    <div class="diagnosis-options">
                        <div class="diagnosis-option">
                            <input type="radio" name="palettes" value="bon">
                            <span>Bon</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="palettes" value="hs">
                            <span>HS</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="palettes" value="kit">
                            <span>KIT</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="palettes" value="stock">
                            <span>STOCK</span>
                        </div>
                    </div>
                    <div class="form-row desktop-two-col">
                        <input type="number" placeholder="Quantité" name="palettes_qte" inputmode="numeric">
                        <input type="text" placeholder="Référence" name="palettes_ref">
                    </div>
                </div>

                <div class="component-diagnosis">
                    <h3>Voyant à huile</h3>
                    <div class="diagnosis-options">
                        <div class="diagnosis-option">
                            <input type="radio" name="voyantHuile" value="3/4">
                            <span>3/4</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="voyantHuile" value="1/2">
                            <span>1/2</span>
                        </div>
                        <div class="diagnosis-option">
                            <input type="radio" name="voyantHuile" value="1">
                            <span>1</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos diagnostic composants -->
            <div class="form-group" style="margin-top: 30px;">
                <label for="diagnostic_photos">📸 Photos du diagnostic des composants</label>
                <input type="file" id="diagnostic_photos" name="diagnostic_photos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,.webp" style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px;">
                <div id="diagnostic_preview" style="margin-top: 10px; color: #666; font-size: 0.9rem;"></div>
                <small style="color: #666;">Photos des composants défaillants, pièces usées</small>
            </div>

            <!-- Étape 5: Analyse et Recommandations -->
            <div class="step" id="step5">
                <h2>📝 Analyse et Recommandations</h2>
                
                <div class="form-group">
                    <label for="observations">Observations générales</label>
                    <textarea id="observations" name="observations" rows="4" placeholder="Observations sur l'état général de la pompe..."></textarea>
                </div>

                <div class="form-group">
                    <label for="analyse">Analyse technique</label>
                    <textarea id="analyse" name="analyse" rows="4" placeholder="Analyse détaillée des problèmes identifiés..."></textarea>
                </div>

                <div class="form-group">
                    <label for="autresCommande">Autres éléments à commander</label>
                    <textarea id="autresCommande" name="autresCommande" rows="3" placeholder="Liste des pièces supplémentaires à commander..."></textarea>
                </div>

                <div class="form-row desktop-two-col">
                    <div class="form-group">
                        <label for="tempsTest">Temps test et démontage (h)</label>
                        <input type="number" id="tempsTest" name="tempsTest" step="0.5" inputmode="decimal">
                    </div>
                    <div class="form-group">
                        <label for="tempsRemontage">Temps remontage (h)</label>
                        <input type="number" id="tempsRemontage" name="tempsRemontage" step="0.5" inputmode="decimal">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tempsTestFinal">Temps test final (h)</label>
                    <input type="number" id="tempsTestFinal" name="tempsTestFinal" step="0.5" inputmode="decimal">
                </div>

                <!-- Photos finales -->
                <div class="form-group" style="margin-top: 30px;">
                    <label for="final_photos">📸 Photos finales (après intervention)</label>
                    <input type="file" id="final_photos" name="final_photos[]" multiple accept=".jpg,.jpeg,.png,.pdf,.heic,.webp" style="width: 100%; padding: 15px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px;">
                    <div id="final_preview" style="margin-top: 10px; color: #666; font-size: 0.9rem;"></div>
                    <small style="color: #666;">Photos après réparation, tests finaux</small>
                </div>
            </div>

            <!-- Étape 6: Résumé et Envoi -->
            <div class="step" id="step6">
                <h2>📋 Résumé du Diagnostic</h2>
                
                <div class="summary-section">
                    <h3>Informations générales</h3>
                    <div id="summaryGeneral"></div>
                </div>

                <div class="summary-section">
                    <h3>État de la pompe</h3>
                    <div id="summaryEtat"></div>
                </div>

                <div class="summary-section">
                    <h3>Mesures techniques</h3>
                    <div id="summaryMesures"></div>
                </div>

                <div class="summary-section">
                    <h3>Composants à remplacer/réparer</h3>
                    <div id="summaryComposants"></div>
                </div>

                <div class="summary-section">
                    <h3>Temps d'intervention</h3>
                    <div id="summaryTemps"></div>
                </div>

                <div class="summary-section">
                    <h3>Photos jointes</h3>
                    <div id="summaryPhotos"></div>
                </div>

                <div class="alert alert-warning" id="alertes" style="display: none;">
                    <strong>⚠️ Points d'attention :</strong>
                    <div id="alertesList"></div>
                </div>

                <!-- Formulaire d'envoi simplifié -->
                <div class="report-send-section">
                    <h3>📧 Envoyer le Rapport</h3>
                    <form method="post" action="" id="diagnosticReportForm" enctype="multipart/form-data">
                        <!-- Champs de sécurité simplifiés -->
                        <div class="honeypot-field">
                            <input type="text" name="cenov_website" id="cenov_website" autocomplete="off" tabindex="-1" placeholder="Ne pas remplir ce champ">
                        </div>
                        
                        <input type="hidden" name="diagnostic_data" id="diagnostic_data">
                        
                        <div class="form-row desktop-two-col">
                            <div class="form-group">
                                <label for="email_destinataire">Email destinataire *</label>
                                <input type="email" id="email_destinataire" name="cenov_email" required placeholder="client@entreprise.fr">
                            </div>
                            <div class="form-group">
                                <label for="email_copie">Email en copie</label>
                                <input type="email" id="email_copie" name="cenov_email_copie" placeholder="manager@entreprise.fr">
                            </div>
                        </div>

                        <div class="form-row desktop-two-col">
                            <div class="form-group">
                                <label for="envoyeur_nom">Votre nom *</label>
                                <input type="text" id="envoyeur_nom" name="cenov_prenom" required placeholder="Jean Dupont">
                            </div>
                            <div class="form-group">
                                <label for="envoyeur_telephone">Votre téléphone</label>
                                <input type="tel" id="envoyeur_telephone" name="cenov_telephone" placeholder="06 12 34 56 78">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message_accompagnement">Message d'accompagnement</label>
                            <textarea id="message_accompagnement" name="cenov_message" rows="3" placeholder="Message optionnel à joindre au rapport..."></textarea>
                        </div>

                        <div class="form-group">
                            <div class="cenov-gdpr-consent">
                                <input type="checkbox" id="cenov-gdpr" name="cenov_gdpr" required>
                                <label for="cenov-gdpr">J'accepte que mes données soient utilisées pour traiter ma demande *</label>
                            </div>
                        </div>

                        <!-- Champs de fichiers cachés pour transférer les fichiers sélectionnés -->
                        <input type="file" name="plaque_photos[]" id="form_plaque_photos" multiple style="display: none;">
                        <input type="file" name="etat_photos[]" id="form_etat_photos" multiple style="display: none;">
                        <input type="file" name="mesures_photos[]" id="form_mesures_photos" multiple style="display: none;">
                        <input type="file" name="diagnostic_photos[]" id="form_diagnostic_photos" multiple style="display: none;">
                        <input type="file" name="final_photos[]" id="form_final_photos" multiple style="display: none;">

                        <div class="form-submit">
                            <button type="submit" name="cenov_submit" value="1" id="sendReportBtn">
                                📧 Envoyer le Rapport
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)">Précédent</button>
                <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">Suivant</button>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 6;

        // Capturer les logs pour debug
        window.debugLogs = [];
        const originalLog = console.log;
        console.log = function(...args) {
            window.debugLogs.push(args.join(' '));
            originalLog.apply(console, args);
        };

        // Initialisation SIMPLIFIÉE
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Initialisation du formulaire...');
            
            updateStepIndicator();
            updateProgress();
            
            // Gestion des radio buttons avec style
            document.querySelectorAll('.radio-item').forEach(item => {
                item.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        
                        // Retirer la classe selected de tous les éléments du même groupe
                        const groupName = radio.name;
                        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                            r.closest('.radio-item').classList.remove('selected');
                        });
                        
                        // Ajouter la classe selected à l'élément actuel
                        this.classList.add('selected');
                        
                        // Gestion spéciale pour "autre"
                        if (radio.value === 'autre' && radio.name === 'etatPompe') {
                            document.getElementById('autreDescription').style.display = 'block';
                        } else if (radio.name === 'etatPompe') {
                            document.getElementById('autreDescription').style.display = 'none';
                        }
                    }
                });
            });

            // Gestion des options de diagnostic
            document.querySelectorAll('.diagnosis-option').forEach(item => {
                item.addEventListener('click', function() {
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        
                        // Retirer la classe selected de tous les éléments du même groupe
                        const groupName = radio.name;
                        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                            r.closest('.diagnosis-option').classList.remove('selected');
                        });
                        
                        // Ajouter la classe selected à l'élément actuel
                        this.classList.add('selected');
                    }
                });
            });

            // Définir la date par défaut
            document.getElementById('dateDiagnostic').valueAsDate = new Date();
            
            // Initialiser les aperçus de photos
            initPhotoPreview('plaque_photos', 'plaque_preview');
            initPhotoPreview('etat_photos', 'etat_preview');
            initPhotoPreview('mesures_photos', 'mesures_preview');
            initPhotoPreview('diagnostic_photos', 'diagnostic_preview');
            initPhotoPreview('final_photos', 'final_preview');
            
            // Gestion du formulaire SIMPLIFIÉE avec DEBUG
            const form = document.getElementById('diagnosticReportForm');
            if (form) {
                console.log('🔍 DEBUG: Formulaire trouvé:', form);
                console.log('🔍 DEBUG: Attribut enctype:', form.getAttribute('enctype'));
                
                form.addEventListener('submit', function(e) {
                    console.log('📧 DEBUG: Soumission du formulaire...');
                    
                    // DEBUG: Vérifier les fichiers avant soumission
                    console.log('🔍 DEBUG: Vérification des champs de fichiers:');
                    const photoFields = ['plaque_photos', 'etat_photos', 'mesures_photos', 'diagnostic_photos', 'final_photos'];
                    let totalFilesJS = 0;
                    photoFields.forEach(field => {
                        const input = document.getElementById(field);
                        if (input) {
                            console.log(`  - ${field}:`, input.files.length, 'fichier(s)');
                            totalFilesJS += input.files.length;
                            for (let i = 0; i < input.files.length; i++) {
                                console.log(`    Fichier ${i+1}:`, input.files[i].name, input.files[i].size, 'bytes');
                            }
                        } else {
                            console.log(`  - ${field}: CHAMP NON TROUVÉ`);
                        }
                    });
                    
                    console.log('📊 DEBUG: TOTAL fichiers JavaScript avant soumission:', totalFilesJS);
                    
                    // CORRECTION: Transférer les fichiers des champs visibles vers les champs cachés du formulaire
                    console.log('🔄 DEBUG: Transfert des fichiers vers le formulaire d\'envoi...');
                    photoFields.forEach(field => {
                        const sourceInput = document.getElementById(field);
                        const targetInput = document.getElementById('form_' + field);
                        
                        if (sourceInput && targetInput && sourceInput.files.length > 0) {
                            // Créer une nouvelle FileList avec les fichiers source
                            const dt = new DataTransfer();
                            for (let i = 0; i < sourceInput.files.length; i++) {
                                dt.items.add(sourceInput.files[i]);
                            }
                            targetInput.files = dt.files;
                            console.log(`  ✅ ${field}: ${targetInput.files.length} fichier(s) transférés`);
                        } else {
                            console.log(`  ⚪ ${field}: aucun fichier à transférer`);
                        }
                    });
                    
                    // DEBUG: Vérifier FormData après transfert
                    const formData = new FormData(form);
                    let formDataFiles = 0;
                    console.log('🔍 DEBUG: Contenu FormData après transfert:');
                    for (const [key, value] of formData.entries()) {
                        if (value instanceof File) {
                            formDataFiles++;
                            console.log(`  FormData FILE - ${key}:`, value.name, value.size, 'bytes');
                        } else if (key.includes('photos')) {
                            console.log(`  FormData FIELD - ${key}:`, value);
                        }
                    }
                    console.log('📊 DEBUG: TOTAL fichiers dans FormData après transfert:', formDataFiles);
                    
                    // Préparer les données de base et ajouter les logs
                    const diagnosticData = collectFormData();
                    
                    // Capturer les logs de la console pour debug
                    diagnosticData.console_logs = window.debugLogs || [];
                    
                    document.getElementById('diagnostic_data').value = JSON.stringify(diagnosticData);
                    
                    console.log('✅ DEBUG: Données collectées:', diagnosticData);
                    console.log('📊 DEBUG: Total photos dans data:', diagnosticData.photos.total);
                    
                    // Laisser la soumission normale se faire
                    return true;
                });
            } else {
                console.error('❌ DEBUG: Formulaire NON TROUVÉ!');
            }
            
            console.log('✅ Initialisation terminée');
        });

        // Fonction pour initialiser l'aperçu des photos avec DEBUG
        function initPhotoPreview(inputId, previewId) {
            const input = document.getElementById(inputId);
            const preview = document.getElementById(previewId);
            
            console.log(`🔍 DEBUG: initPhotoPreview pour ${inputId}:`, input ? 'TROUVÉ' : 'NON TROUVÉ');
            
            if (input && preview) {
                input.addEventListener('change', function() {
                    const files = this.files;
                    console.log(`📸 DEBUG: ${inputId} - Fichiers changés:`, files.length, 'fichier(s)');
                    
                    let previewText = '';
                    
                    if (files.length > 0) {
                        const validFiles = [];
                        const maxSize = 10 * 1024 * 1024; // 10 Mo
                        
                        for (let i = 0; i < files.length; i++) {
                            const file = files[i];
                            console.log(`  - Fichier ${i+1}:`, file.name, file.type, file.size, 'bytes');
                            
                            // Vérification du type
                            if (file.type.match(/^image\/(jpeg|jpg|png|webp|heic)$/) || file.type === 'application/pdf') {
                                if (file.size <= maxSize) {
                                    validFiles.push(file);
                                    console.log(`    ✅ Fichier valide accepté`);
                                } else {
                                    console.log(`    ❌ Fichier trop volumineux`);
                                    alert(`Le fichier "${file.name}" est trop volumineux (max 10 Mo)`);
                                }
                            } else {
                                console.log(`    ❌ Type de fichier non supporté`);
                                alert(`Le fichier "${file.name}" n'est pas dans un format supporté`);
                            }
                        }
                        
                        console.log(`📸 DEBUG: ${inputId} - Fichiers valides:`, validFiles.length);
                        
                        if (validFiles.length > 0) {
                            previewText = `✅ ${validFiles.length} fichier(s) sélectionné(s) :`;
                            validFiles.forEach(file => {
                                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                                previewText += `<br>📎 ${file.name} (${sizeMB} Mo)`;
                            });
                        } else {
                            previewText = 'Aucun fichier valide sélectionné';
                        }
                    } else {
                        previewText = '';
                    }
                    
                    preview.innerHTML = previewText;
                });
            } else {
                console.error(`❌ DEBUG: Impossible d'initialiser ${inputId} - input:`, !!input, 'preview:', !!preview);
            }
        }

        // Fonction pour collecter les données du formulaire
        function collectFormData() {
            return {
                // Informations générales
                gmaoClient: document.getElementById('gmaoClient').value,
                numeroDossier: document.getElementById('numeroDossier').value,
                constructeur: document.getElementById('constructeur').value,
                type: document.getElementById('type').value,
                numeroSerie: document.getElementById('numeroSerie').value,
                typePompe: getRadioValue('typePompe'),
                prestation: getRadioValue('prestation'),
                
                // État
                etatGeneral: getRadioValue('etatGeneral'),
                etatPompe: getRadioValue('etatPompe'),
                autreDetail: document.getElementById('autreDetail').value,
                dateDiagnostic: document.getElementById('dateDiagnostic').value,
                technicienNom: document.getElementById('technicienNom').value,
                
                // Mesures techniques
                mesures: {
                    depressionEntree: document.getElementById('depressionEntree').value,
                    depressionSortie: document.getElementById('depressionSortie').value,
                    pressionEntree: document.getElementById('pressionEntree').value,
                    pressionSortie: document.getElementById('pressionSortie').value,
                    tempAspiration: document.getElementById('tempAspiration').value,
                    tempCorpsPompe: document.getElementById('tempCorpsPompe').value,
                    tempEchappement: document.getElementById('tempEchappement').value,
                    amperage: document.getElementById('amperage').value,
                    dureeEssai: document.getElementById('dureeEssai').value
                },
                
                // Diagnostic composants
                diagnostic: {
                    flasqueACC: getRadioValue('flasqueACC'),
                    flasqueOPACC: getRadioValue('flasqueOPACC'),
                    corpsPompe: getRadioValue('corpsPompe'),
                    deshuilleur: getRadioValue('deshuilleur'),
                    palettes: getRadioValue('palettes'),
                    voyantHuile: getRadioValue('voyantHuile')
                },
                
                // Analyse
                observations: document.getElementById('observations').value,
                analyse: document.getElementById('analyse').value,
                autresCommande: document.getElementById('autresCommande').value,
                
                // Temps
                temps: {
                    test: document.getElementById('tempsTest').value,
                    remontage: document.getElementById('tempsRemontage').value,
                    testFinal: document.getElementById('tempsTestFinal').value
                },
                
                // Commentaires techniques
                commentairesTechniques: document.getElementById('commentairesTechniques').value,
                
                // Photos avec DEBUG
                photos: {
                    plaque: document.getElementById('plaque_photos').files.length,
                    etat: document.getElementById('etat_photos').files.length,
                    mesures: document.getElementById('mesures_photos').files.length,
                    diagnostic: document.getElementById('diagnostic_photos').files.length,
                    final: document.getElementById('final_photos').files.length,
                    total: (() => {
                        const plaqueCount = document.getElementById('plaque_photos').files.length;
                        const etatCount = document.getElementById('etat_photos').files.length;
                        const mesuresCount = document.getElementById('mesures_photos').files.length;
                        const diagnosticCount = document.getElementById('diagnostic_photos').files.length;
                        const finalCount = document.getElementById('final_photos').files.length;
                        const total = plaqueCount + etatCount + mesuresCount + diagnosticCount + finalCount;
                        
                        console.log(`📊 DEBUG: Comptage photos dans collectFormData:`);
                        console.log(`  - plaque: ${plaqueCount}`);
                        console.log(`  - etat: ${etatCount}`);
                        console.log(`  - mesures: ${mesuresCount}`);
                        console.log(`  - diagnostic: ${diagnosticCount}`);
                        console.log(`  - final: ${finalCount}`);
                        console.log(`  - TOTAL: ${total}`);
                        
                        return total;
                    })()
                },
                
                // Timestamp
                dateGeneration: new Date().toISOString()
            };
        }

        function updateStepIndicator() {
            const indicator = document.getElementById('stepIndicator');
            indicator.innerHTML = '';
            
            for (let i = 1; i <= totalSteps; i++) {
                const dot = document.createElement('div');
                dot.className = `step-dot ${i <= currentStep ? 'active' : ''}`;
                indicator.appendChild(dot);
            }
        }

        function updateProgress() {
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';
        }

        function changeStep(direction) {
            if (direction === 1 && currentStep < totalSteps) {
                if (validateCurrentStep()) {
                    currentStep++;
                }
            } else if (direction === -1 && currentStep > 1) {
                currentStep--;
            }

            // Masquer toutes les étapes
            document.querySelectorAll('.step').forEach(step => {
                step.classList.remove('active');
            });

            // Afficher l'étape actuelle
            document.getElementById(`step${currentStep}`).classList.add('active');

            // Mettre à jour l'interface
            updateStepIndicator();
            updateProgress();
            updateButtons();

            // Générer le résumé si on arrive à la dernière étape
            if (currentStep === totalSteps) {
                generateSummary();
            }
        }

        function validateCurrentStep() {
            const currentStepElement = document.getElementById(`step${currentStep}`);
            const requiredFields = currentStepElement.querySelectorAll('input[required]');
            
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    alert(`Veuillez remplir le champ "${field.previousElementSibling.textContent}"`);
                    field.focus();
                    return false;
                }
            }
            
            return true;
        }

        function updateButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
            nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
        }

        function generateSummary() {
            // Résumé général
            const summaryGeneral = document.getElementById('summaryGeneral');
            summaryGeneral.innerHTML = `
                <div class="summary-item"><span>GMAO Client:</span> <span>${document.getElementById('gmaoClient').value}</span></div>
                <div class="summary-item"><span>N° Dossier:</span> <span>${document.getElementById('numeroDossier').value}</span></div>
                <div class="summary-item"><span>Constructeur:</span> <span>${document.getElementById('constructeur').value}</span></div>
                <div class="summary-item"><span>Type:</span> <span>${document.getElementById('type').value}</span></div>
                <div class="summary-item"><span>N° Série:</span> <span>${document.getElementById('numeroSerie').value}</span></div>
                <div class="summary-item"><span>Type Pompe:</span> <span>${getRadioValue('typePompe')}</span></div>
                <div class="summary-item"><span>Prestation:</span> <span>${getRadioValue('prestation')}</span></div>
            `;

            // Résumé état
            const summaryEtat = document.getElementById('summaryEtat');
            summaryEtat.innerHTML = `
                <div class="summary-item"><span>État général:</span> <span>${getRadioValue('etatGeneral')}</span></div>
                <div class="summary-item"><span>État pompe:</span> <span>${getRadioValue('etatPompe')}</span></div>
                <div class="summary-item"><span>Date diagnostic:</span> <span>${document.getElementById('dateDiagnostic').value}</span></div>
                <div class="summary-item"><span>Technicien:</span> <span>${document.getElementById('technicienNom').value}</span></div>
            `;

            // Résumé mesures
            const summaryMesures = document.getElementById('summaryMesures');
            summaryMesures.innerHTML = `
                <div class="summary-item"><span>Dépression (entrée/sortie):</span> <span>${document.getElementById('depressionEntree').value || 'N/A'} / ${document.getElementById('depressionSortie').value || 'N/A'} mbars</span></div>
                <div class="summary-item"><span>Pression (entrée/sortie):</span> <span>${document.getElementById('pressionEntree').value || 'N/A'} / ${document.getElementById('pressionSortie').value || 'N/A'} mbars</span></div>
                <div class="summary-item"><span>Température aspiration:</span> <span>${document.getElementById('tempAspiration').value || 'N/A'} °C</span></div>
                <div class="summary-item"><span>Ampérage:</span> <span>${document.getElementById('amperage').value || 'N/A'} A</span></div>
            `;

            // Composants à réparer
            const composantsAReparer = [];
            document.querySelectorAll('input[name^="flasque"], input[name="corpsPompe"], input[name="deshuilleur"], input[name="palettes"]').forEach(input => {
                if (input.checked && (input.value === 'hs' || input.value === 'rebaguage' || input.value === 'kit')) {
                    composantsAReparer.push(`${input.name}: ${input.value}`);
                }
            });

            const summaryComposants = document.getElementById('summaryComposants');
            summaryComposants.innerHTML = composantsAReparer.length > 0 
                ? composantsAReparer.map(c => `<div class="summary-item"><span>${c}</span></div>`).join('')
                : '<div class="summary-item"><span>Aucun composant nécessitant une intervention</span></div>';

            // Temps d'intervention
            const summaryTemps = document.getElementById('summaryTemps');
            const tempsTotal = (parseFloat(document.getElementById('tempsTest').value) || 0) + 
                              (parseFloat(document.getElementById('tempsRemontage').value) || 0) + 
                              (parseFloat(document.getElementById('tempsTestFinal').value) || 0);
            summaryTemps.innerHTML = `
                <div class="summary-item"><span>Test et démontage:</span> <span>${document.getElementById('tempsTest').value || '0'} h</span></div>
                <div class="summary-item"><span>Remontage:</span> <span>${document.getElementById('tempsRemontage').value || '0'} h</span></div>
                <div class="summary-item"><span>Test final:</span> <span>${document.getElementById('tempsTestFinal').value || '0'} h</span></div>
                <div class="summary-item"><strong><span>Total:</span> <span>${tempsTotal} h</span></strong></div>
            `;

            // Résumé des photos
            const plaquePhotos = document.getElementById('plaque_photos').files.length;
            const etatPhotos = document.getElementById('etat_photos').files.length;
            const mesuresPhotos = document.getElementById('mesures_photos').files.length;
            const diagnosticPhotos = document.getElementById('diagnostic_photos').files.length;
            const finalPhotos = document.getElementById('final_photos').files.length;
            const totalPhotos = plaquePhotos + etatPhotos + mesuresPhotos + diagnosticPhotos + finalPhotos;
            
            const summaryPhotos = document.getElementById('summaryPhotos');
            summaryPhotos.innerHTML = `
                <div class="summary-item"><span>Plaque signalétique:</span> <span>${plaquePhotos} photo(s)</span></div>
                <div class="summary-item"><span>État initial:</span> <span>${etatPhotos} photo(s)</span></div>
                <div class="summary-item"><span>Mesures techniques:</span> <span>${mesuresPhotos} photo(s)</span></div>
                <div class="summary-item"><span>Diagnostic composants:</span> <span>${diagnosticPhotos} photo(s)</span></div>
                <div class="summary-item"><span>Photos finales:</span> <span>${finalPhotos} photo(s)</span></div>
                <div class="summary-item"><strong><span>Total:</span> <span>${totalPhotos} photo(s)</span></strong></div>
            `;

            // Alertes
            const alertes = [];
            if (getRadioValue('etatGeneral') === 'hs') {
                alertes.push('Pompe hors service - intervention urgente requise');
            }
            if (composantsAReparer.length > 3) {
                alertes.push('Nombreux composants à remplacer - évaluer la rentabilité de la réparation');
            }
            if (tempsTotal > 8) {
                alertes.push('Temps d\'intervention élevé - prévoir planning approprié');
            }

            const alertesDiv = document.getElementById('alertes');
            const alertesList = document.getElementById('alertesList');
            if (alertes.length > 0) {
                alertesDiv.style.display = 'block';
                alertesList.innerHTML = alertes.map(a => `<div>• ${a}</div>`).join('');
            } else {
                alertesDiv.style.display = 'none';
            }
        }

        function getRadioValue(name) {
            const radio = document.querySelector(`input[name="${name}"]:checked`);
            return radio ? radio.value : 'Non renseigné';
        }

        // Initialiser les boutons
        updateButtons();
    </script>

    <?php
    // Code PHP SIMPLIFIÉ pour traiter l'envoi du rapport
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cenov_submit'])) {
        
        echo '<div style="background:#f0f0f0;padding:20px;margin:20px;border-radius:8px;">';
        echo '<h3>🔍 DEBUG - Données reçues</h3>';
        
        // DEBUG COMPLET : Afficher $_FILES
        echo '<h4>📁 DEBUG $_FILES:</h4>';
        echo '<pre style="background:#fff;padding:10px;border-radius:4px;font-size:12px;max-height:300px;overflow:auto;">';
        print_r($_FILES);
        echo '</pre>';
        
        // DEBUG : Afficher $_POST (sans données sensibles)
        echo '<h4>📝 DEBUG $_POST keys:</h4>';
        echo '<pre style="background:#fff;padding:10px;border-radius:4px;font-size:12px;">';
        foreach ($_POST as $key => $value) {
            if (is_string($value) && strlen($value) > 100) {
                echo $key . ' => [LONG DATA - ' . strlen($value) . ' chars]' . "\n";
            } else {
                echo $key . ' => ' . (is_array($value) ? '[ARRAY]' : $value) . "\n";
            }
        }
        echo '</pre>';
        
        // LOGS JavaScript transférés via diagnostic_data
        if (isset($_POST['diagnostic_data'])) {
            $diagnostic_data = json_decode(stripslashes($_POST['diagnostic_data']), true);
            if ($diagnostic_data && isset($diagnostic_data['console_logs'])) {
                echo '<h4>🖥️ DEBUG Console Logs JavaScript:</h4>';
                echo '<pre style="background:#fff;padding:10px;border-radius:4px;font-size:12px;max-height:200px;overflow:auto;">';
                foreach ($diagnostic_data['console_logs'] as $log) {
                    echo htmlspecialchars($log) . "\n";
                }
                echo '</pre>';
            }
        }
        
        // Vérification honeypot
        if (!empty($_POST['cenov_website'])) {
            echo '<div class="success-message">✅ Votre rapport a été envoyé avec succès.</div>';
            exit;
        }
        
        // Récupération des données
        $email_destinataire = filter_var($_POST['cenov_email'], FILTER_SANITIZE_EMAIL);
        $email_copie = isset($_POST['cenov_email_copie']) ? filter_var($_POST['cenov_email_copie'], FILTER_SANITIZE_EMAIL) : '';
        $envoyeur_nom = htmlspecialchars($_POST['cenov_prenom']);
        $envoyeur_telephone = htmlspecialchars($_POST['cenov_telephone']);
        $message_accompagnement = htmlspecialchars($_POST['cenov_message']);
        $diagnostic_data = isset($_POST['diagnostic_data']) ? json_decode(stripslashes($_POST['diagnostic_data']), true) : [];
        
        echo '<p>📧 Email destinataire: ' . $email_destinataire . '</p>';
        echo '<p>👤 Envoyeur: ' . $envoyeur_nom . '</p>';
        echo '<p>📊 Données diagnostic: ' . (empty($diagnostic_data) ? 'VIDES' : 'OK') . '</p>';
        
        // Compter les photos
        $photoCount = 0;
        $photoFields = array('plaque_photos', 'etat_photos', 'mesures_photos', 'diagnostic_photos', 'final_photos');
        foreach ($photoFields as $field) {
            if (isset($_FILES[$field]) && !empty($_FILES[$field]['name'][0])) {
                $photoCount += count(array_filter($_FILES[$field]['name']));
            }
        }
        echo '<p>📸 Photos jointes: ' . $photoCount . ' fichier(s)</p>';
        
        // Validation
        if (empty($email_destinataire) || empty($envoyeur_nom)) {
            echo '<div class="error-message">❌ Champs obligatoires manquants</div>';
        } else {
            // Construction de l'email
            $subject = 'Rapport de Diagnostic Pompe à Palettes - ' . date('Y-m-d H:i:s');
            
            $content = "=== RAPPORT DE DIAGNOSTIC POMPE À PALETTES ===\r\n\r\n";
            
            if (!empty($diagnostic_data)) {
                $content .= "--- INFORMATIONS GÉNÉRALES ---\r\n";
                $content .= "GMAO Client: " . ($diagnostic_data['gmaoClient'] ?? 'N/A') . "\r\n";
                $content .= "N° de dossier: " . ($diagnostic_data['numeroDossier'] ?? 'N/A') . "\r\n";
                $content .= "Constructeur: " . ($diagnostic_data['constructeur'] ?? 'N/A') . "\r\n";
                $content .= "Type: " . ($diagnostic_data['type'] ?? 'N/A') . "\r\n";
                $content .= "Numéro de série: " . ($diagnostic_data['numeroSerie'] ?? 'N/A') . "\r\n\r\n";
                
                $content .= "--- ÉTAT DE LA POMPE ---\r\n";
                $content .= "État général: " . ($diagnostic_data['etatGeneral'] ?? 'N/A') . "\r\n";
                $content .= "État de la pompe: " . ($diagnostic_data['etatPompe'] ?? 'N/A') . "\r\n";
                $content .= "Date du diagnostic: " . ($diagnostic_data['dateDiagnostic'] ?? 'N/A') . "\r\n";
                $content .= "Technicien: " . ($diagnostic_data['technicienNom'] ?? 'N/A') . "\r\n\r\n";
            }
            
            if (!empty($message_accompagnement)) {
                $content .= "--- MESSAGE D'ACCOMPAGNEMENT ---\r\n";
                $content .= $message_accompagnement . "\r\n\r\n";
            }
            
            $content .= "--- INFORMATIONS ENVOYEUR ---\r\n";
            $content .= "Nom: " . $envoyeur_nom . "\r\n";
            $content .= "Téléphone: " . $envoyeur_telephone . "\r\n";
            $content .= "Date de génération: " . date('d/m/Y H:i:s') . "\r\n";
            
            // Gestion des photos
            $attachments = array();
            $photoFields = array('plaque_photos', 'etat_photos', 'mesures_photos', 'diagnostic_photos', 'final_photos');
            $photoLabels = array(
                'plaque_photos' => 'Plaque signalétique',
                'etat_photos' => 'État initial',
                'mesures_photos' => 'Mesures techniques', 
                'diagnostic_photos' => 'Diagnostic composants',
                'final_photos' => 'Photos finales'
            );
            
            $totalPhotos = 0;
            $photosInfo = "\r\n--- PHOTOS JOINTES ---\r\n";
            
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/cenov_temp';
            
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }
            
            foreach ($photoFields as $fieldName) {
                if (isset($_FILES[$fieldName]) && !empty($_FILES[$fieldName]['name'][0])) {
                    $fieldPhotos = 0;
                    foreach ($_FILES[$fieldName]['name'] as $key => $name) {
                        if (empty($name)) continue;
                        
                        $file = array(
                            'name' => $_FILES[$fieldName]['name'][$key],
                            'type' => $_FILES[$fieldName]['type'][$key],
                            'tmp_name' => $_FILES[$fieldName]['tmp_name'][$key],
                            'error' => $_FILES[$fieldName]['error'][$key],
                            'size' => $_FILES[$fieldName]['size'][$key]
                        );
                        
                        // Vérifications de sécurité
                        if ($file['error'] !== UPLOAD_ERR_OK) continue;
                        
                        $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'image/heic', 'image/webp');
                        if (!in_array($file['type'], $allowed_types)) continue;
                        
                        $max_size = 10 * 1024 * 1024; // 10 Mo
                        if ($file['size'] > $max_size) continue;
                        
                        // CORRECTION : Déplacer le fichier dans un dossier temporaire permanent
                        $filename = sanitize_file_name($file['name']);
                        $filename = time() . '_' . $key . '_' . $fieldName . '_' . $filename;
                        $temp_file = $temp_dir . '/' . $filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $temp_file)) {
                            $attachments[] = $temp_file; // Utiliser le nouveau chemin permanent
                            $fieldPhotos++;
                            $totalPhotos++;
                        }
                    }
                    
                    if ($fieldPhotos > 0) {
                        $photosInfo .= $photoLabels[$fieldName] . ": " . $fieldPhotos . " photo(s)\r\n";
                    }
                }
            }
            
            if ($totalPhotos > 0) {
                $photosInfo .= "Total: " . $totalPhotos . " photo(s)\r\n";
                $content .= $photosInfo;
            } else {
                $content .= "\r\n--- AUCUNE PHOTO JOINTE ---\r\n";
            }
            
            // Headers
            $headers = [];
            if (function_exists('get_bloginfo') && function_exists('get_option')) {
                $headers[] = 'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>';
            } else {
                $headers[] = 'From: Diagnostic Pompe <noreply@' . $_SERVER['HTTP_HOST'] . '>';
            }
            
            if (!empty($email_copie)) {
                $headers[] = 'Cc: ' . $email_copie;
            }
            
            // Tentative d'envoi
            $sent = false;
            if (function_exists('wp_mail')) {
                echo '<p>🚀 Tentative d\'envoi avec wp_mail...</p>';
                if (!empty($attachments)) {
                    echo '<p>📎 Pièces jointes: ' . count($attachments) . ' fichier(s)</p>';
                }
                $sent = wp_mail($email_destinataire, $subject, $content, $headers, $attachments);
            } else {
                echo '<p>📧 Tentative d\'envoi avec mail() (sans pièces jointes)...</p>';
                $sent = mail($email_destinataire, $subject, $content, implode("\r\n", $headers));
            }
            
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    if (file_exists($file)) {
                        @unlink($file);
                    }
                }
            }
            
            if ($sent) {
                echo '<div class="success-message">✅ Rapport de diagnostic envoyé avec succès à ' . $email_destinataire . '</div>';
                if ($totalPhotos > 0) {
                    echo '<p style="color: green;">📸 ' . $totalPhotos . ' photo(s) incluse(s) dans l\'email</p>';
                }
            } else {
                echo '<div class="error-message">❌ Erreur lors de l\'envoi du rapport.</div>';
                echo '<p>🔧 Vérifiez la configuration email de votre serveur</p>';
                if ($totalPhotos > 0) {
                    echo '<p style="color: orange;">📸 ' . $totalPhotos . ' photo(s) n\'ont pas pu être envoyées</p>';
                }
            }
        }
        
        echo '</div>';
    }
    ?>
</body>
</html>