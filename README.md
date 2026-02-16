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

Il file di configurazione è `config/guasto_mail.php`. Al suo interno è definita una mappa `tipologia => ['email' => 'destinatario', 'template' => 'nome_template']`.

Per maggiori informazioni sulla personalizzazione dei template, vedere la sezione **Template Email Personalizzabili (Git-Friendly)**.

**Esempio di aggiunta di una nuova tipologia**:
```php
return [
    // ... altre tipologie
    'nuova_tipologia' => [
        'email' => 'nuova@comune.rieti.it',
        'template' => 'nuova_tipologia', // Opzionale
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

## Pannello Admin

- **URL di accesso**: `/admin/segnalazioni`
- **Lista segnalazioni**: Visualizza segnalazioni in stato `pending`.
- **Bottoni**: `Approva` / `Rifiuta` per ogni segnalazione.
- **Modal di conferma**: Richiede conferma prima di eseguire l'azione.
- **Email automatica**: All'approvazione viene inviata un'email al comune configurato.

## Accesso Admin

### Creazione Account Admin

Per accedere al pannello admin è necessario un utente con ruolo amministratore.

#### Opzione 1: Creazione manuale tramite Artisan

Laravel 11 non include più la scaffolding di autenticazione di default. Per creare un account admin:

```bash
# Creare un nuovo utente manualmente via Tinker
php artisan tinker

# Esecuire i seguenti comandi in tinker:
use App\Models\User;
$user = User::create([
    'name' => 'Admin',
    'email' => 'admin@comune.rieti.it',
    'password' => bcrypt('la_tua_password_sicura'),
]);
# Aggiungere il ruolo admin (se implementato nel progetto)
```

#### Opzione 2: Creazione tramite Seeder (solo sviluppo)

Creare un seeder per utente admin in `database/seeders/AdminUserSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@comune.rieti.it',
            'password' => Hash::make('admin123'), // Cambiare in produzione!
        ]);
    }
}
```

Eseguire con:
```bash
php artisan db:seed --class=AdminUserSeeder
```

#### Opzione 3: Credenziali in .env (sviluppo)

Per sviluppo locale, aggiungere le credenziali in `.env`:

```env
ADMIN_EMAIL=admin@comune.rieti.it
ADMIN_PASSWORD=admin123
```

Poi usare un seeder che legge queste variabili.

### Accesso al pannello

1. Andare all'URL: `http://localhost:8000/admin/segnalazioni`
2. Effettuare l'accesso con le credenziali admin
3. Visualizzare la lista delle segnalazioni in stato `pending`
4. Approvare o rifiutare le segnalazioni

> **Nota di sicurezza**: In produzione, usare sempre password complesse e implementare un sistema di ruoli proper (es. Laravel Spatie Permissions).

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
│   ├── segnalazione/
│   │   └── create.blade.php
│   └── mail/
│       ├── guasto_custom/     # Template personalizzabili (non versionati)
│       ├── approval_request.blade.php
│       └── approved_notification.blade.php
```

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

## Contributi

Fork -> Branch -> PR

## Supporto

Issue su GitHub

### Struttura guasto_custom

```bash
resources/views/mail/guasto_custom/
├── perdita_acqua.blade.php
├── tombino.blade.php
├── buca.blade.php
├── illuminazione.blade.php
└── altro.blade.php
```

---

Comune di Rieti
