<?php
require '../config/config.php';
$db = new mysqli(CONFIG['DB_HOST'], CONFIG['DB_USER'], CONFIG['DB_PASS'], CONFIG['DB_NAME']);

if ($db->connect_errno) {
    die("Échec de connexion à la base de données : " . $db->connect_error);
}

$db->set_charset("utf8mb4");

?>
