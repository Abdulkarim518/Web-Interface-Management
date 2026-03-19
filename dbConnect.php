<?php
$host = '127.0.0.1';
$dbName = 'schuleverwaltung';
$user = 'root';
$pass = '';

try{
        // Wir legen den weg zur Datenbank fest
        // $dsn --> "Data Source Name" frei festgelegter Variablename 
        $dsn = "mysql:host=$host; dbname=$dbName; charset=utf8mb4;";

        // wir erzeugen ein neues Datenbankzugriffsobjekt (Verbindung zur Datenbank)
        $pdo = new PDO($dsn, $user, $pass);

        // wir statten unser Datenbankobjekt mit der Möglicühkeit aus, Fehlermeldung anzuzeigen
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = "SELECT * FROM schueler";
        $ergebnis = $pdo->query($statement);
        
        // gib mir ein assoziatives array zurück --> ["Vorname "] = "Theo"
        $schueler = $ergebnis->fetchAll(PDO::FETCH_ASSOC);

        // wir durchlaufen alle Datensätze über eine Schleife
        echo "<table>";
        foreach($schueler as $s):
            echo "<tr>";
                echo "<td>" . htmlspecialchars($s["id"]) . "</td>";
                echo "<td>" . htmlspecialchars($s["vorname"]) . "</td>";
                echo "<td>" . htmlspecialchars($s["nachname"]) . "</td>";
            echo "</tr>";
        endforeach;
        
        echo "</table>";
        
        //$erstesStatement = $pdo->query("SELECT NOW()");
        //$dbTime = $erstesStatement->fetchColumn();

        //echo "<p>Die aktuelle zeit der Datenbank lautet:. $dbTime . </p>";

    }
    catch(PDOException $e){

        echo"<p>Verbindungsfehler ... " . $e->getMessage() . "</p>";
        // wenn es nicht funktioniert - dann fange den Fehler auf.
    }
?>
