<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php");
    exit();
}
include('../../conexion.php');

// Cambia el nombre de la variable de conexión si es necesario
$result = $conn->query("SELECT id, nombre, numero_documento, telefono, correo_electronico, direccion, observaciones FROM clientes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Clientes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .contenedor-central {
            max-width: 1100px;
            margin: 40px auto 0 auto;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .boton-admin {
            background: linear-gradient(90deg, #3b82f6 60%, #2563eb 100%);
            color: white;
            padding: 12px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 12px #2563eb40;
            letter-spacing: 0.5px;
        }
        .boton-admin:hover {
            background: linear-gradient(90deg, #2563eb 60%, #3b82f6 100%);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 18px #2563eb60;
        }
        h2 {
            text-align: center;
            color: #60a5fa;
            margin-bottom: 30px;
            font-size: 2.2rem;
            letter-spacing: 1px;
        }
        table {
            width: 100%;
            max-width: 1300px; /* Más ancha */
            margin: 0 auto 18px auto;
            border-collapse: collapse;
            background: rgba(30,41,59,0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px #0f172a80;
            overflow: hidden;
            border: 3px solid #3b82f6; /* Borde azul exterior */
        }
        th, td {
            padding: 14px 10px;
            font-size: 1rem;
            border: 1.5px solid #334155; /* Bordes internos gris oscuro */
        }
        th {
            background: rgba(59,130,246,0.13);
            color: #60a5fa;
            font-family: 'Playfair Display', serif;
            font-size: 1.08rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #3b82f6;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        tr:nth-child(even) { background: rgba(30,41,59,0.93); }
        tr:nth-child(odd) { background: #1e293b; }
        td {
            color: #e2e8f0;
            vertical-align: middle;
        }
        .centrado {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        @media (max-width: 1300px) {
            .contenedor-central {
                max-width: 99vw;
                padding: 0 2vw;
            }
            table {
                min-width: 700px;
                max-width: 99vw;
                font-size: 0.93rem;
            }
        }
        @media (max-width: 900px) {
            table, th, td {
                font-size: 0.92rem;
            }
            th, td {
                padding: 8px 4px;
            }
        }
        @media (max-width: 600px) {
            .contenedor-central {
                padding: 0 2px;
            }
            table {
                min-width: 500px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="contenedor-central">
        <h2>Listado de Clientes</h2>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Número Documento</th>
                    <th>Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th>Dirección</th>
                    <th>Observaciones</th>
                </tr>
                </thead>
                <tbody>
                <?php while($c = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['numero_documento']) ?></td>
                    <td><?= htmlspecialchars($c['telefono']) ?></td>
                    <td><?= htmlspecialchars($c['correo_electronico']) ?></td>
                    <td><?= htmlspecialchars($c['direccion']) ?></td>
                    <td><?= htmlspecialchars($c['observaciones']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="centrado">
            <a href="../dashboard.php" class="boton-admin">← Volver al Panel</a>
        </div>
    </div>
</body>
</html>
