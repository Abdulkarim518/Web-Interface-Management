<?php
// 1. DATENBANKVERBINDUNG
require "dbConnect.php";

$fehler = [];
$erfolg = "";

// 2. LOGIK: DATEN SPEICHERN (POST HANDLING)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? null;

    // Klasse speichern
    if ($action === "klasse_save") {
        $klassenname = trim($_POST["klassenname"] ?? "");
        $schuljahr = trim($_POST["schuljahr"] ?? "");

        if ($klassenname === "") {
            $fehler[] = "Bitte einen Klassennamen eingeben.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO klasse (name, schuljahr) VALUES (?, ?)");
            $stmt->execute([$klassenname, $schuljahr ?: null]);
            header("Location: index.php");
            exit;
        }
    }

    // Schüler speichern
    if ($action === "schueler_save") {
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
            header("Location: index.php");
            exit;
        }
    }

    // Fach speichern
    if ($action === "fach_save") {
        $name = trim($_POST["name"] ?? "");

        if ($name === "") {
            $fehler[] = "Bitte einen Fachnamen eingeben.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO fach (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: index.php");
            exit;
        }
    }

    // Klassenarbeit speichern
    if ($action === "klassenarbeit_save") {
        $titel = trim($_POST["titel"] ?? "");
        $fach_id = $_POST["fach_id"] ?? "";
        $klasse_id = $_POST["klasse_id"] ?? "";
        $datum = $_POST["datum"] ?? "";
        $gewichtung = $_POST["gewichtung"] ?? "";

        if ($titel === "" || $fach_id === "" || $klasse_id === "" || $datum === "") {
            $fehler[] = "Bitte Titel, Fach, Klasse und Datum ausfüllen.";
        } elseif ($gewichtung !== "" && $gewichtung <= 0) {
            $fehler[] = "Die Gewichtung muss größer als 0 sein.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO klassenarbeit (titel, fach_id, klasse_id, datum, gewichtung)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $titel,
                $fach_id,
                $klasse_id,
                $datum,
                $gewichtung !== "" ? $gewichtung : 1.00
            ]);
            header("Location: index.php");
            exit;
        }
    }

    // Note speichern
    if ($action === "note_save") {
        $schueler_id = $_POST["schueler_id"] ?? "";
        $klassenarbeit_id = $_POST["klassenarbeit_id"] ?? "";
        $note = $_POST["note"] ?? "";

        if ($schueler_id === "" || $klassenarbeit_id === "" || $note === "") {
            $fehler[] = "Bitte Schüler, Klassenarbeit und Note auswählen.";
        } elseif (!is_numeric($note) || $note < 1 || $note > 6) {
            $fehler[] = "Die Note muss zwischen 1 und 6 liegen.";
        } else {
            // prüfen, ob für diese Kombination schon eine Note existiert
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

            header("Location: index.php");
            exit;
        }
    }
}

// 3. LOGIK: DATEN LADEN (FÜR DROPDOWNS UND TABELLEN)
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
            <input type="number" step="0.01" min="0.01" name="gewichtung" placeholder="Gewichtung z.B. 1.00">
        </div>

        <button type="submit">Speichern</button>
    </form>
</div>

<div class="card">
    <h2>Note eintragen</h2>
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

<div class="card">
    <h2>Auswertung</h2>
    <table>
        <thead>
            <tr>
                <th>Schüler</th>
                <th>Klasse</th>
                <th>Fach</th>
                <th>Arbeit</th>
                <th>Datum</th>
                <th>Note</th>
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>