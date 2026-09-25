<?php
$host = "localhost";
$db_user = "root";
$db_pass = ""; 
$db_name = "spallazzi_665387";

$db_error = null;

$conn = mysqli_connect($host, $db_user, $db_pass, $db_name);

if (!$conn) {
    $db_error = "Errore di connessione al database: " . mysqli_connect_error();
    // echo $db_error; // debug
}
?>
