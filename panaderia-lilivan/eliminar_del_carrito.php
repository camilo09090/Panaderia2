<?php
// filepath: c:\xampp\htdocs\Lilivan2\eliminar_del_carrito.php
session_start();

$carrito_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (isset($_SESSION['cliente_id'])) {
    // Usuario logueado: eliminar de la base de datos
    $cliente_id = $_SESSION['cliente_id'];
    if ($carrito_id > 0) {
        $conexion = new mysqli('localhost', 'root', '', 'lilivan-v2');
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }
        $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND cliente_id = ?");
        $stmt->bind_param("ii", $carrito_id, $cliente_id);
        $stmt->execute();
        $stmt->close();
        $conexion->close();
    }
} else {
    // Invitado: eliminar del carrito de sesión
    $carrito = $_SESSION['carrito'] ?? [];
    foreach ($carrito as $key => $item) {
        if ($item['id'] == $carrito_id) {
            unset($carrito[$key]);
            break;
        }
    }
    $_SESSION['carrito'] = array_values($carrito); // Reindexar el array
}

header('Location: carrito.php');
exit;