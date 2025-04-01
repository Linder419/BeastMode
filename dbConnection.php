<?php 
//dbConnection.php
//externes Skript zum Verbindungsaufbau mit der Datenbank
//externen Skripten werden verwendet, um php-Skriptteile mehrmals wiederverwenden zu können.

    try{
        //1. Schritt: Verbindungsaufbau herstellen
        $dsn = "mysql:host=localhost;dbname=beastmode;charset=utf8";

        //2. Schritt: PDO (Verbindungsobjekt) erstellen
        #Verbindungsaufbau über PDO (PHP Data Object) muss erlaubt sein (siehe php.ini)
        $pdo = new PDO($dsn,'root','');

    }catch(PDOException $e){
        echo $e -> getMessage() . "<br>";
        die ("Es ist ein Fehler beim Verbindungsaufbau zur Datenbank aufgetreten!");
    }

?>