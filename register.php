<?php
session_start(); // Session starten (für Benutzerverwaltung)
require_once('dbConnection.php'); // Datenbankverbindung einbinden

// Falls der Nutzer bereits eingeloggt ist, Weiterleitung zur Hauptseite
if (isset($_SESSION['user_id'])) {
    header('Location: hauptseite.php');
    exit();
}

$error = ""; // Fehlermeldung vorbereiten

// Wenn das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Eingaben aus dem Formular holen
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    // Prüfen ob alle Felder ausgefüllt wurden
    if (!empty($username) && !empty($email) && !empty($password) && !empty($password_confirm)) {
        // E-Mail-Format prüfen
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Bitte eine gültige E-Mail-Adresse eingeben.";

        // Passwort prüfen: mindestens 8 Zeichen und 1 Großbuchstabe
        } elseif (strlen($password) >= 8 && preg_match('/[A-Z]/', $password)) {
            
            // Passwort-Bestätigung vergleichen
            if ($password === $password_confirm) {
                try {
                    // Prüfen ob Benutzername oder E-Mail bereits verwendet wird
                    $stmt = $pdo->prepare("SELECT id FROM benutzer WHERE benutzername = :username OR email = :email");
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $error = "Benutzername oder E-Mail ist bereits vergeben.";
                    } else {
                        // Passwort verschlüsseln
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        // Benutzer in der Datenbank speichern
                        $stmt = $pdo->prepare("INSERT INTO benutzer (benutzername, email, passwort) VALUES (:username, :email, :password)");
                        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

                        // Wenn erfolgreich, zur Login-Seite weiterleiten
                        if ($stmt->execute()) {
                            header('Location: login.php?message=registered');
                            exit();
                        } else {
                            $error = "Fehler beim Erstellen des Benutzers.";
                        }
                    }
                } catch (PDOException $e) {
                    $error = "Datenbankfehler: " . htmlspecialchars($e->getMessage());
                }
            } else {
                $error = "Die Passwörter stimmen nicht überein.";
            }
        } else {
            $error = "Das Passwort muss mindestens 8 Zeichen lang sein und einen Großbuchstaben enthalten.";
        }
    } else {
        $error = "Bitte alle Felder ausfüllen.";
    }
}
?>











<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeastMode Registrierung</title>
    <style>
        /* Basis-Layout */
        html, body {
            margin: 0;
            padding: 0;
            background-color: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            height: 100%;
        }

        .page-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Kopfbereich mit Logo und Titel */
        .header {
            background-color: #222;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
        }

        .header img {
            height: 80px;
        }

        .header h1 {
            font-size: 2.5em;
            text-transform: uppercase;
            margin: 0;
        }

        /* Formular-Zentrum */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }

        /* Registrierungsbox */
        .register-form {
            background: #222;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .register-form h2 {
            margin-bottom: 20px;
            color: red;
            font-size: 2em;
        }

        .register-form input {
            width: calc(100% - 20px);
            padding: 14px;
            margin: 12px 0;
            border: 2px solid red;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-size: 1.1em;
        }

        .register-form button {
            background-color: red;
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.2em;
            width: calc(100% - 20px);
            margin-top: 12px;
            transition: 0.3s;
        }

        .register-form button:hover {
            background-color: #b30000;
            transform: scale(1.05);
        }

        .error {
            color: red;
            font-size: 1em;
            margin: 10px 0;
        }

        /* Link zum Login */
        .register-link {
            margin-top: 18px;
            font-size: 1em;
        }

        .register-link a {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* Fußbereich */
        .footer {
            background-color: #222;
            color: white;
            text-align: center;
            padding: 30px;
            box-shadow: 0 -4px 8px red;
        }

        .footer span {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page-container">

    <!-- Kopfbereich -->
    <div class="header">
        <img src="logo.png" alt="BeastMode Logo">
        <h1>BeastMode</h1>
    </div>

    <!-- Hauptinhalt: Formular -->
    <div class="main-content">
        <div class="register-form">
            <h2>Registrierung</h2>

            <!-- Fehlermeldung anzeigen -->
            <?php if (!empty($error)) { echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; } ?>

            <!-- Registrierungsformular -->
            <form method="POST">
                <input type="text" name="username" placeholder="Benutzername" required>
                <input type="email" name="email" placeholder="E-Mail-Adresse" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <input type="password" name="password_confirm" placeholder="Passwort bestätigen" required>
                <button type="submit">Registrieren</button>
            </form>

            <div class="register-link">
                <p>Bereits ein Konto? <a href="login.php">Einloggen</a></p>
            </div>
        </div>
    </div>

    <!-- Fußzeile -->
    <div class="footer">
        Entwickelt mit 💪 von <span>Tobias Linder</span> & <span>Aaron Hubmann</span>
    </div>

</div>
</body>
</html>
