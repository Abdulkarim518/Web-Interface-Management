<?php
require "dbConnect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["vorname"], $_POST["nachname"], $_POST["klasse"]) 
        && !empty($_POST["vorname"]) 
        && !empty($_POST["nachname"]) 
        && !empty($_POST["klasse"])) {
        
        $stmt = $pdo->prepare("INSERT INTO schueler (vorname, nachname, klasse_id) VALUES (?, ?, ?)");
        $stmt->execute([$_POST["vorname"], $_POST["nachname"], $_POST["klasse"]]);
    }
}

header("Location: index.php");
exit;
?>