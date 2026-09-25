<?php
session_start();
require_once "config.php";

if ($db_error) {
    header("Location: login.php?error=db_error");
    exit();
}

$redirect_url = "";
$usr = "";
$pwd = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usr = isset($_POST["username"]) ? trim($_POST["username"]) : "";
    $pwd = isset($_POST["password"]) ? $_POST["password"] : "";

    if (empty($usr) || empty($pwd)) {
        $redirect_url = "login.php?error=empty_fields";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $usr);
        $stmt->execute();
        $result = $stmt->get_result();

        // echo "Utenti trovati: " . $result->num_rows; // debug

        if ($result->num_rows === 0) {
            // utente non esiste
            $redirect_url = "login.php?error=user_not_found";
        } else {
            $user = $result->fetch_assoc();

            // verifica password con hash
            if (password_verify($pwd, $user["password"])) {
                // ok
                $_SESSION["logged"] = true;
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $redirect_url = "index.php";
            } else {
                $redirect_url = "login.php?error=invalid_password";
            }
        }

        $stmt->close();
        $conn->close();
    }
} else {
    $redirect_url = "login.php";
}

header("Location: " . $redirect_url);
exit();
?>
