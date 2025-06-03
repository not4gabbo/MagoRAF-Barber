<?php
$messaggio = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["codice"])) {
    $codice = $_POST["codice"];

    $conn = new mysqli("localhost", "root", "", "MagoRAF");

    if ($conn->connect_error) {
        $messaggio = "Errore di connessione al database.";
    } else {
        $stmt = $conn->prepare("DELETE FROM appuntamenti WHERE codice_segreto = ?");
        $stmt->bind_param("s", $codice);
        $stmt->execute();

        $messaggio = $stmt->affected_rows > 0
            ? "Appuntamento eliminato con successo."
            : "Nessun appuntamento trovato con quel codice.";

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Elimina Appuntamento | MagoRAF Barber</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            background-color: #f0f0f5;
            color: #333;
        }

        header {
            background: linear-gradient(to right, #001e9d, #000000);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        nav {
            background-color: #222;
            display: flex;
            justify-content: center;
            padding: 10px 0;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #66b2ff;
        }

        main {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #001e9d;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 16px;
        }

        input[type="text"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        button {
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            background-color: #c0392b;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #a93226;
        }

        .messaggio {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-content p {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-cancel {
            background-color: #7f8c8d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #636e72;
        }

        .btn-confirm {
            background-color: #c0392b;
            color: white;
        }

        .btn-confirm:hover {
            background-color: #a93226;
        }

        footer {
            background-color: #1a1a1a;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            margin-top: 60px;
        }
    </style>
</head>

<body>

    <header>
        <h1>MagoRAF Barber</h1>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="prenota.php">Prenota Appuntamento</a>
        <a href="gestisci_appuntamenti.php">Gestisci Appuntamenti</a>
    </nav>

    <main>
        <div class="container">
            <h2>Elimina Appuntamento</h2>
            <form id="formEliminazione" method="POST">
                <label for="codice">Codice Appuntamento:</label>
                <input type="text" id="codice" name="codice" required placeholder="Es. APT1234">
                <button type="button" onclick="apriPopup()">Elimina</button>
            </form>

            <?php if (!empty($messaggio)): ?>
                <div class="messaggio"><?= htmlspecialchars($messaggio) ?></div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal" id="popupConferma">
        <div class="modal-content">
            <p id="testoConferma">Confermi di voler eliminare l'appuntamento?</p>
            <div class="modal-buttons">
                <button class="btn-cancel" onclick="chiudiPopup()">Annulla</button>
                <button class="btn-confirm" onclick="confermaElimina()">Conferma</button>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2025 MagoRAF Barber - Tutti i diritti riservati
    </footer>

    <script>
        function apriPopup() {
            const codice = document.getElementById("codice").value.trim();
            if (!codice) {
                alert("Inserisci un codice prima di procedere.");
                return;
            }
            document.getElementById("testoConferma").innerText =
                `Sei sicuro di voler eliminare l'appuntamento con codice "${codice}"?`;
            document.getElementById("popupConferma").style.display = "flex";
        }

        function chiudiPopup() {
            document.getElementById("popupConferma").style.display = "none";
        }

        function confermaElimina() {
            document.getElementById("popupConferma").style.display = "none";
            document.getElementById("formEliminazione").submit();
        }
    </script>

</body>

</html>