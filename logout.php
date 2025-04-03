<?php
session_start();

// Alle Sitzungsvariablen löschen
$_SESSION = [];

// Session beenden
session_destroy();

// Benutzer zur Login-Seite weiterleiten
header('Location: login.php');
exit();
?>