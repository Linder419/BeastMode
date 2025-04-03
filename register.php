<?php
session_start();
require_once('dbConnection.php');

// Benutzer wird weitergeleitet, wenn er bereits eingeloggt ist
if (isset($_SESSION['user_id'])) {
    header('Location: hauptseite.php');
    exit();
}

// Registrierung-Logik
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($password_confirm)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Bitte eine gültige E-Mail-Adresse eingeben.";
        } elseif (strlen($password) >= 8 && preg_match('/[A-Z]/', $password)) {
            if ($password === $password_confirm) {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM benutzer WHERE benutzername = :username OR email = :email");
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        $error = "Benutzername oder E-Mail ist bereits vergeben.";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                        $stmt = $pdo->prepare("INSERT INTO benutzer (benutzername, email, passwort) VALUES (:username, :email, :password)");
                        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

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
        .header {
            width: 100%;
            background-color: #222;
            display: flex;
            align-items: center;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(255, 0, 0, 0.3);
            justify-content: center;
            margin-bottom: 40px;
        }
        .header img {
            height: 100px;
            margin-right: 20px;
        }
        .header h1 {
            color: white;
            font-size: 3em;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .register-form {
            background: #222;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.5);
            width: 400px;
            text-align: center;
            margin-bottom: 40px;
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
        .footer {
            width: 100%;
            background-color: #222;
            color: white;
            text-align: center;
            padding: 30px;
            box-shadow: 0 -4px 8px rgba(255, 0, 0, 0.3);
            font-size: 1em;
        }
        .footer span {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="logo.png" alt="BeastMode Logo">
        <h1>BeastMode</h1>
    </div>
    
    <div class="register-form">
        <h2>Registrierung</h2>
        <?php if (!empty($error)) { echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; } ?>
        <form action="" method="POST">
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
    
    <div class="footer">
        Entwickelt mit 💪 von <span>Tobias Linder</span> & <span>Aaron Hubmann</span>
    </div>
</body>
</html>