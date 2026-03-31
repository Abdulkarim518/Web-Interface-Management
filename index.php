<?php
require "dbConnect.php";

/* Klassen laden */
$klassen = $pdo->query("SELECT * FROM klasse")->fetchAll();

/* Schüler laden */
$schueler = $pdo->query("SELECT * FROM schueler")->fetchAll();

/* Fächer laden */
$faecher = $pdo->query("SELECT * FROM fach")->fetchAll();
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
                    <li><a href="schueler.php">Schüler</a></li>
                    <li><a href="noten.php">Noten</a></li>
                    <li><a href="auswertung.php">Auswertung</a></li>
                </ul>
            </nav>
        </header>

        <hr>

        <!-- Klassen -->
        <div class="card">

            <h2>Schüler anlegen</h2>

            <form action="schueler.php" method="post">

                <input name="vorname" placeholder="Vorname" required>
                <input name="nachname" placeholder="Nachname" required>

                <hr>

                <button>Speichern</button>

            </form>
            
        </div>


        <hr>

        <!-- Schüler -->
        <div class="card">

            <h2>Klasse anlegen</h2>

            <form action="schueler.php" method="post">

                <input name="Klassename" placeholder="Klassename" required>

                <hr>

                <button>Speichern</button>

            </form>

        </div>

        <hr>

        <div class="card">

            <h2>Klassenarbeit anlegen</h2>

            <form action="klassenarbeit.php" method="post">

                <input name="Title der Arbeit" placeholder="Title der Arbeit" required>

                <hr>
                <select>
                    <?php foreach($faecher as $f): ?>
                        <option value="<?= $f["id"] ?>">
                            <?= htmlspecialchars($f["name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <hr>
                <button>Speichern</button>

            </form>

        </div>
        <!-- Noten -->
        <div class="card">

            <h2>Note eintragen</h2>

            <form action="noten.php" method="post">

                <select name="schueler wählen">
                    <?php foreach($schueler as $s): ?>
                        <option value="<?= $s["id"] ?>">
                            <?= htmlspecialchars($s["vorname"]) ?>
                            <?= htmlspecialchars($s["nachname"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="klassenarbeit wählen">
                    <?php foreach($klassenarbeiten as $ka): ?>
                        <option value="<?= $ka["id"] ?>">
                            <?= htmlspecialchars($ka["title"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="Note">
                    <?php foreach($Note as $no): ?>
                        <option value="<?= $no["id"] ?>">
                            <?= htmlspecialchars($no["note"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <hr>

                <button>Speichern</button>

            </form>

        </div>

        <hr>

        <!-- Übersicht -->
        <div class="card">

            <h2>Auswertung - Alle Noten</h2>

            <table border="1" width="100%">

                <tr>
                    <th>Schüler</th>
                    <th>klasse</th>
                    <th>Fach</th>
                    <th>Klassenarbeit</th>
                    <th>Note</th>
                </tr>

                <?php
                    $sql="
                    SELECT
                    schueler.vorname,
                    schueler.nachname,
                    fach.name AS fach,
                    note.arbeit_id,
                    note.note
                    FROM note 
                    JOIN schueler ON note.schueler_id=schueler.id
                    JOIN fach ON note.fach_id=fach.id
                    ORDER BY note.id DESC
                    LIMIT 10
                    ";

                    $daten=$pdo->query($sql)->fetchAll();

                    foreach($daten as $d):
                    ?>

                    <tr>
                        <td><?= $d["vorname"] ?> <?= $d["nachname"] ?></td>
                        <td><?= $d["fach"] ?></td>
                        <td><?= $d["arbeit"] ?></td>
                        <td><?= $d["note"] ?></td>
                    </tr>

                <?php endforeach; ?>

            </table>

        </div>

    </body>
</html>