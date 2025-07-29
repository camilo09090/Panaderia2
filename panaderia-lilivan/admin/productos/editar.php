<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php"); 
    exit();
}
include('../../conexion.php');

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto) {
    echo "Producto no encontrado.";
    exit();
}

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = intval(str_replace(['.', ','], '', $_POST['precio'])); // <-- CORREGIDO
    $descripcion = $_POST['descripcion'];
    $descripcion_detallada = $_POST['descripcion_detallada'];
    $stock = $_POST['stock'];
    $imagen_url = $_POST['imagen_url'];

    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . "_" . basename($_FILES['nueva_imagen']['name']);
        $ruta_destino = "../../otros/img/" . $nombre_archivo;

        if (move_uploaded_file($_FILES['nueva_imagen']['tmp_name'], $ruta_destino)) {
            $imagen_url = $nombre_archivo;
        }
    }

    $stmt = $conn->prepare("UPDATE productos SET nombre=?, id_categoria=?, precio=?, descripcion=?, descripcion_detallada=?, stock=?, imagen_url=? WHERE id=?");
    $stmt->bind_param("sidssisi", $nombre, $categoria, $precio, $descripcion, $descripcion_detallada, $stock, $imagen_url, $id);

    if ($stmt->execute()) {
        $mensaje = "Producto actualizado con éxito.";
        $tipo = "success";
    } else {
        $mensaje = "Error al actualizar producto: " . $stmt->error;
        $tipo = "error";
    }
}

$categorias = $conn->query("SELECT * FROM catalogo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #e0e0e0;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .contenedor-form {
            max-width: 600px;
            width: 100%;
            margin: 40px auto 0 auto;
            background: rgba(30,41,59,0.98);
            padding: 38px 32px 28px 32px;
            border-radius: 22px;
            box-shadow: 0 8px 32px #0f172a80;
            border: 2px solid #334155;
            animation: fadeIn 0.7s;
            position: relative;
        }

        h2 {
            text-align: center;
            font-family: 'Playfair Display', serif;
            color: #60a5fa;
            margin-bottom: 32px;
            font-size: 2.1rem;
            letter-spacing: 1px;
            text-shadow: 0 4px 24px #2563eb80;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .grupo-campos {
            display: flex;
            gap: 32px;
        }

        .campo {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 28px;
        }

        label {
            font-weight: bold;
            color: #cbd5e1;
            font-size: 1.05rem;
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            font-size: 1.08rem;
            border: none;
            border-radius: 10px;
            background-color: #334155;
            color: #fff;
            outline: none;
            transition: box-shadow 0.2s;
            box-shadow: 0 2px 8px #33415530 inset;
        }

        input:focus, select:focus, textarea:focus {
            box-shadow: 0 0 0 2px #3b82f6;
            background: #1e293b;
        }

        textarea {
            min-height: 60px;
            resize: vertical;
        }

        .imagen-actual img {
            max-width: 120px;
            border-radius: 8px;
            margin-top: 6px;
            box-shadow: 0 2px 8px #2563eb40;
        }

        .botones {
            display: flex;
            gap: 16px;
            margin-top: 10px;
        }

        button[type="submit"] {
            background: linear-gradient(90deg, #3b82f6 60%, #2563eb 100%);
            color: #fff;
            padding: 12px 0;
            border-radius: 50px;
            border: none;
            font-size: 1.08rem;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            box-shadow: 0 2px 12px #2563eb40;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(90deg, #2563eb 60%, #3b82f6 100%);
            transform: scale(1.04);
            box-shadow: 0 6px 18px #2563eb60;
        }

        .boton-cancelar {
            background: linear-gradient(90deg, #ef4444 60%, #dc2626 100%);
            color: #fff;
            padding: 12px 0;
            border-radius: 50px;
            border: none;
            font-size: 1.08rem;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            text-align: center;
            text-decoration: none;
            box-shadow: 0 2px 12px #dc262640;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .boton-cancelar:hover {
            background: linear-gradient(90deg, #dc2626 60%, #ef4444 100%);
            transform: scale(1.04);
            box-shadow: 0 6px 18px #dc262680;
        }

        @media (max-width: 700px) {
            .contenedor-form {
                padding: 18px 6px 18px 6px;
                max-width: 98vw;
            }

            h2 {
                font-size: 1.3rem;
            }

            .grupo-campos {
                flex-direction: column;
                gap: 0;
            }

            .campo {
                margin-bottom: 22px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
        }

        .swal2-lilivan {
            border-radius: 22px !important;
            box-shadow: 0 8px 32px #2563eb40 !important;
            border: 2px solid #3b82f6 !important;
            padding: 32px 24px !important;
        }
        .swal2-confirm {
            border-radius: 50px !important;
            font-family: 'Roboto', sans-serif !important;
            font-size: 1.13rem !important;
            font-weight: bold !important;
            padding: 14px 38px !important;
            background: linear-gradient(90deg, #3b82f6 60%, #2563eb 100%) !important;
            color: #fff !important;
            box-shadow: 0 2px 12px #2563eb40 !important;
            letter-spacing: 0.5px !important;
            transition: background 0.3s, transform 0.2s, box-shadow 0.2s !important;
        }
        .swal2-confirm:hover {
            background: linear-gradient(90deg, #2563eb 60%, #3b82f6 100%) !important;
            transform: scale(1.04) !important;
            box-shadow: 0 6px 18px #2563eb60 !important;
        }
    </style>
</head>
<body>
    <div class="contenedor-form">
        <h2><i class="fas fa-pen-to-square"></i> Editar Producto</h2>
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="imagen_url" value="<?= htmlspecialchars($producto['imagen_url']) ?>">

            <div class="grupo-campos">
                <div class="campo">
                    <label for="nombre"><i class="fas fa-font"></i> Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
                </div>
                <div class="campo">
                    <label for="categoria"><i class="fas fa-tags"></i> Categoría</label>
                    <select name="categoria" id="categoria">
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>" <?= $producto['id_categoria'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="grupo-campos">
                <div class="campo">
                    <label for="precio"><i class="fas fa-dollar-sign"></i> Precio (COP)</label>
                    <input type="number" name="precio" id="precio" min="0" step="1" value="<?= (int)$producto['precio'] ?>" required>
                </div>
                <div class="campo">
                    <label for="stock"><i class="fas fa-boxes"></i> Stock</label>
                    <input type="number" name="stock" id="stock" value="<?= $producto['stock'] ?>" required>
                </div>
            </div>
            <div class="campo">
                <label for="descripcion"><i class="fas fa-info-circle"></i> Descripción</label>
                <textarea name="descripcion" id="descripcion"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>
            <div class="campo">
                <label for="descripcion_detallada"><i class="fas fa-align-left"></i> Descripción Detallada</label>
                <textarea name="descripcion_detallada" id="descripcion_detallada"><?= htmlspecialchars($producto['descripcion_detallada']) ?></textarea>
            </div>
            <div class="campo">
                <label for="nueva_imagen"><i class="fas fa-image"></i> Nueva imagen (opcional)</label>
                <input type="file" name="nueva_imagen" id="nueva_imagen" accept="image/*">
                <?php if (!empty($producto['imagen_url'])): ?>
                    <div class="imagen-actual">
                        <strong>Imagen actual:</strong><br>
                        <img src="../../otros/img/<?= htmlspecialchars($producto['imagen_url']) ?>" alt="Imagen actual">
                    </div>
                <?php endif; ?>
            </div>
            <div class="botones">
                <button type="submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="listar.php" class="boton-cancelar"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>

    <?php if (!empty($mensaje) && $tipo === "success"): ?>
    <script>
        Swal.fire({
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
                    <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">Producto actualizado</span>
                </div>
            `,
            background: "linear-gradient(135deg, #0f172a 0%, #1e293b 100%)",
            showConfirmButton: true,
            confirmButtonColor: "#2563eb",
            confirmButtonText: "Volver al listado",
            customClass: {
                popup: 'swal2-lilivan-blue'
            },
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            window.location.href = "listar.php";
        });
    </script>
    <style>
    .swal2-lilivan-blue {
        border-radius: 22px !important;
        box-shadow: 0 8px 32px #2563eb80 !important;
        border: 2px solid #2563eb !important;
        padding: 32px 24px !important;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
    }
    .swal2-confirm {
        border-radius: 50px !important;
        font-family: 'Roboto', sans-serif !important;
        font-size: 1.13rem !important;
        font-weight: bold !important;
        padding: 14px 38px !important;
        background: linear-gradient(90deg, #2563eb 60%, #1e293b 100%) !important;
        color: #facc15 !important;
        box-shadow: 0 2px 12px #2563eb40 !important;
        letter-spacing: 0.5px !important;
        margin-top: 18px !important;
        border: none !important;
        transition: background 0.3s, transform 0.2s, box-shadow 0.2s !important;
    }
    .swal2-confirm:hover {
        background: linear-gradient(90deg, #1e293b 60%, #2563eb 100%) !important;
        color: #fff !important;
        transform: scale(1.04) !important;
        box-shadow: 0 6px 18px #2563eb60 !important;
    }
    </style>
    <?php endif; ?>
</body>
</html>
