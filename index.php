<?php
// 1. DATENBANKVERBINDUNG
require "dbConnect.php";

// 2. LOGIK: DATEN SPEICHERN (POST HANDLING)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;

    // Klasse speichern
    if ($action === "klasse_save" && !empty($_POST["klassenname"])) {
        $stmt = $pdo->prepare("INSERT INTO klasse (name) VALUES (?)");
        $stmt->execute([$_POST["klassenname"]]);
    }

    // Schüler speichern
    if ($action === "schueler_save" && !empty($_POST["vorname"]) && !empty($_POST["nachname"]) && !empty($_POST["klasse_id"])) {
        $stmt = $pdo->prepare("INSERT INTO schueler (vorname, nachname, klasse_id) VALUES (?, ?, ?)");
        $stmt->execute([$_POST["vorname"], $_POST["nachname"], $_POST["klasse_id"]]);
    }

    // Fach speichern
    if ($action === "fach_save" && !empty($_POST["name"])) {
        $stmt = $pdo->prepare("INSERT INTO fach (name) VALUES (?)");
        $stmt->execute([$_POST["name"]]);
    }

    // Klassenarbeit speichern
    if ($action === "klassenarbeit_save" && !empty($_POST["titel"]) && !empty($_POST["fach_id"])) {
        $stmt = $pdo->prepare("INSERT INTO klassenarbeit (titel, fach_id) VALUES (?, ?)");
        $stmt->execute([$_POST["titel"], $_POST["fach_id"]]);
    }

    // Note speichern
    if ($action === "note_save" && !empty($_POST["schueler_id"]) && !empty($_POST["klassenarbeit_id"])) {
        $stmt = $pdo->prepare("INSERT INTO note (schueler_id, klassenarbeit_id, note) VALUES (?, ?, ?)");
        $stmt->execute([$_POST["schueler_id"], $_POST["klassenarbeit_id"], $_POST["note"]]);
    }

    // Nach dem Speichern Seite neu laden, um "Doppel-Absenden" zu verhindern
    header("Location: index.php");
    exit;
}

// 3. LOGIK: DATEN LADEN (FÜR DIE DROPDOWNS UND TABELLEN)
$klassen = $pdo->query("SELECT * FROM klasse ORDER BY name")->fetchAll();
$schueler = $pdo->query("SELECT * FROM schueler ORDER BY nachname")->fetchAll();
$faecher = $pdo->query("SELECT * FROM fach ORDER BY name")->fetchAll();
$klassenarbeiten = $pdo->query("SELECT * FROM klassenarbeit ORDER BY titel")->fetchAll();

$noten = $pdo->query("
    SELECT s.vorname, s.nachname, k.name AS klasse, f.name AS fach, ka.titel, n.note
    FROM note n
    JOIN schueler s ON n.schueler_id = s.id
    JOIN klasse k ON s.klasse_id = k.id
    JOIN klassenarbeit ka ON n.klassenarbeit_id = ka.id
    JOIN fach f ON ka.fach_id = f.id
    ORDER BY s.nachname ASC
")->fetchAll();

// AB HIER ERST HTML!
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Schulverwaltungssystem</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

    <header id="seiten-header">
        <h1 id="header-title">Schulverwaltungssystem</h1>
        <nav id="main-nav">
            <ul>
                <li><a href="index.php">Start</a></li>
                <li><a href="#">Schüler</a></li>
                <li><a href="#">Noten</a></li>
                <li><a href="#">Auswertung</a></li>
            </ul>
        </nav>
    </header>

    <div class="card">
        <h2>Schüler anlegen</h2>
        <form method="post">
            <input type="hidden" name="action" value="schueler_save">
            <div class="row">
                <input name="vorname" placeholder="Vorname" required>
                <input name="nachname" placeholder="Nachname" required>
            </div>
            <select name="klasse_id" required>
                <option value="">Klasse wählen</option>
                <?php foreach($klassen as $k): ?>
                    <option value="<?= $k["id"] ?>"><?= htmlspecialchars($k["name"]) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Klasse anlegen</h2>
        <form method="post">
            <input type="hidden" name="action" value="klasse_save">
            <input name="klassenname" placeholder="Name der Klasse (z.B. 10a)" required>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Fach anlegen</h2>
        <form method="post">
            <input type="hidden" name="action" value="fach_save">
            <input name="name" placeholder="Fachname (z.B. Mathematik)" required>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Klassenarbeit anlegen</h2>
        <form method="post">
            <input type="hidden" name="action" value="klassenarbeit_save">
            <input name="titel" placeholder="Titel der Arbeit" required>
            <select name="fach_id" required>
                <option value="">Fach wählen</option>
                <?php foreach($faecher as $f): ?>
                    <option value="<?= $f["id"] ?>"><?= htmlspecialchars($f["name"]) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Note eintragen</h2>
        <form method="post">
            <input type="hidden" name="action" value="note_save">
            <select name="schueler_id" required>
                <option value="">Schüler wählen</option>
                <?php foreach($schueler as $s): ?>
                    <option value="<?= $s["id"] ?>"><?= htmlspecialchars($s["vorname"] . " " . $s["nachname"]) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="klassenarbeit_id" required>
                <option value="">Arbeit wählen</option>
                <?php foreach($klassenarbeiten as $ka): ?>
                    <option value="<?= $ka["id"] ?>"><?= htmlspecialchars($ka["titel"]) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="note" required>
                <?php for($i=1; $i<=6; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit">Speichern</button>
        </form>
    </div>

    <div class="card">
        <h2>Auswertung</h2>
        <table>
            <thead>
                <tr>
                    <th>Schüler</th>
                    <th>Klasse</th>
                    <th>Fach</th>
                    <th>Arbeit</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($noten as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n["vorname"] . " " . $n["nachname"]) ?></td>
                    <td><?= htmlspecialchars($n["klasse"]) ?></td>
                    <td><?= htmlspecialchars($n["fach"]) ?></td>
                    <td><?= htmlspecialchars($n["titel"]) ?></td>
                    <td><strong><?= $n["note"] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>