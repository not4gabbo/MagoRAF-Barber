<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Metodo non consentito";
    exit;
}

$nome = $_POST['nome_cliente'] ?? '';
$cognome = $_POST['cognome_cliente'] ?? '';
$contatto = $_POST['contatto_cliente'] ?? '';
$data = $_POST['data'] ?? '';
$orario = $_POST['orario'] ?? '';
$barbiere_id = (int) ($_POST['barbiere_id'] ?? 0);
$servizio_id = (int) ($_POST['servizio'] ?? 0);

if (!$nome || !$cognome || !$contatto || !$data || !$orario || !$barbiere_id || !$servizio_id) {
    http_response_code(400);
    echo "Dati mancanti o non validi.";
    exit;
}

function generaCodiceUnivoco(int $length = 10): string
{
    $caratteri = 'ABCDEFGHJKLMNPQRSTUVWXYZ123456789';
    return substr(str_shuffle(str_repeat($caratteri, $length)), 0, $length);
}

$codice = generaCodiceUnivoco();

$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    http_response_code(500);
    echo "Connessione al database fallita.";
    exit;
}

$stmt = $conn->prepare("INSERT INTO appuntamenti (nome_cliente, cognome_cliente, contatto_cliente, data, orario_inizio, servizio, parrucchiere, codice_segreto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssiis", $nome, $cognome, $contatto, $data, $orario, $servizio_id, $barbiere_id, $codice);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Conferma Appuntamento</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f4f4f4;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 40px 60px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
        }

        h1 {
            color: #001e9d;
            font-size: 28px;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .codice-container {
            margin: 20px 0;
        }

        code {
            display: inline-block;
            padding: 10px 20px;
            background: #001e9d;
            color: white;
            font-size: 22px;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 10px;
        }

        .copy-btn {
            padding: 10px 16px;
            font-size: 14px;
            background: #f0f0f0;
            color: #001e9d;
            border: 2px solid #001e9d;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .copy-btn:hover {
            background: #001e9d;
            color: white;
        }

        a {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            background: #001e9d;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        a:hover {
            background-color: #000c6d;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php
        if ($stmt->execute()) {
            echo "<h1>Appuntamento confermato!</h1>";
            echo "<p>Grazie <strong>$nome $cognome</strong>, il tuo appuntamento è stato registrato con successo.</p>";
            echo "<p><strong>Codice appuntamento:</strong></p>";
            echo "<div class='codice-container'>";
            echo "<code id='codice'>$codice</code>";
            echo "<button class='copy-btn' onclick='copiaCodice()' id='copyBtn'>Copia</button>";
            echo "</div>";
            echo "<p>Salva questo codice per eventuali modifiche o cancellazioni future.</p>";
            echo "<a href='index.php'>Torna alla home</a>";
        } else {
            echo "<h1>Errore nella prenotazione</h1>";
            echo "<p>" . htmlspecialchars($stmt->error) . "</p>";
            echo "<a href='index.php'>Torna alla home</a>";
        }
        $stmt->close();
        $conn->close();
        ?>
    </div>
    <script>
        function copiaCodice() {
            const codice = document.getElementById('codice').innerText;
            navigator.clipboard.writeText(codice).then(() => {
                const btn = document.getElementById('copyBtn');
                btn.innerText = 'Copiato!';
                setTimeout(() => btn.innerText = 'Copia', 2000);
            }).catch(() => {
                alert('Errore nella copia del codice!');
            });
        }
    </script>
</body>

</html>