<?php
require "dbConnect.php";

/* Klassen laden */
$klassen = $pdo->query("SELECT * FROM klasse")->fetchAll();

/* Schüler laden */
$schueler = $pdo->query("SELECT * FROM schueler")->fetchAll();

/* Fächer laden */
$faecher = $pdo->query("SELECT * FROM fach")->fetchAll();

/* Klassenarbeiten laden */
$klassenarbeiten = $pdo->query("SELECT * FROM klassenarbeit")->fetchAll();
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Schulverwaltungssystem</title>
        <link rel="stylesheet" href="style.css">
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
        <!-- Schüler anlegen -->
        <div class="card">
            <h2>Schüler anlegen</h2>
            <form action="schueler.php" method="post">
                <div class="row">
                    <input name="vorname" placeholder="Vorname" required>
                    <input name="nachname" placeholder="Nachname" required>
                </div>
                <div class="row">
                    
                        
                        <?php foreach($klassen as $k): ?>
                            <option value="<?= $k["id"] ?>">
                                <?= htmlspecialchars($k["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    
               </div>

                 <button type="submit">Schüler Speichern</button>
            </form>
        </div>

        <!-- Klasse anlegen -->
        <!-- Klasse anlegen -->
        <div class="card">
            <h2>Klasse anlegen</h2>
            <form action="index.php" method="post">  <!-- ← Hier geändert -->
                <input type="hidden" name="action" value="klasse_save">  <!-- ← Neu -->
                <div class="row">
                    <input name="klassenname" placeholder="Klassenname" required>
                </div>
                <button type="submit">Klasse Speichern</button>
            </form>
        </div>


        <!-- Klassenarbeit anlegen -->
        <!-- Klassenarbeit anlegen -->
        <div class="card">
            <h2>Klassenarbeit anlegen</h2>
            <form action="index.php" method="post">  <!-- ← HIER GEÄNDERT -->
                <input type="hidden" name="action" value="klassenarbeit_save">  <!-- ← NEU -->
                <div class="row">
                    <input name="titel" placeholder="Titel der Arbeit" required>
                    <div class="row">
                        
                    <option value="">Fach wählen</option>
                            <?php foreach($faecher as $f): ?>
                                <option value="<?= $f["id"] ?>">
                                    <?= htmlspecialchars($f["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        
                    </div>
                </div>
                <button type="submit">Klassenarbeit Speichern</button>
            </form>
        </div>
        <!-- Note eintragen -->
        <div class="card">
            <h2>Note eintragen</h2>
            <form action="note_speichern.php" method="post">
                <div class="row">
                    <select name="schueler_id" required>
                        <option value="">Schüler wählen</option>
                        <?php foreach($schueler as $s): ?>
                            <option value="<?= $s["id"] ?>">
                                <?= htmlspecialchars($s["vorname"] . " " . $s["nachname"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="klassenarbeit_id" required>
                        <option value="">Klassenarbeit wählen</option>
                        <?php foreach($klassenarbeiten as $ka): ?>
                            <option value="<?= $ka["id"] ?>">
                                <?= htmlspecialchars($ka["titel"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="note" required>
                        <option value="">Note wählen</option>
                        <?php for($n = 1; $n <= 6; $n += 0.5): ?>
                            <option value="<?= $n ?>"><?= $n ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit">Note Speichern</button>
            </form>
        </div>

        <!-- Auswertung - Alle Noten -->
        <div class="card">
            <h2>Auswertung – Alle Noten</h2>
            
            <table border="1" width="100%">
                <thead>
                    <tr>
                        <th>Schüler</th>
                        <th>Klasse</th>
                        <th>Fach</th>
                        <th>Klassenarbeit</th>
                        <th>Note</th>
                    </tr>
                </thead>
                
            </table>
        </div>

    </body>
</html>