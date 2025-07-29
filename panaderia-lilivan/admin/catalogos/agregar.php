<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: ../login_form.php"); 
    exit();
}
include('../../conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $stmt = $conn->prepare("INSERT INTO catalogo (nombre) VALUES (?)");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();

    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Guardando...</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        }
        .swal2-lilivan-blue {
            border-radius: 22px !important;
            box-shadow: 0 8px 32px #2563eb80 !important;
            border: 2px solid #2563eb !important;
            padding: 32px 24px !important;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
        }
        .swal2-confirm {
            border-radius: 50px !important;
            font-family: "Roboto", sans-serif !important;
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
    </head>
    <body>
        <script>
            Swal.fire({
                html: `
                    <div style="
                        font-family:\'Roboto\',sans-serif;
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
                        <span style="color:#60a5fa; font-size:1.13rem; font-weight:bold;">¡Categoría agregada!</span>
                        <span style="color:#e0e7ef; font-size:1.05rem; margin-top:6px;">La categoría se ha guardado correctamente.</span>
                    </div>
                `,
                background: "linear-gradient(135deg, #0f172a 0%, #1e293b 100%)",
                showConfirmButton: true,
                confirmButtonColor: "#2563eb",
                confirmButtonText: "Aceptar",
                customClass: {
                    popup: "swal2-lilivan-blue"
                },
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then(() => {
                window.location.href = "listar.php";
            });
        </script>
    </body>
    </html>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Categoría</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto&display=swap" rel="stylesheet">
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

        h2 {
            font-family: 'Playfair Display', serif;
            color: #60a5fa;
            margin-bottom: 32px;
            font-size: 2.1rem;
            letter-spacing: 1px;
            text-shadow: 0 4px 24px #2563eb80;
        }

        form {
            width: 100%;
            max-width: 410px;
            background: rgba(30,41,59,0.98);
            padding: 38px 32px 28px 32px;
            border-radius: 22px;
            box-shadow: 0 8px 32px #0f172a80;
            display: flex;
            flex-direction: column;
            gap: 18px;
            border: 1.5px solid #334155;
            animation: fadeIn 0.7s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
        }

        label {
            font-weight: bold;
            color: #cbd5e1;
            margin-bottom: 8px;
            font-size: 1.05rem;
            letter-spacing: 0.2px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            font-size: 1.08rem;
            border: none;
            border-radius: 10px;
            background-color: #334155;
            color: #fff;
            margin-bottom: 8px;
            outline: none;
            transition: box-shadow 0.2s;
            box-shadow: 0 2px 8px #33415530 inset;
        }

        input[type="text"]:focus {
            box-shadow: 0 0 0 2px #3b82f6;
            background: #1e293b;
        }

        .botones {
            display: flex;
            gap: 16px;
            margin-top: 10px;
        }

        .boton-guardar {
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
        }

        .boton-guardar:hover {
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
        }

        .boton-cancelar:hover {
            background: linear-gradient(90deg, #dc2626 60%, #ef4444 100%);
            transform: scale(1.04);
            box-shadow: 0 6px 18px #dc262680;
        }

        @media (max-width: 500px) {
            form {
                padding: 18px 6px 18px 6px;
                max-width: 98vw;
            }

            h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <h2>➕ Agregar Nueva Categoría</h2>
    <form method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required autocomplete="off">

        <div class="botones">
            <button type="submit" class="boton-guardar">Guardar</button>
            <a href="listar.php" class="boton-cancelar">Cancelar</a>
        </div>
    </form>
</body>
</html>
