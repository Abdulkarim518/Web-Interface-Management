<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    require "dbConnect.php";

    /* Schüler speichern */
    if (isset($_POST["vorname"])) {

        $sql = "INSERT INTO schueler (vorname, nachname, klasse_id)
                VALUES (?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST["vorname"],
            $_POST["nachname"],
            $_POST["klasse"]
        ]);

        header("Location: schueler.php");
        exit;
    }

    /* Klassen holen */
    $klassen = $pdo->query("SELECT * FROM klasse")
                ->fetchAll(PDO::FETCH_ASSOC);

    /* Schüler holen */
    $schueler = $pdo->query("SELECT * FROM schueler")
                ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Schüler</title>
        <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <h2>Schüler</h2>

        <form method="post">

            <input name="vorname" placeholder="Vorname" required>
            <input name="nachname" placeholder="Nachname" required>

            <select name="klasse" required>
                <?php foreach($klassen as $k): ?>
                    <option value="<?= $k["id"] ?>">
                        <?= htmlspecialchars($k["name"]) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button>Speichern</button>

        </form>

        <br>

        <table border="1" width="100%">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Klasse-ID</th>
        </tr>

        <?php foreach($schueler as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s["id"]) ?></td>
                <td>
                    <?= htmlspecialchars($s["vorname"]) ?>
                    <?= htmlspecialchars($s["nachname"]) ?>
                </td>
                <td><?= htmlspecialchars($s["klasse_id"]) ?></td>
            </tr>
        <?php endforeach; ?>

        </table>

    </body>
</html>