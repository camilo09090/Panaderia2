<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Buscar en usuarios (admin/empleados)
    $query = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['usuario'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['id_usuario'] = $usuario['id'];
            // Redirige al panel admin si es admin/administradora
            if ($usuario['rol'] === 'asistente' || $usuario['rol'] === 'administradora') {
                header("Location: dashboard.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            echo "<script>alert('⚠️ Contraseña incorrecta'); window.location.href='login_form.php';</script>";
            exit();
        }
    } else {
        // Buscar en clientes
        $query2 = $conn->prepare("SELECT * FROM clientes WHERE correo_electronico = ?");
        $query2->bind_param("s", $email);
        $query2->execute();
        $resultado2 = $query2->get_result();
        if ($resultado2->num_rows === 1) {
            $cliente = $resultado2->fetch_assoc();
            if (isset($cliente['password']) && password_verify($password, $cliente['password'])) {
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nombre'] = $cliente['nombre'];
                $_SESSION['rol'] = 'cliente';

                // --- FUSIÓN DE CARRITOS AQUÍ ---
                if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
                    $cliente_id = $_SESSION['cliente_id'];
                    foreach ($_SESSION['carrito'] as $item) {
                        $producto_id = isset($item['producto_id']) ? $item['producto_id'] : $item['id'];
                        $cantidad = $item['cantidad'];
                        $query = $conn->prepare("SELECT id, cantidad FROM carrito WHERE cliente_id = ? AND producto_id = ?");
                        $query->bind_param("ii", $cliente_id, $producto_id);
                        $query->execute();
                        $result = $query->get_result();
                        if ($row = $result->fetch_assoc()) {
                            $nueva_cantidad = $row['cantidad'] + $cantidad;
                            $update = $conn->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
                            $update->bind_param("ii", $nueva_cantidad, $row['id']);
                            $update->execute();
                        } else {
                            $insert = $conn->prepare("INSERT INTO carrito (cliente_id, producto_id, cantidad) VALUES (?, ?, ?)");
                            $insert->bind_param("iii", $cliente_id, $producto_id, $cantidad);
                            $insert->execute();
                        }
                    }
                    unset($_SESSION['carrito']);
                }
                // --- FIN FUSIÓN DE CARRITOS ---

                header("Location: ../index.php");
                exit();
            } else {
                echo "<script>alert('⚠️ Contraseña incorrecta'); window.location.href='login_form.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('❌ Usuario no encontrado'); window.location.href='login_form.php';</script>";
            exit();
        }
    }
} else {
    header("Location: login_form.php");
    exit();
}
?>
