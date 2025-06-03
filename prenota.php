<?php
session_start();

$conn = new mysqli("localhost", "root", "", "MagoRAF");
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT id, nome FROM staff");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prenota Appuntamento - MagoRAF Barber</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(90deg, #001e9d, #000000);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        header p {
            margin-top: 8px;
            font-size: 16px;
            font-weight: 300;
        }

        nav {
            background-color: #2c3e50;
            padding: 12px 0;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-weight: 500;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #ccc;
        }

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .select-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }

        .select-wrapper select {
            padding: 12px;
            font-size: 16px;
            min-width: 250px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        #calendar {
            height: 700px;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
        }

        .fc-toolbar-title::first-letter {
            text-transform: uppercase;
        }

        #popup-prenotazione {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        #popup-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        #popup-prenotazione select,
        #popup-prenotazione input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        #popup-prenotazione button {
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-right: 10px;
        }

        #popup-prenotazione button[type="submit"] {
            background-color: #001e9d;
            color: white;
        }

        #popup-prenotazione button[type="button"] {
            background-color: #555;
            color: white;
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
        <p>Prenota il tuo appuntamento online!</p>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="prenota.php">Prenota Appuntamento</a>
        <a href="gestisci_appuntamenti.php">Gestisci Appuntamenti</a>
    </nav>

    <div class="container">
        <h2>Prenota un Appuntamento</h2>

        <div class="select-wrapper">
            <label for="barbiere-select">Scegli un parrucchiere:</label><br><br>
            <select id="barbiere-select">
                <option value="">Seleziona un barbiere</option>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nome']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <?php
        $stmt->close();
        $conn->close();
        ?>

        <div id="calendar"></div>
    </div>

    <div id="popup-prenotazione" style="display: none;">
        <div id="popup-content">
            <h3>Prenota Appuntamento</h3>
            <form id="form-prenotazione" action="prenota_submit.php" method="POST">
                <input type="hidden" name="data" id="data-appuntamento">
                <input type="hidden" name="barbiere_id" id="barbiere-id">

                <label for="nome_cliente">Nome:</label>
                <input type="text" name="nome_cliente" id="nome_cliente" required>

                <label for="cognome_cliente">Cognome:</label>
                <input type="text" name="cognome_cliente" id="cognome_cliente" required>

                <label for="contatto_cliente">Contatto (telefono o email):</label>
                <input type="text" name="contatto_cliente" id="contatto_cliente" required>

                <label for="servizio">Servizio:</label>
                <select name="servizio" id="servizio" required></select>

                <label for="orario">Orario:</label>
                <select name="orario" id="orario" required></select>

                <button type="submit">Conferma</button>
                <button type="button" id="btn-annulla">Annulla</button>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2025 MagoRAF Barber - Tutti i diritti riservati
    </footer>

    <script>
        let calendar;
        let serviziPerBarbiere = {};

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'it',
                validRange: { start: new Date().toISOString().split('T')[0] },
                dateClick: function (info) {
                    const barbiereId = document.getElementById('barbiere-select').value;
                    if (!barbiereId) return;

                    document.getElementById('popup-prenotazione').style.display = 'flex';
                    document.getElementById('data-appuntamento').value = info.dateStr;
                    document.getElementById('barbiere-id').value = barbiereId;
                },
                events: []
            });
            calendar.render();
        });

        document.getElementById('barbiere-select').addEventListener('change', function () {
            const barbiereId = this.value;

            fetch(`get_appointments.php?barbiere_id=${barbiereId}&solo_prenotati=1`)
                .then(response => response.json())
                .then(events => {
                    calendar.removeAllEvents();
                    calendar.addEventSource(events);
                });

            fetch(`get_servizi.php?barbiere_id=${barbiereId}`)
                .then(response => response.json())
                .then(servizi => {
                    serviziPerBarbiere[barbiereId] = servizi;
                    const selectServizio = document.getElementById('servizio');
                    selectServizio.innerHTML = '<option value="">Seleziona un servizio</option>';
                    servizi.forEach(servizio => {
                        const opt = document.createElement('option');
                        opt.value = servizio.id;
                        opt.textContent = `${servizio.nome} (${servizio.durata_minuti} min)`;
                        selectServizio.appendChild(opt);
                    });
                    document.getElementById('orario').innerHTML = '<option value="">Seleziona orario</option>';
                });
        });

        document.getElementById('btn-annulla').addEventListener('click', () => {
            document.getElementById('popup-prenotazione').style.display = 'none';
        });

        document.getElementById('servizio').addEventListener('change', function () {
            const servizioId = this.value;
            const barbiereId = document.getElementById('barbiere-select').value;
            const servizi = serviziPerBarbiere[barbiereId] || [];
            const durata = servizi.find(s => Number(s.id) === Number(servizioId))?.durata_minuti || 0;
            const dataAppuntamento = document.getElementById('data-appuntamento').value;

            fetch(`get_disponibilita.php?barbiere_id=${barbiereId}&data=${dataAppuntamento}&durata=${durata}`)
                .then(response => response.json())
                .then(orariDisponibili => {
                    const orarioSelect = document.getElementById('orario');
                    orarioSelect.innerHTML = '<option value="">Seleziona orario</option>';
                    orariDisponibili.forEach(or => {
                        const opt = document.createElement('option');
                        opt.value = or;
                        opt.textContent = or;
                        orarioSelect.appendChild(opt);
                    });
                });
        });
    </script>
</body>

</html>