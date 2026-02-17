# Segnala un guasto

Un'applicazione Laravel 11 per la segnalazione di problemi stradali.

## Requisiti

- PHP 8.2+
- Composer 2.x
- SQLite (modulo `pdo_sqlite` per PHP)
- Estensioni PHP: `pdo`, `mbstring`, `tokenizer`, `fileinfo`, `intl`, `json`

## Per chi è pensato

Questo progetto è pensato per comuni, città e province. Configura il tuo comune (ad esempio **Bugliano**) modificando `config/city.php`.

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

### Configurazione della città (esempio Bugliano)

```php
// config/city.php
return ['name' => env('CITY_NAME', 'Bugliano')];
```
   ```bash
   php artisan serve
   ```

L'applicazione sarà disponibile all'indirizzo `http://localhost:8000`

## Configurazione

Per la configurazione dettagliata (email, template, IMAP, etc.), consulta il file **[SETUP.md](SETUP.md)**.

La configurazione principale include:

- **Nome città**: Modifica `config/city.php`
- **Email guasti**: Configura `config/guasto_mail.php` con destinatari per ogni tipologia

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

### Tracking email reply
- Tracking delle email inviate con multirecipient (email primarie + cc)
- Registrazione delle risposte alle email inviate
- Relazione tra email inviate e rispostericevute tramite Reply-To
- Tabella `email_response_messages` per memorizzare invii e ricevimenti

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

## Pannello Admin

- **URL di accesso**: `/admin/segnalazioni`
- **Lista segnalazioni**: Visualizza segnalazioni in stato `pending`.
- **Bottoni**: `Approva` / `Rifiuta` per ogni segnalazione.
- **Modal di conferma**: Richiede conferma prima di eseguire l'azione.
- **Email automatica**: Invia email ai destinatari configurati (multipli + cc) con tracking delle risposte

## Accesso Admin

### Creazione Account Admin

Per accedere al pannello admin è necessario un utente con ruolo amministratore.

#### Opzione 1: Creazione tramite Artisan (consigliato)

Esegui il comando per creare l'utente admin. Per sviluppo, le credenziali predefinite sono `admin`/`admin`:

```bash
php artisan admin:create
```

Per personalizzare le credenziali:

```bash
php artisan admin:create "Nome Admin" "admin@comune.rieti.it" "la_tua_password"
```

#### Opzione 2: Creazione tramite Seeder (solo sviluppo)

Esegui il seeder che crea l'utente admin predefinito:

```bash
php artisan db:seed --class=CreateAdminUserSeeder
```

L'utente creato avrà:
- Nome: Admin
- Email: admin@comune.rieti.it
- Password: admin

### Accesso al pannello

1. Andare all'URL: `http://localhost:8000/admin/segnalazioni`
2. Effettuare l'accesso con le credenziali admin
3. Visualizzare la lista delle segnalazioni in stato `pending`
4. Approvare o rifiutare le segnalazioni

> **Nota di sicurezza**: In produzione, usare sempre password complesse e implementare un sistema di ruoli proper (es. Laravel Spatie Permissions).

### tracking Email Reply

Per ricevere le risposte alle email inviate:

1. Configura le credenziali IMAP in `.env`:
   ```env
   IMAP_HOST=imap.gmail.com
   IMAP_PORT=993
   IMAP_ENCRYPTION=ssl
   IMAP_USERNAME=il_tuo_email@dominio.com
   IMAP_PASSWORD=la_tua_password_app
   ```

2. Esegui periodicamente il listener per recuperare le risposte:
   ```bash
   php artisan tinker --execute="(new App\Listeners\ProcessEmailResponses)->handle();"
   ```

3. Le risposte vengono salvate nella tabella `email_response_messages` con:
   - Relazione alla segnalazione originale
   - Corpo del messaggio
   - Metadata (from, to, date)
   - Timestamp di ricezione

## Template Email Personalizzabili (Git-Friendly)

### Problema
Modificare direttamente i template in `resources/views/mail/` causa conflitti di versionamento con git quando si lavora in team o su ambienti multipli.

### Soluzione
Creare una cartella `resources/views/mail/guasto_custom/` per i template modificabili che **non va in versionamento**.

### Struttura

```
resources/views/mail/
├── guasto_custom/        # ← TUTTI I TUOI TEMPLATE CUSTOM QUI
│   ├── perdita_acqua.blade.php
│   ├── tombino.blade.php
│   └── ...
├── approval_request.blade.php      # Template di default (versionato)
└── approved_notification.blade.php # Template di default (versionato)
```

### Configurazione

Il file `config/guasto_mail.php` supporta sia template di default che custom:

```php
return [
    'admin_email' => 'admin@comune.rieti.it',
    'perdita d\'acqua' => [
        'email' => 'acqua@comune.rieti.it',
        'template' => 'perdita_acqua',  // Usa custom se esiste, altrimenti default
    ],
    // ...
];
```

**Logica di caricamento:**
1. Se `resources/views/mail/guasto_custom/xxx.blade.php` esiste → usa quello
2. Altrimenti usa `resources/views/mail/xxx.blade.php` (template di default)

###流程 per personalizzare un template

1. **Copiare il template di default**:
```bash
cp resources/views/mail/approval_request.blade.php \
   resources/views/mail/guasto_custom/perdita_acqua.blade.php
```

2. **Modificare il copia**:
```bash
vi resources/views/mail/guasto_custom/perdita_acqua.blade.php
# Fare le modifiche necessarie
```

3. **Aggiornare la configurazione** in `config/guasto_mail.php`:
```php
'perdita d\'acqua' => [
    'email' => 'acqua@comune.rieti.it',
    'template' => 'mail.guasto_custom.perdita_acqua',  // ← nota _custom
],
```



### Template di default forniti

- `approval_request.blade.php` → Invio all'admin per approvazione
- `approved_notification.blade.php` → Notifica al comune quando approvata

### Attenzione

- **NON** aggiungere file in `guasto_custom/` al repository git
- Usa questa cartella SOLO per personalizzazioni locali
- Per contribuire al project, fai PR solo su template di default in `resources/views/mail/`

## Amministrazione

*Questo pannello amministrativo si riferisce al comune configurato, ad esempio **Bugliano***

### Aggiungere nuove tipologie di guasto

1. Aprire `config/guasto_mail.php`.
2. Aggiungere una nuova chiave con il nome della tipologia e le impostazioni email:

```php
return [
    // ... altre tipologie
    'nuova_tipologia' => [
        'email' => 'nuova@comune.rieti.it',
        'template' => 'mail.guasto.nuova_tipologia', // Opzionale
    ],
];
```

3. (Opzionale) Creare un template Blade specifico in `resources/views/mail/` o in `resources/views/mail/guasto_custom/` per personalizzare l'email.

### Nota sulla privacy e anonimato
Le segnalazioni sono anonime; nessun dato personale dell'utente è memorizzato. Le email inviate contengono solo le informazioni della segnalazione e l'indirizzo del comune destinatario.

## Licenza

GPLv3 - vedi il file [LICENSE](LICENSE) per i dettagli.
