<?php
session_start();
if (!isset($_POST['cantidades']) || !is_array($_POST['cantidades'])) {
    header("Location: carrito.php");
    exit;
}

if (isset($_SESSION['cliente_id'])) {
    $cliente_id = $_SESSION['cliente_id'];
    $conexion = new mysqli('localhost', 'root', '', 'panaderia'); // <-- CAMBIADO
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    foreach ($_POST['cantidades'] as $carrito_id => $cantidad) {
        $carrito_id = intval($carrito_id);
        $cantidad = intval($cantidad);
        if ($cantidad > 0) {
            $stmt = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ? AND cliente_id = ?");
            $stmt->bind_param("iii", $cantidad, $carrito_id, $cliente_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Eliminar si la cantidad es 0
            $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND cliente_id = ?");
            $stmt->bind_param("ii", $carrito_id, $cliente_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $conexion->close();
} else {
    // Actualizar cantidades en el carrito de sesión
    $carrito = $_SESSION['carrito'] ?? [];
    foreach ($_POST['cantidades'] as $producto_id => $cantidad) {
        $producto_id = intval($producto_id);
        $cantidad = intval($cantidad);
        foreach ($carrito as $key => &$item) {
            if ($item['id'] == $producto_id) {
                if ($cantidad > 0) {
                    $item['cantidad'] = $cantidad;
                } else {
                    unset($carrito[$key]);
                }
                break;
            }
        }
    }
    $_SESSION['carrito'] = array_values($carrito); // Reindexar el array
}
header("Location: carrito.php");
exit;