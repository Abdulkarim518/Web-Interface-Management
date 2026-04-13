<?php
require "dbConnect.php";

$sql = "
SELECT
    s.vorname,
    s.nachname,
    k.name AS klasse,
    f.name AS fach,
    ka.titel AS arbeit,
    n.note
FROM note n
JOIN schueler s ON n.schueler_id = s.id
JOIN klasse k ON s.klasse_id = k.id
JOIN klassenarbeit ka ON n.klassenarbeit_id = ka.id
JOIN fach f ON ka.fach_id = f.id
";

$daten = $pdo->query($sql)->fetchAll();
?>

<h2>Auswertung</h2>

<table border="1">
<tr>
<th>Schüler</th>
<th>Klasse</th>
<th>Fach</th>
<th>Arbeit</th>
<th>Note</th>
</tr>

<?php foreach($daten as $d): ?>
<tr>
<td><?= $d["vorname"] ?> <?= $d["nachname"] ?></td>
<td><?= $d["klasse"] ?></td>
<td><?= $d["fach"] ?></td>
<td><?= $d["arbeit"] ?></td>
<td><?= $d["note"] ?></td>
</tr>
<?php endforeach; ?>

</table>