@extends('layouts.app')

@section('title', 'Pannello Admin - Segnalazioni - ' . config('city.name'))
@section('subtitle', 'Approvazione e gestione segnalazioni')

@section('styles')
@endsection

@section('content')
    <div class="container py-5">
            <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item" onclick="filterAdminByStatus('')">
                <div class="stat-number" id="totalCount">{{ $count ?? 0 }}</div>
                <div class="stat-label">Totali</div>
            </div>
            <div class="stat-item" onclick="filterAdminByStatus('pending')">
                <div class="stat-number" id="pendingCount">{{ $pendingCount ?? 0 }}</div>
                <div class="stat-label">In Sospeso</div>
            </div>
            <div class="stat-item" onclick="filterAdminByStatus('approved')">
                <div class="stat-number" id="approvedCount">{{ $approvedCount ?? 0 }}</div>
                <div class="stat-label">Approvate</div>
            </div>
            <div class="stat-item" onclick="filterAdminByStatus('rejected')">
                <div class="stat-number" id="rejectedCount">{{ $rejectedCount ?? 0 }}</div>
                <div class="stat-label">Rifiutate</div>
            </div>
        </div>
        
        <!-- Filter Panel -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Stato</label>
                        <select class="form-select" id="adminFilterStatus">
                            <option value="all">Tutti</option>
                            <option value="pending">In sospeso</option>
                            <option value="approved">Approvato</option>
                            <option value="rejected">Rifiutato</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipologia</label>
                        <select class="form-select" id="adminFilterTipo">
                            <option value="">Tutti</option>
                            <option value="perdita d'acqua">Perdita d'acqua</option>
                            <option value="tombino attappato">Tombino attappato</option>
                            <option value="buca stradale">Buca stradale</option>
                            <option value="illuminazione pubblica">Illuminazione pubblica</option>
                            <option value="altro">Altro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" onclick="updateAdminMap()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            Filtra
                        </button>
                        <button class="btn btn-outline-secondary" onclick="resetAdminFilters()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.3"/></svg>
                            Reset
                        </button>
                        <a href="{{ route('admin.segnalazioni.export', ['status' => $currentStatus, 'tipo' => $currentTipo]) }}" class="btn btn-success">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Esporta CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Segnalazioni List -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span>{{ $currentStatus === '' ? 'Tutte le segnalazioni' : ($currentStatus === 'pending' ? 'Segnalazioni in sospeso' : ($currentStatus === 'approved' ? 'Segnalazioni approvate' : 'Segnalazioni rifiutate')) }}</span>
                    <span class="badge bg-primary">{{ $segnalazioni->count() ?? 0 }}</span>
                </div>
            </div>
            <div class="card-body p-0">
                @include('admin._list')
            </div>
        </div>
    </div>
    
    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalTitle">Conferma Azione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage">Sei sicuro di voler procedere?</p>
                    <input type="hidden" id="segnalazioneId">
                    <input type="hidden" id="newStatus">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-confirm-approve" id="confirmApproveBtn" style="display: none;">Approva</button>
                    <button type="button" class="btn btn-confirm-reject" id="confirmRejectBtn" style="display: none;">Rifiuta</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conferma Eliminazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p>Sei sicuro di voler eliminare questa segnalazione? L'azione non può essere annullata.</p>
                    <input type="hidden" id="deleteSegnalazioneId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-confirm-reject" id="confirmDeleteBtn">Elimina</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Photos Modal -->
    <div class="modal fade" id="photosModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="photosModalTitle">Foto Segnalazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body" id="photosModalBody">
                    <!-- Photos will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Segnalazione Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica Segnalazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipologia</label>
                                <select class="form-select" id="edit_tipo" name="tipo" required>
                                    <option value="">Seleziona tipologia</option>
                                    <option value="perdita d'acqua">Perdita d'acqua</option>
                                    <option value="tombino attappato">Tombino attappato</option>
                                    <option value="buca stradale">Buca stradale</option>
                                    <option value="illuminazione pubblica">Illuminazione pubblica</option>
                                    <option value="altro">Altro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stato</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="pending">In sospeso</option>
                                    <option value="approved">Approvato</option>
                                    <option value="rejected">Rifiutato</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrizione</label>
                            <textarea class="form-control" id="edit_descrizione" name="descrizione" rows="3" maxlength="1000"></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Latitudine</label>
                                <input type="number" class="form-control" id="edit_lat" name="lat" step="0.000001" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitudine</label>
                                <input type="number" class="form-control" id="edit_lng" name="lng" step="0.000001" required>
                            </div>
                        </div>
                        <div id="editPhotosContainer" class="mb-3">
                            <label class="form-label">Foto</label>
                            <div id="editPhotosList" class="row g-2"></div>
                        </div>
                        <div id="editMapContainer" class="mb-3">
                            <label class="form-label">Posizione</label>
                            <div id="editMap" style="height: 300px; border-radius: 4px; overflow: hidden;"></div>
                        </div>
                        <input type="hidden" id="edit_id" name="id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-confirm-approve" id="saveEditBtn">Salva Modifiche</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/admin.js') }}"></script>
@endsection
