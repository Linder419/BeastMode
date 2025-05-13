<?php
session_start();
require_once('dbConnection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kaufen'])) {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE benutzer SET ist_premium = 1 WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['ist_premium'] = 1;

    header('Location: premium_home.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Premium – BeastMode</title>
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
            background-color: red;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .home-button:hover {
            background-color: #b30000;
        }
        .main-content {
            max-width: 800px;
            margin: 40px auto;
            text-align: center;
            background-color: #1a1a1a;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3);
        }
        .main-content img.premium-logo {
            width: 250px;
            margin: 30px auto;
            display: block;
        }
        .main-content h2 {
            margin-bottom: 20px;
            color: red;
        }
        .preis {
            font-size: 2em;
            margin-bottom: 30px;
            color: limegreen;
        }
        .vorteile {
            text-align: left;
            margin: 20px auto;
            max-width: 500px;
            font-size: 1.1em;
        }
        .vorteile li {
            margin-bottom: 10px;
        }
        form {
            margin-top: 40px;
            text-align: left;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 2px solid red;
            background-color: #111;
            color: white;
        }
        .button-wrapper {
            text-align: center;
            margin-top: 30px;
        }
        .buy-button {
            background: linear-gradient(45deg, #ffd700, #ffa500);
            color: black;
            font-size: 1.3em;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-shadow: 1px 1px 2px #000;
            display: inline-block;
            width: auto;
            min-width: 250px;
        }
        .buy-button:hover {
            background: linear-gradient(45deg, #ffcc00, #ff9900);
        }
        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: 60px;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="logo.png" alt="BeastMode Logo">
    <h1>BeastMode</h1>
    <a href="../OrdnerHaupt/index.html" class="home-button">Zur Hauptseite</a>
</div>

<div class="main-content">
    <img src="premium.png" alt="Premium Logo" class="premium-logo">

    <h2>Werde Teil des Premium Clubs!</h2>
    <div class="preis">Nur 5,99 € pro Monat</div>

    <ul class="vorteile">
        <li>✔ Exklusive Trainingspläne</li>
        <li>✔ Fortschrittsanalysen in Echtzeit</li>
        <li>✔ Zugang zu Premium-Tutorials</li>
        <li>✔ Persönliche Zielsetzung und Auswertung</li>
        <li>✔ Motivation durch tägliche Challenges</li>
    </ul>

    <form method="POST">
        <label for="name">Name</label>
        <input type="text" id="name" name="fake_name" placeholder="Max Mustermann">

        <label for="email">E-Mail-Adresse</label>
        <input type="email" id="email" name="fake_email" placeholder="max@example.com">

        <label for="iban">IBAN</label>
        <input type="text" id="iban" name="fake_iban" placeholder="DE00 1234 5678 9012 3456 00">

        <label for="adresse">Adresse</label>
        <input type="text" id="adresse" name="fake_adresse" placeholder="Musterstraße 1, 12345 Musterstadt">

        <div class="button-wrapper">
            <button type="submit" name="kaufen" class="buy-button">Jetzt Premium werden!</button>
        </div>
    </form>
</div>

<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
