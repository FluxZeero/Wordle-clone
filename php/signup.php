<?php session_start(); ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZORZLE - Registrati</title>
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
            <h1>Registrati a Worzle</h1>
                <?php
                    $error_message = "";
                    if (isset($_GET["error"])) {
                        switch ($_GET["error"]) {
                            case "empty_fields":
                                $error_message = "Compila tutti i campi";
                                break;
                            case "invalid_username":
                                $error_message = "Username non valido (4-10 caratteri alfanumerici)";
                                break;
                            case "invalid_password":
                                $error_message = "Password non valida (4-16 caratteri con almeno un numero)";
                                break;
                            case "password_mismatch":
                                $error_message = "Le password non coincidono";
                                break;
                            case "user_exists":
                                $error_message = "Username già in uso";
                                break;
                            case "db_error":
                                $error_message = "Errore di connessione";
                                break;
                            default:
                                $error_message = "";
                        }
                        echo "<p class='login-text-error'>$error_message</p>";
                    } else {
                        echo "<p class='login-text'>Crea il tuo account per iniziare a giocare</p>";
                    }
                ?>

            <form action="signup_handler.php" method="POST" class="login-form" id="signup-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Inserisci username" required>
                    <small>4-10 caratteri alfanumerici</small>
                    <span id="username-error" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Inserisci password" required>
                    <small>4-16 caratteri, almeno un numero</small>
                    <span id="password-error" class="error-message"></span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Conferma Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ripeti password" required>
                    <span id="confirm-error" class="error-message"></span>
                </div>

                <button type="submit" class="login-btn">Registrati</button>
            </form>

            <div class="login-links">
                <p>Hai già un account? <a href="login.php">Accedi</a></p>
            </div>
        </div>
    </main>

    <footer>
        <p>Progetto realizzato da Matteo Spallazzi per l'esame di Progettazione Web, licenza per l'utilizzo delle icone concessa da <a href="https://www.flaticon.com/">flaticon.com</a></p>
    </footer>
</body>
</html>
