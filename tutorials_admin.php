<?php
session_start();
require_once('dbConnection.php');

// Nur eingeloggte Benutzer erlauben
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$meldung = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titel = trim($_POST['titel']);
    $link = trim($_POST['link']);
    $beschreibung = trim($_POST['beschreibung']);
    $kategorie = trim($_POST['kategorie']);
    $suchbegriffe = trim($_POST['suchbegriffe']);

    if ($titel && $link) {
        try {
            $stmt = $pdo->prepare("INSERT INTO tutorials (titel, link, beschreibung, kategorie, suchbegriffe) 
                                   VALUES (:titel, :link, :beschreibung, :kategorie, :suchbegriffe)");
            $stmt->execute([
                'titel' => $titel,
                'link' => $link,
                'beschreibung' => $beschreibung,
                'kategorie' => $kategorie,
                'suchbegriffe' => $suchbegriffe
            ]);
            $meldung = "Tutorial erfolgreich gespeichert! ✅";
        } catch (PDOException $e) {
            $meldung = "Fehler: " . $e->getMessage();
        }
    } else {
        $meldung = "Titel und Link sind Pflichtfelder!";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Tutorial hinzufügen</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

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

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }

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

        .meldung {
            text-align: center;
            margin-top: 20px;
            color: limegreen;
        }

        .header, .footer {
            background-color: #222;
            padding: 30px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>BeastMode – Admin Tutorial hinzufügen</h1>
</div>

<div class="container">
    <h2>Neues Tutorial eintragen</h2>

    <?php if ($meldung): ?>
        <div class="meldung"><?= htmlspecialchars($meldung) ?></div>
    <?php endif; ?>

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

<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
