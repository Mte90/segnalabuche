(function() {
    'use strict';
    
    let map = null;
    let marker = null;
    let userPosition = null;
    let dupAlert = null;
    let duplicatesFound = [];
    
    const rietiCenter = [42.4097, 12.8607];
    
    function haversineDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000;
        const φ1 = lat1 * Math.PI / 180;
        const φ2 = lat2 * Math.PI / 180;
        const Δφ = (lat2 - lat1) * Math.PI / 180;
        const Δλ = (lng2 - lng1) * Math.PI / 180;
        
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        
        return R * c;
    }
    
    async function fetchAddressFromCoords(lat, lng) {
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`);
            const data = await response.json();
            
            if (data && data.address) {
                const addressParts = [
                    data.address.road,
                    data.address.house_number,
                    data.address.city,
                    data.address.postcode,
                    data.address.country
                ].filter(part => part);
                
                const address = addressParts.join(', ');
                
                if (address) {
                    const fullMessage = `Posizione rilevata: ${lat.toFixed(6)}, ${lng.toFixed(6)}<br><small>${address}</small>`;
                    geolocationText.innerHTML = fullMessage;
                    geolocationStatus.classList.add('has-address');
                } else {
                    setGeoLocationStatus('ready', 'Posizione rilevata con successo');
                }
            } else {
                setGeoLocationStatus('ready', 'Posizione rilevata con successo');
            }
        } catch (error) {
            console.error('Errore nel reverse geocoding:', error);
            setGeoLocationStatus('ready', 'Posizione rilevata con successo');
        }
    }
    
    let geolocationStatus = null;
    let geolocationText = null;
    let getLocationBtn = null;
    let fileDropZone = null;
    let fotoInput = null;
    let fileList = null;
    let submitBtn = null;
    let submitText = null;
    let mapPreview = null;
    let mapContainer = null;
    let descrizioneInput = null;
    let descrizioneCount = null;
    let latInput = null;
    let lngInput = null;
    
    function setupEventListeners() {
        descrizioneInput.addEventListener('input', function() {
            descrizioneCount.textContent = this.value.length;
        });
        
        document.getElementById('website').addEventListener('input', function() {
            console.warn('Honeypot field was filled!');
        });
        
        getLocationBtn.addEventListener('click', function() {
            requestGeoLocation();
        });
        
        fileDropZone.addEventListener('click', function() {
            fotoInput.click();
        });
        
        fotoInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });
        
        fileDropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileDropZone.style.borderColor = 'var(--primary-color)';
            fileDropZone.style.background = '#fff5f5';
        });
        
        fileDropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            fileDropZone.style.borderColor = '#dee2e6';
            fileDropZone.style.background = '#f8f9fa';
        });
        
        fileDropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            fileDropZone.style.borderColor = '#dee2e6';
            fileDropZone.style.background = '#f8f9fa';
            handleFiles(e.dataTransfer.files);
        });
        
        document.getElementById('segnalazioneForm').addEventListener('submit', function(e) {
            return validateForm(e);
        });
    }
    
    function setupFAQTrigger() {
        const faqTrigger = document.getElementById('faqTrigger');
        if (faqTrigger) {
            faqTrigger.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('faqModal'));
                modal.show();
            });
        }
    }
    
    function requestGeoLocation() {
        if (!navigator.geolocation) {
            setGeoLocationStatus('error', 'Il browser non supporta la geolocalizzazione');
            return;
        }
        
        setGeoLocationStatus('pending', 'Richiesta posizione...');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                latInput.value = userPosition.lat;
                lngInput.value = userPosition.lng;
                
                fetchAddressFromCoords(userPosition.lat, userPosition.lng);
                
                if (!map) {
                    initMap();
                }
            },
            function(error) {
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        setGeoLocationStatus('error', 'Posizione non concessa. Inserisci manualmente.');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        setGeoLocationStatus('error', 'Posizione non disponibile');
                        break;
                    case error.TIMEOUT:
                        setGeoLocationStatus('error', 'Richiesta posizione scaduta');
                        break;
                    default:
                        setGeoLocationStatus('error', 'Errore durante il rilevamento');
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }
    
    function setGeoLocationStatus(status, message) {
        geolocationStatus.className = 'geolocation-status ' + status;
        
        let icon = '';
        switch(status) {
            case 'ready':
                icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                break;
            case 'error':
                icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
                break;
            default:
                icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>';
        }
        
        geolocationText.innerText = message;
        
        const btn = document.getElementById('getLocationBtn');
        if (btn) {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            newBtn.addEventListener('click', function() {
                requestGeoLocation();
            });
        }
        
        if (status === 'ready' && !map) {
            initMap();
        }
    }
    
    function initMap() {
        if (!userPosition) return;
        
        map = L.map('map').setView([userPosition.lat, userPosition.lng], 16);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        marker = L.marker([userPosition.lat, userPosition.lng], {
            draggable: true
        }).addTo(map);
        
        marker.on('dragend', function() {
            userPosition = {
                lat: marker.getLatLng().lat,
                lng: marker.getLatLng().lng
            };
            latInput.value = userPosition.lat;
            lngInput.value = userPosition.lng;
        });
        
        loadNearbyReports();
    }
    
    function loadNearbyReports() {
        fetch('/api/segnalazioni')
            .then(response => response.json())
            .then(data => {
                const tipo = document.getElementById('tipo').value;
                
                duplicatesFound = data.data.filter(s => {
                    if (tipo && s.tipo !== tipo) return false;
                    
                    const distance = haversineDistance(
                        userPosition.lat,
                        userPosition.lng,
                        s.lat,
                        s.lng
                    );
                    
                    return distance <= 100;
                });

                if (duplicatesFound.length > 0) {
                    document.getElementById('duplicateCount').textContent = duplicatesFound.length;
                    dupAlert.classList.add('show');
                } else {
                    dupAlert.classList.remove('show');
                }
            })
            .catch(error => {
                console.error('Errore nel caricamento duplicati:', error);
                dupAlert.classList.remove('show');
            });
    }
    
    function handleFiles(files) {
        fileList.innerHTML = '';
        
        const validFiles = [];
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/jpg')) {
                alert('File "' + file.name + '" non è un\'immagine valida (JPEG/PNG)');
                continue;
            }
            
            if (file.size > 8 * 1024 * 1024) {
                alert('File "' + file.name + ' supera il limite di 8MB');
                continue;
            }
            
            validFiles.push(file);
        }
        
        if (validFiles.length > 3) {
            alert('Max 3 foto per segnalazione');
            validFiles.splice(3);
        }
        
        validFiles.forEach(function(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'alert alert-info d-flex align-items-center justify-content-between mb-1';
                div.innerHTML = `
                    <div class="d-flex align-items-center">
                        <img src="${e.target.result}" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <div class="ms-2">
                            <div class="fw-bold">${file.name}</div>
                            <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" onclick="this.closest('div').remove()"></button>
                `;
                fileList.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
    
    function validateForm(e) {
        const honeypot = document.getElementById('website').value;
        if (honeypot !== '') {
            e.preventDefault();
            console.log('Honeypot triggered');
            return false;
        }
        
        if (!latInput.value || !lngInput.value) {
            e.preventDefault();
            alert('Si prega di consentire la geolocalizzazione per inviare la segnalazione');
            requestGeoLocation();
            return false;
        }
        
        const descrizione = descrizioneInput.value;
        if (descrizione.length > 1000) {
            e.preventDefault();
            alert('La descrizione non può superare i 1000 caratteri');
            return false;
        }
        
        if (fileList.children.length > 3) {
            e.preventDefault();
            alert('Max 3 foto per segnalazione');
            return false;
        }
        
        submitText.textContent = 'Invio in corso...';
        submitBtn.disabled = true;
        
        return true;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        dupAlert = document.getElementById('duplicateAlert');
        latInput = document.getElementById('latInput');
        lngInput = document.getElementById('lngInput');
        geolocationStatus = document.getElementById('geolocationStatus');
        geolocationText = document.getElementById('geolocationText');
        getLocationBtn = document.getElementById('getLocationBtn');
        fileDropZone = document.getElementById('fileDropZone');
        fotoInput = document.getElementById('foto');
        fileList = document.getElementById('fileList');
        submitBtn = document.getElementById('submitBtn');
        submitText = document.getElementById('submitText');
        mapPreview = document.getElementById('mapPreview');
        mapContainer = document.getElementById('map');
        descrizioneInput = document.getElementById('descrizione');
        descrizioneCount = document.getElementById('descrizioneCount');
        
        setupEventListeners();
        setupFAQTrigger();
        
        const cityTitle = document.getElementById('cityTitle');
        if (cityTitle) {
            cityTitle.textContent = 'Segnala un Guasto - {{ config("city.name") }}';
        }
    });
})();
