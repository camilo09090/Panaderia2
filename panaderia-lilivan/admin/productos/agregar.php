<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php"); 
    exit();
}
include('../../conexion.php');

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    $precio = intval(str_replace(['.', ','], '', $_POST['precio'])); // <-- CORREGIDO
    $descripcion = $_POST['descripcion'];
    $descripcion_detallada = $_POST['descripcion_detallada'];
    $stock = $_POST['stock'];

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen_url = time() . "_" . basename($_FILES['imagen']['name']);
        $rutaDestino = "../../otros/img/" . $imagen_url;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $stmt = $conn->prepare("INSERT INTO productos (nombre, id_categoria, precio, descripcion, descripcion_detallada, stock, imagen_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sidssis", $nombre, $categoria, $precio, $descripcion, $descripcion_detallada, $stock, $imagen_url);

            if ($stmt->execute()) {
                $mensaje = "Producto agregado con éxito.";
                $tipo = "success";
            } else {
                $mensaje = "Error al agregar producto: " . $conn->error;
                $tipo = "error";
            }
        } else {
            $mensaje = "Error al subir la imagen.";
            $tipo = "error";
        }
    } else {
        $mensaje = "Debe subir una imagen válida.";
        $tipo = "error";
    }
}

$categorias = $conn->query("SELECT * FROM catalogo");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
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

        button[type="button"] {
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

        button[type="button"]:hover {
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
    </style>
</head>
<body>
    <div class="contenedor-form">
        <h2><i class="fas fa-plus"></i> Agregar Nuevo Producto</h2>
        <form method="POST" enctype="multipart/form-data" autocomplete="off">
            <div class="grupo-campos">
                <div class="campo">
                    <label for="nombre"><i class="fas fa-font"></i> Nombre</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                <div class="campo">
                    <label for="categoria"><i class="fas fa-tags"></i> Categoría</label>
                    <select name="categoria" id="categoria">
                        <?php while($categoria = $categorias->fetch_assoc()): ?>
                            <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="grupo-campos">
                <div class="campo">
                    <label for="precio"><i class="fas fa-dollar-sign"></i> Precio (COP)</label>
                    <input type="number" step="1" min="0" name="precio" id="precio" required>
                </div>
                <div class="campo">
                    <label for="stock"><i class="fas fa-boxes"></i> Stock</label>
                    <input type="number" name="stock" id="stock" required>
                </div>
            </div>
            <div class="campo">
                <label for="descripcion"><i class="fas fa-info-circle"></i> Descripción</label>
                <textarea name="descripcion" id="descripcion"></textarea>
            </div>
            <div class="campo">
                <label for="descripcion_detallada"><i class="fas fa-align-left"></i> Descripción Detallada</label>
                <textarea name="descripcion_detallada" id="descripcion_detallada"></textarea>
            </div>
            <div class="campo">
                <label for="imagen"><i class="fas fa-image"></i> Imagen</label>
                <input type="file" name="imagen" id="imagen" accept="image/*" required>
            </div>
            <div class="botones">
                <button type="submit"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" onclick="window.location.href='listar.php'"><i class="fas fa-times"></i> Cancelar</button>
            </div>
        </form>
        <?php if (!empty($mensaje)): ?>
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
                            background: linear-gradient(90deg, <?= $tipo === "success" ? "#2563eb 60%, #1e293b 100%" : "#ef4444 60%, #dc2626 100%" ?>);
                            border-radius: 50%;
                            width: 70px;
                            height: 70px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin-bottom: 14px;
                            box-shadow: 0 4px 18px #2563eb80;
                        ">
                            <i class="fas <?= $tipo === "success" ? "fa-check" : "fa-times" ?>" style="color:#facc15; font-size:2.6rem;"></i>
                        </div>
                        <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">
                            <?= $tipo === "success" ? "Producto agregado con éxito" : "Error al agregar producto" ?>
                        </span>
                        <?php if($tipo !== "success"): ?>
                        <span style="color:#e0e7ef; font-size:1.05rem; margin-top:6px;">
                            <?= $mensaje ?>
                        </span>
                        <?php endif; ?>
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
            }).then((result) => {
                if (result.isConfirmed && '<?= $tipo ?>' === 'success') {
                    window.location.href = "listar.php";
                }
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
    </div>
</body>
</html>
