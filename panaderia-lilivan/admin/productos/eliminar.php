<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php");
    exit();
}
include('../../conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar.php");
    exit();
}

$id = (int)$_GET['id'];

$result = $conn->query("SELECT imagen_url FROM productos WHERE id = $id");
if ($result->num_rows === 0) {
    header("Location: listar.php");
    exit();
}
$producto = $result->fetch_assoc();

if ($producto['imagen_url'] && file_exists("../../otros/img/" . $producto['imagen_url'])) {
    unlink("../../otros/img/" . $producto['imagen_url']);
}

$sql = "DELETE FROM productos WHERE id = $id";

if ($conn->query($sql)) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Producto eliminado</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(to bottom right, #0f172a, #1e293b);
            font-family: 'Roboto', sans-serif;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
    </style>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'success',
            title: '¡Eliminado!',
            text: 'El producto ha sido eliminado exitosamente.',
            confirmButtonText: 'OK',
            background: '#1e293b',
            color: '#e0e0e0'
        }).then(() => {
            window.location.href = 'listar.php';
        });
    </script>
</body>
</html>
<?php
    exit();
} else {
    die("Error al eliminar producto: " . $conn->error);
}
?>
