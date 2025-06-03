<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: calendario.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $conn = new mysqli("localhost", "root", "", "MagoRAF");

    if ($conn->connect_error) {
        die("Connessione fallita: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT id, username, password_hash FROM staff WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $db_username, $db_password_hash);
        $stmt->fetch();

        if (password_verify($password, $db_password_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $db_username;
            header("Location: calendario.php");
            exit;
        }

        header("Location: login.php?error=wrongpass");
        exit;
    }

    header("Location: login.php?error=notfound");
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MagoRAF Barber</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #ffffff;
        }

        header {
            background: linear-gradient(90deg, #001e9d, #000000);
            color: white;
            padding: 40px 20px;
            text-align: center;
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
            font-size: 1.1rem;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #66b2ff;
        }

        .container {
            max-width: 500px;
            background-color: #ffffff;
            margin: 50px auto;
            padding: 40px 30px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
            border-radius: 12px;
        }

        h2 {
            color: #001e9d;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            text-align: center;
            margin-bottom: 20px;
        }

        .error {
            color: #d8000c;
            background-color: #ffd2d2;
            border: 1px solid #d8000c;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: 500;
        }

        input[type="text"],
        input[type="password"] {
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input[type="submit"] {
            background-color: #001e9d;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0030d1;
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
        <h1>Benvenuto da MagoRAF Barber</h1>
        <p>Il tuo salone di fiducia, ora anche online!</p>
    </header>

    <nav>
        <a href="index.php">Home</a>
        <a href="prenota.php">Prenota Appuntamento</a>
        <a href="gestisci_appuntamenti.php">Gestisci Appuntamenti</a>
    </nav>

    <div class="container">
        <h2>Login</h2>
        <p>Inserisci le tue credenziali per accedere.</p>

        <?php
        if (isset($_GET['error'])) {
            $msg = match ($_GET['error']) {
                'wrongpass' => 'Password errata. Riprova.',
                'notfound' => 'Utente non trovato.',
                default => 'Errore sconosciuto.'
            };
            echo "<div class='error'>$msg</div>";
        }
        ?>

        <form method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="Entra">
        </form>
    </div>

    <footer>
        &copy; 2025 MagoRAF Barber - Tutti i diritti riservati
    </footer>

</body>

</html>