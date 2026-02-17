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
                            <option value="">Tutti</option>
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
                    <span>Segnalazioni in sospeso</span>
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
@endsection

@section('scripts')
<script>
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const photosModal = new bootstrap.Modal(document.getElementById('photosModal'));
    let segnalazioneId = null;
    let newStatus = null;
    let currentPhotos = [];
    
    function showPhotos(photosJson, tipo) {
        try {
            currentPhotos = typeof photosJson === 'string' ? JSON.parse(photosJson) : photosJson;
            
            const modalTitle = document.getElementById('photosModalTitle');
            if (modalTitle) {
                modalTitle.textContent = `Foto - ${tipo}`;
            }
            
            const modalBody = document.getElementById('photosModalBody');
            if (modalBody && currentPhotos.length > 0) {
                let html = '<div class="row g-3">';
                currentPhotos.forEach((photo, index) => {
                    html += `
                        <div class="col-md-6 col-lg-4">
                            <a href="${photo}" target="_blank" class="text-decoration-none">
                                <img src="${photo}" class="img-fluid rounded shadow" style="max-width: 100%; cursor: pointer;" alt="Foto ${index + 1}">
                                <p class="text-center small mt-2">Clicca per ingrandire</p>
                            </a>
                        </div>
                    `;
                });
                html += '</div>';
                modalBody.innerHTML = html;
                photosModal.show();
            } else {
                modalBody.innerHTML = '<p class="text-center text-muted">Nessuna foto disponibile</p>';
                photosModal.show();
            }
        } catch (e) {
            alert('Errore nel caricamento delle foto');
        }
    }
    
    function showDeleteModal(id) {
        document.getElementById('deleteSegnalazioneId').value = id;
        deleteModal.show();
    }
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const id = document.getElementById('deleteSegnalazioneId').value;
        deleteSegnalazione(id);
    });
    
    function deleteSegnalazione(id) {
        deleteModal.hide();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('Errore: Token CSRF non trovato');
            return;
        }
        
        fetch(`/admin/segnalazioni/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Segnalazione eliminata con successo');
            location.reload();
        })
        .catch(error => {
            console.error('Errore:', error);
            alert('Errore durante l\'eliminazione della segnalazione');
        });
    }
    
    function showConfirmModal(id, status) {
        segnalazioneId = id;
        newStatus = status;
        
        const title = status === 'approved' ? 'Approva Segnalazione' : 'Rifiuta Segnalazione';
        const message = status === 'approved' 
            ? 'Confermi di voler approvare questa segnalazione? Verrà inviata una notifica al comune.'
            : 'Confermi di voler rifiutare questa segnalazione? L\'azione non può essere annullata.';
        
        document.getElementById('confirmModalTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        
        document.getElementById('confirmApproveBtn').style.display = status === 'approved' ? 'block' : 'none';
        document.getElementById('confirmRejectBtn').style.display = status === 'rejected' ? 'block' : 'none';
        
        if (status !== 'approved' && status !== 'rejected') {
            document.getElementById('confirmApproveBtn').style.display = 'block';
            document.getElementById('confirmRejectBtn').style.display = 'block';
        }
        
        confirmModal.show();
    }
    
    document.getElementById('confirmApproveBtn').addEventListener('click', function() {
        updateStatus('approved');
    });
    
    document.getElementById('confirmRejectBtn').addEventListener('click', function() {
        updateStatus('rejected');
    });
    
    function updateStatus(status) {
        confirmModal.hide();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('Errore: Token CSRF non trovato');
            return;
        }
        
        fetch(`/admin/segnalazioni/${segnalazioneId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Stato aggiornato con successo');
            location.reload();
        })
        .catch(error => {
            console.error('Errore:', error);
            alert('Errore durante l\'aggiornamento dello stato');
        });
    }
    
    // Admin filter - now uses AJAX for list updates
    function updateAdminMap() {
        const status = document.getElementById('adminFilterStatus').value;
        const tipo = document.getElementById('adminFilterTipo').value;
        
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        if (tipo) params.append('tipo', tipo);
        params.append('ajax', '1');
        
        const url = `/admin/segnalazioni?${params.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                const cardBody = document.querySelector('.card-body');
                if (cardBody) {
                    cardBody.innerHTML = data.html;
                }
                
                // Update stats count badge
                const badge = document.querySelector('.card-header .badge');
                if (badge && data.count !== undefined) {
                    badge.textContent = data.count;
                }
            })
            .catch(error => {
                console.error('Errore nel caricamento AJAX:', error);
                alert('Errore nel caricamento delle segnalazioni');
            });
    }
    
    function resetAdminFilters() {
        window.location.href = '/admin/segnalazioni';
    }
    
    // Admin stats filter function - updates select then triggers AJAX
    function filterAdminByStatus(status) {
        const select = document.getElementById('adminFilterStatus');
        if (select) {
            select.value = status;
            updateAdminMap();
        }
    }
</script>
@endsection
