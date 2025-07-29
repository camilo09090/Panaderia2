<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: login_form.php");
    exit();
}

include("../conexion.php");

// Consultas con manejo de errores
$result = $conn->query("SELECT COUNT(*) FROM productos");
if (!$result) die("Error en la consulta de productos: " . $conn->error);
$total_productos = $result->fetch_row()[0];

$result = $conn->query("SELECT COUNT(*) FROM catalogo");
if (!$result) die("Error en la consulta de catalogo: " . $conn->error);
$total_catalogo = $result->fetch_row()[0];

$result = $conn->query("SELECT COUNT(*) FROM clientes");
if (!$result) die("Error en la consulta de clientes: " . $conn->error);
$total_clientes = $result->fetch_row()[0];

$result = $conn->query("SELECT COUNT(*) FROM pedidos");
if (!$result) die("Error en la consulta de pedidos: " . $conn->error);
$total_pedidos = $result->fetch_row()[0];

$result = $conn->query("SELECT COUNT(*) FROM pedidos WHERE estado IS NULL OR estado = 'pendiente'");
if (!$result) die("Error en la consulta de pedidos pendientes: " . $conn->error);
$total_pendientes = $result->fetch_row()[0];

$result = $conn->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado'");
if (!$result) die("Error en la consulta de pedidos entregados: " . $conn->error);
$total_entregados = $result->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración | Lilivan</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', 'Playfair Display', serif;
            background: linear-gradient(120deg, #f6f6f6 60%, #b3d33522 100%);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
        }
        header {
            background: #fff;
            padding: 32px 0 24px 0;
            border-bottom: 2px solid #e9ecef;
            box-shadow: 0 2px 16px #b3d33522;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        header h1 {
            margin: 0;
            color: #b3d335;
            font-size: 2.2rem;
            font-family: 'Playfair Display', serif;
            letter-spacing: 2px;
            text-shadow: 0 4px 24px #b3d33540;
        }
        .saludo {
            color: #7a7a7a;
            font-size: 1.08rem;
            margin-top: 10px;
            text-align: center;
            font-weight: 500;
        }
        .btn-cerrar {
            position: absolute;
            right: 32px;
            top: 32px;
            background: linear-gradient(90deg, #ef4444 60%, #dc2626 100%);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px #dc262640;
            display: inline-block;
        }
        .btn-cerrar:hover {
            background: linear-gradient(90deg, #dc2626 60%, #ef4444 100%);
            transform: scale(1.07);
            box-shadow: 0 6px 18px #dc262660;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto 0 auto;
            padding: 0 18px;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
            margin-bottom: 40px;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px #b3d33522;
            padding: 32px 24px 24px 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            border: 2px solid #e9ecef;
        }
        .card:hover {
            box-shadow: 0 8px 32px #b3d33540;
            transform: translateY(-4px) scale(1.03);
            border-color: #b3d335;
        }
        .card .icon {
            font-size: 2.5rem;
            color: #b3d335;
            margin-bottom: 12px;
        }
        .card .titulo {
            font-size: 1.15rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            text-align: center;
        }
        .card .valor {
            font-size: 2.2rem;
            font-weight: bold;
            color: #b3d335;
            margin-bottom: 10px;
            text-align: center;
        }
        .card .extra {
            font-size: 1.05rem;
            color: #7a7a7a;
            margin-bottom: 10px;
            text-align: center;
        }
        .card .boton-admin {
            background: #b3d335;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px #b3d33533;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            min-width: 160px;
            text-align: center;
            margin-top: 8px;
        }
        .card .boton-admin:hover {
            background: #8bbf1f;
            color: #fff;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 18px #b3d33560;
        }
        .card-pedidos {
            border: 2px solid #b3d335;
            background: linear-gradient(120deg, #fffde7 80%, #b3d33511 100%);
        }
        .card .status {
            font-size: 1em;
            margin-top: 4px;
            color: #e67e22;
            font-weight: 500;
        }
        .card .status.entregado {
            color: #27ae60;
        }
        @media (max-width: 900px) {
            .dashboard-container {
                max-width: 99vw;
                padding: 0 2vw;
            }
            .cards-grid {
                gap: 18px;
            }
        }
        @media (max-width: 600px) {
            .dashboard-container {
                padding: 0 2px;
            }
            .cards-grid {
                grid-template-columns: 1fr;
            }
            .card {
                padding: 18px 8px;
            }
        }
        footer {
            text-align: center;
            padding: 40px 0 20px;
            color: #7a7a7a;
            font-size: 0.95em;
            background: #fff;
            border-top: 2px solid #e9ecef;
            margin-top: 60px;
        }
    </style>
</head>
<body>
<header>
    <h1><i class="fas fa-chart-line"></i> Lilivan - Panel de Administración</h1>
    <p class="saludo">Bienvenid@, <?= htmlspecialchars($_SESSION['usuario']) ?></p>
    <a href="logout.php" class="btn-cerrar"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
</header>

<div class="dashboard-container">
    <div class="cards-grid">
        <div class="card">
            <span class="icon"><i class="fas fa-bread-slice"></i></span>
            <span class="titulo">Productos</span>
            <span class="valor"><?= $total_productos ?></span>
            <a href="productos/listar.php" class="boton-admin"><i class="fas fa-box"></i> Gestionar</a>
        </div>
        <div class="card">
            <span class="icon"><i class="fas fa-tags"></i></span>
            <span class="titulo">Categorías</span>
            <span class="valor"><?= $total_catalogo ?></span>
            <a href="catalogos/listar.php" class="boton-admin"><i class="fas fa-list"></i> Gestionar</a>
        </div>
        <div class="card">
            <span class="icon"><i class="fas fa-users"></i></span>
            <span class="titulo">Clientes</span>
            <span class="valor"><?= $total_clientes ?></span>
            <a href="clientes/listar.php" class="boton-admin"><i class="fas fa-user"></i> Ver</a>
        </div>
        <div class="card card-pedidos">
            <span class="icon"><i class="fas fa-shopping-basket"></i></span>
            <span class="titulo">Pedidos</span>
            <span class="valor"><?= $total_pedidos ?></span>
            <span class="extra"><i class="fas fa-clock"></i> Pendientes: <span class="status"><?= $total_pendientes ?></span></span>
            <span class="extra"><i class="fas fa-check-circle"></i> Entregados: <span class="status entregado"><?= $total_entregados ?></span></span>
            <a href="pedidos.php" class="boton-admin"><i class="fas fa-clipboard-list"></i> Gestionar</a>
        </div>
        <?php if ($_SESSION['rol'] === 'administradora'): ?>
        <div class="card">
            <span class="icon"><i class="fas fa-user-shield"></i></span>
            <span class="titulo">Empleados/Admins</span>
            <span class="valor">--</span>
            <a href="usuarios/listar.php" class="boton-admin"><i class="fas fa-user-cog"></i> Gestionar</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer>
    &copy; <?= date("Y") ?> Lilivan. Todos los derechos reservados.
</footer>
</body>
</html>