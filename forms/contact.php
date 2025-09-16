<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://idjwi-tourisme.vercel.app');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Gérer la requête OPTIONS (préflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Connexion DB
require_once '../db/config.php';

// Récupérer les données JSON
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Vérifier que $data est bien un tableau
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Erreur: données JSON invalides.']);
    exit;
}

// Extraction des champs
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$message = trim($data['message'] ?? '');

$errors = [];

// Validation
if ($name === '') {
    $errors[] = 'Le nom est requis.';
}
if ($email === '') {
    $errors[] = "L'email est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide.";
}
if ($message === '') {
    $errors[] = 'Le message est requis.';
} elseif (strlen($message) > 800) {
    $errors[] = 'Le message est trop long (800 caractères max).';
}

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Enregistrer l'email dans la table enregistres si pas déjà présent
$verifying_registration = $db->prepare("SELECT id FROM enregistres WHERE email = ?");
$verifying_registration->bind_param('s', $email);
$verifying_registration->execute();
$verifying_registration->store_result();

if ($verifying_registration->num_rows == 0) {
    $registration = $db->prepare("INSERT INTO enregistres (email, nom) VALUES (?, ?)");
    $registration->bind_param('ss', $email, $name);
    $registration->execute();
    $registration->close();
}
$verifying_registration->close();

// Envoi d'email à l'admin
require_once '../mailer/mailer.php';
mailer(
    'gentillenoir075@outlook.com',
    'Nouveau message de contact',
    "<p>Message de <strong>$name</strong> ($email) :</p>
    <p>" . nl2br(htmlspecialchars($message)) . "</p>"
);

// Réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Merci de nous avoir contacté. Vous recevrez un email de retour.'
]);

$db->close();
