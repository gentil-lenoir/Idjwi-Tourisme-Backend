<?php
header('Access-Control-Allow-Origin: https://idjwi-tourisme.vercel.app');
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
    $sql = "SELECT id, nom, contenu, note, created_at 
            FROM temoignages 
            ORDER BY created_at DESC";
    $result = $db->query($sql);

    $temoignages = [];
    while ($row = $result->fetch_assoc()) {
        $temoignages[] = [
            'id' => (int)$row['id'],
            'author' => $row['nom'],
            'text' => $row['contenu'],
            'rating' => (int)$row['note'],
            // Avatar par défaut (peut être remplacé par un champ en DB plus tard)
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($row['nom']) . '&background=random'
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $temoignages
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des témoignages.',
        'error' => $e->getMessage()
    ]);
}

$db->close();
