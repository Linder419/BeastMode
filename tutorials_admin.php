<?php
session_start(); // Session starten, um Benutzerdaten zu speichern
require_once('dbConnection.php'); // Verbindung zur Datenbank

// Nur eingeloggte Benutzer dürfen diese Seite sehen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$meldung = ""; // Variable für Erfolg/Fehlermeldung vorbereiten

// Wenn Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben aus dem Formular bereinigen
    $titel = trim($_POST['titel']);
    $link = trim($_POST['link']);
    $beschreibung = trim($_POST['beschreibung']);
    $kategorie = trim($_POST['kategorie']);
    $suchbegriffe = trim($_POST['suchbegriffe']);

    // Prüfen ob Titel und Link vorhanden sind
    if ($titel && $link) {
        try {
            // Datenbankeintrag vorbereiten und ausführen
            $stmt = $pdo->prepare("INSERT INTO tutorials (titel, link, beschreibung, kategorie, suchbegriffe) 
                                   VALUES (:titel, :link, :beschreibung, :kategorie, :suchbegriffe)");
            $stmt->execute([
                'titel' => $titel,
                'link' => $link,
                'beschreibung' => $beschreibung,
                'kategorie' => $kategorie,
                'suchbegriffe' => $suchbegriffe
            ]);
            $meldung = "Tutorial erfolgreich gespeichert! ✅"; // Erfolgsmeldung
        } catch (PDOException $e) {
            $meldung = "Fehler: " . $e->getMessage(); // Fehler anzeigen, wenn DB-Abfrage fehlschlägt
        }
    } else {
        $meldung = "Titel und Link sind Pflichtfelder!"; // Warnung bei leeren Pflichtfeldern
    }
}
?>



















<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tutorial hinzufügen</title>
    <style>
        /* Grund-Design in dunklem Stil */
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Hauptbox für Formular */
        .container {
            max-width: 700px;
            margin: 100px auto 40px;
            padding: 30px;
            background-color: #1a1a1a;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Labels für Eingabefelder */
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        /* Eingabefelder und Textbereiche */
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            background: #222;
            color: white;
            border: 2px solid red;
            border-radius: 6px;
        }

        textarea {
            resize: vertical;
        }

        /* Absende-Button */
        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 25px;
            background: red;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #b30000;
        }

        /* Ausgabe von Erfolgs- oder Fehlermeldungen */
        .meldung {
            text-align: center;
            margin-top: 20px;
            color: limegreen;
        }

        /* Kopf- und Fußzeile */
        .header, .footer {
            background-color: #222;
            padding: 30px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- Kopfzeile -->
<div class="header">
    <h1>BeastMode – Admin Tutorial hinzufügen</h1>
</div>

<!-- Hauptinhalt mit Formular -->
<div class="container">
    <h2>Neues Tutorial eintragen</h2>

    <!-- Erfolg / Fehler anzeigen -->
    <?php if ($meldung): ?>
        <div class="meldung"><?= htmlspecialchars($meldung) ?></div>
    <?php endif; ?>

    <!-- Formular zur Eingabe eines Tutorials -->
    <form method="POST">
        <label for="titel">Titel*</label>
        <input type="text" name="titel" id="titel" required>

        <label for="link">YouTube-Link*</label>
        <input type="text" name="link" id="link" placeholder="z. B. https://www.youtube.com/watch?v=xyz123" required>

        <label for="beschreibung">Beschreibung</label>
        <textarea name="beschreibung" id="beschreibung" rows="4"></textarea>

        <label for="kategorie">Kategorie (z. B. Training, Ernährung)</label>
        <input type="text" name="kategorie" id="kategorie">

        <label for="suchbegriffe">Suchbegriffe (z. B. bizeps, curls, arm, training)</label>
        <input type="text" name="suchbegriffe" id="suchbegriffe">

        <input type="submit" value="Tutorial speichern">
    </form>
</div>

<!-- Fußzeile -->
<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
