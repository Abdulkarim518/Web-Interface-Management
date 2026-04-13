<?php
// index.php (ZENTRALE STEUERUNG)
require "dbConnect.php";

// =====================
// POST HANDLING
// =====================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? null;

    // KLASSE SPEICHERN
    if ($action === "klasse_save") {
        $stmt = $pdo->prepare("INSERT INTO klasse (name) VALUES (?)");
        $stmt->execute([$_POST["klassenname"]]);
    }

    // SCHÜLER SPEICHERN (OHNE AUSWAHL)
    if ($action === "schueler_save") {

        // erste Klasse holen
        $klasse = $pdo->query("SELECT id FROM klasse LIMIT 1")->fetch();

        $stmt = $pdo->prepare("INSERT INTO schueler (vorname, nachname, klasse_id) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST["vorname"],
            $_POST["nachname"],
            $klasse["id"]
        ]);
    }

    // FACH SPEICHERN
    if ($action === "fach_save") {
        $stmt = $pdo->prepare("INSERT INTO fach (name) VALUES (?)");
        $stmt->execute([$_POST["name"]]);
    }

    // KLASSENARBEIT SPEICHERN (OHNE AUSWAHL)
    if ($action === "klassenarbeit_save") {

        // erstes Fach holen
        $fach = $pdo->query("SELECT id FROM fach LIMIT 1")->fetch();

        $stmt = $pdo->prepare("INSERT INTO klassenarbeit (titel, fach_id) VALUES (?, ?)");
        $stmt->execute([
            $_POST["titel"],
            $fach["id"]
        ]);
    }

    // NOTE SPEICHERN
    if ($action === "note_save") {
        $stmt = $pdo->prepare("INSERT INTO note (schueler_id, klassenarbeit_id, note) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST["schueler_id"],
            $_POST["klassenarbeit_id"],
            $_POST["note"]
        ]);
    }

    header("Location: index.php");
    exit;
}
// =====================
// DATEN LADEN
// =====================
$klassen = $pdo->query("SELECT * FROM klasse")->fetchAll();
$schueler = $pdo->query("SELECT * FROM schueler")->fetchAll();
$faecher = $pdo->query("SELECT * FROM fach")->fetchAll();
$klassenarbeiten = $pdo->query("SELECT * FROM klassenarbeit")->fetchAll();

// AUSWERTUNG
$noten = $pdo->query("
SELECT
    s.vorname,
    s.nachname,
    k.name AS klasse,
    f.name AS fach,
    ka.titel,
    n.note
FROM note n
JOIN schueler s ON n.schueler_id = s.id
JOIN klasse k ON s.klasse_id = k.id
JOIN klassenarbeit ka ON n.klassenarbeit_id = ka.id
JOIN fach f ON ka.fach_id = f.id
")->fetchAll();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Schulverwaltung</title>
        <link rel="stylesheet" href="Style.css">
    </head>
    <body>

        <header id="seiten-header">
            <h1 id="header-title">Schulverwaltungssystem</h1>

            <nav id="main-nav">
                <ul>
                    <li><a href="index.php">Start</a></li>
                    <li><a href="schueler.php">Schüler</a></li>
                    <li><a href="noten.php">Noten</a></li>
                    <li><a href="auswertung.php">Auswertung</a></li>
                </ul>
            </nav>
        </header>
        <h2>Schüler anlegen</h2>
        <form method="post">
            <input type="hidden" name="action" value="schueler_save">
            <input name="vorname" placeholder="Vorname" required>
            <input name="nachname" placeholder="Nachname" required>

            

            <button>Speichern</button>
        </form>

        <h2>Klasse anlegen</h2>
            <form method="post">
            <input type="hidden" name="action" value="klasse_save">
            <input name="klassenname" required>

            <select name="klasse" required>
                <option value="">Klasse wählen</option>
                <?php foreach($klassen as $k): ?>
                <option value="<?= $k["id"] ?>"><?= $k["name"] ?></option>
                <?php endforeach; ?>
            </select>
            <button>Speichern</button>
        </form>

        <h2>Fach anlegen</h2>
        <form method="post">
        <input type="hidden" name="action" value="fach_save">
        <input name="name" required>
        <select name="fach_id" required>
            <option value="">Fach wählen</option>
            <?php foreach($faecher as $f): ?>
                <option value="<?= $f["id"] ?>"><?= $f["name"] ?></option>
            <?php endforeach; ?>
        </select>
        <button>Speichern</button>
        </form>

        <h2>Klassenarbeit anlegen</h2>
        <form method="post">
        <input type="hidden" name="action" value="klassenarbeit_save">
        <input name="titel" required>


        <button>Speichern</button>
        </form>

        <h2>Note eintragen</h2>
        <form method="post">
        <input type="hidden" name="action" value="note_save">

        <select name="schueler_id" required>
        <option>Schüler wählen</option>
        <?php foreach($schueler as $s): ?>
        <option value="<?= $s["id"] ?>">
        <?= $s["vorname"] ?> <?= $s["nachname"] ?>
        </option>
        <?php endforeach; ?>
        </select>

        <select name="klassenarbeit_id" required>
        <option>Arbeit wählen</option>
        <?php foreach($klassenarbeiten as $ka): ?>
        <option value="<?= $ka["id"] ?>">
        <?= $ka["titel"] ?>
        </option>
        <?php endforeach; ?>
        </select>

        <select name="note">
        <?php for($i=1;$i<=6;$i+=0.5): ?>
        <option value="<?= $i ?>"><?= $i ?></option>
        <?php endfor; ?>
        </select>

        <button>Speichern</button>
        </form>

        <h2>Auswertung</h2>
        <table border="1">
        <tr>
        <th>Schüler</th>
        <th>Klasse</th>
        <th>Fach</th>
        <th>Arbeit</th>
        <th>Note</th>
        </tr>

        <?php foreach($noten as $n): ?>
        <tr>
        <td><?= $n["vorname"] ?> <?= $n["nachname"] ?></td>
        <td><?= $n["klasse"] ?></td>
        <td><?= $n["fach"] ?></td>
        <td><?= $n["titel"] ?></td>
        <td><?= $n["note"] ?></td>
        </tr>
        <?php endforeach; ?>

        </table>

    </body>
</html>
