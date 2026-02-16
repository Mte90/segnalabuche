# Segnala un guasto - Rieti

Un'applicazione Laravel 11 per la segnalazione di problemi stradali nel comune di Rieti.

## Requisiti

- PHP 8.2+
- Composer 2.x
- SQLite (modulo `pdo_sqlite` per PHP)
- Estensioni PHP: `pdo`, `mbstring`, `tokenizer`, `fileinfo`, `intl`, `json`

### Installazione modulo SQLite

```bash
sudo apt-get install php8.5-sqlite3
```

## Installazione

1. Clona il repository
   ```bash
   git clone <repository-url>
   cd segnalabuche
   ```

2. Installa le dipendenze
   ```bash
   composer install
   ```

3. Configura il file environment
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configura il database SQLite in `.env`:
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```

5. Crea il database e avvia le migrazioni:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

6. Configura la directory storage per i file upload:
   ```bash
   php artisan storage:link
   ```

7. Avvia il server di sviluppo:
   ```bash
   php artisan serve
   ```

L'applicazione sarà disponibile all'indirizzo `http://localhost:8000`

## Configurazione

### Configurazione città

Modifica il file `config/city.php` per impostare il nome della città:

```php
return [
    'name' => 'Rieti',
];
```

### Configurazione email per guasti

Modifica il file `config/guasto_mail.php` per configurare gli indirizzi email di destinazione:

```php
return [
    'perdita d\'acqua' => [
        'email' => 'acqua@comune.rieti.it',
    ],
    'tombino attappato' => [
        'email' => 'tombini@comune.rieti.it',
    ],
    'buca stradale' => [
        'email' => 'strade@comune.rieti.it',
    ],
    'illuminazione pubblica' => [
        'email' => 'illuminazione@comune.rieti.it',
    ],
    'altro' => [
        'email' => 'protocollo@comune.rieti.it',
    ],
];
```

### Configurazione email generale

Configura in `.env` le impostazioni SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tua.email@dominio.com
MAIL_PASSWORD=tpfz gbiq xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tua.email@dominio.com
MAIL_FROM_NAME="Segnalazioni Comune di Rieti"
```

## Funzionalità

### Segnalazione guasti
- Invio segnalazioni di problemi stradali
- Tipologie di guasti: perdita d'acqua, tombino attappato, buca stradale, illuminazione pubblica, altro
- Geolocalizzazione tramite API del browser
- Supporto anonimo (nessun account richiesto)

### Gestionale admin
- Pannello di approvazione segnalazioni
- Filtri per stato (pendente, approvato, rifiutato)
- Visualizzazione coordinate e foto
- Invio email di notifica all'approvazione

### Anti-spam
- Rate limiting per IP
- Honeypot field invisibile
- Verifica duplicati entro 100 metri

### Foto
- Massimo 3 foto per segnalazione
- Formati supportati: JPEG, PNG
- Dimensione massima per foto: 8MB
- Ridimensionamento automatico a 1200px max

### Mappa pubblica
- Visualizzazione Leaflet.js
- Filtri per stato e tipologia
- Marker interattivi

## Struttura del progetto

```
app/
├── Http/Controllers/
│   └── Api/
│       └── SegnalazioneController.php
├── Models/
│   └── Segnalazione.php
└── Services/
    └── DuplicateChecker.php
database/
├── migrations/
└── seeders/
resources/
├── views/
│   ├── admin/
│   │   └── segnalazioni.blade.php
│   ├── mappa/
│   │   └── index.blade.php
│   └── segnalazione/
│       └── create.blade.php
```

## Licenza

GPLv3 - vedi il file [LICENSE](LICENSE) per i dettagli.

## Contributi

Fork -> Branch -> PR

## Supporto

Issue su GitHub

---

Comune di Rieti
