<?php
session_start();

// Farben und Logo je nach Premium-Status
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : '../OrdnerHaupt/index.html';

$ausgabe = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gewicht = isset($_POST['gewicht']) ? (float)$_POST['gewicht'] : 0;
    $groesse = isset($_POST['groesse']) ? (float)$_POST['groesse'] : 0;
    $alter = isset($_POST['alter']) ? (int)$_POST['alter'] : 0;
    $trainingstage = isset($_POST['trainingstage']) ? (int)$_POST['trainingstage'] : 0;
    $ziel = $_POST['ziel'] ?? '';
    $geschlecht = $_POST['geschlecht'] ?? '';

    if ($gewicht && $groesse && $alter && $trainingstage && $ziel && $geschlecht) {
        $bmr = ($geschlecht === 'm') ?
            66.47 + (13.7 * $gewicht) + (5.0 * $groesse) - (6.8 * $alter) :
            655.1 + (9.6 * $gewicht) + (1.8 * $groesse) - (4.7 * $alter);

        $aktivitaetsfaktor = 1.2 + ($trainingstage * 0.1);
        if ($aktivitaetsfaktor > 1.9) $aktivitaetsfaktor = 1.9;

        $kalorienbedarf = $bmr * $aktivitaetsfaktor;

        if ($ziel === 'abnehmen') $kalorienbedarf -= 300;
        elseif ($ziel === 'zunehmen') $kalorienbedarf += 300;

        $protein = $gewicht * 2;
        $fett = $gewicht * 1;
        $kcalProtein = $protein * 4;
        $kcalFett = $fett * 9;
        $kcalKohlenhydrate = $kalorienbedarf - $kcalProtein - $kcalFett;
        $kohlenhydrate = $kcalKohlenhydrate / 4;

        $ausgabe = "<div class='empfehlung'>
            <strong>Täglicher Bedarf:</strong><br>
            " . round($kalorienbedarf) . " kcal<br>
            " . round($protein) . " g Protein<br>
            " . round($kohlenhydrate) . " g Kohlenhydrate<br>
            " . round($fett) . " g Fett
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ernährung – BeastMode</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #222;
            color: white;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: relative;
            box-shadow: 0 4px 8px <?= $main_color ?>;
        }
        .header img {
            height: 80px;
            margin-right: 10px;
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
            background-color: <?= $is_premium ? '#d4af37' : '#b30000' ?>;
        }
        .footer {
            background-color: #222;
            text-align: center;
            padding: 30px;
            color: white;
            margin-top: 60px;
            box-shadow: 0 -4px 8px <?= $main_color ?>;
        }
        .main-content {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .tipp, .intro, .proteinliste {
            background-color: #1a1a1a;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px <?= $main_color ?>;
        }
        .rechner {
            margin-top: 40px;
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px <?= $main_color ?>;
            text-align: center;
        }
        .rechner form {
            max-width: 500px;
            margin: 0 auto;
        }
        .form-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 25px;
        }
        .form-row label {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.2em;
        }
        .form-row input[type="number"],
        .form-row select {
            padding: 12px;
            font-size: 1em;
            border-radius: 6px;
            border: 2px solid <?= $main_color ?>;
            background-color: #111;
            color: white;
            width: 100%;
            max-width: 320px;
        }
        .gender-options {
            display: flex;
            gap: 30px;
            justify-content: center;
        }
        .gender-options label {
            font-weight: normal;
            font-size: 1.1em;
        }
        input[type="submit"] {
            padding: 12px 25px;
            background: <?= $main_color ?>;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: <?= $is_premium ? '#d4af37' : '#b30000' ?>;
        }
        .empfehlung {
            margin-top: 30px;
            font-size: 1.1em;
            color: lime;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="<?= $logo ?>" alt="BeastMode Logo">
    <h1>BeastMode</h1>
    <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
</div>

<div class="main-content">
    <div class="intro">
        <h2>Warum Ernährung so wichtig ist</h2>
        <p>
            Ob du Muskeln aufbauen, Gewicht verlieren oder einfach fit bleiben willst – deine Ernährung spielt eine zentrale Rolle.
            Je nach Ziel und Trainingshäufigkeit brauchst du eine andere Menge an Kalorien und Nährstoffen. Unser Rechner hilft dir dabei.
        </p>
    </div>

    <div class="proteinliste">
        <h2>Proteinreiche Lebensmittel</h2>
        <ul>
            <li>Hähnchenbrust</li>
            <li>Magerquark</li>
            <li>Eier</li>
            <li>Linsen und Kichererbsen</li>
            <li>Tofu</li>
            <li>Thunfisch</li>
            <li>Griechischer Joghurt</li>
        </ul>
    </div>

    <div class="rechner">
        <h2>Kalorien- & Makronährstoffrechner</h2>
        <form method="POST">
            <div class="form-row">
                <label for="ziel">Ziel</label>
                <select id="ziel" name="ziel" required>
                    <option value="abnehmen">Gewicht verlieren</option>
                    <option value="halten">Gewicht halten</option>
                    <option value="zunehmen">Muskeln aufbauen</option>
                </select>
            </div>

            <div class="form-row">
                <label for="geschlecht">Geschlecht</label>
                <div class="gender-options">
                    <label><input type="radio" name="geschlecht" value="m" required> Männlich</label>
                    <label><input type="radio" name="geschlecht" value="w"> Weiblich</label>
                </div>
            </div>

            <div class="form-row">
                <label for="gewicht">Gewicht (kg)</label>
                <input type="number" name="gewicht" id="gewicht" required>
            </div>

            <div class="form-row">
                <label for="groesse">Größe (cm)</label>
                <input type="number" name="groesse" id="groesse" required>
            </div>

            <div class="form-row">
                <label for="alter">Alter</label>
                <input type="number" name="alter" id="alter" required>
            </div>

            <div class="form-row">
                <label for="trainingstage">Trainingseinheiten pro Woche</label>
                <input type="number" name="trainingstage" id="trainingstage" min="0" max="7" required>
            </div>

            <input type="submit" value="Berechnen">
        </form>
        <?= $ausgabe ?>
    </div>
</div>

<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
