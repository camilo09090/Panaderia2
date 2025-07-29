<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php"); 
    exit();
}

include('../../conexion.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM catalogo WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} else {
    header("Location: listar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminando...</title>
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
            text: 'La categoría ha sido eliminada correctamente.',
            showConfirmButton: false,
            timer: 2000,
            background: '#1e293b',
            color: '#e0e0e0'
        }).then(() => {
            window.location.href = 'listar.php';
        });
    </script>
</body>
</html>
