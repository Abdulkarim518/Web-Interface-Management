<?php
$host = '127.0.0.1';
$dbName = 'schuleverwaltung';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Verbindungsfehler: " . $e->getMessage());
}
// HIER DARF NICHTS MEHR STEHEN! Kein HTML, kein Echo, kein foreach.
?>