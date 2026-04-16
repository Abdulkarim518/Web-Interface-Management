<?php
require "dbConnect.php";

$fehler = [];

// Klassenarbeit speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "klassenarbeit_save") {
    $titel = trim($_POST["titel"] ?? "");
    $fach_id = $_POST["fach_id"] ?? "";
    $klasse_id = $_POST["klasse_id"] ?? "";
    $datum = $_POST["datum"] ?? "";

    if ($titel === "" || $fach_id === "" || $klasse_id === "" || $datum === "") {
        $fehler[] = "Bitte Titel, Fach, Klasse und Datum ausfüllen.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO klassenarbeit (titel, fach_id, klasse_id, datum)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$titel, $fach_id, $klasse_id, $datum]);
        header("Location: klassenarbeiten.php");
        exit;
    }
}

// Klassenarbeit löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "klassenarbeit_delete") {
    $id = $_POST["id"] ?? "";

    if ($id !== "") {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM note WHERE klassenarbeit_id = ?");
        $stmt->execute([$id]);
        $notenAnzahl = $stmt->fetchColumn();

        if ($notenAnzahl > 0) {
            $fehler[] = "Die Klassenarbeit kann nicht gelöscht werden, weil noch Noten verknüpft sind.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM klassenarbeit WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: klassenarbeiten.php");
            exit;
        }
    }
}

$faecher = $pdo->query("SELECT * FROM fach ORDER BY name ASC")->fetchAll();
$klassen = $pdo->query("SELECT * FROM klasse ORDER BY name ASC")->fetchAll();

$klassenarbeiten = $pdo->query("
    SELECT ka.*, f.name AS fach_name, k.name AS klassen_name
    FROM klassenarbeit ka
    JOIN fach f ON ka.fach_id = f.id
    JOIN klasse k ON ka.klasse_id = k.id
    ORDER BY ka.datum DESC, ka.titel ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klassenarbeiten</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header id="seiten-header">
    <h1 id="header-title">Klassenarbeiten</h1>
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
    <h2>Klassenarbeit anlegen</h2>
    <form method="post">
        <input type="hidden" name="action" value="klassenarbeit_save">
        <input name="titel" placeholder="Titel der Arbeit" required>

        <select name="fach_id" required>
            <option value="">Fach wählen</option>
            <?php foreach ($faecher as $f): ?>
                <option value="<?= htmlspecialchars($f["id"]) ?>"><?= htmlspecialchars($f["name"]) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="klasse_id" required>
            <option value="">Klasse wählen</option>
            <?php foreach ($klassen as $k): ?>
                <option value="<?= htmlspecialchars($k["id"]) ?>"><?= htmlspecialchars($k["name"]) ?></option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="datum" required>

        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card">
    <h2>Klassenarbeiten Übersicht</h2>
    <table class="modern-table">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Fach</th>
                <th>Klasse</th>
                <th>Datum</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($klassenarbeiten as $ka): ?>
                <tr>
                    <td class="highlight"><?= htmlspecialchars($ka["titel"]) ?></td>
                    <td><?= htmlspecialchars($ka["fach_name"]) ?></td>
                    <td><?= htmlspecialchars($ka["klassen_name"]) ?></td>
                    <td><?= htmlspecialchars($ka["datum"]) ?></td>
                    <td class="action-cell">
                        <form method="post" onsubmit="return confirm('Klassenarbeit wirklich löschen?');">
                            <input type="hidden" name="action" value="klassenarbeit_delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($ka["id"]) ?>">
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