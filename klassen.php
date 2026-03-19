<?php
    ini_set('display_errors',1);
    error_reporting(E_ALL);

    require "dbConnect.php";

    /* Klasse speichern */
    if (isset($_POST["name"])) {

        $sql = "INSERT INTO klasse (name) VALUES (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST["name"]]);

        header("Location: klassen.php");
        exit;
    }

    /* Klassen laden */
    $stmt = $pdo->prepare("SELECT * FROM klasse");
    $stmt->execute();

    $klassen = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
    <head>
    <meta charset="UTF-8">
    <title>Klassen</title>
    <link rel="stylesheet" href="style.css">
    </head>

    <body>

        <h2>Klassen</h2>

        <form method="post">

            <input type="text" name="name" required placeholder="Klasse eingeben">

            <button>Speichern</button>

        </form>

        <table border="1">

            <tr>
                <th>ID</th>
                <th>Name</th>
            </tr>

            <?php foreach($klassen as $k): ?>
                <tr>
                    <td><?= $k["id"] ?></td>
                    <td><?= htmlspecialchars($k["name"]) ?></td>
                </tr>
            <?php endforeach; ?>

        </table>

    </body>
</html>