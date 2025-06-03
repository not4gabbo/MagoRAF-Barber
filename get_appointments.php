<?php
header('Content-Type: application/json');

$barbiere_id = isset($_GET['barbiere_id']) ? intval($_GET['barbiere_id']) : 0;
$solo_prenotati = isset($_GET['solo_prenotati']) && $_GET['solo_prenotati'] === '1';

// Connessione al DB
$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Connessione al database fallita']);
    exit;
}

// Costruzione query
$query = "SELECT a.id, a.data, a.orario_inizio, a.nome_cliente, a.cognome_cliente, a.codice_segreto, a.stato, s.durata_minuti, s.nome AS servizio 
          FROM appuntamenti a 
          JOIN servizi s ON a.servizio = s.id 
          WHERE a.parrucchiere = ?";

if ($solo_prenotati) {
    $query .= " AND a.stato = 'Prenotato'";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $barbiere_id);
$stmt->execute();
$result = $stmt->get_result();

$eventi = [];

while ($row = $result->fetch_assoc()) {
    $start = $row['data'] . 'T' . $row['orario_inizio'];
    $startDateTime = new DateTime($start);
    $endDateTime = clone $startDateTime;
    $durata = intval($row['durata_minuti']) ?: 15; // Default a 15 min se non settata
    $endDateTime->modify("+$durata minutes");


    $colore = ($row['stato'] === 'Completato') ? '#28a745' : '#001e9d'; // Verde se completato, blu se prenotato

    $eventi[] = [
        'id' => $row['id'],
        'title' => $row['stato'],
        'start' => $start,
        'end' => $endDateTime->format('Y-m-d\TH:i:s'),
        'color' => $colore,
        'extendedProps' => [
            'nome' => $row['nome_cliente'],
            'cognome' => $row['cognome_cliente'],
            'data' => $row['data'],
            'orario' => $row['orario_inizio'],
            'servizio' => $row['servizio'],
            'id_appuntamento' => $row['id'],
            'stato' => $row['stato'],
            'codice_segreto' => $row['codice_segreto'] // <-- AGGIUNTO
        ]
    ];
}

echo json_encode($eventi);

$stmt->close();
$conn->close();
?>