<?php
header('Access-Control-Allow-Origin:' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Gérer la requête OPTIONS (préflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../db/config.php';

try {
    $sql = "SELECT id, nom, email, created_at 
            FROM enregistres 
            ORDER BY created_at DESC";
    $result = $db->query($sql);

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => (int)$row['id'],
            'name' => $row['nom'],
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($row['nom']) . '&background=random'
        ];
    }

    echo json_encode([
        'success' => true,
        'users' => $users
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des utilisateurs.',
        'error' => $e->getMessage()
    ]);
}

$db->close();
