<?php
require_once "config.php";

// check db error
if ($db_error) {
    header("Location: signup.php?error=db_error");
    exit();
}

$redirect_url = "";

// controllo se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // prendo i dati dal form
    $username = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";
    $confirm_password = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : "";

    // controllo campi vuoti
    if (empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: signup.php?error=empty_fields");
        exit();
    }

    // validazione username: 4-10 caratteri alfanumerici
    if (!preg_match('/^[a-zA-Z0-9]{4,10}$/', $username)) {
        header("Location: signup.php?error=invalid_username");
        exit();
    }

    // validazione password: 4-16 caratteri con almeno un numero
    if (!preg_match('/^(?=.*[0-9]).{4,16}$/', $password)) {
        header("Location: signup.php?error=invalid_password");
        exit();
    }

    // controllo che le password coincidano
    if ($password !== $confirm_password) {
        header("Location: signup.php?error=password_mismatch");
        exit();
    }

        // controllo se username già esiste nel database
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // esiste già
            $redirect_url = "signup.php?error=user_exists";
        } else {
            // creo nuovo utente
            // hash della password con bcrypt
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                // registrazione ok
                $redirect_url = "login.php?success=registered";
            } else {
                // errore database
                $redirect_url = "signup.php?error=db_error";
            }
        }

        $stmt->close();

    $conn->close();
} else {
    $redirect_url = "signup.php";
}

header("Location: " . $redirect_url);
exit();
?>
