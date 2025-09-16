<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Headers: content-type');
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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);


file_put_contents('datas.txt', $data);
file_put_contents('raw_input.txt', $raw);

$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Le JSON reçu est invalide.',
        'error' => json_last_error_msg()
    ]);
    exit;
}

// Vérifier que $data est bien un tableau
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Erreur: données JSON invalides.']);
    exit;
}

// Extraction des champs avec valeurs par défaut
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$rating = trim($data['rating'] ?? 0);
$message = trim($data['message'] ?? '');


$errors = [];

// Nom
if ($name === '') {
    $errors[] = 'Le nom est requis.';
}
// Email
if ($email === '') {
    $errors[] = "L'email est requis.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'email n'est pas valide.";
}
// message
if ($message === '') {
    $errors[] = 'La message est réquise.';
}

require_once '../mailer/mailer.php';

// Insertion dans DB
$stmt = $db->prepare("INSERT INTO temoignages (nom, email, note, contenu) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssis', $name, $email, $rating, $message);

if ($stmt->execute()) {
    // Vérifier si l'email existe déjà dans enregistres
    $verifying_registration = $db->prepare("SELECT id FROM enregistres WHERE email = ?");
    $verifying_registration->bind_param('s', $email);
    $verifying_registration->execute();
    $verifying_registration->store_result();

    if ($verifying_registration->num_rows == 0) {
        // Si l'email n'existe pas, on l'ajoute
        $registration = $db->prepare("INSERT INTO enregistres (email, nom) VALUES (?, ?)");
        $registration->bind_param('ss', $email, $name);
        $registration->execute();
        $registration->close();
    }

    $verifying_registration->close();
    echo json_encode(['success' => true, 'message' => 'Votre Temoignage a été enregistrée.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur, veuillez réessayer.']);
}

// pour le client
mailer(
    $email,
    'Merci pour le Temoignage',
    "<p>Bonjour $name,</p>
    <p>Merci pour votre temoignage au sein de notre site, c'est super sympa de notre part</p>
    <p>Cordialement,<br>L'équipe Idjwi Tourisme</p>"
);

// pour l admin
mailer(
    'gentillenoir075@outlook.com',
    'Nouvelle temoignage',
    "<p>Nouvelle temoignage de $name ($email)</p>
    <p>Connectez-vous au panneau d'administration pour plus de détails.</p>"
);
$stmt->close();
$db->close();