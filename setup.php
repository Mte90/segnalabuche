<?php

/**
 * Setup.php - Setup script for Segnala un Guasto
 *
 * Esegui: http://tuo-sito.com/setup.php
 *
 * NOTA: Questo script crea solo se il database è vuoto.
 * Una volta eseguito, rimuovi questofile per sicurezza.
 */
$base_path = __DIR__;
$database_path = $base_path.'/database/database.sqlite';

// Configurazione
$config = [
    'admin_name' => 'Admin',
    'admin_email' => 'admin@comune.bugliano.it',
    'admin_password' => 'admin',
];

function logMessage($message, $type = 'info')
{
    $color = $type === 'error' ? 'red' : ($type === 'success' ? 'green' : 'blue');
    echo "<div style='color: {$color}; padding: 5px 0;'>[{$type}] {$message}</div>";
}

function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function executeSql($pdo, $sql)
{
    try {
        $pdo->exec($sql);

        return true;
    } catch (PDOException $e) {
        return false;
    }
}

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Setup - Segnala un Guasto</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .container { background: #f8f9fa; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px; }
        .btn:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Setup - Segnala un Guasto</h1>";

if (! file_exists($database_path)) {
    echo '<p>Il database non esiste. Creare database/database.sqlite prima di eseguire setup.</p>';
    echo '</div></body></html>';
    exit;
}

try {
    $pdo = new PDO('sqlite:'.$database_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    logMessage('Errore database: '.$e->getMessage(), 'error');
    echo '</div></body></html>';
    exit;
}

// Controllo se database è vuoto
$tablesCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
$tables = $tablesCheck->fetchAll(PDO::FETCH_COLUMN);

if (count($tables) > 0) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h2 style='color: #856404;'>⚠️ Database già configurato</h2>";
    echo '<p>Il database contiene già le seguenti tabelle:</p>';
    echo '<ul>';
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo "<li><strong>{$table}</strong>: {$count} record</li>";
    }
    echo '</ul>';
    echo '<p>QUESTO SETUP È STATO GIA ESEGUITO!</p>';
    echo '<p>Non eseguire questo script se il database ha già dati.</p>';
    echo "<p><a href='/' class='btn'>Vai al sito</a></p>";
    echo '</div></div></body></html>';
    exit;
}

// Creazione tabelle
echo '<h2>1. Creazione Tabelle</h2>';

$tables = [
    'users' => 'CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        created_at DATETIME,
        updated_at DATETIME
    )',
    'segnalazioni' => "CREATE TABLE segnalazioni (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        foto TEXT NOT NULL DEFAULT '[]',
        tipo TEXT NOT NULL,
        lat REAL NOT NULL,
        lng REAL NOT NULL,
        descrizione TEXT,
        status TEXT DEFAULT 'pending',
        is_resolved BOOLEAN DEFAULT 0,
        approved_at DATETIME,
        created_at DATETIME,
        updated_at DATETIME
    )",
    'email_response_messages' => 'CREATE TABLE email_response_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        segnalazione_id INTEGER,
        type TEXT,
        subject TEXT,
        body TEXT,
        external_message_id TEXT,
        in_reply_to TEXT,
        metadata TEXT,
        sent_at DATETIME,
        received_at DATETIME,
        status TEXT,
        FOREIGN KEY (segnalazione_id) REFERENCES segnalazioni(id)
    )',
];

foreach ($tables as $name => $sql) {
    if (executeSql($pdo, $sql)) {
        logMessage("Tabella {$name} creata", 'success');
    } else {
        logMessage("Errore tabella {$name}", 'error');
    }
}

// Creazione admin user
echo '<h2>2. Admin User</h2>';
$hashPassword = hashPassword($config['admin_password']);
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, datetime('now'), datetime('now'))");
$stmt->execute([$config['admin_name'], $config['admin_email'], $hashPassword]);
logMessage("Admin creato: {$config['admin_email']} / {$config['admin_password']}", 'success');

// Segnalazioni demo
echo '<h2>3. Segnalazioni Demo</h2>';

$demoData = [
    ['foto' => '["https://placehold.co/600x400/28a745/white?text=Perdita+Acqua"]', 'tipo' => "perdita d'acqua", 'lat' => 42.4097, 'lng' => 12.8607, 'descrizione' => "Perdita d'acqua in via Roma, fronte civico 10", 'status' => 'pending'],
    ['foto' => '["https://placehold.co/600x400/dc3545/white?text=Buca+Stradale"]', 'tipo' => 'buca stradale', 'lat' => 42.4102, 'lng' => 12.8591, 'descrizione' => 'Buca stradale su via Garibaldi, vicino al semaforo', 'status' => 'approved'],
    ['foto' => '["https://placehold.co/600x400/ffc107/white?text=Lampione+Spento"]', 'tipo' => 'illuminazione pubblica', 'lat' => 42.4089, 'lng' => 12.8621, 'descrizione' => 'Lampione spento in piazza Roma', 'status' => 'approved'],
    ['foto' => '[]', 'tipo' => 'altro', 'lat' => 42.4093, 'lng' => 12.8635, 'descrizione' => 'Cartellonistica fuori uso in via Cavour', 'status' => 'pending'],
];

foreach ($demoData as $data) {
    $stmt = $pdo->prepare("INSERT INTO segnalazioni (foto, tipo, lat, lng, descrizione, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    $stmt->execute([$data['foto'], $data['tipo'], $data['lat'], $data['lng'], $data['descrizione'], $data['status']]);
    logMessage('Demo: '.$data['descrizione'], 'success');
}

echo "<h2 style='color: green;'>✅ Setup completato!</h2>";
echo "<p>Admin: <strong>{$config['admin_email']}</strong> / <strong>{$config['admin_password']}</strong></p>";
echo "<p><a href='/' class='btn'>Vai al sito</a></p>";
echo "<p style='color: #666; font-size: 0.9em;'>Rimuovi questo file setup.php dopo l'uso.</p>";

echo '</div></body></html>';
