<?php

if (!isset($_GET['barbiere_id'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Parametro barbiere_id mancante']);
    exit;
}

$barbiere_id = (int) $_GET['barbiere_id'];

$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['errore' => 'Errore connessione DB']);
    exit;
}

$stmt = $conn->prepare("
    SELECT s.id, s.nome, s.durata_minuti
    FROM servizi s
    INNER JOIN staff_servizi ss ON s.id = ss.id_servizio
    WHERE ss.id_parrucchiere = ?
");
$stmt->bind_param("i", $barbiere_id);
$stmt->execute();
$result = $stmt->get_result();

$servizi = [];
while ($row = $result->fetch_assoc()) {
    $servizi[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($servizi);
