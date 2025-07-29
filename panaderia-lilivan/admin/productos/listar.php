<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php");
    exit();
}
include('../../conexion.php');

// Agrega el campo descripcion_detallada en la consulta
$productos = $conn->query("SELECT p.id AS id_producto, p.nombre, p.descripcion, p.descripcion_detallada, p.precio, p.stock, p.imagen_url AS imagen, p.id_categoria, c.nombre AS categoria_nombre FROM productos p LEFT JOIN catalogo c ON p.id_categoria = c.id ORDER BY p.stock DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
          font-family: 'Roboto', sans-serif;
          background: #f6f6f6;
          color: #333;
          margin: 0;
          padding: 0;
          min-height: 100vh;
        }
        .contenedor-central {
          max-width: 1300px;
          margin: 40px auto 0 auto;
          padding: 0 10px;
          display: flex;
          flex-direction: column;
          align-items: center;
        }
        h2 {
          text-align: center;
          font-family: 'Playfair Display', serif;
          color: #b3d335;
          margin-bottom: 30px;
          font-size: 2.2rem;
          letter-spacing: 1px;
          text-shadow: 0 4px 24px #b3d33540;
        }
        .contenedor-superior {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
          width: 100%;
        }
        .boton-admin {
          background: #b3d335;
          color: #fff;
          padding: 12px 28px;
          border-radius: 50px;
          text-decoration: none;
          font-size: 1rem;
          font-weight: bold;
          display: inline-flex;
          align-items: center;
          gap: 8px;
          transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
          box-shadow: 0 2px 12px #b3d33533;
          letter-spacing: 0.5px;
          border: none;
        }
        .boton-admin:hover {
          background: #8bbf1f;
          color: #fff;
          transform: translateY(-2px) scale(1.04);
          box-shadow: 0 6px 18px #b3d33560;
        }
        .tabla-productos {
          width: 100%;
          max-width: 900px; /* antes 1300px */
          margin: 0 auto 18px auto;
          border-collapse: collapse;
          background: #fff;
          border-radius: 18px;
          box-shadow: 0 8px 32px #b3d33522;
          overflow: hidden;
          border: 2px solid #b3d335;
        }
        .tabla-productos th, .tabla-productos td {
          padding: 8px 6px; /* antes 14px 10px */
          font-size: 0.95rem; /* antes 1rem */
          border: 1.5px solid #e9ecef;
        }
        .tabla-productos th {
          background: #b3d33522;
          color: #b3d335;
          font-family: 'Playfair Display', serif;
          font-size: 1rem; /* antes 1.08rem */
          letter-spacing: 0.5px;
          border-bottom: 2px solid #b3d335;
          position: sticky;
          top: 0;
          z-index: 2;
        }
        .tabla-productos tr:nth-child(even) {
          background: #f6f6f6;
        }
        .tabla-productos tr:hover {
          background: #e9ecef;
          transition: background 0.2s;
        }
        .tabla-productos td {
          color: #333;
          vertical-align: middle;
        }
        .tabla-productos td.descripcion-detallada, .tabla-productos td[data-label="Detallada"] {
          text-align: justify;
          max-width: 250px; /* antes 500px */
          width: 250px;
          min-width: 120px; /* antes 350px */
          background: #f6f6f6;
          border-radius: 8px;
          padding-right: 4px; /* antes 8px */
          max-height: 80px; /* antes 120px */
          overflow-y: auto;
          white-space: pre-line;
          scrollbar-width: thin;
          scrollbar-color: #b3d335 #e9ecef;
        }
        .tabla-productos img {
          max-width: 40px; /* antes 60px */
          height: auto;
          border-radius: 8px;
          box-shadow: 0 2px 8px #b3d33540;
          border: 2px solid #b3d335;
          background: #fff;
        }
        td.acciones, .tabla-productos td[data-label="Acciones"] {
          white-space: nowrap;
          min-width: 100px; /* antes 160px */
          display: flex;
          flex-direction: row;
          gap: 6px; /* antes 10px */
          justify-content: center;
          align-items: center;
          height: 100%;
        }
        .acciones a, .acciones button {
          text-decoration: none;
          padding: 10px 16px;
          border-radius: 50px;
          font-size: 1.05rem;
          border: none;
          cursor: pointer;
          font-weight: bold;
          transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
          box-shadow: 0 2px 8px #b3d33540;
          display: inline-flex;
          align-items: center;
          gap: 6px;
          justify-content: center;
        }
        .boton-editar {
          background: linear-gradient(90deg, #facc15 60%, #eab308 100%);
          color: #333;
        }
        .boton-editar:hover {
          background: linear-gradient(90deg, #eab308 60%, #facc15 100%);
          transform: scale(1.07);
        }
        .boton-eliminar {
          background: linear-gradient(90deg, #ef4444 60%, #dc2626 100%);
          color: #fff;
        }
        .boton-eliminar:hover {
          background: linear-gradient(90deg, #dc2626 60%, #ef4444 100%);
          transform: scale(1.07);
        }
        @media (max-width: 900px) {
          .contenedor-central {
            max-width: 99vw;
            padding: 0 2vw;
          }
          .tabla-productos th, .tabla-productos td {
            font-size: 0.95rem;
            padding: 10px 6px;
          }
        }
        @media (max-width: 700px) {
          .tabla-productos, thead, tbody, th, td, tr {
            display: block;
          }
          .tabla-productos thead tr {
            display: none;
          }
          .tabla-productos tr {
            margin-bottom: 18px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 16px #b3d33540;
            padding: 10px 0;
          }
          .tabla-productos td {
            padding: 10px 12px;
            border: none;
            position: relative;
          }
          .tabla-productos td:before {
            content: attr(data-label);
            font-weight: bold;
            color: #b3d335;
            display: block;
            margin-bottom: 4px;
            font-size: 0.97rem;
          }
          .tabla-productos img {
            max-width: 90vw;
          }
          td.acciones {
            flex-direction: column;
            gap: 6px;
            align-items: center;
            padding-right: 0;
          }
        }
    </style>
</head>
<body>
    <div class="contenedor-central">
        
        <h2>Productos</h2>
        <div class="contenedor-superior">
            <a href="../dashboard.php" class="boton-admin">← Volver al Panel</a>
            <a href="agregar.php" class="boton-admin"><i class="fas fa-plus"></i> Agregar Producto</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="tabla-productos">
                <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID Producto</th>
                    <th><i class="fas fa-font"></i> Nombre</th>
                    <th><i class="fas fa-info-circle"></i> Descripción</th>
                    <th><i class="fas fa-align-left"></i> Descripción Detallada</th>
                    <th><i class="fas fa-dollar-sign"></i> Precio</th>
                    <th><i class="fas fa-boxes"></i> Stock</th>
                    <th><i class="fas fa-image"></i> Imagen</th>
                    <th><i class="fas fa-tags"></i> ID Categoría</th>
                    <th><i class="fas fa-tag"></i> Categoría</th>
                    <th><i class="fas fa-cogs"></i> Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php while($p = $productos->fetch_assoc()): ?>
                <tr>
                    <td data-label="ID Producto"><?= (int)$p['id_producto'] ?></td>
                    <td data-label="Nombre"><?= htmlspecialchars($p['nombre']) ?></td>
                    <td data-label="Descripción"><?= htmlspecialchars($p['descripcion']) ?></td>
                    <td class="descripcion-detallada" data-label="Detallada"><?= htmlspecialchars($p['descripcion_detallada']) ?></td>
                    <td data-label="Precio">$<?= number_format($p['precio'], 0, ',', '.') ?> COP</td>
                    <td data-label="Stock"><?= (int)$p['stock'] ?></td>
                    <td data-label="Imagen">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="../../otros/img/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                        <?php else: ?>
                            <span>Sin imagen</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="ID Categoría"><?= (int)$p['id_categoria'] ?></td>
                    <td data-label="Categoría"><?= htmlspecialchars($p['categoria_nombre']) ?></td>
                    <td class="acciones" data-label="Acciones">
                        <a href="editar.php?id=<?= $p['id_producto'] ?>" class="boton-editar" title="Editar"><i class="fas fa-pen"></i> Editar</a>
                        <a href="#" onclick="confirmarEliminacion(<?= $p['id_producto'] ?>)" class="boton-eliminar" title="Eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEliminacion(id) {
        Swal.fire({
            title: '',
            html: `
                <div style="
                    font-family:'Roboto',sans-serif;
                    color:#e0e7ef;
                    font-size:1.22rem;
                    font-weight:600;
                    letter-spacing:0.7px;
                    margin-bottom:10px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                ">
                    <div style="
                        background: linear-gradient(90deg, #1e293b 60%, #2563eb 100%);
                        border-radius: 50%;
                        width: 70px;
                        height: 70px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 14px;
                        box-shadow: 0 4px 18px #2563eb80;
                    ">
                        <i class="fas fa-exclamation-triangle" style="color:#facc15; font-size:2.6rem;"></i>
                    </div>
                    <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">¿Estás seguro de eliminar?</span>
                    <span style="color:#e0e7ef; font-size:1.05rem; margin-top:6px;">Esta acción no se puede deshacer.</span>
                </div>
            `,
            background: "linear-gradient(135deg, #0f172a 0%, #1e293b 100%)",
            showCancelButton: true,
            confirmButtonColor: "#2563eb",
            cancelButtonColor: "#334155",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            customClass: {
                popup: 'swal2-lilivan-blue'
            },
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('eliminar.php?id=' + id)
                    .then(response => response.text())
                    .then(() => {
                        Swal.fire({
                            title: '',
                            html: `
                                <div style="
                                    font-family:'Roboto',sans-serif;
                                    color:#e0e7ef;
                                    font-size:1.22rem;
                                    font-weight:600;
                                    letter-spacing:0.7px;
                                    margin-bottom:10px;
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                ">
                                    <div style="
                                        background: linear-gradient(90deg, #2563eb 60%, #1e293b 100%);
                                        border-radius: 50%;
                                        width: 70px;
                                        height: 70px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin-bottom: 14px;
                                        box-shadow: 0 4px 18px #2563eb80;
                                    ">
                                        <i class="fas fa-check" style="color:#facc15; font-size:2.6rem;"></i>
                                    </div>
                                    <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">Producto eliminado correctamente</span>
                                </div>
                            `,
                            background: "linear-gradient(135deg, #0f172a 0%, #1e293b 100%)",
                            showConfirmButton: true,
                            confirmButtonColor: "#2563eb",
                            confirmButtonText: "Aceptar",
                            customClass: {
                                popup: 'swal2-lilivan-blue'
                            },
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            window.location.reload();
                        });
                    });
            }
        });
    }
    </script>
    <style>
    .swal2-lilivan-blue {
        border-radius: 22px !important;
        box-shadow: 0 8px 32px #2563eb80 !important;
        border: 2px solid #2563eb !important;
        padding: 32px 24px !important;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    }
    .swal2-confirm, .swal2-cancel {
        border-radius: 50px !important;
        font-family: 'Roboto', sans-serif !important;
        font-size: 1.13rem !important;
        font-weight: bold !important;
        padding: 14px 38px !important;
        box-shadow: 0 2px 12px #2563eb40 !important;
        letter-spacing: 0.5px !important;
        margin-top: 18px !important;
        border: none !important;
        transition: background 0.3s, transform 0.2s, box-shadow 0.2s !important;
    }
    .swal2-confirm {
        background: linear-gradient(90deg, #2563eb 60%, #1e293b 100%) !important;
        color: #facc15 !important;
    }
    .swal2-confirm:hover {
        background: linear-gradient(90deg, #1e293b 60%, #2563eb 100%) !important;
        color: #fff !important;
        transform: scale(1.04) !important;
        box-shadow: 0 6px 18px #2563eb60 !important;
    }
    .swal2-cancel {
        background: linear-gradient(90deg, #334155 60%, #1e293b 100%) !important;
        color: #e0e7ef !important;
    }
    .swal2-cancel:hover {
        background: linear-gradient(90deg, #1e293b 60%, #334155 100%) !important;
        color: #fff !important;
        transform: scale(1.04) !important;
        box-shadow: 0 6px 18px #33415580 !important;
    }
    </style>
</body>
</html>
