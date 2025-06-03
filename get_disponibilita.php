<?php

header('Content-Type: application/json');

// Verifica parametri richiesti
if (!isset($_GET['barbiere_id'], $_GET['data'], $_GET['durata'])) {
    http_response_code(400);
    echo json_encode(["errore" => "Parametri mancanti"]);
    exit;
}

$barbiere_id = intval($_GET['barbiere_id']);
$data = $_GET['data']; // formato YYYY-MM-DD
$durata = intval($_GET['durata']); // in minuti

// Controllo anti-zero
if ($durata <= 0) {
    http_response_code(400);
    echo json_encode(["errore" => "Durata non valida"]);
    file_put_contents("log_debug.txt", "ERRORE: Durata non valida (<= 0)\n", FILE_APPEND);
    exit;
}

// Verifica validità della data
if (!DateTime::createFromFormat('Y-m-d', $data)) {
    http_response_code(400);
    echo json_encode(["errore" => "Formato data non valido"]);
    file_put_contents("log_debug.txt", "ERRORE: Data non valida: $data\n", FILE_APPEND);
    exit;
}

// Connessione al database
$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["errore" => "Connessione al database fallita"]);
    file_put_contents("log_debug.txt", "ERRORE: Connessione DB fallita\n", FILE_APPEND);
    exit;
}

// Orario di apertura e chiusura del barbiere
$orarioApertura = new DateTime("$data 09:00");
$orarioChiusura = new DateTime("$data 18:00"); // Chiusura alle 18
$intervalloMinuti = 15;

// Limite massimo per inizio slot in base alla durata
$orarioUltimoInizio = clone $orarioChiusura;
$orarioUltimoInizio->modify("-{$durata} minutes");

// Data e ora attuali
$oggi = (new DateTime())->format('Y-m-d');
$oraAdesso = new DateTime();

if ($data === $oggi && $oraAdesso > $orarioApertura) {
    $orarioApertura = clone $oraAdesso;
    // Arrotonda al prossimo intervallo utile
    $minuti = intval($orarioApertura->format('i'));
    $arrotondati = ceil($minuti / $intervalloMinuti) * $intervalloMinuti;
    $orarioApertura->setTime((int) $orarioApertura->format('H'), 0);
    $orarioApertura->modify("+{$arrotondati} minutes");

    // Se l’orario attuale supera già l’ultimo possibile slot, nessuna disponibilità
    if ($orarioApertura > $orarioUltimoInizio) {
        echo json_encode([]); // Nessuno slot possibile
        exit;
    }
}

// Recupera gli appuntamenti esistenti
$query = "
    SELECT a.orario_inizio, s.durata_minuti
    FROM appuntamenti a
    JOIN servizi s ON a.servizio = s.id
    WHERE a.parrucchiere = ? AND a.data = ? AND a.stato = 'Prenotato'
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $barbiere_id, $data);
$stmt->execute();
$result = $stmt->get_result();

$appuntamenti = [];
while ($row = $result->fetch_assoc()) {
    $inizio = new DateTime("$data {$row['orario_inizio']}");
    $fine = clone $inizio;
    $fine->modify("+{$row['durata_minuti']} minutes");
    $appuntamenti[] = [$inizio, $fine];
}
$stmt->close();
$conn->close();

// Calcola slot disponibili
$disponibili = [];
$slot = clone $orarioApertura;

while ($slot <= $orarioUltimoInizio) {
    $slotFine = clone $slot;
    $slotFine->modify("+{$durata} minutes");

    $conflitto = false;
    foreach ($appuntamenti as [$inizio, $fine]) {
        if ($slot < $fine && $slotFine > $inizio) {
            $conflitto = true;
            break;
        }
    }

    if (!$conflitto) {
        $disponibili[] = $slot->format("H:i");
    }

    $slot->modify("+{$intervalloMinuti} minutes");
}

// Risposta finale
echo json_encode($disponibili);
