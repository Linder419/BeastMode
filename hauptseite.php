<?php
session_start();
require_once('dbConnection.php');

// Benutzerpruefung
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Premium-Check
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$shadow_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : 'OrdnerHaupt/index.html';

// Fehler-Variable
$error = "";

// Liste der Uebungen
$uebungen = [
    "Flachbankdrücken", "Schrägbankdrücken", "Kniebeugen", "Kreuzheben", "Schulterdrücken",
    "Bizepscurls", "Trizepsdrücken", "Latziehen", "Rudern", "Beinpresse", "Ausfallschritte",
    "Dips", "Klimmzüge", "Seitheben", "Wadenheben"
];

// Speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uebungen'])) {
    $user_id = $_SESSION['user_id'];

    foreach ($_POST['uebungen'] as $index => $uebung) {
        foreach ($_POST['gewichte'][$index] as $satzIndex => $gewicht) {
            $wiederholungen = $_POST['wiederholungen'][$index][$satzIndex];

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
        function addExercise() {
            const container = document.getElementById('exercise-container');
            const index = container.children.length;
            const newBlock = document.createElement('div');
            newBlock.classList.add('exercise-block');
            newBlock.innerHTML = `
                <select name="uebungen[${index}]" required>
                    <option value="" disabled selected>Übung wählen</option>
                    <?php foreach ($uebungen as $uebung) { echo "<option value='$uebung'>$uebung</option>"; } ?>
                </select>
                <button type="button" class="action-btn" onclick="removeExercise(this)">Übung löschen</button>
                <div class="satz-container" data-index="${index}"></div>
                <button type="button" class="action-btn" onclick="addSatz(this)">Satz hinzufügen</button>
            `;
            container.appendChild(newBlock);
        }

        function addSatz(button) {
            const satzContainer = button.previousElementSibling;
            const index = satzContainer.dataset.index;
            const newSatz = document.createElement('div');
            newSatz.classList.add('satz-block');
            newSatz.innerHTML = `
                <input type="number" name="gewichte[${index}][]" placeholder="Gewicht (kg)" required>
                <input type="number" name="wiederholungen[${index}][]" placeholder="Wiederholungen" required>
                <button type="button" class="action-btn" onclick="removeSatz(this)">Satz löschen</button>
            `;
            satzContainer.appendChild(newSatz);
        }

        function removeExercise(button) {
            button.parentElement.remove();
        }

        function removeSatz(button) {
            button.parentElement.remove();
        }
    </script>
</head>
<body>
<div class="header">
    <img src="<?= $logo ?>" alt="BeastMode Logo">
    <h1>BeastMode</h1>
    <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
</div>

<div class="main-content">
    <h2>Trainingseintrag</h2>
    <form action="" method="POST">
        <div id="exercise-container"></div>
        <button type="button" class="action-btn" onclick="addExercise()">Übung hinzufügen</button>
        <button type="submit" class="action-btn">Speichern</button>
    </form>
</div>

<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>
</body>
</html>
