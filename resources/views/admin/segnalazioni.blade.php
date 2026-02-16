<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Segnalazioni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            padding: 1.5rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header-section h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .header-section p {
            font-size: 1rem;
            opacity: 0.95;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #e9ecef;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .status-pending {
            background: var(--warning-color);
            color: #856404;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-approved {
            background: var(--success-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .status-rejected {
            background: var(--secondary-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .segnalazione-item {
            padding: 1.25rem;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .segnalazione-item:hover {
            background: #f8f9fa;
        }
        
        .segnalazione-item:last-child {
            border-bottom: none;
        }
        
        .segnalazione-info h6 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .segnalazione-info p {
            margin-bottom: 0.25rem;
            color: #495057;
            font-size: 0.95rem;
        }
        
        .segnalazione-info .extra-info {
            color: #6c757d;
            font-size: 0.85rem;
            font-style: italic;
        }
        
        .segnalazione-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }
        
        .btn-approve {
            background: var(--success-color);
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-approve:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .btn-reject {
            background: var(--danger-color);
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-reject:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-data svg {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .stats-bar {
            display: flex;
            justify-content: space-around;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        .modal-footer {
            border-top: none;
        }
        
        .btn-confirm-approve {
            background: var(--success-color);
            border: none;
        }
        
        .btn-confirm-approve:hover {
            background: #218838;
        }
        
        .btn-confirm-reject {
            background: var(--danger-color);
            border: none;
        }
        
        .btn-confirm-reject:hover {
            background: #c82333;
        }
        
        .btn-cancel {
            background: var(--secondary-color);
            border: none;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <header class="header-section">
        <div class="container">
            <h1>Pannello di Controllo</h1>
            <p>Approvazione e gestione segnalazioni</p>
        </div>
    </header>
    
    <div class="container py-5">
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-number" id="totalCount">{{ $count ?? 0 }}</div>
                <div class="stat-label">Totali</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="pendingCount">{{ $pendingCount ?? 0 }}</div>
                <div class="stat-label">In Sospeso</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="approvedCount">{{ $approvedCount ?? 0 }}</div>
                <div class="stat-label">Approvate</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="rejectedCount">{{ $rejectedCount ?? 0 }}</div>
                <div class="stat-label">Rifiutate</div>
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
                @forelse($segnalazioni as $segnalazione)
                    <div class="segnalazione-item">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="segnalazione-info">
                                    <h6>{{ $segnalazione->tipo }}</h6>
                                    <p>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        ID: {{ $segnalazione->id }} | 
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><calendar></calendar></svg>
                                        {{ $segnalazione->created_at->format('d/m/Y H:i') }}
                                    </p>
                                    @if($segnalazione->descrizione)
                                        <p class="mb-1">{{ $segnalazione->descrizione }}</p>
                                    @endif
                                    <p class="extra-info">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle></svg>
                                        Coordinate: {{ $segnalazione->lat }}, {{ $segnalazione->lng }}
                                    </p>
                                    @if(!empty($segnalazione->foto))
                                        <p class="extra-info">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                            {{ count($segnalazione->foto) }} foto allegata(e)
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <span class="status-pending">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        In sospeso
                                    </span>
                                    <div class="segnalazione-actions">
                                        <button type="button" class="btn btn-approve" onclick="showConfirmModal({{$segnalazione->id}}, 'approved')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            Approva
                                        </button>
                                        <button type="button" class="btn btn-reject" onclick="showConfirmModal({{$segnalazione->id}}, 'rejected')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                            Rifiuta
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="no-data">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <h5>Nessuna segnalazione in sospeso</h5>
                        <p>Tutte le segnalazioni sono state gestite</p>
                    </div>
                @endforelse
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
            <p>Pannello Admin - Comune di Rieti</p>
            <p class="mb-0">&copy; {{ date('Y') }} Comune di Rieti. Tutti i diritti riservati.</p>
        </div>
    </footer>
    
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        let segnalazioneId = null;
        let newStatus = null;
        
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
    </script>
</body>
</html>
