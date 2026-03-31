<?php
require "dbConnect.php";

if (isset($_POST["titel"])) {
    $stmt = $pdo->prepare("INSERT INTO klassenarbeit (titel, datum, gewichtung, fach_id)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST["titel"],
        $_POST["datum"],
        $_POST["gewichtung"],
        $_POST["fach"]
    ]);
    header("Location: klassenarbeiten.php");
    exit;
}

$faecher = $pdo->query("SELECT * FROM fach")->fetchAll();
$arbeiten = $pdo->query("SELECT * FROM klassenarbeit")->fetchAll();
?>

<h2>Klassenarbeiten</h2>

<form method="post">
    <input name="titel" placeholder="Titel" required>
    <input type="date" name="datum">
    <input name="gewichtung" placeholder="Gewichtung">

    <select name="fach">
        <?php foreach($faecher as $f): ?>
            <option value="<?= $f["id"] ?>"><?= $f["name"] ?></option>
        <?php endforeach; ?>
    </select>

    <button>Speichern</button>
</form>