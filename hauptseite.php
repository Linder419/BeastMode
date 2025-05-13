<?php
session_start();
require_once('dbConnection.php');

// Prüfen, ob der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Premium-Nutzer erkennen (für Design und Weiterleitungen)
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$shadow_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : 'OrdnerHaupt/index.html';

$error = "";

// Liste der verfügbaren Übungen
$uebungen = [
    "Flachbankdrücken", "Schrägbankdrücken", "Kniebeugen", "Kreuzheben", "Schulterdrücken",
    "Bizepscurls", "Trizepsdrücken", "Latziehen", "Rudern", "Beinpresse", "Ausfallschritte",
    "Dips", "Klimmzüge", "Seitheben", "Wadenheben"
];

// Formular wurde abgeschickt – Daten verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uebungen'])) {
    $user_id = $_SESSION['user_id'];

    // Jede Übung und zugehörige Sätze durchgehen
    foreach ($_POST['uebungen'] as $index => $uebung) {
        foreach ($_POST['gewichte'][$index] as $satzIndex => $gewicht) {
            $wiederholungen = $_POST['wiederholungen'][$index][$satzIndex];

            // Nur gültige Zahlen speichern
            if (!empty($gewicht) && !empty($wiederholungen) && is_numeric($gewicht) && is_numeric($wiederholungen)) {
                try {
                    $saetze = $satzIndex + 1;
                    $stmt = $pdo->prepare("INSERT INTO trainingseinheiten (user_id, uebung, saetze, gewicht, wiederholungen, datum) 
                        VALUES (:user_id, :uebung, :saetze, :gewicht, :wiederholungen, NOW())");

                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':uebung', $uebung, PDO::PARAM_STR);
                    $stmt->bindParam(':saetze', $saetze, PDO::PARAM_INT);
                    $stmt->bindParam(':gewicht', $gewicht, PDO::PARAM_INT);
                    $stmt->bindParam(':wiederholungen', $wiederholungen, PDO::PARAM_INT);
                    $stmt->execute();
                } catch (PDOException $e) {
                    $error = "Datenbankfehler: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
    // Nach Speichern weiterleiten zur Übersicht
    header('Location: overview.php');
    exit();
}
?>























<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainingseintrag – BeastMode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #111;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: #222;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            box-shadow: 0 4px 8px <?= $shadow_color ?>;
            position: relative;
        }
        .header img {
            height: 80px;
        }
        .header h1 {
            font-size: 2.5em;
            text-transform: uppercase;
            margin: 0;
        }
        .home-button {
            position: absolute;
            right: 60px;
            top: 50%;
            transform: translateY(-50%);
            background-color: <?= $main_color ?>;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .home-button:hover {
            background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
        }
        .main-content {
            width: 80%;
            max-width: 1000px;
            background: #1a1a1a;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 20px <?= $shadow_color ?>;
            margin: 40px auto;
            flex: 1;
        }
        .exercise-block, .satz-block {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            justify-content: center;
        }
        select, input {
            padding: 8px;
            border: 2px solid <?= $main_color ?>;
            border-radius: 6px;
            background: #111;
            color: white;
            font-size: 1em;
        }
        .action-btn {
            background-color: <?= $main_color ?>;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 1em;
            border-radius: 6px;
            margin-top: 10px;
        }
        .action-btn:hover {
            background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
        }
        .footer {
            background-color: #222;
            text-align: center;
            padding: 30px;
            color: white;
            margin-top: auto;
            box-shadow: 0 -4px 8px <?= $shadow_color ?>;
        }
    </style>

    <script>
    // Diese Funktion fügt eine neue Übung mit zugehörigem Bereich für Sätze hinzu
    function addExercise() {
        const container = document.getElementById('exercise-container'); // Container, in dem alle Übungen erscheinen
        const index = container.children.length; // Index für die neue Übung (z.B. 0, 1, 2...), wichtig für eindeutige Namen

        // Erstelle ein neues <div>-Element für diese Übung
        const newBlock = document.createElement('div');
        newBlock.classList.add('exercise-block'); // CSS-Klasse für Layout

        // Fülle das Element mit HTML-Inhalt:
        // - Dropdown zur Übungsauswahl
        // - Button zum Löschen der gesamten Übung
        // - Leerer Bereich für die Sätze
        // - Button zum Hinzufügen von Sätzen
        newBlock.innerHTML = `
            <select name="uebungen[${index}]" required>
                <option value="" disabled selected>Übung wählen</option>
                <?php foreach ($uebungen as $uebung) { echo "<option value='$uebung'>$uebung</option>"; } ?>
            </select>
            <button type="button" class="action-btn" onclick="removeExercise(this)">Übung löschen</button>
            <div class="satz-container" data-index="${index}"></div>
            <button type="button" class="action-btn" onclick="addSatz(this)">Satz hinzufügen</button>
        `;

        // Füge das neue Übungs-Element dem Container hinzu
        container.appendChild(newBlock);
    }

    // Diese Funktion fügt einen Satz (Gewicht + Wiederholungen) zur entsprechenden Übung hinzu
    function addSatz(button) {
        const satzContainer = button.previousElementSibling; // Der <div> über dem Button enthält die Sätze
        const index = satzContainer.dataset.index; // Index der zugehörigen Übung (z. B. 0, 1, 2)

        // Neues <div> für den Satz
        const newSatz = document.createElement('div');
        newSatz.classList.add('satz-block'); // CSS für Darstellung der Sätze

        // HTML-Inhalt mit zwei Eingabefeldern + Lösch-Button
        newSatz.innerHTML = `
            <input type="number" name="gewichte[${index}][]" placeholder="Gewicht (kg)" required>
            <input type="number" name="wiederholungen[${index}][]" placeholder="Wiederholungen" required>
            <button type="button" class="action-btn" onclick="removeSatz(this)">Satz löschen</button>
        `;

        // Füge den neuen Satz zur Liste der Sätze der Übung hinzu
        satzContainer.appendChild(newSatz);
    }

    // Diese Funktion entfernt die gesamte Übung inkl. aller Sätze
    function removeExercise(button) {
        button.parentElement.remove(); // Entfernt das <div>, das die Übung enthält
    }

    // Diese Funktion entfernt einen einzelnen Satz innerhalb einer Übung
    function removeSatz(button) {
        button.parentElement.remove(); // Entfernt das Satz-<div>, in dem der Button liegt
    }
</script>

</head>
<body>

<!-- Kopfbereich mit Logo + Premiumfarben -->
<div class="header">
    <img src="<?= $logo ?>" alt="BeastMode Logo">
    <h1>BeastMode</h1>
    <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
</div>

<!-- Hauptbereich für das Formular -->
<div class="main-content">
    <h2>Trainingseintrag</h2>
    <form action="" method="POST">
        <div id="exercise-container"></div>
        <button type="button" class="action-btn" onclick="addExercise()">Übung hinzufügen</button>
        <button type="submit" class="action-btn">Speichern</button>
    </form>
</div>

<!-- Fußzeile -->
<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
