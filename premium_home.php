<?php
session_start();
require_once('dbConnection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Optional prüfen, ob wirklich Premium (kannst du aktivieren, wenn du willst)
// if ($_SESSION['ist_premium'] != 1) {
//     header('Location: OrdnerHaupt/index.html');
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>BeastMode Premium</title>
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
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 4px 8px rgba(255, 215, 0, 0.4);
        }
        .header img {
            height: 80px;
            margin-right: 20px;
        }
        .header h1 {
            font-size: 2.8em;
            color: gold;
            text-transform: uppercase;
            margin: 0;
        }
        .home-button, .logout-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: gold;
            color: #111;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1em;
        }
        .home-button {
            right: 140px;
        }
        .logout-button {
            right: 30px;
        }
        .home-button:hover, .logout-button:hover {
            background-color: #e6c200;
        }
        .main-content {
            max-width: 1200px;
            margin: 80px auto 60px;
            padding: 20px;
            text-align: center;
        }
        .main-content h2 {
            color: gold;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .navigation {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }
        .nav-item {
            background-color: #1a1a1a;
            border: 2px solid gold;
            padding: 20px 30px;
            border-radius: 10px;
            transition: 0.3s;
            width: 250px;
            text-align: center;
        }
        .nav-item a {
            text-decoration: none;
            color: gold;
            font-size: 1.2em;
            font-weight: bold;
        }
        .nav-item:hover {
            background-color: #333;
            transform: scale(1.05);
        }
        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: auto;
            box-shadow: 0 -4px 8px rgba(255, 215, 0, 0.3);
        }
        .footer span {
            color: gold;
        }
        .premium-banner {
            margin-top: 40px;
        }
        .premium-banner img {
            max-width: 300px;
            border-radius: 20px;
        }
        .extras {
            margin-top: 50px;
            background-color: #1a1a1a;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.4);
        }
        .extras h3 {
            color: gold;
            margin-bottom: 20px;
        }
        .extras p {
            font-size: 1.1em;
            color: #ccc;
        }
    </style>
</head>
<body>

<div class="header">
    <img src="premium.png" alt="Premium Logo">
    <h1>BeastMode Premium</h1>

    <a href="logout.php" class="logout-button">Logout</a>
</div>

<div class="main-content">
    <h2>Willkommen im Premium Club, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

    <div class="premium-banner">
        <img src="../premium.png" alt="Premium Logo">
    </div>

    <div class="navigation">
        <div class="nav-item"><a href="hauptseite.php">Übungseingabe</a></div>
        <div class="nav-item"><a href="overview.php">Trainingsverlauf</a></div>
        <div class="nav-item"><a href="videos.php">Eigene Videos</a></div>
        <div class="nav-item"><a href="transformation.php">Transformation</a></div>
        <div class="nav-item"><a href="tutorial.php">Tutorials</a></div>
        <div class="nav-item"><a href="ernaehrung.php">Ernährung & Makros</a></div>
        <!-- Premium-exklusive Bereiche -->
        <div class="nav-item"><a href="premium_challenges.php">Tägliche Challenges</a></div>
        <div class="nav-item"><a href="premium_stats.php">Erweiterte Statistiken</a></div>
        <div class="nav-item"><a href="premium_coaching.php">Zielgerichtetes Coaching</a></div>
    </div>

    <div class="extras">
        <h3>Exklusive Premium-Vorteile</h3>
        <p>Maximiere deinen Fortschritt mit erweiterten Statistiken, täglichen Motivations-Challenges und persönlicher Zielverfolgung!  
        Du bist jetzt Teil der Elite – der BeastMode Premium Family!</p>
    </div>
</div>

<div class="footer">
    Entwickelt mit 💪 von <span>Tobias Linder</span> & <span>Aaron Hubmann</span>
</div>

</body>
</html>
