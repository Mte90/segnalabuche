# Guida alla Configurazione

Questo documento spiega come configurare Segnala un Guasto per il tuo comune.

## Configurazione città

Modifica il file `config/city.php` per impostare il nome della città:

```php
    return [
        'name' => 'Bugliano',
    ];
```

## Configurazione email per guasti

Il file di configurazione è `config/guasto_mail.php`. Al suo interno è definita una mappa `tipologia => ['emails' => [...], 'cc' => [...], 'template' => 'nome_template']`. Ogni tipologia può avere più destinatari primari (`emails`) e copia (`cc`).

Per maggiori informazioni sulla personalizzazione dei template, vedere la sezione **Template Email Personalizzabili (Git-Friendly)**.

**Esempio di aggiunta di una nuova tipologia**:
```php
return [
    // ... altre tipologie
    'nuova_tipologia' => [
        'emails' => ['nuova@comune.bugliano.it'],
        'cc' => ['cc@comune.bugliano.it'],
        'template' => 'nuova_tipologia', // Opzionale
    ],
];
```

## Configurazione email generale

Configura in `.env` le impostazioni SMTP:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tua.email@dominio.com
MAIL_PASSWORD=tpfz gbiq xxxx xxxx
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tua.email@dominio.com
MAIL_FROM_NAME="Segnalazioni"
```

## Configurazione IMAP per risposte email

Per ricevere le risposte alle email inviate, configura le impostazioni IMAP in `.env`:

```env
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_USERNAME=tua.email@dominio.com
IMAP_PASSWORD=tpfz gbiq xxxx xxxx
```

Questo permette al sistema di recuperare automaticamente le risposte alle email inviate e registrarle nella tabella `email_response_messages`.

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
    'admin_email' => 'admin@comune.bugliano.it',
    'perdita d\'acqua' => [
        'emails' => ['acqua@comune.bugliano.it'],
        'cc' => [],
        'template' => 'perdita_acqua',  // Usa custom se esiste, altrimenti default
    ],
    // ...
];
```

**Logica di caricamento:**
1. Se `resources/views/mail/guasto_custom/xxx.blade.php` esiste → usa quello
2. Altrimenti usa `resources/views/mail/xxx.blade.php` (template di default)

### Workflow per personalizzare un template

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
    'emails' => ['acqua@comune.bugliano.it'],
    'cc' => [],
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
        'emails' => ['nuova@comune.bugliano.it'],
        'cc' => [],
        'template' => 'mail.guasto.nuova_tipologia', // Opzionale
    ],
];
```

3. (Opzionale) Creare un template Blade specifico in `resources/views/mail/` o in `resources/views/mail/guasto_custom/` per personalizzare l'email.

### Nota sulla privacy e anonimato
Le segnalazioni sono anonime; nessun dato personale dell'utente è memorizzato. Le email inviate contengono solo le informazioni della segnalazione e l'indirizzo del comune destinatario.

## Struttura guasto_custom

```
resources/views/mail/guasto_custom/
├── perdita_acqua.blade.php
├── tombino.blade.php
├── buca.blade.php
├── illuminazione.blade.php
└── altro.blade.php
```

---

Progetto personale
