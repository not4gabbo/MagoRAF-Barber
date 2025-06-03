<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id_appuntamento']) ? intval($data['id_appuntamento']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID non valido']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connessione fallita']);
    exit;
}

$stmt = $conn->prepare("UPDATE appuntamenti SET stato = 'Completato' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Errore nell\'aggiornamento']);
}

$stmt->close();
$conn->close();
?>