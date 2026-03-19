<?php
    require "dbConnect.php";

    $sql = "
    SELECT
        s.vorname,
        s.nachname,
        f.name AS fach,
        n.arbeit,
        n.note
    FROM note n
    JOIN schueler s ON n.schueler_id = s.id
    JOIN fach f ON n.fach_id = f.id
    ";

    $daten = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Auswertung</title>
        <link rel="stylesheet" href="Style.css">
    </head>

    <body>

        <h2>Auswertung</h2>

        <table border="1" width="100%">

            <tr>
                <th>Schüler</th>
                <th>Fach</th>
                <th>Arbeit</th>
                <th>Note</th>
            </tr>

            <?php if(count($daten) === 0): ?>
                <tr>
                    <td colspan="4">Keine Daten vorhanden</td>
                </tr>
            <?php endif; ?>

            <?php foreach($daten as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d["vorname"]) ?> <?= htmlspecialchars($d["nachname"]) ?></td>
                    <td><?= htmlspecialchars($d["fach"]) ?></td>
                    <td><?= htmlspecialchars($d["arbeit"]) ?></td>
                    <td><?= htmlspecialchars($d["note"]) ?></td>
                </tr>
            <?php endforeach; ?>

        </table>

    </body>
</html>