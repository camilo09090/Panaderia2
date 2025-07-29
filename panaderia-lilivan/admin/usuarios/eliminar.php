<?php
session_start();
if ($_SESSION['rol'] !== 'administradora') {
    http_response_code(403);
    exit('No autorizado');
}
include("../../conexion.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "ok";
} else {
    http_response_code(400);
    echo "ID no recibido";
}
?>
