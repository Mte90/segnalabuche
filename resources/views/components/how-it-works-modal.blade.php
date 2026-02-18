<!-- How It Works Modal -->
<div class="modal fade" id="howItWorksModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h5 class="modal-title">Come Funziona il Portale</h5>
            <button type="button" class="btn-close close-button" data-bs-dismiss="modal" aria-label="Chiudi"></button>
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
                    <p class="small text-muted mb-0">La tua segnalazione verrà inviata automaticamente ai destinatari configurati nel comune. Quando verrà approvata, riceverai una notifica via email.</p>
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
