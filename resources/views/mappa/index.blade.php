@extends('layouts.app')

@section('title', 'Segnalazioni Stradali - ' . config('city.name'))
@section('subtitle', 'Visualizza e segnala problemi sulla viabilità urbana')

@section('leaflet_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filter Box Container -->
        <div class="filter-box">
            <!-- Stats Bar -->
            <div class="stats-container">
                <div class="stat-item" onclick="filterByStatus('')">
                    <div class="stat-number" id="pendingCount">{{ $pendingCount ?? 0 }}</div>
                    <div class="stat-label">In sospeso</div>
                </div>
                <div class="stat-item" onclick="filterByStatus('approved')">
                    <div class="stat-number" id="approvedCount">{{ $approvedCount ?? 0 }}</div>
                    <div class="stat-label">Approvato</div>
                </div>
                <div class="stat-item" onclick="filterByStatus('rejected')">
                    <div class="stat-number" id="rejectedCount">{{ $rejectedCount ?? 0 }}</div>
                    <div class="stat-label">Rifiutato</div>
                </div>
            </div>
            
            <!-- Filter Panel -->
            <div class="filters-panel">
                <div class="filters-panel-inner">
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
            </div>
            
        <!-- Map Layout -->
        <div class="map-wrapper">
            <!-- Filter Box Container with How It Works link -->
            <div class="filter-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><strong>Legenda e informazioni</strong></h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#howItWorksModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        Come funziona
                    </button>
                </div>
                <div class="row align-items-start g-3">
                    <div class="col-md-8">
                        <div class="legend">
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
                    <div class="col-md-4">
                        <div class="info-card h-100">
                            <h6 class="mb-2"><strong>Informazioni</strong></h6>
                            <p class="mb-1"><strong>In sospeso:</strong> Segnalazioni in attesa di approvazione</p>
                            <p class="mb-1"><strong>Approvato:</strong> Segnalazioni confermate e visibili</p>
                            <p class="mb-0"><strong>Rifiutato:</strong> Segnalazioni non confermate</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>
    </div>
    
    @include('components.how-it-works-modal')
@endsection

@section('leaflet_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section('scripts')
<script src="{{ asset('js/mappa.js') }}"></script>
@endsection
