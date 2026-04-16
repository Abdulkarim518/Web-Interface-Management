<?php
require "dbConnect.php";

// GETRENNTE FEHLERARRAYS
$fehlerKlasseSave = [];
$fehlerKlasseDelete = [];

$fehlerSchuelerSave = [];
$fehlerSchuelerDelete = [];

$fehlerFachSave = [];

$fehlerKlassenarbeitSave = [];
$fehlerKlassenarbeitDelete = [];

$fehlerNoteSave = [];
$fehlerNoteDelete = [];

// POST-LOGIK
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;

    // Klasse speichern
    if ($action === "klasse_save") {
        $klassenname = trim($_POST["klassenname"] ?? "");
        $schuljahr = trim($_POST["schuljahr"] ?? "");

        if ($klassenname === "") {
            $fehlerKlasseSave[] = "Bitte einen Klassennamen eingeben.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO klasse (name, schuljahr) VALUES (?, ?)");
            $stmt->execute([$klassenname, $schuljahr ?: null]);
            header("Location: index.php#klassen-liste");
            exit;
        }
    }

    // Schüler speichern
    elseif ($action === "schueler_save") {
        $vorname = trim($_POST["vorname"] ?? "");
        $nachname = trim($_POST["nachname"] ?? "");
        $geburtsdatum = $_POST["geburtsdatum"] ?? null;
        $schueler_nr = trim($_POST["schueler_nr"] ?? "");
        $klasse_id = $_POST["klasse_id"] ?? "";

        if ($vorname === "" || $nachname === "" || $klasse_id === "") {
            $fehlerSchuelerSave[] = "Bitte Vorname, Nachname und Klasse ausfüllen.";
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
            header("Location: index.php#schueler-liste");
            exit;
        }
    }

    // Fach speichern
    elseif ($action === "fach_save") {
        $name = trim($_POST["name"] ?? "");

        if ($name === "") {
            $fehlerFachSave[] = "Bitte einen Fachnamen eingeben.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO fach (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: index.php#fach-bereich");
            exit;
        }
    }

    // Klassenarbeit speichern
    elseif ($action === "klassenarbeit_save") {
        $titel = trim($_POST["titel"] ?? "");
        $fach_id = $_POST["fach_id"] ?? "";
        $klasse_id = $_POST["klasse_id"] ?? "";
        $datum = $_POST["datum"] ?? "";

        if ($titel === "" || $fach_id === "" || $klasse_id === "" || $datum === "") {
            $fehlerKlassenarbeitSave[] = "Bitte Titel, Fach, Klasse und Datum ausfüllen.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO klassenarbeit (titel, fach_id, klasse_id, datum)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $titel,
                $fach_id,
                $klasse_id,
                $datum
            ]);
            header("Location: index.php#klassenarbeiten-liste");
            exit;
        }
    }

    // Note speichern
    elseif ($action === "note_save") {
        $schueler_id = $_POST["schueler_id"] ?? "";
        $klassenarbeit_id = $_POST["klassenarbeit_id"] ?? "";
        $note = $_POST["note"] ?? "";

        if ($schueler_id === "" || $klassenarbeit_id === "" || $note === "") {
            $fehlerNoteSave[] = "Bitte Schüler, Klassenarbeit und Note auswählen.";
        } elseif (!is_numeric($note) || $note < 1 || $note > 6) {
            $fehlerNoteSave[] = "Die Note muss zwischen 1 und 6 liegen.";
        } else {
            $check = $pdo->prepare("
                SELECT id FROM note
                WHERE schueler_id = ? AND klassenarbeit_id = ?
            ");
            $check->execute([$schueler_id, $klassenarbeit_id]);
            $vorhanden = $check->fetch();

            if ($vorhanden) {
                $stmt = $pdo->prepare("
                    UPDATE note
                    SET note = ?
                    WHERE schueler_id = ? AND klassenarbeit_id = ?
                ");
                $stmt->execute([$note, $schueler_id, $klassenarbeit_id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO note (schueler_id, klassenarbeit_id, note)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$schueler_id, $klassenarbeit_id, $note]);
            }

            header("Location: index.php#auswertung");
            exit;
        }
    }

    // Klasse löschen
    elseif ($action === "klasse_delete") {
        $id = $_POST["klasse_id"] ?? "";

        if ($id !== "") {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM schueler WHERE klasse_id = ?");
            $stmt->execute([$id]);
            $schuelerAnzahl = $stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM klassenarbeit WHERE klasse_id = ?");
            $stmt->execute([$id]);
            $arbeitenAnzahl = $stmt->fetchColumn();

            if ($schuelerAnzahl > 0 || $arbeitenAnzahl > 0) {
                $fehlerKlasseDelete[] = "Die Klasse kann nicht gelöscht werden, weil noch Schüler oder Klassenarbeiten damit verknüpft sind.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM klasse WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: index.php#klassen-liste");
                exit;
            }
        }
    }

    // Schüler löschen
    elseif ($action === "schueler_delete") {
        $id = $_POST["schueler_id"] ?? "";

        if ($id !== "") {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM note WHERE schueler_id = ?");
            $stmt->execute([$id]);
            $notenAnzahl = $stmt->fetchColumn();

            if ($notenAnzahl > 0) {
                $fehlerSchuelerDelete[] = "Der Schüler kann nicht gelöscht werden, weil noch Noten mit ihm verknüpft sind.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM schueler WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: index.php#schueler-liste");
                exit;
            }
        }
    }

    // Klassenarbeit löschen
    elseif ($action === "klassenarbeit_delete") {
        $id = $_POST["klassenarbeit_id"] ?? "";

        if ($id !== "") {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM note WHERE klassenarbeit_id = ?");
            $stmt->execute([$id]);
            $notenAnzahl = $stmt->fetchColumn();

            if ($notenAnzahl > 0) {
                $fehlerKlassenarbeitDelete[] = "Die Klassenarbeit kann nicht gelöscht werden, weil noch Noten damit verknüpft sind.";
            } else {
                $stmt = $pdo->prepare("DELETE FROM klassenarbeit WHERE id = ?");
                $stmt->execute([$id]);
                header("Location: index.php#klassenarbeiten-liste");
                exit;
            }
        }
    }

    // Note löschen
    elseif ($action === "note_delete") {
        $id = $_POST["note_id"] ?? "";

        if ($id !== "") {
            $stmt = $pdo->prepare("DELETE FROM note WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: index.php#auswertung");
            exit;
        }
    }
}

// DATEN LADEN
$klassen = $pdo->query("SELECT * FROM klasse ORDER BY name ASC")->fetchAll();
$schueler = $pdo->query("SELECT * FROM schueler ORDER BY nachname ASC, vorname ASC")->fetchAll();
$faecher = $pdo->query("SELECT * FROM fach ORDER BY name ASC")->fetchAll();

$klassenarbeiten = $pdo->query("
    SELECT ka.*, f.name AS fach_name, k.name AS klassen_name
    FROM klassenarbeit ka
    JOIN fach f ON ka.fach_id = f.id
    JOIN klasse k ON ka.klasse_id = k.id
    ORDER BY ka.datum DESC, ka.titel ASC
")->fetchAll();

$noten = $pdo->query("
    SELECT 
        n.id,
        s.vorname,
        s.nachname,
        k.name AS klasse,
        f.name AS fach,
        ka.titel,
        ka.datum,
        n.note
    FROM note n
    JOIN schueler s ON n.schueler_id = s.id
    JOIN klasse k ON s.klasse_id = k.id
    JOIN klassenarbeit ka ON n.klassenarbeit_id = ka.id
    JOIN fach f ON ka.fach_id = f.id
    ORDER BY s.nachname ASC, s.vorname ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schulverwaltungssystem</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body id="start">

<header id="seiten-header">
    <h1 id="header-title">Schulverwaltungssystem</h1>
    <nav id="main-nav">
        <ul>
            <li><a href="#start">Start</a></li>
            <li><a href="#schueler-bereich">Schüler</a></li>
            <li><a href="#noten-bereich">Noten</a></li>
            <li><a href="#auswertung">Auswertung</a></li>
        </ul>
    </nav>
</header>

<div class="card" id="klassen-bereich">
    <h2>Klasse anlegen</h2>

    <?php if (!empty($fehlerKlasseSave)): ?>
        <div class="error-box">
            <?php foreach ($fehlerKlasseSave as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="klasse_save">
        <input name="klassenname" placeholder="Name der Klasse (z.B. E2FI)" required>
        <input name="schuljahr" placeholder="Schuljahr (z.B. 2025/2026)">
        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card" id="klassen-liste">
    <h2>Klassen Übersicht</h2>

    <?php if (!empty($fehlerKlasseDelete)): ?>
        <div class="error-box">
            <?php foreach ($fehlerKlasseDelete as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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
                            <input type="hidden" name="klasse_id" value="<?= htmlspecialchars($k["id"]) ?>">
                            <button type="submit" class="delete-btn">✖</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" id="schueler-bereich">
    <h2>Schüler anlegen</h2>

    <?php if (!empty($fehlerSchuelerSave)): ?>
        <div class="error-box">
            <?php foreach ($fehlerSchuelerSave as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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

<div class="card" id="schueler-liste">
    <h2>Schüler Übersicht</h2>

    <?php if (!empty($fehlerSchuelerDelete)): ?>
        <div class="error-box">
            <?php foreach ($fehlerSchuelerDelete as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <table class="modern-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Geburtsdatum</th>
                <th>Schüler-ID</th>
                <th>Klasse-ID</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schueler as $s): ?>
                <tr>
                    <td class="highlight"><?= htmlspecialchars($s["vorname"] . " " . $s["nachname"]) ?></td>
                    <td><?= htmlspecialchars($s["geburtsdatum"]) ?></td>
                    <td><?= htmlspecialchars($s["schueler_nr"]) ?></td>
                    <td><?= htmlspecialchars($s["klasse_id"]) ?></td>
                    <td class="action-cell">
                        <form method="post" onsubmit="return confirm('Schüler wirklich löschen?');">
                            <input type="hidden" name="action" value="schueler_delete">
                            <input type="hidden" name="schueler_id" value="<?= htmlspecialchars($s["id"]) ?>">
                            <button type="submit" class="delete-btn">✖</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card" id="fach-bereich">
    <h2>Fach anlegen</h2>

    <?php if (!empty($fehlerFachSave)): ?>
        <div class="error-box">
            <?php foreach ($fehlerFachSave as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="fach_save">
        <input name="name" placeholder="Fachname (z.B. Mathematik)" required>
        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card" id="noten-bereich">
    <h2>Klassenarbeit anlegen</h2>

    <?php if (!empty($fehlerKlassenarbeitSave)): ?>
        <div class="error-box">
            <?php foreach ($fehlerKlassenarbeitSave as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="klassenarbeit_save">
        <input name="titel" placeholder="Titel der Arbeit" required>

        <select name="fach_id" required>
            <option value="">Fach wählen</option>
            <?php foreach ($faecher as $f): ?>
                <option value="<?= htmlspecialchars($f["id"]) ?>">
                    <?= htmlspecialchars($f["name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="klasse_id" required>
            <option value="">Klasse wählen</option>
            <?php foreach ($klassen as $k): ?>
                <option value="<?= htmlspecialchars($k["id"]) ?>">
                    <?= htmlspecialchars($k["name"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="row">
            <input type="date" name="datum" required>
        </div>

        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card" id="klassenarbeiten-liste">
    <h2>Klassenarbeiten Übersicht</h2>

    <?php if (!empty($fehlerKlassenarbeitDelete)): ?>
        <div class="error-box">
            <?php foreach ($fehlerKlassenarbeitDelete as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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
                            <input type="hidden" name="klassenarbeit_id" value="<?= htmlspecialchars($ka["id"]) ?>">
                            <button type="submit" class="delete-btn">✖</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Note eintragen</h2>

    <?php if (!empty($fehlerNoteSave)): ?>
        <div class="error-box">
            <?php foreach ($fehlerNoteSave as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="note_save">

        <select name="schueler_id" required>
            <option value="">Schüler wählen</option>
            <?php foreach ($schueler as $s): ?>
                <option value="<?= htmlspecialchars($s["id"]) ?>">
                    <?= htmlspecialchars($s["vorname"] . " " . $s["nachname"]) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="klassenarbeit_id" required>
            <option value="">Arbeit wählen</option>
            <?php foreach ($klassenarbeiten as $ka): ?>
                <option value="<?= htmlspecialchars($ka["id"]) ?>">
                    <?= htmlspecialchars($ka["titel"] . " - " . $ka["fach_name"] . " (" . $ka["klassen_name"] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="note" required>
            <option value="">Note wählen</option>
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>

        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card" id="auswertung">
    <h2>Auswertung</h2>

    <?php if (!empty($fehlerNoteDelete)): ?>
        <div class="error-box">
            <?php foreach ($fehlerNoteDelete as $f): ?>
                <p><?= htmlspecialchars($f) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Schüler</th>
                <th>Klasse</th>
                <th>Fach</th>
                <th>Arbeit</th>
                <th>Datum</th>
                <th>Note</th>
                <th>Löschen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($noten as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n["vorname"] . " " . $n["nachname"]) ?></td>
                    <td><?= htmlspecialchars($n["klasse"]) ?></td>
                    <td><?= htmlspecialchars($n["fach"]) ?></td>
                    <td><?= htmlspecialchars($n["titel"]) ?></td>
                    <td><?= htmlspecialchars($n["datum"]) ?></td>
                    <td><strong><?= htmlspecialchars($n["note"]) ?></strong></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Note wirklich löschen?');">
                            <input type="hidden" name="action" value="note_delete">
                            <input type="hidden" name="note_id" value="<?= htmlspecialchars($n["id"]) ?>">
                            <button type="submit">Löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>