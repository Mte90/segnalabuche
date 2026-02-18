@extends('layouts.app')

@section('title', 'Segnala un Guasto - ' . config('city.name'))
@section('subtitle', 'Aiutaci a mantenere la città in condizioni sicure e pulite')

@section('leaflet_css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
    <div class="container py-5">
        <!-- Stats Box (Filter Box simplified) -->
        <div class="filter-box mb-4">
            <div class="stats-container">
                <div class="stat-item" onclick="window.location.href='/mappa'">
                    <div class="stat-number" id="pendingCount">{{ $pendingCount ?? 0 }}</div>
                    <div class="stat-label">In sospeso</div>
                </div>
                <div class="stat-item" onclick="window.location.href='/mappa'">
                    <div class="stat-number" id="approvedCount">{{ $approvedCount ?? 0 }}</div>
                    <div class="stat-label">Approvato</div>
                </div>
                <div class="stat-item" onclick="window.location.href='/mappa'">
                    <div class="stat-number" id="rejectedCount">{{ $rejectedCount ?? 0 }}</div>
                    <div class="stat-label">Rifiutato</div>
                </div>
            </div>
        </div>
        
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
                        <div class="d-none">
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
                        <div id="mapPreview" class="leaflet-container d-none">
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
                                <p class="text-muted mb-0 small">Formato: JPEG, PNG • Max 8MB ciascuna</p>
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
            </div>
        </div>
    </div>
    
    @include('components.how-it-works-modal')
@endsection

@section('leaflet_js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection

@section("scripts")
<script src="{{ asset('js/create.js') }}"></script>
@endsection
