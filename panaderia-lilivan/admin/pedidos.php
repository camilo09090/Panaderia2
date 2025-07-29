<?php
// filepath: c:\xampp\htdocs\Lilivan-v4\admin\pedidos.php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php");
    exit();
}
include('../conexion.php');

// Cambiar estado de pedido
if (isset($_POST['cambiar_estado']) && isset($_POST['pedido_id']) && isset($_POST['estado'])) {
    $nuevo_estado = $_POST['estado'];
    $pedido_id = $_POST['pedido_id'];
    $stmt = $conn->prepare("UPDATE pedidos SET estado=? WHERE id=?");
    $stmt->bind_param("si", $nuevo_estado, $pedido_id);
    $stmt->execute();
    $stmt->close();
}

// Eliminar pedido
if (isset($_POST['eliminar_pedido']) && isset($_POST['pedido_id'])) {
    $pedido_id = $_POST['pedido_id'];
    // Elimina detalles primero por FK
    $conn->query("DELETE FROM pedido_detalles WHERE id_pedido = $pedido_id");
    $conn->query("DELETE FROM pedidos WHERE id = $pedido_id");
}

// Consulta pedidos pendientes
$pendientes = $conn->query("SELECT p.*, c.nombre AS cliente_nombre, c.telefono, c.direccion 
    FROM pedidos p 
    JOIN clientes c ON p.id_cliente = c.id 
    WHERE p.estado IS NULL OR p.estado = 'pendiente'
    ORDER BY p.fecha DESC");

// Consulta pedidos entregados
$entregados = $conn->query("SELECT p.*, c.nombre AS cliente_nombre, c.telefono, c.direccion 
    FROM pedidos p 
    JOIN clientes c ON p.id_cliente = c.id 
    WHERE p.estado = 'entregado'
    ORDER BY p.fecha DESC");

// Función para obtener detalles de un pedido
function obtenerDetalles($conn, $pedido_id) {
    $detalles = [];
    $sql = "SELECT pd.*, pr.nombre 
            FROM pedido_detalles pd 
            JOIN productos pr ON pd.id_producto = pr.id 
            WHERE pd.id_pedido = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $detalles[] = $row;
    }
    $stmt->close();
    return $detalles;
}

// Código para agregar un nuevo pedido (ejemplo)
if (isset($_POST['nuevo_pedido'])) {
    $cliente_id = $_POST['cliente_id'];
    $fecha = date('Y-m-d H:i:s');
    $total_final = $_POST['total_final'];
    $estado = 'pendiente';

    $stmt = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha, total, estado) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $cliente_id, $fecha, $total_final, $estado);
    $stmt->execute();
    $stmt->close();

    // Redirigir o mostrar mensaje de éxito
    header("Location: pedidos.php?mensaje=Pedido agregado exitosamente.");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos | Admin Lilivan</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f6f6f6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h2 {
            color: #b3d335;
            margin-top: 32px;
            margin-bottom: 10px;
            font-size: 1.6em;
        }
        .tabla-pedidos {
            width: 100%;
            max-width: 950px;
            margin: 0 auto 32px auto;
            border-collapse: collapse;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 32px #b3d33522;
            overflow: hidden;
            border: 2px solid #b3d335;
        }
        .tabla-pedidos th, .tabla-pedidos td {
            padding: 8px 6px;
            font-size: 0.97rem;
            border: 1.5px solid #e9ecef;
        }
        .tabla-pedidos th {
            background: #b3d33522;
            color: #b3d335;
            font-size: 1.05em;
            border-bottom: 2px solid #b3d335;
        }
        .acciones-form {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .btn-estado, .btn-eliminar {
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            font-size: 0.97em;
            transition: background 0.2s;
        }
        .btn-estado {
            background: #b3d335;
            color: #fff;
        }
        .btn-estado:hover {
            background: #8bbf1f;
        }
        .btn-eliminar {
            background: #e74c3c;
            color: #fff;
        }
        .btn-eliminar:hover {
            background: #c0392b;
        }
        .detalles-pedido {
            background: #f6f6f6;
            border-radius: 8px;
            margin: 8px 0;
            padding: 8px 12px;
            font-size: 0.96em;
        }
        .detalles-pedido ul {
            margin: 0;
            padding-left: 18px;
        }
        .estado-pendiente {
            color: #e67e22;
            font-weight: bold;
        }
        .estado-entregado {
            color: #27ae60;
            font-weight: bold;
        }
        @media (max-width: 900px) {
            .tabla-pedidos {
                max-width: 100vw;
                font-size: 0.93em;
            }
        }
    </style>
</head>
<body>
    <div style="max-width:950px;margin:0 auto;">
        <a href="dashboard.php" class="boton-admin" style="margin:24px 0 18px 0;display:inline-block;"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        <h2 style="text-align:center;"><i class="fas fa-clock"></i> Pedidos Pendientes</h2>
        <table class="tabla-pedidos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($p = $pendientes->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                    <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($p['telefono']) ?></td>
                    <td><?= htmlspecialchars($p['direccion']) ?></td>
                    <td>$<?= number_format($p['total'], 0, ',', '.') ?></td>
                    <td class="estado-pendiente"><?= isset($p['estado']) ? ucfirst($p['estado']) : 'Pendiente' ?></td>
                    <td>
                        <div class="detalles-pedido">
                            <ul>
                            <?php foreach(obtenerDetalles($conn, $p['id']) as $d): ?>
                                <li><?= htmlspecialchars($d['nombre']) ?> x<?= $d['cantidad'] ?> ($<?= number_format($d['precio_unitario'], 0, ',', '.') ?>)</li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    </td>
                    <td>
                        <form method="post" class="acciones-form" onsubmit="return false;">
                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="estado" value="entregado">
                            <button type="button" class="btn-estado" onclick="confirmarEntregado(this.form)" title="Marcar como entregado"><i class="fas fa-check"></i> Entregado</button>
                            <button type="button" class="btn-eliminar" onclick="confirmarEliminacion(this.form)" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>

        <h2 style="text-align:center;"><i class="fas fa-check-circle"></i> Pedidos Entregados</h2>
        <table class="tabla-pedidos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Productos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while($p = $entregados->fetch_assoc()): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></td>
                    <td><?= htmlspecialchars($p['cliente_nombre']) ?></td>
                    <td><?= htmlspecialchars($p['telefono']) ?></td>
                    <td><?= htmlspecialchars($p['direccion']) ?></td>
                    <td>$<?= number_format($p['total'], 0, ',', '.') ?></td>
                    <td class="estado-entregado"><?= ucfirst($p['estado']) ?></td>
                    <td>
                        <div class="detalles-pedido">
                            <ul>
                            <?php foreach(obtenerDetalles($conn, $p['id']) as $d): ?>
                                <li><?= htmlspecialchars($d['nombre']) ?> x<?= $d['cantidad'] ?> ($<?= number_format($d['precio_unitario'], 0, ',', '.') ?>)</li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    </td>
                    <td>
                        <form method="post" class="acciones-form" onsubmit="return false;">
                            <input type="hidden" name="pedido_id" value="<?= $p['id'] ?>">
                            <button type="button" class="btn-eliminar" onclick="confirmarEliminacion(this.form)" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEntregado(form) {
        Swal.fire({
            title: '¿Marcar como entregado?',
            text: "El pedido pasará a la lista de entregados.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#27ae60',
            cancelButtonColor: '#e67e22',
            confirmButtonText: 'Sí, entregar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear input oculto para submit
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cambiar_estado';
                input.value = 'entregado';
                form.appendChild(input);
                form.submit();
            }
        });
    }
    function confirmarEliminacion(form) {
        Swal.fire({
            title: '¿Eliminar pedido?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#b3d335',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear input oculto para submit
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'eliminar_pedido';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        });
    }
    </script>
</body>
</html>