<?php
session_start();
include 'conexion.php';

// Verifica que el usuario esté logueado y tenga productos en el carrito
if (!isset($_SESSION['cliente_id'])) {
    header("Location: carrito.php");
    exit;
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener datos del cliente
$stmt = $conn->prepare("SELECT nombre, numero_documento, telefono, correo_electronico, direccion FROM clientes WHERE id = ?");
if (!$stmt) {
    die("Error en la consulta de cliente: " . $conn->error);
}
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$stmt->bind_result($nombre, $numero_documento, $telefono, $correo_electronico, $direccion);
$stmt->fetch();
$stmt->close();

// Obtener productos del carrito
$sql = "SELECT c.id, c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen_url
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.cliente_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$result = $stmt->get_result();
$productos = [];
$total = 0;
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
    $total += $row['precio'] * $row['cantidad'];
}
$stmt->close();

$envio = ($total >= 10000) ? 3500 : 0;
$total_final = $total + $envio;

// Actualizar datos de facturación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar'])) {
    $nombre = $_POST['nombre'];
    $numero_documento = $_POST['numero_documento'];
    $telefono = $_POST['telefono'];
    $correo_electronico = $_POST['correo_electronico'];
    $direccion = $_POST['direccion'];
    $stmt = $conn->prepare("UPDATE clientes SET nombre=?, numero_documento=?, telefono=?, correo_electronico=?, direccion=? WHERE id=?");
    $stmt->bind_param("sssssi", $nombre, $numero_documento, $telefono, $correo_electronico, $direccion, $cliente_id);
    $stmt->execute();
    $stmt->close();
    header("Location: finlizarcompra.php");
    exit;
}

// PROCESAR PEDIDO
$pedido_exito = false;
$mensaje_confirmacion = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_pedido'])) {
    if (empty($productos)) {
        $mensaje_confirmacion = "No tienes productos en el carrito.";
    } else {
        // 1. Insertar en tabla pedidos
        $fecha = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha, total) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $cliente_id, $fecha, $total_final);
        $stmt->execute();
        $pedido_id = $stmt->insert_id;
        $stmt->close();

        // 2. Insertar en pedido_detalles
        $stmt = $conn->prepare("INSERT INTO pedido_detalles (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        foreach ($productos as $prod) {
            $stmt->bind_param("iiid", $pedido_id, $prod['producto_id'], $prod['cantidad'], $prod['precio']);
            $stmt->execute();
        }
        $stmt->close();

        // 3. Vaciar carrito
        $conn->query("DELETE FROM carrito WHERE cliente_id = $cliente_id");

        // 4. Mostrar mensaje de éxito
        $pedido_exito = true;
        $mensaje_confirmacion = "¡Tu pedido ha sido registrado exitosamente!<br>En breve nos comunicaremos contigo para confirmar la entrega.<br><br><b>Número de pedido:</b> #$pedido_id";

        // Construir mensaje
        $mensaje = "🟢 NUEVO PEDIDO PANADERÍA LILIVAN 🟢%0A";
        $mensaje .= "Cliente: $nombre%0A";
        $mensaje .= "Documento: $numero_documento%0A";
        $mensaje .= "Teléfono: $telefono%0A";
        $mensaje .= "Correo: $correo_electronico%0A";
        $mensaje .= "Dirección: $direccion%0A";
        $mensaje .= "--------------------%0A";
        $mensaje .= "Pedido #%23$pedido_id%0A";
        foreach ($productos as $prod) {
            $mensaje .= "- " . $prod['nombre'] . " x" . $prod['cantidad'] . " ($" . number_format($prod['precio'] * $prod['cantidad'], 0, ',', '.') . ")%0A";
        }
        $mensaje .= "Envío: " . ($envio > 0 ? '$3.500' : 'No aplica') . "%0A";
        $mensaje .= "Total: $" . number_format($total_final, 0, ',', '.') . "%0A";
        $mensaje .= "--------------------%0A";
        $mensaje .= "¡Revisar el panel de administración!";

        // Tu número y API Key de CallMeBot
        $telefono_admin = "573243746504"; // tu número autorizado
        $apikey = "1132954"; // tu API Key real

        // Enviar mensaje por WhatsApp usando CallMeBot
        $mensaje = urlencode($mensaje);
        $url = "https://api.callmebot.com/whatsapp.php?phone=$telefono_admin&text=$mensaje&apikey=$apikey";
        file_get_contents($url);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar compra | Panadería Lilivan</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --verde: #b3d335;
            --gris: #f6f6f6;
            --gris-claro: #e9ecef;
            --texto: #333;
            --texto-sec: #7a7a7a;
        }
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--gris);
            color: var(--texto);
            margin: 0;
            padding: 0;
        }
        header {
            background: #fff;
            box-shadow: 0 2px 12px #0001;
            border-bottom: 1.5px solid var(--gris-claro);
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 32px;
        }
        .logo {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .logo img {
            height: 54px;
            margin-bottom: 2px;
        }
        .logo span {
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            color: var(--texto-sec);
            letter-spacing: 2px;
        }
        nav {
            display: flex;
            gap: 32px;
        }
        .nav-link {
            color: var(--texto);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.08em;
            padding: 6px 0;
            border-bottom: 2px solid transparent;
            transition: color 0.2s, border-color 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--verde);
            border-bottom: 2px solid var(--verde);
        }
        .btn-cuenta {
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: bold;
            font-size: 1em;
            margin-left: 24px;
            cursor: pointer;
            box-shadow: 0 2px 8px #b3d33533;
            transition: background 0.2s;
        }
        .btn-cuenta:hover {
            background: #8bbf1f;
        }
        .checkout-main {
            max-width: 1200px;
            margin: 40px auto;
            display: flex;
            gap: 40px;
            align-items: flex-start;
            background: transparent;
        }
        .facturacion-col {
            flex: 2;
            background: #fff;
            border-radius: 16px;
            padding: 30px 25px;
            box-shadow: 0 2px 12px #b3d33522;
            border: 1.5px solid #e9ecef;
        }
        .facturacion-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 18px;
            color: #b3d335;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .facturacion-form label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #555;
        }
        .facturacion-form input[type="text"],
        .facturacion-form input[type="email"] {
            width: 100%;
            padding: 9px 10px;
            margin-bottom: 14px;
            border: 1.5px solid var(--gris-claro);
            border-radius: 7px;
            font-size: 1em;
            background: #f6f6f6;
        }
        .facturacion-form .form-row {
            display: flex;
            gap: 18px;
        }
        .facturacion-form .form-row > div {
            flex: 1;
        }
        .facturacion-form button {
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 0;
            font-size: 1.1em;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .facturacion-form button:hover {
            background: #8bbf1f;
        }
        .pedido-col {
            flex: 1.2;
            background: #fff;
            border-radius: 16px;
            padding: 28px 22px;
            box-shadow: 0 2px 12px #b3d33522;
            min-width: 320px;
            max-width: 370px;
            border: 1.5px solid #e9ecef;
        }
        .pedido-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 18px;
            color: #b3d335;
            font-family: 'Montserrat', sans-serif;
            letter-spacing: 0.5px;
        }
        .pedido-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .pedido-table th, .pedido-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #e9ecef;
            font-size: 1em;
        }
        .pedido-table th {
            background: #f6f6f6;
            color: #b3d335;
            font-weight: 700;
        }
        .pedido-table tr:last-child td {
            border-bottom: none;
        }
        .pedido-total {
            font-size: 1.15rem;
            font-weight: bold;
            color: #b3d335;
            margin-top: 10px;
            text-align: right;
        }
        .pedido-envio {
            font-size: 1em;
            color: #7a7a7a;
            margin-bottom: 8px;
            text-align: right;
        }
        .pedido-btn {
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 14px 0;
            font-size: 1.1em;
            font-weight: bold;
            width: 100%;
            margin-top: 18px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .pedido-btn:hover {
            background: #8bbf1f;
        }
        .pedido-exito {
            background: #f6f6f6;
            border: 2px solid #b3d335;
            border-radius: 14px;
            padding: 40px 30px;
            margin: 40px auto;
            max-width: 500px;
            text-align: center;
            color: #333;
            font-size: 1.15em;
        }
        .pedido-exito .icono {
            font-size: 3em;
            color: #b3d335;
            margin-bottom: 18px;
        }
        @media (max-width: 900px) {
            .checkout-main {
                flex-direction: column;
                gap: 24px;
                padding: 0 10px;
            }
        }
        @media (max-width: 600px) {
            .checkout-main {
                flex-direction: column;
                gap: 18px;
                padding: 0 4px;
            }
            .pedido-col {
                min-width: unset;
                max-width: unset;
            }
        }
        /* Footer igual que en carrito/productos */
        footer {
            background: #fff;
            border-top: 1.5px solid var(--gris-claro);
            margin-top: 60px;
            padding: 40px 0 0 0;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.2fr;
            gap: 40px;
            padding: 0 32px;
        }
        .footer-logo img {
            height: 48px;
        }
        .footer-logo span {
            font-family: 'Playfair Display', serif;
            font-size: 13px;
            color: var(--texto-sec);
            letter-spacing: 2px;
        }
        .footer-logo p {
            margin: 18px 0 10px 0;
            color: var(--texto-sec);
            font-size: 1.05em;
        }
        .footer-products {
            display: flex;
            gap: 10px;
            margin: 12px 0 18px 0;
        }
        .footer-product {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid var(--verde);
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .footer-product img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .footer-social {
            margin-top: 10px;
        }
        .footer-social a {
            color: var(--texto-sec);
            margin-right: 14px;
            font-size: 1.3em;
            transition: color 0.2s;
        }
        .footer-social a:hover {
            color: var(--verde);
        }
        .footer-title {
            font-weight: bold;
            font-size: 1.08em;
            margin-bottom: 12px;
            font-family: 'Roboto', sans-serif;
        }
        .footer-list, .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
            color: var(--texto-sec);
            font-size: 1em;
        }
        .footer-list li, .footer-contact li {
            margin-bottom: 10px;
        }
        .footer-list a {
            color: var(--texto-sec);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-list a:hover {
            color: var(--verde);
        }
        .footer-contact i {
            color: var(--verde);
            margin-right: 8px;
        }
        .footer-btn {
            background: var(--verde);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
            font-weight: bold;
            font-size: 1em;
            margin-top: 12px;
            cursor: pointer;
            box-shadow: 0 2px 8px #b3d33533;
            transition: background 0.2s;
            display: inline-block;
        }
        .footer-btn:hover {
            background: #8bbf1f;
        }
        .footer-copy {
            text-align: center;
            color: var(--texto-sec);
            font-size: 0.98em;
            margin: 32px 0 18px 0;
        }
         .btn-nav-carrito {
      color: var(--verde);
      background: transparent;
      padding: 7px 16px;
      border-radius: 50px;
      text-decoration: none;
      font-weight: bold;
      font-size: 0.98rem;
      letter-spacing: 0.5px;
      border: 2px solid var(--verde);
      transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.2s;
      box-shadow: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      outline: none;
      margin-left: 0;
    }
    .btn-nav-carrito.active,
    .btn-nav-carrito:hover {
      background: var(--verde);
      color: #fff;
      border-color: var(--verde-oscuro);
      transform: translateY(-2px) scale(1.04);
      box-shadow: 0 2px 8px #b3d33540;
      text-decoration: none;
    }
    </style>
</head>
<body>
<header>
    <div class="nav-container">
        <div class="logo">
            <img src="./otros/img/logo.png" alt="Panadería Lilivan Logo">
            <span>DESDE 2001</span>
        </div>
        <nav>
            <a href="index.php" class="nav-link">Inicio</a>
            <a href="productos.php" class="nav-link">Productos</a>
            <button id="carritoNavBtn" class="btn-nav-carrito" onclick="window.location.href='carrito.php'">
        <i class="fas fa-shopping-basket"></i>
        Carrito
        <span id="carritoContador" style="background:#b3d335;color:#fff;border-radius:50%;padding:2px 8px;font-size:0.95em;margin-left:6px;display:none;">0</span>
      </button>
        </nav>
        <?php if (isset($_SESSION['cliente_nombre'])): ?>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-weight:bold;color:var(--verde);">
                    <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['cliente_nombre']) ?>
                </span>
                <form action="" method="post" style="display:inline;">
                    <button type="submit" name="cerrar_sesion" class="btn-cuenta" style="background:#e74c3c;">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        <?php else: ?>
            <a href="./admin/login_form.php" class="btn-cuenta"><i class="fas fa-user"></i> Mi cuenta</a>
        <?php endif; ?>
    </div>
</header>

<?php if ($pedido_exito): ?>
    <div class="pedido-exito">
        <div class="icono"><i class="fas fa-check-circle"></i></div>
        <?= $mensaje_confirmacion ?>
        <br><br>
        <a href="index.php" style="color:#fff;background:#b3d335;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">Volver al inicio</a>
    </div>
<?php else: ?>
<div class="checkout-main">
    <!-- Columna de facturación -->
    <form class="facturacion-col facturacion-form" method="post" autocomplete="off">
        <div class="facturacion-title"><i class="fas fa-file-invoice"></i> Detalles de facturación</div>
        <div class="form-row">
            <div>
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
            </div>
            <div>
                <label for="numero_documento">Cédula/NIT</label>
                <input type="text" name="numero_documento" id="numero_documento" value="<?= htmlspecialchars($numero_documento) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" value="<?= htmlspecialchars($telefono) ?>" required>
            </div>
            <div>
                <label for="correo_electronico">Correo electrónico</label>
                <input type="email" name="correo_electronico" id="correo_electronico" value="<?= htmlspecialchars($correo_electronico) ?>" required>
            </div>
        </div>
        <label for="direccion">Dirección</label>
        <input type="text" name="direccion" id="direccion" value="<?= htmlspecialchars($direccion) ?>" required>
        <button type="submit" name="actualizar"><i class="fas fa-save"></i> Guardar y continuar</button>
    </form>

    <!-- Columna de resumen del pedido -->
    <div class="pedido-col">
        <div class="pedido-title"><i class="fas fa-shopping-basket"></i> Tu pedido</div>
        <table class="pedido-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $prod): ?>
                <tr>
                    <td><?= htmlspecialchars($prod['nombre']) ?></td>
                    <td><?= $prod['cantidad'] ?></td>
                    <td>$<?= number_format($prod['precio'] * $prod['cantidad'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pedido-envio">
            Envío: <?= $envio > 0 ? '$3.500 (Domicilio 7:00 A.M. a 7:00 P.M.)' : 'No aplica' ?>
        </div>
        <div class="pedido-total">
            Total: $<?= number_format($total_final, 0, ',', '.') ?>
        </div>
        <form method="post">
            <button type="submit" name="realizar_pedido" class="pedido-btn"><i class="fas fa-check"></i> Realizar pedido</button>
        </form>
        <?php if ($mensaje_confirmacion && !$pedido_exito): ?>
            <div style="color:#e74c3c;margin-top:10px;"><?= $mensaje_confirmacion ?></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<footer>
    <div class="footer-container">
        <div class="footer-logo">
            <img src="./otros/img/logo-lilivan.png" alt="Panadería Lilivan Logo">
            <span>DESDE 2001</span>
            <p>La mejor y más tradicional panadería del Huila, con el mismo sabor de siempre desde 2001.</p>
            <div class="footer-products">
                <div class="footer-product"><img src="./otros/img/panaderia 1.webp" alt="Pan"></div>
                <div class="footer-product"><img src="./otros/img/panaderia2.jpg" alt="Pasteles"></div>
                <div class="footer-product"><img src="./otros/img/panaderia3.jpeg" alt="Postres"></div>
                <div class="footer-product"><img src="./otros/img/panaderia4.webp" alt="Tortas"></div>
            </div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div>
            <div class="footer-title">Información</div>
            <ul class="footer-list">
                <li><a href="#">Promociones del día</a></li>
                <li><a href="#">Pedidos especiales</a></li>
            </ul>
        </div>
        <div>
            <div class="footer-title">Contacto</div>
            <ul class="footer-contact">
                <li><i class="fas fa-map-marker-alt"></i> Calle 37#6-42, Granjas (Neiva-Huila)</li>
                <li><i class="fas fa-phone"></i> 3163007815</li>
                <li><i class="fas fa-envelope"></i> panaderialilivan@gmail.com</li>
            </ul>
            <button class="footer-btn"><i class="fas fa-phone"></i> Pedidos: 3163007815</button>
        </div>
        <div>
            <div class="footer-title">Enlaces rápidos</div>
            <ul class="footer-list">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="productos.php">Productos</a></li>
                <li><a href="ini_sesi_form.php">Iniciar sesión</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-copy">
        Panadería Lilivan &copy; 2025 Todos los derechos reservados. Diseñado por <span style="color:var(--verde);font-weight:bold;">Janus</span>.
    </div>
</footer>
</body>
</html>