<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mappa Segnalazioni - {{ config('city.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #dc3545;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #c82333 100%);
            color: white;
            padding: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .map-container {
            flex: 1;
            padding: 0;
            position: relative;
        }
        
        #map {
            height: calc(100vh - 180px);
            width: 100%;
            border-radius: 0;
        }
        
        .filters-panel {
            background: white;
            padding: 1.25rem;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
            margin-bottom: 1rem;
        }
        
        .filter-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: var(--warning-color);
            color: #856404;
        }
        
        .status-approved {
            background: var(--success-color);
            color: white;
        }
        
        .status-rejected {
            background: var(--secondary-color);
            color: white;
        }
        
        .marker-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .info-card h5 {
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }
        
        .legend {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            color: #495057;
        }
        
        /* Stats Bar */
        .stats-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }
        
        .stats-container .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stats-container .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stats-container .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .stats-container .stat-item:hover .stat-number {
            color: #c82333;
        }
        
        .info-card p {
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .info-card .badge {
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        .legend {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }
        
        .color-pending { background: var(--warning-color); }
        .color-approved { background: var(--success-color); }
        .color-rejected { background: var(--secondary-color); }
    </style>
</head>
<body>
     <header class="header-section">
         <div class="container">
             <div class="d-flex justify-content-between align-items-center mb-3">
                 <div>
                     <h1 id="cityTitle">Segnalazioni Stradali</h1>
                     <p>Visualizza e segnala problemi sulla viabilità urbana</p>
                 </div>
             </div>
              <div class="text-center mt-3">
                  <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#faqModal">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                      Come funziona?
                  </button>
              </div>
          </div>
          <!-- Stats Bar (clickable to filter) -->
          <div class="container py-2 mt-3">
              <div class="stats-container">
                  <div class="stat-item" onclick="filterByStatus('pending')">
                      <div class="stat-number" id="pendingCount">0</div>
                      <div class="stat-label">In sospeso</div>
                  </div>
                  <div class="stat-item" onclick="filterByStatus('approved')">
                      <div class="stat-number" id="approvedCount">0</div>
                      <div class="stat-label">Approvato</div>
                  </div>
                  <div class="stat-item" onclick="filterByStatus('rejected')">
                      <div class="stat-number" id="rejectedCount">0</div>
                      <div class="stat-label">Rifiutato</div>
                  </div>
                  <div class="stat-item" onclick="filterWithPhotos()">
                      <div class="stat-number" id="withPhotosCount">0</div>
                      <div class="stat-label">Con foto</div>
                  </div>
              </div>
          </div>
      </header>
    
    <div class="container-fluid">
        <div class="filters-panel">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="filter-label">Stato</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Tutti</option>
                        <option value="pending">In sospeso</option>
                        <option value="approved">Approvato</option>
                        <option value="rejected">Rifiutato</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="filter-label">Tipologia</label>
                    <select class="form-select" id="filterTipo">
                        <option value="">Tutti</option>
                        <option value="perdita d'acqua">Perdita d'acqua</option>
                        <option value="tombino attappato">Tombino attappato</option>
                        <option value="buca stradale">Buca stradale</option>
                        <option value="illuminazione pubblica">Illuminazione pubblica</option>
                        <option value="altro">Altro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary" onclick="updateMap()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Aggiorna Mappa
                    </button>
                    <button class="btn btn-outline-secondary" onclick="resetFilters()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/></svg>
                        Reset Filtri
                    </button>
                </div>
            </div>
        </div>
        
        <div class="map-container">
            <div id="map"></div>
        </div>
        
        <div class="container mb-4" style="max-width: 80%; margin: 0 auto;">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h5>Informazioni</h5>
                        <p><strong>In sospeso:</strong> Segnalazioni in attesa di approvazione</p>
                        <p><strong>Approvato:</strong> Segnalazioni confermate e visibili</p>
                        <p><strong>Rifiutato:</strong> Segnalazioni non confermate</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="legend">
                        <h6 class="mb-3"><strong>Legenda</strong></h6>
                        <div class="legend-item">
                            <div class="legend-color color-pending"></div>
                            <span>In sospeso</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color color-approved"></div>
                            <span>Approvato</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color color-rejected"></div>
                            <span>Rifiutato</span>
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
                    <a href="{{ url('/') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        Segnala un Guasto
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4 text-muted small">
        <div class="container">
            <p>Mappa Segnalazioni</p>
            <p class="mb-0">&copy; {{ date('Y') }} Progetto personale. Tutti i diritti riservati.</p>
        </div>
    </footer>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let map = null;
        let markersLayer = null;
        
        const rietiCenter = [42.4097, 12.8607];
        
        // Set city name from config
        const cityTitle = document.getElementById('cityTitle');
        if (cityTitle) {
            cityTitle.textContent = 'Segnalazioni Stradali - {{ config("city.name") }}';
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            updateMap();
            updateStats();
        });
        
        function updateStats() {
            // Set the stats counts from PHP variables
            document.getElementById('pendingCount').textContent = {{ $pendingCount ?? 0 }};
            document.getElementById('approvedCount').textContent = {{ $approvedCount ?? 0 }};
            document.getElementById('rejectedCount').textContent = {{ $rejectedCount ?? 0 }};
            document.getElementById('withPhotosCount').textContent = {{ $withPhotosCount ?? 0 }};
        }
        
        function filterByStatus(status) {
            const select = document.getElementById('filterStatus');
            if (select) {
                select.value = status;
                updateMap();
            }
        }
        
        function filterWithPhotos() {
            // Placeholder function
            // Could implement a filter to show segnalazioni with photos only
        }
        
        function initMap() {
            map = L.map('map').setView(rietiCenter, 14);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            markersLayer = L.featureGroup().addTo(map);
        }
        
        function updateMap() {
            const status = document.getElementById('filterStatus').value;
            const tipo = document.getElementById('filterTipo').value;
            
            let url = '/api/segnalazioni?';
            const params = [];
            
            if (status) params.push('status=' + encodeURIComponent(status));
            if (tipo) params.push('tipo=' + encodeURIComponent(tipo));
            
            if (params.length > 0) {
                url += params.join('&');
            }
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    updateMarkers(data.data);
                })
                .catch(error => {
                    console.error('Errore nel caricamento:', error);
                    alert('Errore nel caricamento delle segnalazioni');
                });
        }
        
        function resetFilters() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterTipo').value = '';
            updateMap();
        }
        
        function updateMarkers(segnalazioni) {
            if (markersLayer) {
                markersLayer.clearLayers();
            }
            
            const icons = {
                pending: L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div class='marker-icon' style='background: var(--warning-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>?</div>",
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                }),
                approved: L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div class='marker-icon' style='background: var(--success-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>✔</div>",
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                }),
                rejected: L.divIcon({
                    className: 'custom-div-icon',
                    html: "<div class='marker-icon' style='background: var(--secondary-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>✖</div>",
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                })
            };
            
            segnalazioni.forEach(function(segnalazione) {
                const statusClass = segnalazione.status;
                const icon = icons[statusClass] || icons.pending;
                
                const marker = L.marker([segnalazione.lat, segnalazione.lng], { icon: icon });
                
                const tipoIcon = getTipoIcon(segnalazione.tipo);
                const badgeClass = getBadgeClass(segnalazione.status);
                const badgeText = getBadgeText(segnalazione.status);
                
                const popupContent = `
                    <div style="max-width: 300px;">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge ${badgeClass} me-auto">${badgeText}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            ${tipoIcon}
                            <strong class="ms-2" style="color: #495057;">${segnalazione.tipo}</strong>
                        </div>
                         ${segnalazione.descrizione ? `<p style="color: #495057; margin: 0 0 0.5rem 0;">${segnalazione.descrizione}</p>` : ''}
                         
                         ${segnalazione.foto && segnalazione.foto.length > 0 ? `
                             <div class="mb-2">
                                 <div class="d-flex flex-wrap gap-1">
                                     ${segnalazione.foto.slice(0, 3).map((foto, idx) => `
                                         <a href="${foto}" target="_blank" class="text-decoration-none">
                                             <img src="${foto}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" alt="Foto ${idx + 1}">
                                         </a>
                                     `).join('')}
                                 </div>
                                 ${segnalazione.foto.length > 3 ? `<p class="text-muted small">+${segnalazione.foto.length - 3} foto in più</p>` : ''}
                             </div>
                         ` : ''}
                         
                         <p style="color: #6c757d; margin: 0;">Data: ${new Date(segnalazione.created_at).toLocaleDateString('it-IT')}</p>
                     </div>
                 `;
                
                marker.bindPopup(popupContent);
                markersLayer.addLayer(marker);
            });
            
            if (segnalazioni.length > 0) {
                const bounds = markersLayer.getBounds();
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
        
        function getTipoIcon(tipo) {
            const iconsMap = {
                "perdita d'acqua": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>',
                "tombino attappato": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>',
                "buca stradale": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"></path><path d="M5 12v-3"></path><path d="M19 12v-3"></path><path d="M8 12v-5"></path><path d="M16 12v-5"></path></svg>',
                "illuminazione pubblica": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path><path d="M9 2h6"></path></svg>',
                "altro": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line><line x1="12" y1="8" x2="12" y2="16"></line></svg>'
            };
            return iconsMap[tipo] || '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>';
        }
        
        function getBadgeClass(status) {
            const classes = {
                pending: 'badge bg-warning text-dark',
                approved: 'badge bg-success',
                rejected: 'badge bg-secondary'
            };
            return classes[status] || classes.pending;
        }
        
        function getBadgeText(status) {
            const texts = {
                pending: 'In sospeso',
                approved: 'Approvato',
                rejected: 'Rifiutato'
            };
            return texts[status] || status;
        }
    </script>
</body>
</html>
