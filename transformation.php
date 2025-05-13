<?php
session_start(); // Session starten (für Benutzer-Login-Zugriff)
require_once('dbConnection.php'); // Datenbankverbindung einbinden

// Wenn der Benutzer nicht eingeloggt ist, zurück zum Login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

$upload_dir = 'uploads_transform/'; // Ordner für hochgeladene Bilder
$upload_error = '';
$success_message = '';

// Premium-Infos aus der Session auslesen
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png';
$main_color = $is_premium ? 'gold' : 'red';
$home_link = $is_premium ? 'premium_home.php' : '../OrdnerHaupt/index.html';

// === Bild löschen ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Bildinformationen aus der Datenbank holen
    $stmt = $pdo->prepare("SELECT dateiname FROM transformationen WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
    $eintrag = $stmt->fetch();

    if ($eintrag) {
        // Datei löschen, falls vorhanden
        $dateipfad = $upload_dir . $eintrag['dateiname'];
        if (file_exists($dateipfad)) {
            unlink($dateipfad);
        }

        // Datenbankeintrag löschen
        $stmt = $pdo->prepare("DELETE FROM transformationen WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
        $success_message = "Bild erfolgreich gelöscht!";
    }
}

// === Bild hochladen ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bild']) && !isset($_POST['delete_id'])) {
    $beschreibung = trim($_POST['beschreibung'] ?? '');

    if ($_FILES['bild']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['bild']['tmp_name'];
        $file_name = basename($_FILES['bild']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Erlaubte Bildformate
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            // Neuer Dateiname mit eindeutiger ID
            $new_filename = uniqid('trans_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;

            // Ordner erstellen, falls er nicht existiert
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Datei verschieben
            if (move_uploaded_file($file_tmp, $destination)) {
                // In DB speichern
                $stmt = $pdo->prepare("INSERT INTO transformationen (user_id, dateiname, beschreibung) VALUES (:user_id, :dateiname, :beschreibung)");
                $stmt->execute([
                    'user_id' => $user_id,
                    'dateiname' => $new_filename,
                    'beschreibung' => $beschreibung
                ]);
                $success_message = "Upload erfolgreich!";
            } else {
                $upload_error = "Fehler beim Verschieben der Datei.";
            }
        } else {
            $upload_error = "Ungültiges Dateiformat.";
        }
    } else {
        $upload_error = "Upload-Fehlercode: " . $_FILES['bild']['error'];
    }
}

// Alle Bilder des aktuellen Users holen
$stmt = $pdo->prepare("SELECT * FROM transformationen WHERE user_id = :user_id ORDER BY upload_datum DESC");
$stmt->execute(['user_id' => $user_id]);
$bilder = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>









<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Transformation - BeastMode</title>
    <style>
        /* --- Layout & Farben --- */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #111;
            color: white;
        }

        .page-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- Kopfbereich --- */
        .header {
            background-color: #222;
            color: white;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 8px <?= $main_color ?>;
            position: relative;
        }

        .header img {
            height: 80px;
            vertical-align: middle;
            margin-right: 10px;
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

        /* --- Hauptinhalt --- */
        .main-content {
            flex: 1;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            text-align: center;
        }

        .upload-form textarea,
        .upload-form input[type="file"],
        .upload-form input[type="submit"] {
            width: 100%;
            margin: 10px 0;
        }

        .upload-form textarea {
            padding: 10px;
            background: #222;
            color: white;
            border: 1px solid <?= $main_color ?>;
            border-radius: 6px;
        }

        .upload-form input[type="submit"] {
            background-color: <?= $main_color ?>;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .upload-form input[type="submit"]:hover {
            background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
        }

        /* --- Bilder anzeigen --- */
        .entry {
            margin-bottom: 40px;
        }

        .gallery img,
        .entry img {
            width: 100%;
            max-width: 500px;
            height: auto;
            max-height: 500px;
            margin-bottom: 5px;
            border-radius: 12px;
            border: 2px solid <?= $main_color ?>;
            object-fit: cover;
        }

        .delete-btn {
            background-color: <?= $main_color ?>;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
        }

        .delete-btn:hover {
            background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
        }

        /* --- Fußzeile --- */
        .footer {
            background-color: #222;
            color: white;
            padding: 30px;
            text-align: center;
            box-shadow: 0 -4px 8px <?= $main_color ?>;
        }
    </style>
</head>
<body>
<div class="page-container">

    <!-- Kopfzeile mit Logo und Zurück-Link -->
    <div class="header">
        <img src="<?= $logo ?>" alt="BeastMode Logo">
        <h1>BeastMode</h1>
        
        <!-- Link zurück zur Startseite (je nach Premiumstatus unterschiedlich) -->
        <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
    </div>

    <div class="main-content">
        <h2>Transformation – Vorher-Nachher</h2>

        <!-- Erfolgsmeldung nach Upload -->
        <?php if ($success_message): ?>
            <p style="color: limegreen;"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>

        <!-- Fehlermeldung bei Uploadproblemen -->
        <?php if ($upload_error): ?>
            <p style="color: red;"><?= htmlspecialchars($upload_error) ?></p>
        <?php endif; ?>

        <!-- Formular zum Hochladen eines Bildes + optional Beschreibung -->
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <textarea name="beschreibung" rows="3" placeholder="Beschreibung (optional)"></textarea>
            <input type="file" name="bild" accept="image/*" required><br>
            <input type="submit" value="Bild hochladen">
        </form>

        <h3>Deine bisherigen Transformationen</h3>

        <?php if (count($bilder) === 0): ?>

            <!-- Hinweis, wenn noch keine Bilder vorhanden sind -->
            <p>Du hast noch keine Bilder hochgeladen.</p>
        <?php else: ?>

            <!-- Schleife für jedes hochgeladene Bild -->
            <?php foreach ($bilder as $b): ?>
                <div class="entry">

                    <!-- Bild anzeigen -->
                    <img src="<?= $upload_dir . htmlspecialchars($b['dateiname']) ?>" alt="Transformation">
                    <div>
                        <!-- Beschreibung und Uploaddatum anzeigen -->
                        📝 <?= nl2br(htmlspecialchars($b['beschreibung'])) ?><br>
                        📅 Hochgeladen: <?= date("d.m.Y H:i", strtotime($b['upload_datum'])) ?>
                    </div>

                    <!-- Formular zum Löschen eines Bildes -->
                    <form method="POST" onsubmit="return confirm('Bild wirklich löschen?');">
                        <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="delete-btn">Bild löschen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Fußzeile -->
    <div class="footer">
        Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
    </div>

</div>
</body>
</html>
