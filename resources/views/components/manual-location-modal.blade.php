<!-- Manual Location Modal -->
<div class="modal fade" id="manualLocationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Imposta Posizione Manuale</h5>
            <button type="button" class="btn-close close-button" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
            <p class="text-muted mb-3">Clicca sulla mappa per selezionare la posizione del guasto. Il punto verrà evidenziato con un marker.</p>
            
            <div id="manualMapContainer" class="rounded overflow-hidden" style="height: 400px; width: 100%;">
                <div id="manualMap"></div>
            </div>
            
            <div class="mt-3 p-3 bg-light rounded">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Latitudine</label>
                        <input type="text" id="manualLat" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Longitudine</label>
                        <input type="text" id="manualLng" class="form-control" readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
            <button type="button" class="btn btn-primary" id="confirmManualLocation">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                Conferma Posizione
            </button>
        </div>
    </div>
</div>
