<?php
session_start();
require_once('dbConnection.php');

// Benutzerpruefung
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fehler-Variable
$error = "";

// Liste der Uebungen
$uebungen = [
    "Flachbankdrücken", "Schrägbankdrücken", "Kniebeugen", "Kreuzheben", "Schulterdrücken",
    "Bizepscurls", "Trizepsdrücken", "Latziehen", "Rudern", "Beinpresse", "Ausfallschritte",
    "Dips", "Klimmzüge", "Seitheben", "Wadenheben"
];

// Speichern
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <title>Trainingseintrag</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            min-height: 100vh;
        }
        .header, .footer {
            width: 100%;
            background-color: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
        }
        .header img {
            height: 100px;
            margin-right: 20px;
        }
        .header h1 {
            color: white;
            font-size: 3em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .main-content {
            width: 80%;
            max-width: 1000px;
            background: #1a1a1a;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.5);
            margin: 40px 0;
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
            border: 2px solid red;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-size: 1em;
        }
        .action-btn {
            background-color: red;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            font-size: 1em;
            border-radius: 6px;
            margin-top: 10px;
        }
        .action-btn:hover {
            background-color: #b30000;
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
        <img src="logo.png" alt="BeastMode Logo">
        <h1>BeastMode</h1>
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
