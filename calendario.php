<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Prenota Appuntamento - MagoRAF Barber</title>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        header {
            background: linear-gradient(90deg, #001e9d, #000000);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        .top-buttons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-buttons span {
            font-weight: 500;
        }

        .top-buttons form button {
            background-color: white;
            color: #001e9d;
            border: 2px solid white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
        }

        .top-buttons form button:hover {
            background-color: #001e9d;
            color: white;
        }

        .container {
            padding: 30px;
            max-width: 1200px;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #calendar {
            height: 700px;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .fc-toolbar-title::first-letter {
            text-transform: uppercase;
        }

        footer {
            background-color: #1a1a1a;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            margin-top: 60px;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            max-width: 90%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        button {
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        #popupDettagli button {
            background-color: #001e9d;
            color: white;
            padding: 10px 20px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Benvenuto, <?= htmlspecialchars($username) ?></h1>
        <div class="top-buttons">
            <span><?= htmlspecialchars($username) ?></span>
            <form action="logout.php" method="post">
                <button type="submit">Disconnettiti</button>
            </form>
        </div>
    </header>

    <div class="container">
        <div id="calendar"></div>
    </div>

    <footer>
        &copy; 2025 MagoRAF Barber - Tutti i diritti riservati
    </footer>

    <div id="popupDettagli" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Dettagli Appuntamento</h3>
            <p><strong>Nome:</strong> <span id="popup-nome"></span></p>
            <p><strong>Cognome:</strong> <span id="popup-cognome"></span></p>
            <p><strong>Data:</strong> <span id="popup-data"></span></p>
            <p><strong>Orario:</strong> <span id="popup-orario"></span></p>
            <p><strong>Servizio:</strong> <span id="popup-servizio"></span></p>
            <p><strong>Codice Segreto:</strong> <span id="popup-codice"></span></p>
            <div id="completa-container" style="margin-top: 10px;"></div>
            <button onclick="chiudiDettagli()">Chiudi</button>
        </div>
    </div>

    <script>
        let calendar;

        function initCalendar(barbiereId) {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'it',
                allDaySlot: false,
                slotMinTime: "08:00:00",
                slotMaxTime: "20:00:00",
                nowIndicator: true,
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: {
                    url: 'get_appointments.php',
                    method: 'GET',
                    extraParams: {
                        barbiere_id: barbiereId
                    },
                    failure: () => alert('Errore nel caricamento degli appuntamenti!')
                },
                eventColor: '#001e9d',
                eventClick: function (info) {
                    const props = info.event.extendedProps;

                    document.getElementById('popup-nome').textContent = props.nome;
                    document.getElementById('popup-cognome').textContent = props.cognome;
                    document.getElementById('popup-data').textContent = props.data;
                    document.getElementById('popup-orario').textContent = props.orario;
                    document.getElementById('popup-servizio').textContent = props.servizio;
                    document.getElementById('popup-codice').textContent = props.codice_segreto;

                    const container = document.getElementById('completa-container');
                    container.innerHTML = '';

                    if (props.stato === "Prenotato") {
                        const button = document.createElement("button");
                        button.textContent = "Completa Appuntamento";
                        button.style.backgroundColor = "#28a745";
                        button.style.color = "white";
                        button.style.marginTop = "10px";
                        button.onclick = function () {
                            fetch("completa_appuntamento.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/json" },
                                body: JSON.stringify({ id_appuntamento: props.id_appuntamento })
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        alert("Appuntamento completato!");
                                        calendar.refetchEvents();
                                        chiudiDettagli();
                                    } else {
                                        alert("Errore nel completamento.");
                                    }
                                });
                        };
                        container.appendChild(button);
                    }

                    document.getElementById('popupDettagli').style.display = 'flex';
                },
                editable: false
            });

            calendar.render();
        }

        function chiudiDettagli() {
            document.getElementById('popupDettagli').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            initCalendar(<?= json_encode($user_id) ?>);
        });
    </script>
</body>

</html>