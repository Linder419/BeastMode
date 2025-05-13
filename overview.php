<?php
session_start();
require_once('dbConnection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$filter_date = $_GET['datum'] ?? '';

// Premium-Infos auslesen
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$shadow_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : 'OrdnerHaupt/index.html';

// Einzelnen Eintrag löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM trainingseinheiten WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    } catch (PDOException $e) {
        die("Löschen fehlgeschlagen: " . htmlspecialchars($e->getMessage()));
    }
}

try {
    if ($filter_date) {
        $stmt = $pdo->prepare("SELECT * FROM trainingseinheiten WHERE user_id = :user_id AND DATE(datum) = :datum ORDER BY datum DESC");
        $stmt->bindParam(':datum', $filter_date);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM trainingseinheiten WHERE user_id = :user_id ORDER BY datum DESC");
    }
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $eintraege = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Datenbankfehler: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Trainingseinträge</title>
<style>
    html, body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #111;
        color: white;
    }
    .page-container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .header, .footer {
        background-color: #222;
        color: white;
        padding: 30px;
        text-align: center;
        box-shadow: 0 4px 8px <?= $shadow_color ?>;
        position: relative;
    }
    .header img {
        height: 80px;
        vertical-align: middle;
        margin-right: 20px;
    }
    .header h1 {
        display: inline-block;
        vertical-align: middle;
        font-size: 2.5em;
        text-transform: uppercase;
        margin: 0;
    }
    .home-button {
        position: absolute;
        right: 30px;
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
        padding: 40px;
        max-width: 1200px;
        margin: 0 auto;
        flex: 1;
    }
    .main-content h2 {
        text-align: center;
        margin-bottom: 30px;
    }
    .filter-form {
        text-align: center;
        margin-bottom: 30px;
    }
    .filter-form input[type="date"] {
        padding: 10px;
        border-radius: 6px;
        border: none;
        font-size: 1em;
    }
    .filter-form button {
        padding: 10px 20px;
        margin-left: 10px;
        background-color: <?= $main_color ?>;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }
    .filter-form button:hover {
        background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #1a1a1a;
        box-shadow: 0 5px 20px <?= $shadow_color ?>;
        border-radius: 10px;
        overflow: hidden;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #444;
        text-align: center;
    }
    th {
        background-color: <?= $main_color ?>;
        color: white;
        text-transform: uppercase;
    }
    tr.spacer-row td {
        padding-top: 25px;
        border: none;
        background-color: transparent;
    }
    tr:hover {
        background-color: #222;
    }
    .delete-form {
        margin: 0;
    }
    .delete-btn {
        background-color: <?= $main_color ?>;
        color: white;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 5px;
        font-weight: bold;
    }
    .delete-btn:hover {
        background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
    }
    .button-red {
        background-color: <?= $main_color ?>;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1em;
        margin-top: 20px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-weight: bold;

    }
    .button-red:hover {
        background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
    }
    .footer {
        background-color: #222;
        color: white;
        padding: 30px;
        text-align: center;
        margin-top: auto;
        box-shadow: 0 -4px 8px <?= $shadow_color ?>;
    }
</style>
</head>
<body>
<div class="page-container">
    <div class="header">
        <img src="<?= $logo ?>" alt="BeastMode Logo">
        <h1>BeastMode</h1>
        <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
    </div>

    <div class="main-content">
        <h2>Deine Trainingseinträge</h2>

        <form method="GET" class="filter-form">
            <input type="date" name="datum" value="<?= htmlspecialchars($filter_date) ?>">
            <button type="submit">Filtern</button>
        </form>

        <?php if (count($eintraege) === 0): ?>
            <p>Keine Einträge gefunden für dieses Datum.</p>
            <a href="hauptseite.php" class="button-red">Übung eingeben</a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Übung</th>
                        <th>Gewicht (kg)</th>
                        <th>Wdh.</th>
                        <th>Satz</th>
                        <th>Aktion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $last_uebung = null;
                    foreach ($eintraege as $eintrag): 
                        if ($last_uebung !== null && $last_uebung !== $eintrag['uebung']) {
                            echo '<tr class="spacer-row"><td colspan="6"></td></tr>';
                        }
                        $last_uebung = $eintrag['uebung'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars(date("d.m.Y H:i", strtotime($eintrag['datum']))) ?></td>
                            <td><?= htmlspecialchars($eintrag['uebung']) ?></td>
                            <td><?= htmlspecialchars($eintrag['gewicht']) ?></td>
                            <td><?= htmlspecialchars($eintrag['wiederholungen']) ?></td>
                            <td><?= htmlspecialchars($eintrag['saetze']) ?></td>
                            <td>
                                <form method="POST" class="delete-form" onsubmit="return confirm('Eintrag wirklich löschen?');">
                                    <input type="hidden" name="delete_id" value="<?= $eintrag['id'] ?>">
                                    <button type="submit" class="delete-btn">Löschen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="footer">
        Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
    </div>
</div>
</body>
</html>
