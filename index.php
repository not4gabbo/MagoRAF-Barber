<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MagoRAF Barber - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
        }

        body {
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
            color: #1c1c1c;
        }

        header {
            background: linear-gradient(90deg, #001e9d, #000000);
            color: white;
            padding: 40px 20px;
            text-align: center;
            position: relative;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .top-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .top-buttons a,
        .top-buttons button {
            background-color: #fff;
            color: #001e9d;
            border: 2px solid #001e9d;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .top-buttons a:hover,
        .top-buttons button:hover {
            background-color: #001e9d;
            color: #fff;
        }

        nav {
            background-color: #2c3e50;
            padding: 12px 0;
            text-align: center;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;
            font-weight: 500;
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #66b2ff;
        }

        .container {
            background-color: #fff;
            max-width: 900px;
            margin: 40px auto;
            padding: 40px 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #001e9d;
        }

        ul {
            list-style: none;
            padding: 0;
            text-align: center;
        }

        ul li {
            margin: 8px 0;
            font-weight: 500;
        }

        iframe {
            width: 100%;
            height: 400px;
            border: none;
            margin-top: 20px;
        }

        footer {
            background-color: #1a1a1a;
            color: #fff;
            text-align: center;
            padding: 20px;
            font-size: 0.9rem;
            margin-top: 60px;
        }

        .carousel {
            position: relative;
            width: 100%;
            overflow: hidden;
            margin: 30px 0;
            height: 450px;
        }

        .carousel-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .carousel-track img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            flex-shrink: 0;
        }

        p {
            line-height: 1.6;
            font-size: 1.05rem;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <header>
        <h1>Benvenuto da MagoRAF Barber</h1>
        <p>Il tuo salone di fiducia, ora anche online!</p>
        <div class="top-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span style="color: #fff; font-weight: bold;">Ciao, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <form action="logout.php" method="post" style="margin: 0;">
                    <button type="submit">Disconnettiti</button>
                </form>
            <?php else: ?>
                <a href="login.php">Login Staff</a>
            <?php endif; ?>
        </div>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="prenota.php">Prenota Appuntamento</a>
        <a href="gestisci_appuntamenti.php">Gestisci Appuntamenti</a>
    </nav>

    <div class="container">
        <h2>Chi siamo</h2>
        <p>Siamo un team di professionisti pronti a offrirti il miglior servizio di taglio e styling, con la comodità di
            una prenotazione online semplice e veloce.</p>

        <div class="carousel">
            <div class="carousel-track" id="carousel-track">
                <img src="./imgs/home/1.png" alt="Immagine 1">
                <img src="./imgs/home/2.png" alt="Immagine 2">
            </div>
        </div>

        <h2>Orari di apertura</h2>
        <ul>
            <li>Lunedì: Chiuso</li>
            <li>Martedì - Sabato: 09:00 - 18:00</li>
            <li>Domenica: Chiuso</li>
        </ul>

        <h2>Contatti</h2>
        <p>Email: info@magorafbarber.it</p>
        <p>Telefono: 0123 456789</p>

        <h2>Dove trovarci</h2>
        <p>Via Piacenza, 25/B, 26013 Crema CR</p>
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5607.182961382635!2d9.685713312482493!3d45.35705817095159!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47813b1e50884db9%3A0x8f9650cd5adb0436!2sLa%20Barbieria%20di%20Crema!5e0!3m2!1sit!2sit!4v1747942991260!5m2!1sit!2sit"
            allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <footer>
        &copy; 2025 MagoRAF Barber - Tutti i diritti riservati
    </footer>

    <script>
        const track = document.getElementById('carousel-track');
        const images = track.querySelectorAll('img');
        let index = 0;

        setInterval(() => {
            index = (index + 1) % images.length;
            track.style.transform = `translateX(-${index * 100}%)`;
        }, 3000);
    </script>
</body>

</html>