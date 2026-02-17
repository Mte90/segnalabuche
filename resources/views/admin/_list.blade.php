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
                              <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPhotos({!! htmlspecialchars(json_encode($segnalazione->foto), ENT_QUOTES, 'UTF-8') !!}, '{{ $segnalazione->tipo }}')">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><eye></eye></svg>
                                 Visualizza {{ count($segnalazione->foto) }} foto{{ count($segnalazione->foto) > 1 ? 's' : '' }}
                             </button>
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
                        <button type="button" class="btn btn-approve" onclick="showConfirmModal({{ $segnalazione->id }}, 'approved')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Approva
                        </button>
                        <button type="button" class="btn btn-reject" onclick="showConfirmModal({{ $segnalazione->id }}, 'rejected')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            Rifiuta
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="showDeleteModal({{ $segnalazione->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="3 6 5 6 21 6"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            Elimina
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="no-data">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <h5>Nessuna segnalazione trovata</h5>
        <p>Prova a modificare i filtri</p>
    </div>
@endforelse
