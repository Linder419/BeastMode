<?php
session_start();
require_once('dbConnection.php');

if (isset($_SESSION['user_id'])) {
    header('Location: OrdnerHaupt/index.html');
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(htmlspecialchars($_POST['username']));
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim(htmlspecialchars($_POST['password']));

    if (!empty($username) && !empty($email) && !empty($password)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Bitte eine gültige E-Mail-Adresse eingeben.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, passwort FROM benutzer WHERE benutzername = :username AND email = :email");
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['passwort'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;

                    setcookie('benutzername', $username, time() + 3600);
                    setcookie('benutzer_id', $user['id'], time() + 3600);

                    header('Location: OrdnerHaupt/index.html');
                    exit();
                } else {
                    $error = "Benutzername, E-Mail oder Passwort ist falsch.";
                }
            } catch (PDOException $e) {
                $error = "Datenbankfehler: " . htmlspecialchars($e->getMessage());
            }
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
    <title>BeastMode-Login</title>
    <style>
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
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        .login-form {
            background: #222;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(255, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-form h2 {
            margin-bottom: 20px;
            color: red;
            font-size: 2em;
        }
        .login-form input {
            width: calc(100% - 20px);
            padding: 14px;
            margin: 12px 0;
            border: 2px solid red;
            border-radius: 6px;
            background: #111;
            color: #fff;
            font-size: 1.1em;
        }
        .login-form button {
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
        .login-form button:hover {
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
            background-color: #222;
            color: white;
            text-align: center;
            padding: 30px;
        }
        .footer span {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page-container">
    <div class="header">
        <img src="logo.png" alt="BeastMode Logo">
        <h1>BeastMode</h1>
    </div>

    <div class="main-content">
        <div class="login-form">
            <h2>Anmeldung</h2>
            <?php if (!empty($error)) { echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; } ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Benutzername" required autofocus>
                <input type="email" name="email" placeholder="E-Mail-Adresse" required>
                <input type="password" name="password" placeholder="Passwort" required>
                <button type="submit">Anmelden</button>
            </form>
            <div class="register-link">
                <p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p>
            </div>
        </div>
    </div>

    <div class="footer">
        Entwickelt mit 💪 von <span>Tobias Linder</span> & <span>Aaron Hubmann</span>
    </div>
</div>
</body>
</html>
