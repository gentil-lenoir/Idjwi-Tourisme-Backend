<?php
header('Access-Control-Allow-Origin: https://idjwi-tourisme.vercel.app/');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db/config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');

if ($name === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'Nom et email sont requis.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => "L'email n'est pas valide."]);
    exit;
}

// Vérifier si l'email existe déjà
$verifying_registration = $db->prepare("SELECT id FROM enregistres WHERE email = ?");
$verifying_registration->bind_param('s', $email);
$verifying_registration->execute();
$verifying_registration->store_result();

if ($verifying_registration->num_rows == 0) {
    $registration = $db->prepare("INSERT INTO enregistres (email, nom) VALUES (?, ?)");
    $registration->bind_param('ss', $email, $name);
    $registration->execute();
    $registration->close();

    echo json_encode(['success' => true, 'message' => 'Merci pour votre inscription à notre newsletter.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà inscrit.']);
}

$verifying_registration->close();
$db->close();
