<?php
session_start();
if(isset($_SESSION["logged"])){
    session_destroy();
}
header("location: index.php");
?>
