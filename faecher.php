<?php
require "dbConnect.php";

$fehler = [];

// Fach speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "fach_save") {
    $name = trim($_POST["name"] ?? "");

    if ($name === "") {
        $fehler[] = "Bitte einen Fachnamen eingeben.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO fach (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: faecher.php");
        exit;
    }
}

$faecher = $pdo->query("SELECT * FROM fach ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fächer</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header id="seiten-header">
    <h1 id="header-title">Fächerverwaltung</h1>
    <nav id="main-nav">
        <ul>
            <li><a href="index.php">Start</a></li>
            <li><a href="klassen.php">Klassen</a></li>
            <li><a href="schueler.php">Schüler</a></li>
            <li><a href="faecher.php">Fächer</a></li>
            <li><a href="klassenarbeiten.php">Klassenarbeiten</a></li>
            <li><a href="noten.php">Noten</a></li>
            <li><a href="auswertung.php">Auswertung</a></li>
        </ul>
    </nav>
</header>

<?php if (!empty($fehler)): ?>
    <div class="card">
        <h2>Fehler</h2>
        <?php foreach ($fehler as $f): ?>
            <p><?= htmlspecialchars($f) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2>Fach anlegen</h2>
    <form method="post">
        <input type="hidden" name="action" value="fach_save">
        <input name="name" placeholder="Fachname (z.B. Mathematik)" required>
        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card">
    <h2>Fächer Übersicht</h2>
    <table class="modern-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fach</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faecher as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f["id"]) ?></td>
                    <td class="highlight"><?= htmlspecialchars($f["name"]) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>