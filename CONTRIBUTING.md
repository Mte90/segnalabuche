# Linee guida per contribuire a Segnala un guasto - Rieti

Grazie per il tuo interesse a contribuire a questo progetto! Questo documento fornisce tutte le informazioni necessarie per iniziare a contribuire.

## Table of Contents

- [Linee guida per contribuire](#linee-guida-per-contribuire-a-segnala-un-guasto-rieti)
  - [Table of Contents](#table-of-contents)
  - [Codice di Condotta](#codice-di-conduotta)
  - [Come Contribuire](#come-contribuire)
    - [1. Fork del Repository](#1-fork-del-repository)
    - [2. Clonare il Repository](#2-clonare-il-repository)
    - [3. Creare un Branch](#3-creare-un-branch)
    - [4. Apportare le Modifiche](#4-apportare-le-modifiche)
    - [5. Commit delle Modifiche](#5-commit-delle-modifiche)
    - [6. Push delle Modifiche](#6-push-delle-modifiche)
    - [7. Aprire una Pull Request](#7-aprire-una-pull-request)
  - [Linee Guida per le Pull Request](#linee-guida-per-le-pull-request)
  - [Linee Guida per i Commit](#linee-guida-per-i-commit)
  - [Testing](#testing)
  - [Style Guide](#style-guide)
    - [PHP](#php)
    - [Blade Templates](#blade-templates)
    - [JavaScript](#javascript)
  - [Settaggio dell'Ambiente di Sviluppo](#settaggio-dellambiente-di-sviluppo)

## Codice di Condotta

Questo progetto è conformed al Codice di Condotta Open Source. Per favore, rispetta le seguenti linee guida:

- Essere rispettosi e inclusivi
- Accetta le critiche costruttive
- Fai riferimento agli issue o PR via GitHub
- Rispetta le decisioni dei maintainers

## Come Contribuire

### 1. Fork del Repository

Clicca sul pulsante "Fork" in alto a destra su GitHub per creare una tua copia del repository.

### 2. Clonare il Repository

Clona il tuo fork localmente:

```bash
git clone https://github.com/tuo-username/segnalabuche.git
cd segnalabuche
```

### 3. Creare un Branch

Crea un branch per la tua nuova funzionalità o fix:

```bash
git checkout -b feature/nome-della-funzionalita
# oppure
git checkout -b fix/nome-del-fix
```

### 4. Apportare le Modifiche

Apporta le tue modifiche al codice. Segui le linee guida dello stile e i principi del progetto.

### 5. Commit delle Modifiche

Fai commit con un messaggio chiaro e descrittivo:

```bash
git add .
git commit -m "Descrizione chiara e concisa delle modifiche"
```

Vedi la sezione [Linee Guida per i Commit](#linee-guida-per-i-commit) per il formato dei messaggi.

### 6. Push delle Modifiche

Pusha il tuo branch al tuo fork su GitHub:

```bash
git push origin feature/nome-della-funzionalita
```

### 7. Aprire una Pull Request

1. Vai al tuo fork su GitHub
2. Clicca su "Compare & pull request"
3. Fornisci un titolo chiaro e una descrizione dettagliata delle tue modifiche
4. Riferisci eventuali issue correlate

## Linee Guida per le Pull Request

- Fai riferimento a tutte le issue corrispondenti nel messaggio della PR
- Fornisci una descrizione chiara delle modifiche
- Includi screenshot o video per cambiamenti UI/UX
- Assicurati che i test passino
- Aggiorna la documentazione se necessario

## Linee Guida per i Commit

Utilizza il seguente formato per i messaggi di commit:

```
<tipo>: <descrizione>

[corpo opzionale]

[footer opzionale]
```

Tipi di commit:

- `feat`: Nuova funzionalità
- `fix`: Correzione di un bug
- `docs`: Cambiamenti alla documentazione
- `style`: Cambiamenti che non influenzano il significato del codice
- `refactor`: Cambiamento del codice che non corregge un bug né aggiunge una funzionalità
- `test`: Aggiunta di test
- `chore`: Cambiamenti di manutenzione

Esempi:

```
feat: aggiungi supporto per foto in PDF
fix: risolto errore nell'invio email
docs: aggiornata documentazione di installazione
```

## Testing

Prima di inviare una pull request, assicurati che:

1. Tutti i test passino:
   ```bash
   php artisan test
   ```

2. Lo standard di codifica sia rispettato:
   ```bash
   composer sniff
   ```

3. Non ci siano errori LSP:
   ```bash
   php artisan route:list
   php artisan view:clear
   ```

## Style Guide

### PHP

- Segui il [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Usa 4 spazi per l'indentazione
- Usa nomi di variabili e metodi descrittivi in camelCase
- Aggiungi commenti per logica complessa

### Blade Templates

- Usa la sintassi Blade per la logica di visualizzazione
- Mantieni il codice HTML pulito e indentato
- Usa classi Bootstrap consistenti per lo stile

### JavaScript

- Usa Vanilla JS (senza jQuery)
- Segui lo stile Airbnb JavaScript
- Aggiungi commenti per logica complessa
- Usa async/await per operazioni asincrone

## Settaggio dell'Ambiente di Sviluppo

1. Installa le dipendenze:
   ```bash
   composer install
   ```

2. Copia `.env.example` in `.env` e configura le tue variabili d'ambiente

3. Genera la chiave dell'applicazione:
   ```bash
   php artisan key:generate
   ```

4. Esegui le migrazioni:
   ```bash
   php artisan migrate
   ```

5. Avvia il server di sviluppo:
   ```bash
   php artisan serve
   ```

---

Grazie per il tuo contributo! 🚀
