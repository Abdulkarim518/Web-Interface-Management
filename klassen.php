<?php
require "dbConnect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["klasse"]) && !empty($_POST["klasse"])) {
        $stmt = $pdo->prepare("INSERT INTO klasse (name) VALUES (?)");
        $stmt->execute([$_POST["klasse"]]);
    }
}

// Zurück zur Startseite
header("Location: index.php");
exit;
?>