<?php
session_start();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - home</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/index.css">
    <script src="../js/rules_dialog.js"></script>
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

    <main class="welcome-section">
        <div class="welcome-container">
            <img src="../res/img/main_logo.png" alt="Logo Worzle" class="welcome-logo">
            <?php
            if(isset($_SESSION["logged"]) && $_SESSION["logged"] == true){
                $username = $_SESSION["username"];
                echo '<h1>Ciao ' . htmlspecialchars($username) . '</h1>';
            }
            ?>
            <h1>Benvenuto su Worzle!</h1>
            <p class="welcome-text">Metti alla prova il tuo vocabolario con il gioco di parole più avvincente</p>

            <div class="welcome-buttons">
                <?php
                if(isset($_SESSION["logged"]) && $_SESSION["logged"] == true){
                    $username = $_SESSION["username"];
                    echo '<button id="main-play" class="play" onclick="window.location.href=\'game.php\'">Gioca</button>';
                } else {
                    echo '<button id="main-login" class="login" onclick="window.location.href=\'login.php\'">Login</button>';
                    echo '<button id="main-signup" class="signup" onclick="window.location.href=\'signup.php\'">Sign up</button>';
                }
                ?>
            </div>
            <div class="welcome-buttons">
                <button id="regole" class="regole">Regole</button>
            </div>
        </div>
    </main>
    <div id="regole-dialog" class="dialog-overlay">
        <div class="dialog-container">
            <button id="close-dialog" class="close-button">X</button>
            <h2>Regole di Worzle</h2>
            <div class="regole-content">
                <p>Benvenuto in Worzle! Ecco come si gioca:</p>
                <ul>
                    <li>Hai 6 tentativi per indovinare la parola del giorno</li>
                    <li>Ogni tentativo deve essere una parola valida di 5 lettere</li>
                    <li>Dopo ogni tentativo, i colori delle caselle cambieranno per mostrarti quanto sei vicino alla parola corretta</li>
                </ul>
                <h3>Significato dei colori:</h3>
                <ul>
                    <li><strong style="color: #4CAF50;">Verde:</strong> La lettera è nella parola e nella posizione corretta</li>
                    <li><strong style="color: #FF9800;">Arancione:</strong> La lettera è nella parola ma nella posizione sbagliata</li>
                    <li><strong style="color: #666;">Grigio:</strong> La lettera non è presente nella parola</li>
                </ul>
                <h3>Modalità custom:</h3>
                <ul>
                    <li>Se hai già giocato alla parola del giorno è possibile giocare con la modalità custom</li>
                    <li>Scegli la lunghezza della parola (da 5 fino a un massimo di 8 lettere)</li>
                    <li>Inserisci la parola da indovinare (deve essere presente nella lista di parole disponibili)</li>
                    <li>Imposta un limite di tempo se lo desideri</li>
                    <li>Infine inserisci quanti tentativi ha il giocatore per indovinare la parola</li>
                    <li>Divertiti!</li>
                </ul>
                <p>Buona fortuna!</p>
                <div class="welcome-buttons">
                    <button class="regole" onclick="window.location.href='../html/documentazione.html'">Documentazione completa</button>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>
</body>
</html>