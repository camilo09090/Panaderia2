<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "panaderia";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>

