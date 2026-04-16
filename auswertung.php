<?php
require "dbConnect.php";

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
    <title>Auswertung</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<header id="seiten-header">
    <h1 id="header-title">Auswertung</h1>
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

<div class="card">
    <h2>Auswertung</h2>
    <table class="modern-table">
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
                    <td class="highlight"><?= htmlspecialchars($n["vorname"] . " " . $n["nachname"]) ?></td>
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