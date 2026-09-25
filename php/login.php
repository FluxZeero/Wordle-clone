<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - Login</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/login.css">
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
                    echo '<p>Ciao, ' . htmlspecialchars($username) . '</p>';
                } else {
                    echo '<button id="login" class="login" onclick="window.location.href=\'login.php\'">Login</button>';
                    echo '<button id="signup" class="signup" onclick="window.location.href=\'signup.php\'">Sign up</button>';
                }
            ?>
        </div>
    </header>

    <main class="login-section">
        <div class="login-container">
            <img src="../res/img/main_logo.png" alt="Logo Worzle" class="login-logo">
            <h1>Accedi a Worzle</h1>
                <?php
                    $error_message = "";
                    $success_message = "";

                    if (isset($_GET["error"])) {
                        switch ($_GET["error"]) {
                            case "empty_fields":
                                $error_message = "Compila tutti i campi";
                                break;
                            case "user_not_found":
                                $error_message = "Utente non trovato";
                                break;
                            case "invalid_password":
                                $error_message = "Password non valida";
                                break;
                            case "db_error":
                                $error_message = "Errore di connessione";
                                break;
                            default:
                                $error_message = "";
                        }
                        echo "<p class='login-text-error'>$error_message</p>";
                    } else if (isset($_GET["success"]) && $_GET["success"] == "registered") {
                        echo "<p class='login-text-success'>Registrazione completata! Effettua il login</p>";
                    } else {
                        echo "<p class='login-text'>Inserisci le tue credenziali per continuare</p>";
                    }
                ?>
            

            <form action="login_handler.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Inserisci username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Inserisci password" required>
                </div>

                <button type="submit" class="login-btn">Accedi</button>
            </form>

            <div class="login-links">
                <p>Non hai un account? <a href="signup.php">Registrati</a></p>
            </div>
        </div>
    </main>



    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>
</body>
</html>
