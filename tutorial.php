<?php
session_start();
require_once('dbConnection.php');

// Wenn der Benutzer nicht eingeloggt ist, wird er zur Login-Seite weitergeleitet
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$suchbegriff = $_GET['suche'] ?? ''; // Suchbegriff aus URL holen (falls vorhanden)

// Farben, Logo und Links je nach Premium-Status setzen
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : '../OrdnerHaupt/index.html';

// Tutorials aus der Datenbank holen – Suche in Titel, Beschreibung und Suchbegriffen
$stmt = $pdo->prepare("SELECT * FROM tutorials WHERE titel LIKE :s OR beschreibung LIKE :s OR suchbegriffe LIKE :s ORDER BY id DESC");
$stmt->execute(['s' => '%' . $suchbegriff . '%']);
$tutorials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// YouTube-Link ggf. in eingebettetes Format umwandeln
function convertToEmbed($url) {
    if (strpos($url, 'watch?v=') !== false) {
        return preg_replace('/watch\?v=([a-zA-Z0-9_-]+)/', 'embed/$1', $url);
    }
    return $url;
}
?>

























<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Tutorials – BeastMode</title>
<style>

   { box-sizing: border-box; }
    html, body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #111;
        color: white;
        height: 100%;
    }
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
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
        box-shadow: 0 4px 8px <?= $is_premium ? 'gold' : 'red' ?>;
    }
    .header img {
        height: 80px;
        margin-right: 10px;
    }
    .header h1 {
        font-size: 2.5em;
        text-transform: uppercase;
        margin: 0;
        color: white;
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
    .main-content {
        max-width: 1000px;
        margin: 40px auto;
        padding: 20px;
        text-align: center;
        flex: 1;
    }
    .search-bar {
        margin-bottom: 30px;
    }
    input[type="text"] {
        width: 70%;
        padding: 10px;
        font-size: 1em;
        border-radius: 6px;
        border: 2px solid <?= $main_color ?>;
        background-color: #1a1a1a;
        color: white;
    }
    input[type="submit"] {
        padding: 10px 20px;
        background-color: <?= $main_color ?>;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: <?= $is_premium ? '#d4af37' : '#b30000' ?>;
    }
    .tutorial {
        background-color: #1a1a1a;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 12px;
        box-shadow: 0 0 10px <?= $is_premium ? 'gold' : 'rgba(255,0,0,0.2)' ?>;
    }
    iframe {
        width: 100%;
        max-width: 700px;
        height: 400px;
        border: none;
        border-radius: 12px;
        margin-bottom: 10px;
    }
    .tutorial-title {
        font-size: 1.3em;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .tutorial-description {
        color: #ccc;
    }
    .footer {
        background-color: #222;
        text-align: center;
        padding: 30px;
        color: white;
        margin-top: 40px;
        box-shadow: 0 -4px 8px <?= $is_premium ? 'gold' : 'red' ?>;
    }

</style>
</head>
<body>

<!-- Kopfzeile mit Logo und Button zurück zur Startseite -->
<div class="header">
    <img src="<?= $logo ?>" alt="BeastMode Logo">
    <h1>BeastMode</h1>
    <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
</div>

<!-- Hauptbereich -->
<div class="main-content">
    <h2>Tutorials – Tipps & Techniken</h2>

    <!-- Suchfeld zum Filtern der Tutorials -->
    <form method="GET" class="search-bar">
        <input type="text" name="suche" placeholder="z.B. Brust, Rücken, Beine..." value="<?= htmlspecialchars($suchbegriff) ?>">
        <input type="submit" value="Suchen">
    </form>

    <!-- Falls keine Ergebnisse gefunden wurden -->
    <?php if (count($tutorials) === 0): ?>
        <p>Keine Tutorials gefunden.</p>
    
    <!-- Tutorials anzeigen -->
    <?php else: ?>
        <?php foreach ($tutorials as $tut): ?>
            <div class="tutorial">
                
                <!-- Titel -->
                <div class="tutorial-title"><?= htmlspecialchars($tut['titel']) ?></div>
                
                <!-- YouTube-Video eingebettet -->
                <iframe src="<?= convertToEmbed(htmlspecialchars($tut['link'])) ?>" allowfullscreen></iframe>
                
                <!-- Beschreibung -->
                <div class="tutorial-description"><?= nl2br(htmlspecialchars($tut['beschreibung'])) ?></div>
                
                <!-- Kategorieanzeige -->
                <div style="color: grey; font-size: 0.9em; margin-top: 5px;">
                    Kategorie: <?= htmlspecialchars($tut['kategorie']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Fußzeile -->
<div class="footer">
    Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
</div>

</body>
</html>
