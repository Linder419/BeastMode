<?php
session_start(); // Startet die Session (damit $_SESSION verwendet werden kann)
require_once('dbConnection.php'); // Bindet die Datei für die DB-Verbindung ein

// Zugriff nur erlaubt, wenn der Benutzer eingeloggt ist
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Die ID des aktuell eingeloggten Benutzers
$upload_dir = 'uploads/'; // Ordner, in dem die Videos gespeichert werden
$upload_error = '';        // Fehlermeldung für Uploads
$success_message = '';     // Erfolgsmeldung nach Upload

// Prüfen ob der Benutzer Premium ist → Design & Funktionen anpassen
$is_premium = isset($_SESSION['ist_premium']) && $_SESSION['ist_premium'] == 1;
$logo = $is_premium ? 'premium.png' : 'logo.png'; // Premium-Logo oder Standardlogo
$main_color = $is_premium ? 'gold' : 'red';        // Farbwahl
$shadow_color = $main_color;
$home_link = $is_premium ? 'premium_home.php' : '../OrdnerHaupt/index.html';


// VIDEO LÖSCHEN

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id']; // Die ID des Videos, das gelöscht werden soll

    // Hole Dateinamen des Videos aus der DB (Sicherstellen, dass es dem User gehört)
    $stmt = $pdo->prepare("SELECT dateiname FROM videos WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
    $video = $stmt->fetch();

    if ($video) {
        $dateipfad = $upload_dir . $video['dateiname']; // Pfad zur Videodatei
        if (file_exists($dateipfad)) {
            unlink($dateipfad); // Datei vom Server löschen
        }

        // Danach den Eintrag aus der Datenbank entfernen
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);

        $success_message = "Video erfolgreich gelöscht!";
    }
}



// VIDEO HOCHLADEN

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video']) && !isset($_POST['delete_id'])) {
    $beschreibung = trim($_POST['beschreibung'] ?? ''); // Optionale Beschreibung

    // Datei wurde erfolgreich übermittelt?
    if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['video']['tmp_name']; // Temporärer Dateipfad
        $file_name = basename($_FILES['video']['name']); // Ursprünglicher Dateiname
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // Dateiendung ermitteln

        $allowed = ['mp4', 'mov', 'avi', 'webm', 'mkv']; // Erlaubte Formate
        if (in_array($file_ext, $allowed)) {
            // Einmaliger Dateiname erzeugen
            $new_filename = uniqid('vid_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;

            // Falls der Upload-Ordner nicht existiert → erstellen
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Datei an den richtigen Ort verschieben
            if (move_uploaded_file($file_tmp, $destination)) {
                // Eintrag in die Datenbank speichern
                $stmt = $pdo->prepare("INSERT INTO videos (user_id, dateiname, beschreibung) 
                                       VALUES (:user_id, :dateiname, :beschreibung)");
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
        // Fehlercode anzeigen (z. B. bei zu großer Datei oder Abbruch)
        $upload_error = "Upload-Fehlercode: " . $_FILES['video']['error'];
    }
}



// VIDEOS AUS DER DATENBANK LADEN

$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = :user_id ORDER BY upload_datum DESC");
$stmt->execute(['user_id' => $user_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Alle Videos als Array
?>
























<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Video-Upload - BeastMode</title>
<style>
    /* Standard-Layout + Farben + Responsive-Styling */
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

    .footer {
        box-shadow: 0 -4px 8px <?= $shadow_color ?>;
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
        max-width: 900px;
        margin: 0 auto;
        flex: 1;
        text-align: center;
    }

    /* Upload-Formular */
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
        padding: 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    .upload-form input[type="submit"]:hover {
        background-color: <?= $main_color === 'gold' ? '#d4af37' : '#b30000' ?>;
    }

    .message { color: limegreen; }
    .error { color: red; }

    /* Videobereich */
    video {
        width: 100%;
        max-width: 700px;
        height: auto;
        margin: 20px 0;
        border: 2px solid <?= $main_color ?>;
        border-radius: 10px;
    }

    .video-block {
        margin-bottom: 40px;
    }

    .video-info {
        color: #ccc;
        font-size: 0.95em;
    }

    /* Button zum Löschen von Videos */
    .delete-btn {
        background-color: <?= $is_premium ? 'gold' : 'darkred' ?>;
        color: <?= $is_premium ? 'black' : 'white' ?>;
        border: none;
        padding: 6px 12px;
        cursor: pointer;
        border-radius: 5px;
        margin-top: 10px;
        font-weight: bold;
    }

    .delete-btn:hover {
        background-color: <?= $is_premium ? '#d4af37' : '#a30000' ?>;
    }
</style>
</head>
<body>
<div class="page-container">

    <!-- Kopfbereich mit Logo & Zurück-Link -->
    <div class="header">
        <img src="<?= $logo ?>" alt="BeastMode Logo">
        <h1>BeastMode</h1>
        <a href="<?= $home_link ?>" class="home-button">Zur Hauptseite</a>
    </div>

    <!-- Hauptbereich mit Formular und Videos -->
    <div class="main-content">
        <h2>Video-Upload</h2>

        <!-- Erfolg/Fehler anzeigen -->
        <?php if ($success_message): ?><p class="message"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>
        <?php if ($upload_error): ?><p class="error"><?= htmlspecialchars($upload_error) ?></p><?php endif; ?>

        <!-- Upload-Formular -->
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <textarea name="beschreibung" rows="3" placeholder="Kurzbeschreibung zur Übung (optional)"></textarea>
            <input type="file" name="video" accept="video/*" required><br>
            <input type="submit" value="Video hochladen">
        </form>

        <h3>Deine Videos</h3>

        <!-- Hochgeladene Videos anzeigen -->
        <?php if (count($videos) === 0): ?>
            <p>Du hast noch keine Videos hochgeladen.</p>
        <?php else: ?>
            <?php foreach ($videos as $video): ?>
                <div class="video-block">
                    <video controls>
                        <source src="<?= $upload_dir . htmlspecialchars($video['dateiname']) ?>" type="video/mp4">
                        Dein Browser unterstützt dieses Videoformat nicht.
                    </video>
                    <div class="video-info">
                        📅 <?= date("d.m.Y H:i", strtotime($video['upload_datum'])) ?><br>
                        📝 <?= nl2br(htmlspecialchars($video['beschreibung'])) ?>
                    </div>
                    <form method="POST" onsubmit="return confirm('Willst du dieses Video wirklich löschen?');">
                        <input type="hidden" name="delete_id" value="<?= $video['id'] ?>">
                        <button type="submit" class="delete-btn">Video löschen</button>
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
