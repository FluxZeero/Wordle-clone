<?php
// pagina del gioco
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - gioca</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/game.css">
    <link rel="stylesheet" href="../css/font.css">
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

    <main class="game-container">
        <div id="message" class="message hidden"></div>

        <div id="timer-container" class="timer-container hidden">
            <span id="timer">00:00</span>
        </div>

        <!-- Griglia di gioco -->
        <div id="game-grid" class="game-grid">
        </div>

        <div id="keyboard" class="keyboard">
            <div class="keyboard-row">
                <button class="key" id="key-Q">Q</button>
                <button class="key" id="key-W">W</button>
                <button class="key" id="key-E">E</button>
                <button class="key" id="key-R">R</button>
                <button class="key" id="key-T">T</button>
                <button class="key" id="key-Y">Y</button>
                <button class="key" id="key-U">U</button>
                <button class="key" id="key-I">I</button>
                <button class="key" id="key-O">O</button>
                <button class="key" id="key-P">P</button>
            </div>
            <div class="keyboard-row">
                <button class="key" id="key-A">A</button>
                <button class="key" id="key-S">S</button>
                <button class="key" id="key-D">D</button>
                <button class="key" id="key-F">F</button>
                <button class="key" id="key-G">G</button>
                <button class="key" id="key-H">H</button>
                <button class="key" id="key-J">J</button>
                <button class="key" id="key-K">K</button>
                <button class="key" id="key-L">L</button>
            </div>
            <div class="keyboard-row">
                <button class="key key-wide" id="key-ENTER">INVIO</button>
                <button class="key" id="key-Z">Z</button>
                <button class="key" id="key-X">X</button>
                <button class="key" id="key-C">C</button>
                <button class="key" id="key-V">V</button>
                <button class="key" id="key-B">B</button>
                <button class="key" id="key-N">N</button>
                <button class="key" id="key-M">M</button>
                <button class="key key-wide" id="key-BACKSPACE">&#9003;</button>
            </div>
        </div>

        <div id="game-over-modal" class="modal hidden">
            <div class="modal-content">
                <h2 id="modal-title">Risultato</h2>
                <p id="modal-message"></p>
                <p id="modal-solution"></p>
                <button id="play-again" class="btn-primary">Gioca ancora</button>
                <button id="home-btn" class="btn-primary">Torna alla home</button>
            </div>
        </div>
    </main>

    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>

    <script src="../js/game.js"></script>
</body>
</html>