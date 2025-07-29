<?php
session_start();
include 'conexion.php';

// Obtener productos del carrito según el usuario
$productos = [];
$total = 0;

if (isset($_SESSION['cliente_id'])) {
    $cliente_id = $_SESSION['cliente_id'];
    $sql = "SELECT c.id, c.producto_id, c.cantidad, p.nombre, p.precio, p.imagen_url, p.descripcion
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
        // Asegura la ruta completa de la imagen
        $imagen_url = $row['imagen_url'];
        if (strpos($imagen_url, 'otros/img/') !== 0) {
            $imagen_url = 'otros/img/' . $imagen_url;
        }
        $productos[] = [
            'id' => $row['id'],
            'producto_id' => $row['producto_id'],
            'nombre' => $row['nombre'],
            'precio' => $row['precio'],
            'imagen_url' => $imagen_url,
            'descripcion' => $row['descripcion'],
            'cantidad' => $row['cantidad']
        ];
        $total += $row['precio'] * $row['cantidad'];
    }
} else {
    foreach ($_SESSION['carrito'] ?? [] as $item) {
        $productos[] = $item;
        $total += $item['precio'] * $item['cantidad'];
    }
}

// Construir el mensaje de WhatsApp
$mensaje = "Hola, quiero confirmar mi compra de los siguientes productos:%0A";
foreach ($productos as $prod) {
    $mensaje .= "- " . $prod['cantidad'] . " x " . $prod['nombre'] . "%0A";
}
$mensaje .= "%0AEl total a pagar sería de *$" . number_format($total, 0, ',', '.') . "* pesos.";
$mensaje = urlencode($mensaje);

// Actualizar cantidad o eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['carrito_id'])) {
    $accion = $_POST['accion'];
    $carrito_id = $_POST['carrito_id'];

    if (isset($_SESSION['cliente_id'])) {
        // Usuario logueado: modificar en base de datos
        if ($accion === 'sumar') {
            $conn->query("UPDATE carrito SET cantidad = cantidad + 1 WHERE id = $carrito_id AND cliente_id = $cliente_id");
        } elseif ($accion === 'restar') {
            $conn->query("UPDATE carrito SET cantidad = GREATEST(cantidad - 1, 1) WHERE id = $carrito_id AND cliente_id = $cliente_id");
        } elseif ($accion === 'eliminar') {
            $conn->query("DELETE FROM carrito WHERE id = $carrito_id AND cliente_id = $cliente_id");
        }
    } else {
        // Invitado: modificar en sesión
        $carrito = $_SESSION['carrito'] ?? [];
        foreach ($carrito as $i => $item) {
            if ($item['id'] == $carrito_id) {
                if ($accion === 'sumar') {
                    $carrito[$i]['cantidad'] += 1;
                } elseif ($accion === 'restar') {
                    $carrito[$i]['cantidad'] = max(1, $carrito[$i]['cantidad'] - 1);
                } elseif ($accion === 'eliminar') {
                    array_splice($carrito, $i, 1);
                }
                break;
            }
        }
        $_SESSION['carrito'] = $carrito;
    }
    header("Location: carrito.php");
    exit;
}

// Cerrar sesión
if (isset($_POST['cerrar_sesion'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras | Lilivan</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
      background-color: var(--gris);
      color: var(--texto);
      margin: 0;
      padding: 0;
    }
    .main-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 30px;
      max-width: 1200px;
      margin: 40px auto;
    }
    .carrito-col {
      flex: 2;
      background: #fff;
      border-radius: 16px;
      padding: 30px 25px;
      box-shadow: 0 2px 12px #b3d33522;
      border: 1.5px solid #e9ecef;
    }
    .carrito-header {
      font-size: 1.5rem;
      font-weight: bold;
      margin-bottom: 18px;
      color: #b3d335;
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .btn-volver-productos {
      background: #b3d335;
      color: #fff;
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      cursor: pointer;
      box-shadow: 0 2px 8px #b3d33533;
      transition: transform 0.2s, background 0.2s;
      margin-right: 8px;
    }
    .btn-volver-productos:hover {
      background: #8bbf1f;
      color: #fff;
      transform: scale(1.12) rotate(-8deg);
    }
    .tabla-carrito {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 2px 12px #b3d33522;
      overflow: hidden;
      margin-bottom: 24px;
    }
    .tabla-carrito th, .tabla-carrito td {
      padding: 16px 10px;
      text-align: left;
      font-family: 'Montserrat', sans-serif;
      font-size: 1rem;
      border-bottom: 1px solid #e9ecef;
      vertical-align: middle;
    }
    .tabla-carrito th {
      background: #f6f6f6;
      color: #b3d335;
      font-weight: 700;
      font-size: 1.08rem;
      border-bottom: 2px solid #e9ecef;
    }
    .tabla-carrito tr:last-child td {
      border-bottom: none;
    }
    .img-carrito {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #b3d335;
      background: #f6f6f6;
    }
    .nombre-carrito {
      font-weight: 700;
      color: #333;
      font-size: 1.05rem;
      margin-bottom: 2px;
    }
    .desc-carrito {
      color: #7a7a7a;
      font-size: 0.97rem;
    }
    .btn-cantidad {
      background: #fff;
      border: 2px solid #b3d335;
      color: #b3d335;
      font-size: 1.1rem;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: bold;
      margin: 0 2px;
      transition: background 0.2s, color 0.2s, border 0.2s, transform 0.15s;
    }
    .btn-cantidad:hover {
      background: #b3d335;
      color: #fff;
      border: 2px solid #8bbf1f;
      transform: scale(1.12);
    }
    .cantidad-carrito {
      font-weight: bold;
      color: #b3d335;
      margin: 0 8px;
      font-size: 1.08rem;
    }
    .btn-eliminar {
      background: #fff;
      border: none;
      color: #b3d335;
      font-size: 1.3rem;
      cursor: pointer;
      transition: color 0.2s, transform 0.15s;
    }
    .btn-eliminar:hover {
      color: #e74c3c;
      transform: scale(1.1);
    }
    .resumen-col {
      flex: 1;
      background: #fff;
      border-radius: 16px;
      padding: 28px 22px;
      box-shadow: 0 2px 12px #b3d33522;
      min-width: 320px;
      max-width: 370px;
      border: 1.5px solid #e9ecef;
    }
    .resumen-title {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 18px;
      color: #b3d335;
      font-family: 'Montserrat', sans-serif;
      letter-spacing: 0.5px;
    }
    .resumen-line {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 1.08rem;
      color: #333;
      font-family: 'Montserrat', sans-serif;
    }
    .resumen-total {
      font-size: 1.35rem;
      font-weight: 700;
      color: #b3d335;
      margin-bottom: 14px;
      font-family: 'Montserrat', sans-serif;
      border-top: 1px solid #e9ecef;
      padding-top: 10px;
    }
    .resumen-box {
      background: #f6f6f6;
      border-radius: 7px;
      padding: 12px 14px;
      margin-bottom: 14px;
      font-size: 1rem;
      color: #7a7a7a;
      border: 1px solid #e9ecef;
      font-family: 'Montserrat', sans-serif;
    }
    .resumen-box strong {
      color: #b3d335;
      font-weight: 700;
    }
    .resumen-btn {
      display: block;
      width: 100%;
      padding: 18px 0;
      background: #b3d335;
      color: #fff;
      font-size: 1.25rem;
      font-weight: 700;
      border: none;
      border-radius: 8px;
      margin: 32px 0 0 0;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      text-align: center;
      text-decoration: none;
      box-shadow: 0 2px 8px #b3d33533;
      font-family: 'Montserrat', sans-serif;
    }
    .resumen-btn:hover {
      background: #8bbf1f;
      color: #fff;
      transform: scale(1.04);
    }
    /* Barra superior redes */
    
    /* Header principal */
    header {
      background: #fff;
      box-shadow: 0 2px 12px #0001;
      padding: 0 0 0 0;
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
    @media (max-width: 900px) {
      .nav-container, .footer-container {
          flex-direction: column;
          padding: 18px 10px;
      }
    }
    @media (max-width: 600px) {
      .nav-container {
          flex-direction: column;
          gap: 12px;
          padding: 12px 6px;
      }
      nav {
          gap: 18px;
      }
    }
    /* Footer */
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
        /* Responsive */
        @media (max-width: 900px) {
            .nav-container, .footer-container {
                flex-direction: column;
                padding: 18px 10px;
            }
            .footer-container {
                grid-template-columns: 1fr 1fr;
                gap: 24px;
            }
        }
        @media (max-width: 600px) {
            .nav-container {
                flex-direction: column;
                gap: 12px;
                padding: 12px 6px;
            }
            nav {
                gap: 18px;
            }
            .footer-container {
                grid-template-columns: 1fr;
                gap: 18px;
                padding: 0 10px;
            }
            .footer-logo img {
                height: 38px;
            }
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
            <img src="./otros/img/logo.png" alt="Lilivan Logo">
            <span>DESDE 2001</span>
        </div>
        <nav>
            <a href="index.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : '' ?>">Inicio</a>
            <a href="productos.php" class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'productos.php' ? ' active' : '' ?>">Productos</a>
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

<div class="main-container">
    <!-- Columna izquierda: Productos en tabla -->
    <div class="carrito-col">
        <div class="carrito-header">
            <a href="productos.php" class="btn-volver-productos" title="Volver a productos">
                <i class="fas fa-arrow-left"></i>
            </a>
            Carro de compras <span style="color:#bfcfff;font-size:1rem;">(<?= count($productos) ?>)</span>
        </div>
        <?php if (empty($productos)): ?>
            <p style="color:#bfcfff;">No tienes productos en el carrito.</p>
        <?php else: ?>
        <table class="tabla-carrito">
            <thead>
                <tr>
                    <th></th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $prod): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($prod['imagen_url']) ?>" alt="Producto"
                             class="img-carrito"
                             onerror="this.onerror=null;this.src='https://via.placeholder.com/90x90?text=Sin+Imagen';">
                    </td>
                    <td>
                        <div class="nombre-carrito"><?= htmlspecialchars($prod['nombre']) ?></div>
                        <div class="desc-carrito"><?= htmlspecialchars($prod['descripcion']) ?></div>
                    </td>
                    <td>$<?= number_format($prod['precio'], 0, ',', '.') ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="carrito_id" value="<?= $prod['id'] ?>">
                            <input type="hidden" name="accion" value="restar">
                            <button type="submit" class="btn-cantidad">-</button>
                        </form>
                        <span class="cantidad-carrito"><?= $prod['cantidad'] ?></span>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="carrito_id" value="<?= $prod['id'] ?>">
                            <input type="hidden" name="accion" value="sumar">
                            <button type="submit" class="btn-cantidad">+</button>
                        </form>
                    </td>
                    <td>$<?= number_format($prod['precio'] * $prod['cantidad'], 0, ',', '.') ?></td>
                    <td>
                        <form method="POST" class="form-eliminar" style="display:inline;">
                            <input type="hidden" name="carrito_id" value="<?= $prod['id'] ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit" class="btn-eliminar" title="Eliminar producto">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Columna derecha: Resumen -->
    <?php
    $envio = ($total >= 10000) ? 3500 : 0;
    ?>
    <div class="resumen-col resumen-peterpan">
        <div class="resumen-title">Totales del carrito</div>
        <div class="resumen-line">
            <span>Subtotal</span>
            <span>$<?= number_format($total, 0, ',', '.') ?></span>
        </div>
        <div class="resumen-line">
            <span>Envío</span>
            <span><?= $envio > 0 ? 'Domicilio (7:00 A.M. a 7:00 P.M.): $3.500' : 'No aplica' ?></span>
        </div>
        <div class="resumen-total">
            Total<br>
            $<?= number_format($total + $envio, 0, ',', '.') ?>
        </div>
        <div class="resumen-box">
            <strong>Compra segura</strong><br>
            Tu pedido es a domicilio y se paga al momento de la entrega.<br>
            <b>Solo se hacen domicilios por compras mayores a $10.000</b>
        </div>
        <?php $usuario_logueado = isset($_SESSION['cliente_id']); ?>
        <?php if ($usuario_logueado): ?>
            <a href="finlizarcompra.php" target="_blank"
               class="resumen-btn">
                Finalizar compra
            </a>
        <?php else: ?>
            <button type="button" class="resumen-btn" onclick="alertaLoginRedirect()">
                Finalizar compra
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.form-eliminar').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Eliminar producto?',
            text: "¿Estás seguro de eliminar este producto del carrito?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b3d335',
            cancelButtonColor: '#888',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#fff',
            color: '#333',
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});

function alertaLoginRedirect() {
    Swal.fire({
        icon: 'warning',
        title: 'Debes iniciar sesión',
        text: 'Inicia sesión para continuar con la compra.',
        showCancelButton: true,
        confirmButtonText: 'Iniciar sesión',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#b3d335',
        cancelButtonColor: '#64748b',
        background: '#fff',
        color: '#333',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = './admin/login_form.php';
        }
    });
}
</script>
 <!-- Footer -->
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
            Panadería  &copy; 2025 Todos los derechos reservados. Diseñado por <span style="color:var(--verde);font-weight:bold;">Janus</span>.
        </div>
    </footer>
</body>
</html>
