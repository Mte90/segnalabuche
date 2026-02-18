(function() {
    'use strict';
    
    let confirmModal = null;
    let deleteModal = null;
    let photosModal = null;
    let editModal = null;
    let segnalazioneId = null;
    let newStatus = null;
    let currentPhotos = [];
    let editMap = null;
    let editMarker = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        photosModal = new bootstrap.Modal(document.getElementById('photosModal'));
        editModal = new bootstrap.Modal(document.getElementById('editModal'));
        
        document.getElementById('deleteSegnalazioneId').addEventListener('click', function() {
            deleteSegnalazione(document.getElementById('deleteSegnalazioneId').value);
        });
        
        document.getElementById('confirmApproveBtn').addEventListener('click', function() {
            updateStatus('approved');
        });
        
        document.getElementById('confirmRejectBtn').addEventListener('click', function() {
            updateStatus('rejected');
        });
        
        document.getElementById('saveEditBtn').addEventListener('click', function() {
            updateSegnalazione();
        });
    });
    
    window.showPhotos = function(photosJson, tipo) {
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
                if (modalBody) {
                    modalBody.innerHTML = '<p class="text-center text-muted">Nessuna foto disponibile</p>';
                }
                photosModal.show();
            }
        } catch (e) {
            alert('Errore nel caricamento delle foto');
        }
    };
    
    window.showDeleteModal = function(id) {
        document.getElementById('deleteSegnalazioneId').value = id;
        deleteModal.show();
    };
    
    window.deleteSegnalazione = function(id) {
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
    };
    
    window.showConfirmModal = function(id, status) {
        segnalazioneId = id;
        newStatus = status;
        
        const title = status === 'approved' ? 'Approva Segnalazione' : 'Rifiuta Segnalazione';
        const message = status === 'approved' 
            ? 'Confermi di voler approvare questa segnalazione? Verrà inviata una notifica al comune.'
            : 'Confermi di voler rifiutare questa segnalazione? L\'azione non può essere annullata.';
        
        const confirmModalTitle = document.getElementById('confirmModalTitle');
        if (confirmModalTitle) {
            confirmModalTitle.textContent = title;
        }
        
        const confirmMessage = document.getElementById('confirmMessage');
        if (confirmMessage) {
            confirmMessage.textContent = message;
        }
        
        const confirmApproveBtn = document.getElementById('confirmApproveBtn');
        const confirmRejectBtn = document.getElementById('confirmRejectBtn');
        
        if (confirmApproveBtn && confirmRejectBtn) {
            confirmApproveBtn.style.display = status === 'approved' ? 'block' : 'none';
            confirmRejectBtn.style.display = status === 'rejected' ? 'block' : 'none';
            
            if (status !== 'approved' && status !== 'rejected') {
                confirmApproveBtn.style.display = 'block';
                confirmRejectBtn.style.display = 'block';
            }
        }
        
        confirmModal.show();
    };
    
    window.updateStatus = function(status) {
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
    };
    
    window.updateAdminMap = function() {
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
                
                const badge = document.querySelector('.card-header .badge');
                if (badge && data.count !== undefined) {
                    badge.textContent = data.count;
                }
                
                const cardTitle = document.querySelector('.card-header > span');
                const statusText = status ? (status === 'pending' ? 'In sospeso' : status === 'approved' ? 'Approvate' : 'Rifiutate') : 'In sospeso';
                if (cardTitle) {
                    cardTitle.textContent = statusText;
                }
                
                if (data.stats) {
                    document.getElementById('totalCount').textContent = data.stats.total ?? '';
                    document.getElementById('pendingCount').textContent = data.stats.pending ?? '';
                    document.getElementById('approvedCount').textContent = data.stats.approved ?? '';
                    document.getElementById('rejectedCount').textContent = data.stats.rejected ?? '';
                }
                
                loadMapInEditModal();
            })
            .catch(error => {
                console.error('Errore nel caricamento AJAX:', error);
                alert('Errore nel caricamento delle segnalazioni');
            });
    };
    
    window.resetAdminFilters = function() {
        window.location.href = '/admin/segnalazioni';
    };
    
    window.filterAdminByStatus = function(status) {
        const select = document.getElementById('adminFilterStatus');
        if (select) {
            select.value = status;
            updateAdminMap();
        }
    };
    
    window.showEditModal = function(segnalazione) {
        document.getElementById('edit_id').value = segnalazione.id;
        document.getElementById('edit_tipo').value = segnalazione.tipo;
        document.getElementById('edit_status').value = segnalazione.status;
        document.getElementById('edit_descrizione').value = segnalazione.descrizione || '';
        document.getElementById('edit_lat').value = segnalazione.lat;
        document.getElementById('edit_lng').value = segnalazione.lng;
        
        // Clear and populate photos list
        const photosList = document.getElementById('editPhotosList');
        if (photosList && segnalazione.foto && segnalazione.foto.length > 0) {
            let photosHtml = '';
            segnalazione.foto.forEach((photo, index) => {
                photosHtml += `
                    <div class="col-6 col-md-3">
                        <div class="position-relative">
                            <img src="${photo}" class="img-fluid rounded" style="height: 100px; object-fit: cover;" alt="Foto ${index + 1}">
                            <span class="badge bg-primary position-absolute top-0 start-0">Foto ${index + 1}</span>
                        </div>
                    </div>
                `;
            });
            photosList.innerHTML = photosHtml;
        }
        
        editModal.show();
    };
    
    window.updateSegnalazione = function() {
        const id = document.getElementById('edit_id').value;
        const dati = {
            tipo: document.getElementById('edit_tipo').value,
            status: document.getElementById('edit_status').value,
            descrizione: document.getElementById('edit_descrizione').value,
            lat: parseFloat(document.getElementById('edit_lat').value),
            lng: parseFloat(document.getElementById('edit_lng').value),
        };
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('Errore: Token CSRF non trovato');
            return;
        }
        
        // Validate required fields
        if (!dati.tipo || !dati.status || !dati.lat || !dati.lng) {
            alert('Tutti i campi contrassegnati con * sono obbligatori');
            return;
        }
        
        if (isNaN(dati.lat) || isNaN(dati.lng)) {
            alert('Le coordinate devono essere numeri validi');
            return;
        }
        
        fetch(`/admin/segnalazioni/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(dati)
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Segnalazione aggiornata con successo');
            // Close modal and refresh the list
            editModal.hide();
            updateAdminMap();
        })
        .catch(error => {
            console.error('Errore:', error);
            alert('Errore durante l\'aggiornamento della segnalazione');
        });
    };
})();
