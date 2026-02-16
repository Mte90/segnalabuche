<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Segnala un Guasto - {{ config('city.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header-section {
            background: var(--primary-color);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-family: 'Cinzel', serif;
        }
        
        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .file-input-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-input-container:hover {
            border-color: var(--primary-color);
            background: #fff5f5;
        }
        
        .file-input-container .file-icon {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 0.75rem;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color) 0%, #c82333 100%);
            border: none;
            border-radius: 8px;
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5);
        }
        
        .geolocation-status {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .geolocation-status.ready {
            background: #d4edda;
            color: #155724;
        }
        
        .geolocation-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .geolocation-status.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .duplicate-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: none;
        }
        
        .duplicate-alert.show {
            display: block;
        }
        
        .duplicate-alert .alert-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .leaflet-container {
            height: 300px;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .info-card h6 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .info-card p {
            color: #6c757d;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <header class="header-section">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1>Segnala un Guasto</h1>
                    <p>Aiutaci a mantenere la città in condizioni sicure e pulite</p>
                </div>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#faqModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Come funziona?
                </button>
            </div>
        </div>
    </header>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Duplicati Alert -->
                <div id="duplicateAlert" class="duplicate-alert">
                    <div class="alert-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line><line x1="12" y1="8" x2="12" y2="16"></line></svg>
                        <span>Attenzione: Segnalazioni Simili Rilevate</span>
                    </div>
                    <div class="alert-body" id="duplicateText">
                        Sono state trovate <span id="duplicateCount">0</span> segnalazioni simili entro 100 metri da questa posizione. Verifica che la tua segnalazione non sia già presente.
                    </div>
                </div>
                
                <!-- Form Segnalazione -->
                <div class="section-card">
                    <form id="segnalazioneForm" action="/api/segnalazioni" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Honeypot Field -->
                        <div style="display: none;">
                            <input type="text" name="website" id="website" value="" autocomplete="off">
                        </div>
                        
                        <!-- Geolocalizzazione -->
                        <div class="geolocation-status pending" id="geolocationStatus">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span id="geolocationText">Posizione in fase di rilevamento...</span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="getLocationBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Aggiorna Posizione
                            </button>
                        </div>
                        
                        <!-- Coordinate (nascoste, popolate da JS) -->
                        <input type="hidden" name="lat" id="latInput" required>
                        <input type="hidden" name="lng" id="lngInput" required>
                        
                        <!-- Mappa Anteprima -->
                        <div id="mapPreview" class="leaflet-container" style="display: none;">
                            <div id="map"></div>
                        </div>
                        
                        <div class="form-section-title mb-4">Dati della Segnalazione</div>
                        
                        <!-- Tipo Guasto -->
                        <div class="mb-4">
                            <label for="tipo" class="form-label">Tipo di Guasto <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Seleziona il tipo di guasto</option>
                                <option value="perdita d'acqua">Perdita d'acqua</option>
                                <option value="tombino attappato">Tombino attappato</option>
                                <option value="buca stradale">Buca stradale</option>
                                <option value="illuminazione pubblica">Illuminazione pubblica</option>
                                <option value="altro">Altro</option>
                            </select>
                        </div>
                        
                        <!-- Descrizione -->
                        <div class="mb-4">
                            <label for="descrizione" class="form-label">Descrizione</label>
                            <textarea class="form-control" id="descrizione" name="descrizione" rows="4" placeholder="Descrivi in dettaglio il problema..."></textarea>
                            <div class="form-text text-end">
                                <span id="descrizioneCount">0</span>/1000 caratteri
                            </div>
                        </div>
                        
                        <!-- Foto -->
                        <div class="mb-4">
                            <label class="form-label">Foto (max 3, JPEG/PNG)</label>
                            <div class="file-input-container" id="fileDropZone">
                                <div class="file-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                </div>
                                <p class="mb-2"><strong>Trascina le foto qui</strong> o clicca per selezionarle</p>
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">Formato: JPEG, PNG • Max 8MB ciascuna</p>
                                <input type="file" id="foto" name="foto[]" class="d-none" accept="image/jpeg,image/png,image/jpg" multiple>
                            </div>
                            <div id="fileList" class="mt-3"></div>
                        </div>
                        
                        <!-- Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-submit" id="submitBtn">
                                <span id="submitText">Invia Segnalazione</span>
                            </button>
                            <p class="text-center text-muted small mt-3">
                                La tua segnalazione verrà revista entro 24 ore
                            </p>
                        </div>
                    </form>
                </div>
                
                <!-- Note informative -->
                <div class="info-card card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title" style="font-family: 'Cinzel', serif;">Come funziona</h5>
                        <hr>
                        <div class="mb-3">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Geolocalizzazione automatica</h6>
                                    <p class="mb-0 text-muted small">La tua posizione viene rilevata per facilitare l'individuazione del guasto</p>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line><line x1="12" y1="8" x2="12" y2="16"></line></svg>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Anonimato garantito</h6>
                                    <p class="mb-0 text-muted small">Non è richiesto alcun account per inviare una segnalazione</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Modal -->
    <div class="modal fade" id="faqModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Come Funziona il Portale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                1. Invia la Segnalazione
                            </h6>
                            <p>Seleziona il tipo di guasto (perdita d'acqua, tombino attappato, etc.), aggiungi una descrizione e fino a 3 foto. Il sistema geolocalizzerà automaticamente la tua posizione.</p>
                            <p class="small text-muted mb-0">Le foto devono essere in formato JPEG o PNG e non superare i 8MB ciascuna.</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line><line x1="12" y1="8" x2="12" y2="16"></line></svg>
                                2. Verifica Duplicati
                            </h6>
                            <p>Il sistema controlla automaticamente se ci sono segnalazioni simili entro 100 metri. Se trovi problemi già segnalati, considera di aggiungere ulteriori dettagli.</p>
                        </div>
                        <div class="col-12">
                            <h6 class="text-primary mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                3. Invio e Monitoraggio
                            </h6>
                            <p>Conferma la segnalazione e clicca su "Invia Segnalazione". Riceverai una conferma. Potrai monitorare lo stato della tua segnalazione sulla <a href="{{ route('mappa') }}" class="text-primary text-decoration-underline">mappa pubblica</a>.</p>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                <strong>Anonimato garantito:</strong> Non è richiesto alcun account per inviare una segnalazione. I dati vengono salvati solo per finalità amministrative.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <a href="{{ route('mappa') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Vai alla Mappa
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4 text-muted small">
        <div class="container">
            <p class="mb-2">
                <a href="{{ route('mappa') }}" class="text-primary text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Vai alla Mappa Segnalazioni
                </a>
            </p>
            <p>Segnala un Guasto</p>
            <p class="mb-0">&copy; {{ date('Y') }} Progetto personale. Tutti i diritti riservati.</p>
        </div>
    </footer>
    
    <!-- Leaflet JS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let map = null;
        let marker = null;
        let userPosition = null;
        const dupAlert = document.getElementById('duplicateAlert');
        let duplicatesFound = [];
        
        const rietiCenter = [42.4097, 12.8607];
        
        const latInput = document.getElementById('latInput');
        const lngInput = document.getElementById('lngInput');
        const geolocationStatus = document.getElementById('geolocationStatus');
        const geolocationText = document.getElementById('geolocationText');
        const getLocationBtn = document.getElementById('getLocationBtn');
        const fileDropZone = document.getElementById('fileDropZone');
        const fotoInput = document.getElementById('foto');
        const fileList = document.getElementById('fileList');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const mapPreview = document.getElementById('mapPreview');
        const mapContainer = document.getElementById('map');
        const descrizioneInput = document.getElementById('descrizione');
        const descrizioneCount = document.getElementById('descrizioneCount');
        
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });
        
        function setupEventListeners() {
            descrizioneInput.addEventListener('input', function() {
                descrizioneCount.textContent = this.value.length;
            });
            
            document.getElementById('website').addEventListener('input', function() {
                console.warn('Honeypot field was filled!');
            });
            
            getLocationBtn.addEventListener('click', function() {
                requestGeoLocation();
            });
            
            fileDropZone.addEventListener('click', function() {
                fotoInput.click();
            });
            
            fotoInput.addEventListener('change', function(e) {
                handleFiles(e.target.files);
            });
            
            fileDropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                fileDropZone.style.borderColor = 'var(--primary-color)';
                fileDropZone.style.background = '#fff5f5';
            });
            
            fileDropZone.addEventListener('dragleave', function(e) {
                e.preventDefault();
                fileDropZone.style.borderColor = '#dee2e6';
                fileDropZone.style.background = '#f8f9fa';
            });
            
            fileDropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                fileDropZone.style.borderColor = '#dee2e6';
                fileDropZone.style.background = '#f8f9fa';
                handleFiles(e.dataTransfer.files);
            });
            
            document.getElementById('segnalazioneForm').addEventListener('submit', function(e) {
                return validateForm(e);
            });
        }
        
        function requestGeoLocation() {
            if (!navigator.geolocation) {
                setGeoLocationStatus('error', 'Il browser non supporta la geolocalizzazione');
                return;
            }
            
            setGeoLocationStatus('pending', 'Richiesta posizione...');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    
                    latInput.value = userPosition.lat;
                    lngInput.value = userPosition.lng;
                    
                    setGeoLocationStatus('ready', 'Posizione rilevata con successo');
                    
                    if (!map) {
                        initMap();
                    }
                },
                function(error) {
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            setGeoLocationStatus('error', 'Posizione non concessa. Inserisci manualmente.');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            setGeoLocationStatus('error', 'Posizione non disponibile');
                            break;
                        case error.TIMEOUT:
                            setGeoLocationStatus('error', 'Richiesta posizione scaduta');
                            break;
                        default:
                            setGeoLocationStatus('error', 'Errore durante il rilevamento');
                    }
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
        
        function setGeoLocationStatus(status, message) {
            geolocationStatus.className = 'geolocation-status ' + status;
            
            let icon = '';
            switch(status) {
                case 'ready':
                    icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                    break;
                case 'error':
                    icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                    break;
                default:
                    icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
            }
            
            geolocationStatus.innerHTML = icon + ' <span id="geolocationText">' + message + '</span> ' +
                '<button type="button" class="btn btn-sm btn-outline-primary ms-auto" id="getLocationBtn">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>' +
                ' Aggiorna Posizione' +
                '</button>';
            
            document.getElementById('getLocationBtn').addEventListener('click', function() {
                requestGeoLocation();
            });
            
            if (status === 'ready' && !map) {
                initMap();
            }
        }
        
        function initMap() {
            if (!userPosition) return;
            
            map = L.map('map').setView([userPosition.lat, userPosition.lng], 16);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            marker = L.marker([userPosition.lat, userPosition.lng], {
                draggable: true
            }).addTo(map);
            
            marker.on('dragend', function() {
                userPosition = {
                    lat: marker.getLatLng().lat,
                    lng: marker.getLatLng().lng
                };
                latInput.value = userPosition.lat;
                lngInput.value = userPosition.lng;
            });
            
            loadNearbyReports();
        }
        
        function loadNearbyReports() {
            const nearbyReports = [
                { lat: 42.409, lng: 12.861, tipo: "Perdita d'acqua", descrizione: "Viale Europa, fronte civico 45" },
                { lat: 42.410, lng: 12.859, tipo: "Buca stradale", descrizione: "Via San Pietro, prima curva" }
            ];
            
            if (nearbyReports.length > 0) {
                duplicatesFound = nearbyReports;
                document.getElementById('duplicateCount').textContent = nearbyReports.length;
                dupAlert.classList.add('show');
            }
        }
        
        function handleFiles(files) {
            fileList.innerHTML = '';
            
            const validFiles = [];
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/jpg')) {
                    alert('File "' + file.name + '" non è un\'immagine valida (JPEG/PNG)');
                    continue;
                }
                
                if (file.size > 8 * 1024 * 1024) {
                    alert('File "' + file.name + ' supera il limite di 8MB');
                    continue;
                }
                
                validFiles.push(file);
            }
            
            if (validFiles.length > 3) {
                alert('Max 3 foto per segnalazione');
                validFiles.splice(3);
            }
            
            validFiles.forEach(function(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'alert alert-info d-flex align-items-center justify-content-between mb-1';
                    div.innerHTML = `
                        <div class="d-flex align-items-center">
                            <img src="${e.target.result}" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <div class="ms-2">
                                <div class="fw-bold">${file.name}</div>
                                <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" onclick="this.closest('div').remove()"></button>
                    `;
                    fileList.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
        
        function validateForm(e) {
            const honeypot = document.getElementById('website').value;
            if (honeypot !== '') {
                e.preventDefault();
                console.log('Honeypot triggered');
                return false;
            }
            
            if (!latInput.value || !lngInput.value) {
                e.preventDefault();
                alert('Si prega di consentire la geolocalizzazione per inviare la segnalazione');
                requestGeoLocation();
                return false;
            }
            
            const descrizione = descrizioneInput.value;
            if (descrizione.length > 1000) {
                e.preventDefault();
                alert('La descrizione non può superare i 1000 caratteri');
                return false;
            }
            
            if (fileList.children.length > 3) {
                e.preventDefault();
                alert('Max 3 foto per segnalazione');
                return false;
            }
            
            submitText.textContent = 'Invio in corso...';
            submitBtn.disabled = true;
            
            return true;
        }
    </script>
</body>
</html>
