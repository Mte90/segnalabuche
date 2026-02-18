@extends('layouts.app')

@section('title', 'Segnalazioni Stradali - ' . config('city.name'))
@section('subtitle', 'Visualizza e segnala problemi sulla viabilità urbana')

@section('leaflet_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('styles')
<style>
    :root {
        --primary-color: #dc3545;
        --secondary-color: #6c757d;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
    }
    
    .map-wrapper {
        max-width: 80%;
        margin: 0 auto;
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
    
    .stat-item {
        cursor: pointer;
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
@endsection

@section('content')
    <div class="container-fluid">
        <div class="filters-panel">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="filter-label">Stato</label>
                    <select class="form-select" id="filterStatus">
                        <option value="">Tutti</option>
                        <option value="pending">In sospeso</option>
                        <option value="approved">Approvato</option>
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
                
                <div class="col-md-6 text-start">
                    <button class="btn btn-sm btn-outline-success" id="manualPositionToggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Imposta Posizione Manuale
                    </button>
                </div>
            </div>
        </div>
        
    <!-- Map Layout -->
        <div class="map-wrapper">
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
            
            <div class="map-container">
                <div id="map"></div>
            </div>
            
            <div class="container mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5>Informazioni</h5>
                            <p><strong>In sospeso:</strong> Segnalazioni in attesa di approvazione</p>
                            <p><strong>Approvato:</strong> Segnalazioni confermate e visibili</p>
                            <p><strong>Rifiutato:</strong> Segnalazioni non confermate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('leaflet_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('scripts')
<script>
    let map = null;
    let markersLayer = null;
    
    const rietiCenter = [42.4097, 12.8607];
    
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        updateMap();
        updateStats();
        setupManualPosition();
    });
    
    function setupManualPosition() {
        const toggleBtn = document.getElementById('manualPositionToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                toggleManualPositionMode();
            });
        }
    }
    
    let manualPositionMode = false;
    
    function toggleManualPositionMode() {
        if (!map) {
            alert('Caricare la mappa prima di impostare la posizione manuale');
            return;
        }
        
        manualPositionMode = !manualPositionMode;
        
        if (manualPositionMode) {
            document.getElementById('manualPositionToggle').classList.add('active');
            map.on('click', onMapClick);
            alert('Clicca sulla mappa per impostare la posizione. Il punto verrà evidenziato.');
        } else {
            document.getElementById('manualPositionToggle').classList.remove('active');
            map.off('click', onMapClick);
            alert('Modalità posizione manuale disattivata');
        }
    }
    
    function onMapClick(e) {
        const lat = e.latlng.lat.toFixed(6);
        const lng = e.latlng.lng.toFixed(6);
        
        alert('Posizione selezionata:\nLatitudine: ' + lat + '\nLongitudine: ' + lng);
        // Add visual marker for the selected position
        L.marker([e.latlng.lat, e.latlng.lng], {
            draggable: true
        }).addTo(map);
    }
    
    function updateStats() {
        document.getElementById('pendingCount').textContent = @js($pendingCount ?? 0);
        document.getElementById('approvedCount').textContent = @js($approvedCount ?? 0);
        document.getElementById('rejectedCount').textContent = @js($rejectedCount ?? 0);
        document.getElementById('withPhotosCount').textContent = @js($withPhotosCount ?? 0);
    }
</script>
<script src="{{ asset('js/mappa.js') }}"></script>
@endsection
