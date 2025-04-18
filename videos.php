<?php
session_start();
require_once('dbConnection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/';
$upload_error = '';
$success_message = '';

// Video löschen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Hole Dateinamen
    $stmt = $pdo->prepare("SELECT dateiname FROM videos WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);
    $video = $stmt->fetch();

    if ($video) {
        $dateipfad = $upload_dir . $video['dateiname'];
        if (file_exists($dateipfad)) {
            unlink($dateipfad); // Datei löschen
        }

        // DB-Eintrag löschen
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $delete_id, 'user_id' => $user_id]);

        $success_message = "Video erfolgreich gelöscht!";
    }
}

// Video hochladen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['video'])) {
    $beschreibung = trim($_POST['beschreibung'] ?? '');

    if ($_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['video']['tmp_name'];
        $file_name = basename($_FILES['video']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed = ['mp4', 'mov', 'avi', 'webm', 'mkv'];
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid('vid_', true) . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($file_tmp, $destination)) {
                $stmt = $pdo->prepare("INSERT INTO videos (user_id, dateiname, beschreibung) VALUES (:user_id, :dateiname, :beschreibung)");
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
        $upload_error = "Upload-Fehlercode: " . $_FILES['video']['error'];
    }
}

// Videos abrufen
$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = :user_id ORDER BY upload_datum DESC");
$stmt->execute(['user_id' => $user_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Video-Upload – BeastMode</title>
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
            box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
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
        }
        .main-content {
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            flex: 1;
            text-align: center;
        }
        .upload-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #222;
            color: white;
            border: 1px solid red;
            border-radius: 6px;
        }
        .upload-form input[type="file"],
        .upload-form input[type="submit"] {
            margin: 15px 0;
        }
        .upload-form input[type="submit"] {
            padding: 10px 20px;
            background: red;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .upload-form input[type="submit"]:hover {
            background-color: #b30000;
        }
        .message { color: limegreen; }
        .error { color: red; }
        video {
            width: 100%;
            max-width: 700px;
            margin: 20px 0 5px 0;
            border: 2px solid red;
            border-radius: 10px;
        }
        .video-block {
            margin-bottom: 40px;
        }
        .video-info {
            color: #ccc;
            font-size: 0.95em;
        }
        .delete-btn {
            background-color: darkred;
            color: white;
            border: none;
            padding: 6px 12px;
            cursor: pointer;
            border-radius: 5px;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #a30000;
        }
        .header {
            background-color: #222;
            color: white;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
            position: relative;
        }
        .home-button {
            position: absolute;
            right: 30px;
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

        
    </style>
</head>
<body>
<div class="page-container">
    <div class="header">
        <img src="logo.png" alt="BeastMode Logo">
        <h1>BeastMode</h1>
        <a href="OrdnerHaupt/index.html" class="home-button">Zur Hauptseite</a>
    </div>

    <div class="main-content">
        <h2>Video-Upload</h2>

        <?php if ($success_message): ?>
            <p class="message"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <?php if ($upload_error): ?>
            <p class="error"><?= htmlspecialchars($upload_error) ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <textarea name="beschreibung" rows="3" placeholder="Kurzbeschreibung zur Übung (optional)"></textarea>
            <input type="file" name="video" accept="video/*" required><br>
            <input type="submit" value="Video hochladen">
        </form>

        <h3>Deine Videos</h3>
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

    <div class="footer">
        Entwickelt mit 💪 von Tobias Linder & Aaron Hubmann
    </div>
</div>
</body>
</html>
