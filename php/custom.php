<?php
session_start();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - custom</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href = "../css/custom.css">
    <link rel="stylesheet" href="../css/font.css">
    <script src="../js/custom.js" defer></script>
</head>
<body>
    <header>
        <div class="left">
            <a href="index.php" id="home" class="logo">
                <img src="../res/img/main_logo.png" alt="Logo worzle" class="logo">
            </a>
        </div>
        <div class="center">
            <div>
                <a href="game.php" id="game"><h3>Parola di oggi</h3></a>
            </div>
            <div>
                <a href="leaderboards.php" id="classifiche"><h3>Classifiche</h3></a>
            </div>
            <div>
                <a href="custom.php" id="custom"><h3>Custom mode</h3></a>
            </div>
        </div>
        <div class="right">
            <?php
                if(isset($_SESSION["logged"]) && $_SESSION["logged"] == true){
                    $username = $_SESSION["username"];
                    echo '<p>Ciao ' . htmlspecialchars($username) . '</p>';
                    echo '<button id="logout" class="logout" onclick="window.location.href=\'logout.php\'">Logout</button>';
                } else {
                    echo '<button id="login" class="login" onclick="window.location.href=\'login.php\'">Login</button>';
                    echo '<button id="signup" class="signup" onclick="window.location.href=\'signup.php\'">Sign up</button>';
                }
            ?>
        </div>
    </header>

    <div class = "main-container">
        <h1>Modalita Custom</h1>
        <p class="subtitle">Personalizza la tua partita</p>

        <form id="custom-form" class="form">

            <div class="campo">
                <label>Lunghezza parola</label>
                <div class="radio-group">
                    <input type="radio" id="len-5" name="word-length" value="5" checked>
                    <label for="len-5" class="radio-label">5 lettere</label>

                    <input type="radio" id="len-6" name="word-length" value="6" >
                    <label for="len-6" class="radio-label">6 lettere</label>

                    <input type="radio" id="len-7" name="word-length" value="7">
                    <label for="len-7" class="radio-label">7 lettere</label>
                </div>
            </div>

            <div class="campo">
                <label for="attempts">Numero tentativi</label>
                <input type="range" id="attempts" name="attempts" min="1" max="10" value="6">
                <span id="attempts-value" class="range-value">6</span>
            </div>

            <div class="campo">
                <label for="timer-toggle">Timer</label>

                <div class="toggle">
                    <input type="checkbox" id="timer-toggle" name="timer-toggle">
                    <label for="timer-toggle" class="toggle-text">Abilita timer</label>
                </div>
            </div>

            <div class="campo timer-opt hidden" id = "timer-options">
                <label for="timer">Tempo (secondi)</label>
                <input type="range" id="timer" name="timer" min="30" max="300" value="120" step="30">
                <span id="timer-value" class="range-value">2:00</span>
            </div>

            <button type="submit" class="btn-primary btn-gioca">Inizia a giocare</button>

        </form>
    </div>

    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>
</body>
</html>
