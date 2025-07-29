<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php");
    exit();
}
include('../../conexion.php');
$categorias = $conn->query("SELECT * FROM catalogo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Categorías</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto&display=swap" rel="stylesheet">
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
            max-width: 900px;
            margin: 40px auto 0 auto;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: #60a5fa;
            margin-bottom: 30px;
            font-size: 2.2rem;
            letter-spacing: 1px;
            text-shadow: 0 4px 24px #2563eb80;
        }
        .contenedor-superior {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
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
        .tabla-categorias {
            width: 100%;
            max-width: 1300px; /* Más ancha */
            margin: 0 auto 18px auto;
            border-collapse: collapse;
            background: rgba(30,41,59,0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px #0f172a80;
            overflow: hidden;
            border: 3px solid #3b82f6; /* Borde azul igual a productos */
        }
        .tabla-categorias th, .tabla-categorias td {
            padding: 14px 10px;
            font-size: 1rem;
            border: 1.5px solid #334155; /* Bordes internos igual a productos */
        }
        .tabla-categorias th {
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
        .tabla-categorias tr:nth-child(even) {
            background: rgba(30,41,59,0.93);
        }
        .tabla-categorias tr:hover {
            background: #334155;
            transition: background 0.2s;
        }
        .tabla-categorias td {
            color: #e2e8f0;
            vertical-align: middle;
        }
        td.acciones, .tabla-categorias td[data-label="Acciones"] {
            white-space: nowrap;
            min-width: 160px;
            display: flex;
            flex-direction: row;
            gap: 10px;
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
            box-shadow: 0 2px 8px #33415540;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }
        .boton-editar {
            background: linear-gradient(90deg, #facc15 60%, #eab308 100%);
            color: #1e293b;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            padding: 10px 16px;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px #33415540;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .boton-editar:hover {
            background: linear-gradient(90deg, #eab308 60%, #facc15 100%);
            transform: scale(1.07);
        }
        .boton-eliminar {
            background: linear-gradient(90deg, #ef4444 60%, #dc2626 100%);
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            padding: 10px 16px;
            font-size: 1.05rem;
            box-shadow: 0 2px 8px #33415540;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
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
            .tabla-categorias th, .tabla-categorias td {
                font-size: 0.95rem;
                padding: 10px 6px;
            }
        }
        @media (max-width: 700px) {
            .tabla-categorias, thead, tbody, th, td, tr {
                display: block;
            }
            .tabla-categorias thead tr {
                display: none;
            }
            .tabla-categorias tr {
                margin-bottom: 18px;
                background: rgba(30,41,59,0.98);
                border-radius: 14px;
                box-shadow: 0 4px 16px #0f172a60;
                padding: 10px 0;
            }
            .tabla-categorias td {
                padding: 10px 12px;
                border: none;
                position: relative;
            }
            .tabla-categorias td:before {
                content: attr(data-label);
                font-weight: bold;
                color: #60a5fa;
                display: block;
                margin-bottom: 4px;
                font-size: 0.97rem;
            }
            td.acciones {
                flex-direction: column;
                gap: 6px;
                align-items: center;
                padding-right: 0;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmarEliminarCategoria(id) {
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
                                    <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">Categoría eliminada correctamente</span>
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
</head>
<body>
    <div class="contenedor-central">
        <h2>Categorías</h2>
        <div class="contenedor-superior">
            <a href="../dashboard.php" class="boton-admin">← Volver al Panel</a>
            <a href="agregar.php" class="boton-admin"><i class="fas fa-plus"></i> Agregar Categoría</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="tabla-categorias">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-font"></i> Nombre</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($c = $categorias->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?= (int)$c['id'] ?></td>
                        <td data-label="Nombre"><?= htmlspecialchars($c['nombre']) ?></td>
                        <td class="acciones" data-label="Acciones">
                            <a href="editar.php?id=<?= $c['id'] ?>" class="boton-editar" title="Editar"><i class="fas fa-pen"></i> Editar</a>
                            <a href="#" class="boton-eliminar" onclick="confirmarEliminarCategoria(<?= $c['id'] ?>)" title="Eliminar"><i class="fas fa-trash"></i> Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
