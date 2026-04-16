<?php
require "dbConnect.php";

$fehler = [];

// Schüler speichern
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "schueler_save") {
    $vorname = trim($_POST["vorname"] ?? "");
    $nachname = trim($_POST["nachname"] ?? "");
    $geburtsdatum = $_POST["geburtsdatum"] ?? null;
    $schueler_nr = trim($_POST["schueler_nr"] ?? "");
    $klasse_id = $_POST["klasse_id"] ?? "";

    if ($vorname === "" || $nachname === "" || $klasse_id === "") {
        $fehler[] = "Bitte Vorname, Nachname und Klasse ausfüllen.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO schueler (vorname, nachname, geburtsdatum, schueler_nr, klasse_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $vorname,
            $nachname,
            $geburtsdatum ?: null,
            $schueler_nr ?: null,
            $klasse_id
        ]);
        header("Location: schueler.php");
        exit;
    }
}

// Schüler löschen
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "schueler_delete") {
    $id = $_POST["id"] ?? "";

    if ($id !== "") {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM note WHERE schueler_id = ?");
        $stmt->execute([$id]);
        $notenAnzahl = $stmt->fetchColumn();

        if ($notenAnzahl > 0) {
            $fehler[] = "Der Schüler kann nicht gelöscht werden, weil noch Noten verknüpft sind.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM schueler WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: schueler.php");
            exit;
        }
    }
}

$klassen = $pdo->query("SELECT * FROM klasse ORDER BY name ASC")->fetchAll();

$schueler = $pdo->query("
    SELECT s.*, k.name AS klassenname
    FROM schueler s
    JOIN klasse k ON s.klasse_id = k.id
    ORDER BY s.nachname ASC, s.vorname ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schüler</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header id="seiten-header">
    <h1 id="header-title">Schülerverwaltung</h1>
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
    <h2>Schüler anlegen</h2>
    <form method="post">
        <input type="hidden" name="action" value="schueler_save">

        <div class="row">
            <input name="vorname" placeholder="Vorname" required>
            <input name="nachname" placeholder="Nachname" required>
        </div>

        <div class="row">
            <input type="date" name="geburtsdatum">
            <input name="schueler_nr" placeholder="Schüler-ID">
        </div>

        <select name="klasse_id" required>
            <option value="">Klasse wählen</option>
            <?php foreach ($klassen as $k): ?>
                <option value="<?= htmlspecialchars($k["id"]) ?>">
                    <?= htmlspecialchars($k["name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card">
    <h2>Schüler Übersicht</h2>
    <table class="modern-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Geburtsdatum</th>
                <th>Schüler-ID</th>
                <th>Klasse</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schueler as $s): ?>
                <tr>
                    <td class="highlight"><?= htmlspecialchars($s["vorname"] . " " . $s["nachname"]) ?></td>
                    <td><?= htmlspecialchars($s["geburtsdatum"]) ?></td>
                    <td><?= htmlspecialchars($s["schueler_nr"]) ?></td>
                    <td><?= htmlspecialchars($s["klassenname"]) ?></td>
                    <td class="action-cell">
                        <form method="post" onsubmit="return confirm('Schüler wirklich löschen?');">
                            <input type="hidden" name="action" value="schueler_delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($s["id"]) ?>">
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