<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Headers: content-type');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000');
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
file_put_contents('raw.txt', $raw);
file_put_contents('debug.txt', print_r($data, true));

// Vérifier que $data est bien un tableau
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Erreur: données JSON invalides.']);
    exit;
}

// Extraction des champs avec valeurs par défaut
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$visit_date = trim($data['date'] ?? '');
$people = intval($data['people'] ?? 1);
$type = $data['type'] ?? 'classique';
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
// Téléphone
if ($phone === '') {
    $errors[] = 'Le numéro de téléphone est requis.';
}
// Date
if ($visit_date === '') {
    $errors[] = 'La date est requise.';
} else {
    $today = new DateTime();
    $today->setTime(0,0,0,0);
    $selected = DateTime::createFromFormat('Y-m-d', $visit_date);
    if (!$selected) {
        $errors[] = 'La date est invalide.';
    } elseif ($selected < $today) {
        $errors[] = 'La date doit être aujourd\'hui ou dans le futur.';
    }
}
// Nombre de personnes
if ($people < 1) $errors[] = 'Le nombre de personnes doit être au moins 1.';
// Type de visite
$validTypes = ['classique', 'privee', 'familiale'];
if (!in_array($type, $validTypes)) $errors[] = 'Type de visite invalide.';
// Message
if (strlen($message) > 800) $errors[] = 'Le message est trop long (800 caractères max).';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

require_once '../mailer/mailer.php';

// Insertion dans DB
$stmt = $db->prepare("INSERT INTO reservations (name, email, phone, visit_date, people, type, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssiss', $name, $email, $phone, $visit_date, $people, $type, $message);

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
    echo json_encode(['success' => true, 'message' => 'Votre réservation a été enregistrée.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur, veuillez réessayer.']);
}

// pour le client
mailer(
    $email,
    'Confirmation de votre réservation',
    "<p>Bonjour $name,</p>
    <p>Merci pour votre réservation. Voici un récapitulatif :</p>
    <ul>
        <li>Date de visite : $visit_date</li>
        <li>Nombre de personnes : $people</li>
        <li>Type de visite : $type</li>
    </ul>
    <p>Nous avons hâte de vous accueillir !</p>
    <p>Cordialement,<br>L'équipe Idjwi Tourisme</p>"
);

// pour l admin
mailer(
    'basemehabajuwe3@gmail.com',
    'Nouvelle réservation reçue',
    "<p>Nouvelle réservation de $name ($email, $phone) :</p>
    <ul>
        <li>Date de visite : $visit_date</li>
        <li>Nombre de personnes : $people</li>
        <li>Type de visite : $type</li>
        <li>Message : " . nl2br(htmlspecialchars($message)) . "</li>
    </ul>
    <p>Connectez-vous au panneau d'administration pour plus de détails.</p>"
);
$stmt->close();
$db->close();