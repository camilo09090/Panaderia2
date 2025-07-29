<?php
session_start();
$producto_id = $_POST['id'] ?? null;
$cantidad = $_POST['cantidad'] ?? 1;
$mensaje = '';
$contador = 0;

if (empty($producto_id) || !is_numeric($producto_id)) {
    echo json_encode([
        'mensaje' => 'Producto inválido',
        'contador' => 0
    ]);
    exit;
}

if (isset($_SESSION['cliente_id'])) {
    // Carrito en base de datos para usuario logueado
    $cliente_id = $_SESSION['cliente_id'];
    $conexion = new mysqli('localhost', 'root', '', 'panaderia'); // <-- CAMBIADO
    if ($conexion->connect_error) {
        echo json_encode([
            'mensaje' => 'Error de conexión',
            'contador' => 0
        ]);
        exit;
    }
    $sql = "SELECT id, cantidad FROM carrito WHERE cliente_id = ? AND producto_id = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo json_encode([
            'mensaje' => 'Error en la consulta',
            'contador' => 0
        ]);
        exit;
    }
    $stmt->bind_param("ii", $cliente_id, $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $nueva_cantidad = $row['cantidad'] + $cantidad;
        $update = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
        $update->bind_param("ii", $nueva_cantidad, $row['id']);
        $update->execute();
        $update->close();
        $mensaje = 'Cantidad actualizada en el carrito.';
    } else {
        $insert = $conexion->prepare("INSERT INTO carrito (cliente_id, producto_id, cantidad, fecha_agregado) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iii", $cliente_id, $producto_id, $cantidad);
        $insert->execute();
        $insert->close();
        $mensaje = 'Producto agregado al carrito.';
    }
    $stmt->close();
    // Contar productos en el carrito
    $res = $conexion->query("SELECT SUM(cantidad) FROM carrito WHERE cliente_id = $cliente_id");
    $row = $res->fetch_row();
    $contador = (int)$row[0];
    $conexion->close();
} else {
    // Carrito en sesión para usuario invitado
    $conexion = new mysqli('localhost', 'root', '', 'panaderia'); // <-- CAMBIADO
    if ($conexion->connect_error) {
        echo json_encode([
            'mensaje' => 'Error de conexión',
            'contador' => 0
        ]);
        exit;
    }
    $sql = "SELECT id, nombre, descripcion, precio, imagen_url FROM productos WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Asegúrate de guardar la ruta completa
        $row['imagen_url'] = 'otros/img/' . $row['imagen_url'];
        $carrito = $_SESSION['carrito'] ?? [];
        $encontrado = false;
        $producto_id = intval($producto_id);

        $nuevo_carrito = [];
        foreach ($carrito as $item) {
            if (intval($item['id']) !== $producto_id) {
                $nuevo_carrito[] = $item;
            } else {
                $row['cantidad'] = $item['cantidad'] + $cantidad;
                $row['producto_id'] = $row['id']; // <-- Añadido aquí
                $encontrado = true;
            }
        }
        if (!$encontrado) {
            $row['cantidad'] = $cantidad;
            $row['producto_id'] = $row['id']; // <-- Añadido aquí
        }
        $nuevo_carrito[] = $row;
        $_SESSION['carrito'] = array_values($nuevo_carrito);

        $mensaje = $encontrado ? 'Cantidad actualizada en el carrito.' : 'Producto agregado al carrito.';
        $contador = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $contador += $item['cantidad'];
        }
    } else {
        $mensaje = 'Producto no encontrado';
        $contador = 0;
    }
    $stmt->close();
    $conexion->close();
}


echo json_encode([
    'mensaje' => $mensaje,
    'contador' => $contador
]);
exit;
