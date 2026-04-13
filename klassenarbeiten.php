<?php
require "dbConnect.php";

// KLASSENARBEIT SPEICHERN (ohne fach_id)
// PHP-Code
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] === "klassenarbeit_save") {
    if (isset($_POST["titel"]) && !empty($_POST["titel"])) {
        $stmt = $pdo->prepare("INSERT INTO klassenarbeit (titel) VALUES (?)");
        $stmt->execute([$_POST["titel"]]);
        header("Location: index.php");
        exit;
    }
}