<?php
require "dbConnect.php";

$fehler = [];

// Klasse speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "klasse_save") {
    $klassenname = trim($_POST["klassenname"] ?? "");
    $schuljahr = trim($_POST["schuljahr"] ?? "");

    if ($klassenname === "") {
        $fehler[] = "Bitte einen Klassennamen eingeben.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO klasse (name, schuljahr) VALUES (?, ?)");
        $stmt->execute([$klassenname, $schuljahr ?: null]);
        header("Location: klassen.php");
        exit;
    }
}

// Klasse löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "klasse_delete") {
    $id = $_POST["id"] ?? "";

    if ($id !== "") {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM schueler WHERE klasse_id = ?");
        $stmt->execute([$id]);
        $schuelerAnzahl = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM klassenarbeit WHERE klasse_id = ?");
        $stmt->execute([$id]);
        $arbeitenAnzahl = $stmt->fetchColumn();

        if ($schuelerAnzahl > 0 || $arbeitenAnzahl > 0) {
            $fehler[] = "Die Klasse kann nicht gelöscht werden, weil noch Schüler oder Klassenarbeiten verknüpft sind.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM klasse WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: klassen.php");
            exit;
        }
    }
}

$klassen = $pdo->query("SELECT * FROM klasse ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klassen</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header id="seiten-header">
    <h1 id="header-title">Klassenverwaltung</h1>
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
    <h2>Klasse anlegen</h2>
    <form method="post">
        <input type="hidden" name="action" value="klasse_save">
        <input name="klassenname" placeholder="Name der Klasse (z.B. E2FI)" required>
        <input name="schuljahr" placeholder="Schuljahr (z.B. 2025/2026)">
        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card">
    <h2>Klassen Übersicht</h2>
    <table class="modern-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Klasse</th>
                <th>Schuljahr</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($klassen as $k): ?>
                <tr>
                    <td><?= htmlspecialchars($k["id"]) ?></td>
                    <td class="highlight"><?= htmlspecialchars($k["name"]) ?></td>
                    <td><?= htmlspecialchars($k["schuljahr"]) ?></td>
                    <td class="action-cell">
                        <form method="post" onsubmit="return confirm('Klasse wirklich löschen?');">
                            <input type="hidden" name="action" value="klasse_delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($k["id"]) ?>">
                            <button type="submit" class="delete-btn">✖</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>