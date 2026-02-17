(function() {
    'use strict';
    
    let map = null;
    let markersLayer = null;
    
    const rietiCenter = [42.4097, 12.8607];
    
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        updateMap();
        updateStats();
    });
    
    window.filterWithPhotos = function() {
        alert('Funzionalità filtro foto in arrivo: mostra solo le segnalazioni con foto');
    };
    
    window.filterByStatus = function(status) {
        const select = document.getElementById('filterStatus');
        if (select) {
            select.value = status;
            updateMap();
        }
    };
    
    window.initMap = function() {
        map = L.map('map').setView(rietiCenter, 14);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        markersLayer = L.featureGroup().addTo(map);
    };
    
    window.updateMap = function() {
        const status = document.getElementById('filterStatus').value;
        const tipo = document.getElementById('filterTipo').value;
        
        let url = '/api/segnalazioni?';
        const params = [];
        
        if (status) params.push('status=' + encodeURIComponent(status));
        if (tipo) params.push('tipo=' + encodeURIComponent(tipo));
        
        if (params.length > 0) {
            url += params.join('&');
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                updateMarkers(data.data);
            })
            .catch(error => {
                console.error('Errore nel caricamento:', error);
                alert('Errore nel caricamento delle segnalazioni');
            });
    };
    
    window.resetFilters = function() {
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterTipo').value = '';
        updateMap();
    };
    
    window.updateMarkers = function(segnalazioni) {
        if (markersLayer) {
            markersLayer.clearLayers();
        }
        
        const icons = {
            pending: L.divIcon({
                className: 'custom-div-icon',
                html: "<div class='marker-icon' style='background: var(--warning-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>?</div>",
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            }),
            approved: L.divIcon({
                className: 'custom-div-icon',
                html: "<div class='marker-icon' style='background: var(--success-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>✔</div>",
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            }),
            rejected: L.divIcon({
                className: 'custom-div-icon',
                html: "<div class='marker-icon' style='background: var(--secondary-color); width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);'>✖</div>",
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            })
        };
        
        segnalazioni.forEach(function(segnalazione) {
            const statusClass = segnalazione.status;
            const icon = icons[statusClass] || icons.pending;
            
            const marker = L.marker([segnalazione.lat, segnalazione.lng], { icon: icon });
            
            const tipoIcon = getTipoIcon(segnalazione.tipo);
            const badgeClass = getBadgeClass(segnalazione.status);
            const badgeText = getBadgeText(segnalazione.status);
            
            const popupContent = `
                <div style="max-width: 300px;">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge ${badgeClass} me-auto">${badgeText}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        ${tipoIcon}
                        <strong class="ms-2" style="color: #495057;">${segnalazione.tipo}</strong>
                    </div>
                     ${segnalazione.descrizione ? `<p style="color: #495057; margin: 0 0 0.5rem 0;">${segnalazione.descrizione}</p>` : ''}
                     
                     ${segnalazione.foto && segnalazione.foto.length > 0 ? `
                         <div class="mb-2">
                             <div class="d-flex flex-wrap gap-1">
                                 ${segnalazione.foto.slice(0, 3).map((foto, idx) => `
                                     <a href="${foto}" target="_blank" class="text-decoration-none">
                                         <img src="${foto}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;" alt="Foto ${idx + 1}">
                                     </a>
                                 `).join('')}
                             </div>
                             ${segnalazione.foto.length > 3 ? `<p class="text-muted small">+${segnalazione.foto.length - 3} foto in più</p>` : ''}
                         </div>
                     ` : ''}
                     
                     <p style="color: #6c757d; margin: 0;">Data: ${new Date(segnalazione.created_at).toLocaleDateString('it-IT')}</p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            markersLayer.addLayer(marker);
        });
        
        if (segnalazioni.length > 0) {
            const bounds = markersLayer.getBounds();
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    };
    
    window.getTipoIcon = function(tipo) {
        const iconsMap = {
            "perdita d'acqua": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path></svg>',
            "tombino attappato": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="3"></circle></svg>',
            "buca stradale": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"></path><path d="M5 12v-3"></path><path d="M19 12v-3"></path><path d="M8 12v-5"></path><path d="M16 12v-5"></path></svg>',
            "illuminazione pubblica": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path><path d="M9 2h6"></path></svg>',
            "altro": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line><line x1="12" y1="8" x2="12" y2="16"></line></svg>'
        };
        return iconsMap[tipo] || '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle></svg>';
    };
    
    window.getBadgeClass = function(status) {
        const classes = {
            pending: 'badge bg-warning text-dark',
            approved: 'badge bg-success',
            rejected: 'badge bg-secondary'
        };
        return classes[status] || classes.pending;
    };
    
    window.getBadgeText = function(status) {
        const texts = {
            pending: 'In sospeso',
            approved: 'Approvato',
            rejected: 'Rifiutato'
        };
        return texts[status] || status;
    };
})();
