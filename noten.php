<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    require "dbConnect.php";

    /* Note speichern */
    if (isset($_POST["note"])) {

        $sql = "INSERT INTO note
                (schueler_id, fach_id, arbeit_id, note)
                VALUES (?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["schueler"],
            $_POST["fach"],
            $_POST["arbeit"],
            $_POST["note"]
        ]);

        header("Location: noten.php");
        exit;
    }

    /* Daten holen */
    $schueler = $pdo->query("SELECT * FROM schueler")
                ->fetchAll(PDO::FETCH_ASSOC);

    $faecher = $pdo->query("SELECT * FROM fach")
                ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Noten</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <h2>Noten eintragen</h2>

        <form method="post">

            <select name="schueler" required>
                <?php foreach($schueler as $s): ?>
                    <option value="<?= $s["id"] ?>">
                        <?= htmlspecialchars($s["vorname"]) ?>
                        <?= htmlspecialchars($s["nachname"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="fach" required>
                <?php foreach($faecher as $f): ?>
                    <option value="<?= $f["id"] ?>">
                        <?= htmlspecialchars($f["name"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input name="arbeit" placeholder="Arbeit" required>

            <input type="number" step="0.1" min="1" max="6" name="note" required>

            <button>Speichern</button>

        </form>

    </body>
</html>