<?php
session_start();
if (isset($_POST['cerrar_sesion'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conexion.php';

// Obtener productos con categorías
$sql = "SELECT p.id, p.nombre, p.descripcion, p.descripcion_detallada, p.precio, p.stock, p.imagen_url, c.nombre AS categoria_nombre
        FROM productos p
        LEFT JOIN catalogo c ON p.id_categoria = c.id";
$result = $conn->query($sql);
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

// Obtener todas las categorías para filtros
$categorias_result = $conn->query("SELECT nombre FROM catalogo");
$categorias = [];
while ($cat = $categorias_result->fetch_assoc()) {
    $categorias[] = $cat['nombre'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos | Panadería Lilivan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --verde: #b3d335;
      --verde-oscuro: #8bbf1f;
      --gris: #f6f6f6;
      --gris-claro: #e9ecef;
      --texto: #222;
      --texto-sec: #7a7a7a;
      --sombra: 0 4px 24px #b3d33522;
      --radius: 18px;
    }
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(120deg, #f6f6f6 0%, #e9ecef 100%);
      color: var(--texto);
      margin: 0;
      min-height: 100vh;
    }
    /* NAV Moderno */
    .header-pan {
      background: #fff;
      border-bottom: 2px solid var(--gris-claro);
      box-shadow: 0 2px 12px #0001;
      padding: 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .header-pan .nav-container {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 22px 32px 10px 32px;
    }
    .header-pan .logo {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
    .header-pan .logo img {
      height: 54px;
      filter: drop-shadow(0 2px 8px #b3d33533);
    }
    .header-pan .logo span {
      font-family: 'Playfair Display', serif;
      font-size: 13px;
      color: var(--texto-sec);
      letter-spacing: 2px;
      margin-top: 2px;
    }
    .header-pan nav {
      display: flex;
      gap: 32px;
      align-items: center;
    }
    .header-pan .nav-link {
      color: var(--texto);
      text-decoration: none;
      font-weight: 500;
      font-size: 1.08em;
      padding: 6px 0;
      border-bottom: 2px solid transparent;
      transition: color 0.2s, border-color 0.2s;
      letter-spacing: 0.5px;
    }
    .header-pan .nav-link:hover, .header-pan .nav-link.active {
      color: var(--verde);
      border-bottom: 2px solid var(--verde);
    }
    .header-pan .btn-cuenta {
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
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .header-pan .btn-cuenta:hover {
      background: var(--verde-oscuro);
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
    /* HERO */
    main {
      margin-top: 0; /* Quitar separación extra */
      padding-top: 0;
    }
    .productos-hero {
      width: 100%;
      background: linear-gradient(120deg, #b3d33511 0%, #fff 100%);
      padding: 32px 0 18px 0; /* Menos padding arriba */
      text-align: center;
      margin-bottom: 12px;
      box-shadow: 0 2px 24px #b3d33511;
    }
    .productos-hero h1 {
      font-family: 'Playfair Display', serif;
      font-size: 2.5rem;
      color: var(--verde);
      margin-bottom: 10px;
      letter-spacing: 1px;
      text-shadow: 0 2px 8px #b3d33522;
    }
    .productos-hero .subtitulo {
      color: var(--texto-sec);
      font-size: 1.15rem;
      margin-bottom: 0;
      font-family: 'Roboto', sans-serif;
    }
    /* FILTROS */
    .filtros {
      display: flex;
      justify-content: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 30px;
      margin-top: 18px;
    }
    .filtros button {
      background-color: #fff;
      border: 1.5px solid var(--verde);
      color: var(--texto);
      padding: 8px 18px;
      border-radius: 20px;
      font-size: 15px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
      box-shadow: 0 2px 8px #b3d33511;
      letter-spacing: 0.5px;
    }
    .filtros button.active,
    .filtros button:hover {
      background-color: var(--verde);
      color: #fff;
      border-color: var(--verde);
      box-shadow: 0 4px 18px #b3d33522;
    }
    /* GRID DE PRODUCTOS */
    .catalogo {
      display: grid;
      grid-template-columns: repeat(4, 1fr); /* 4 columnas iguales */
      gap: 32px;
      padding: 0 40px 40px 40px;
      background: transparent;
      justify-items: center;
    }

    /* Ajusta la tarjeta para que ocupe todo el ancho de la columna */
    .producto {
      width: 100%;
      min-width: 0;
      max-width: 100%;
      height: 430px; /* altura fija para todas */
      margin-bottom: 0;
    }

    @media (max-width: 1200px) {
      .catalogo { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 900px) {
      .catalogo { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
      .catalogo { grid-template-columns: 1fr; }
    }
    .producto {
      width: 260px; /* ancho fijo para todas */
      min-width: 260px;
      max-width: 260px;
      background: #fff;
      border-radius: 22px;
      box-shadow: 0 2px 18px #b3d33522;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 24px 18px 18px 18px;
      transition: box-shadow 0.2s, transform 0.2s;
      border: none;
      position: relative;
      margin-bottom: 12px;
      height: 430px; /* altura fija para todas */
      justify-content: flex-start;
    }
    .producto:hover {
      box-shadow: 0 8px 32px #b3d33540;
      transform: scale(1.03);
    }
    .categoria-label {
      position: absolute;
      top: 18px;
      left: 18px;
      background: #b3d335;
      color: #fff;
      font-size: 1em;
      font-weight: bold;
      padding: 6px 18px;
      border-radius: 50px;
      z-index: 2;
      box-shadow: 0 2px 8px #b3d33533;
      letter-spacing: 0.5px;
    }
    .producto img {
      width: 150px; /* imagen más grande */
      height: 150px;
      object-fit: cover;
      border-radius: 14px;
      margin-bottom: 14px;
      box-shadow: 0 2px 8px #b3d33522;
      background: #fff;
      display: block;
      margin-left: auto;
      margin-right: auto;
    }
    .producto .contenido {
      width: 100%;
      text-align: center;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }
    .producto h2 {
      font-family: 'Playfair Display', serif;
      font-size: 1.15rem;
      font-weight: bold;
      color: #333;
      margin-bottom: 8px;
      margin-top: 0;
    }
    .producto .descripcion {
      color: #7a7a7a;
      font-size: 1em;
      margin-bottom: 10px;
      min-height: 38px;
    }
    .precio-stock {
      display: flex;
      flex-direction: column;
      gap: 4px;
      margin-bottom: 10px;
    }
    .precio {
      color: #b3d335;
      font-weight: bold;
      font-size: 1.08em;
      margin-bottom: 2px;
    }
    .stock {
      color: #27ae60;
      font-size: 0.98em;
    }
    .botones {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-top: 8px;
      width: 100%;
    }
    .boton {
      background: #b3d335;
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 8px 0; /* menos alto */
      font-weight: bold;
      font-size: 1em;
      cursor: pointer;
      box-shadow: 0 2px 8px #b3d33533;
      transition: background 0.2s, transform 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      min-height: 36px; /* altura mínima uniforme */
    }
    .boton.btn-info {
      background: #fff;
      color: #b3d335;
      border: 2px solid #b3d335;
    }
    .boton.btn-info:hover {
      background: #b3d335;
      color: #fff;
    }
    .boton.btn-carrito {
      background: #b3d335;
      color: #fff;
      border: none;
    }
    .boton.btn-carrito:hover {
      background: #8bbf1f;
    }
    /* Modal estilos */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0; top: 0; right: 0; bottom: 0;
            width: 100vw; height: 100vh;
            background: rgba(51,51,51,0.25);
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px #b3d33540;
            padding: 32px 24px;
            max-width: 420px;
            width: 95vw;
            position: relative;
            animation: modalFadeIn 0.3s;
        }
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(1.08);}
            to   { opacity: 1; transform: scale(1);}
        }
        .close {
            position: absolute;
            top: 18px;
            right: 18px;
            background: none;
            border: none;
            font-size: 2em;
            color: #b3d335;
            cursor: pointer;
        }
        .modal-img img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px #b3d33522;
            background: #fff;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .categoria-modal {
            color: #b3d335;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .precio-modal {
            color: #b3d335;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .descripcion-modal {
            color: #333;
            margin-bottom: 10px;
            text-align: justify;
        }
        .cantidad-control {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        .cantidad-control input[type="number"] {
            width: 60px;
            text-align: center;
            font-size: 1.1em;
            border-radius: 8px;
            border: 1.5px solid #b3d335;
            padding: 4px 8px;
        }
        .cantidad-control button {
            background: #b3d335;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background 0.2s;
        }
        .cantidad-control button:hover {
            background: #8bbf1f;
        }
        @media (max-width: 600px) {
            .modal-content { padding: 18px 6px; max-width: 98vw; }
            .modal-img img { width: 80px; height: 80px; }
        }
    /* MODAL AGREGADO */
    #modalAgregado .modal-content {
      max-width: 350px;
      text-align: center;
      padding: 2.2rem 1.2rem 1.2rem 1.2rem;
    }
    #modalAgregado .fa-check-circle {
      font-size: 2.5em;
      color: var(--verde);
      margin-bottom: 10px;
    }
    /* FOOTER */
    .footer-pan {
      background: #fff;
      border-top: 2px solid var(--gris-claro);
      margin-top: 60px;
      padding: 40px 0 0 0;
      box-shadow: 0 -2px 24px #b3d33511;
    }
    .footer-pan .footer-container {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 2fr 1fr 1fr 1.2fr;
      gap: 40px;
      padding: 0 32px;
    }
    .footer-pan .footer-logo img {
      height: 48px;
      filter: drop-shadow(0 2px 8px #b3d33533);
    }
    .footer-pan .footer-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 13px;
      color: var(--texto-sec);
      letter-spacing: 2px;
    }
    .footer-pan .footer-logo p {
      margin: 18px 0 10px 0;
      color: var(--texto-sec);
      font-size: 1.05em;
    }
    .footer-pan .footer-products {
      display: flex;
      gap: 10px;
      margin: 12px 0 18px 0;
    }
    .footer-pan .footer-product {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      overflow: hidden;
      border: 2px solid var(--verde);
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px #b3d33522;
    }
    .footer-pan .footer-product img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .footer-pan .footer-social {
      margin-top: 10px;
    }
    .footer-pan .footer-social a {
      color: var(--texto-sec);
      margin-right: 14px;
      font-size: 1.3em;
      transition: color 0.2s;
    }
    .footer-pan .footer-social a:hover {
      color: var(--verde);
    }
    .footer-pan .footer-title {
      font-weight: bold;
      font-size: 1.08em;
      margin-bottom: 12px;
      font-family: 'Roboto', sans-serif;
      color: var(--texto);
    }
    .footer-pan .footer-list, .footer-pan .footer-contact {
      list-style: none;
      padding: 0;
      margin: 0;
      color: var(--texto-sec);
      font-size: 1em;
    }
    .footer-pan .footer-list li, .footer-pan .footer-contact li {
      margin-bottom: 10px;
    }
    .footer-pan .footer-list a {
      color: var(--texto-sec);
      text-decoration: none;
      transition: color 0.2s;
    }
    .footer-pan .footer-list a:hover {
      color: var(--verde);
    }
    .footer-pan .footer-contact i {
      color: var(--verde);
      margin-right: 8px;
    }
    .footer-pan .footer-btn {
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
    .footer-pan .footer-btn:hover {
      background: var(--verde-oscuro);
    }
    .footer-pan .footer-copy {
      text-align: center;
      color: var(--texto-sec);
      font-size: 0.98em;
      margin: 32px 0 18px 0;
    }
    @media (max-width: 1100px) {
      .footer-pan .footer-container {
        grid-template-columns: 1fr 1fr;
        gap: 24px;
      }
    }
    @media (max-width: 700px) {
      .header-pan .nav-container, .footer-pan .footer-container {
        flex-direction: column;
        padding: 18px 10px;
      }
      .footer-pan .footer-container {
        grid-template-columns: 1fr;
        gap: 18px;
        padding: 0 10px;
      }
      .footer-pan .footer-logo img {
        height: 38px;
      }
    }
    @media (max-width: 600px) {
      .header-pan .nav-container {
        flex-direction: column;
        gap: 12px;
        padding: 12px 6px;
      }
      .header-pan nav {
        gap: 18px;
      }
      .productos-hero h1 { font-size: 1.5rem; }
      .catalogo { padding: 0 4px 40px 4px; gap: 16px; }
      .footer-pan .footer-logo img { height: 32px; }
    }
    /* NUEVO ESTILO DESTACADOS */
    .destacados-section {
      background: linear-gradient(120deg,#fff 80%,#b3d33511 100%);
      padding: 0;
    }
    .destacados-carrusel {
      display: flex;
      gap: 32px;
      padding: 18px 32px;
      scroll-behavior: smooth;
      overflow-x: auto;
      flex-wrap: wrap;
      justify-content: center;
    }
    .destacados-carrusel::-webkit-scrollbar {
      height: 8px;
      background: #e9ecef;
      border-radius: 8px;
    }
    .destacados-carrusel::-webkit-scrollbar-thumb {
      background: #b3d335;
      border-radius: 8px;
    }
  </style>
</head>
<body>
<header class="header-pan">
  <div class="nav-container">
    <div class="logo">
      <img src="./otros/img/logo.png" alt="Panadería Lilivan Logo">
      <span>DESDE 2001</span>
    </div>
    <nav>
      <a href="index.php" class="nav-link">Inicio</a>
      <a href="productos.php" class="nav-link active">Productos</a>
      <button id="carritoNavBtn" class="btn-nav-carrito" onclick="window.location.href='carrito.php'">
        <i class="fas fa-shopping-basket"></i>
        Carrito
        <span id="carritoContador" style="background:#b3d335;color:#fff;border-radius:50%;padding:2px 8px;font-size:0.95em;margin-left:6px;display:none;">0</span>
      </button>
    </nav>
    <?php if (isset($_SESSION['cliente_nombre'])): ?>
      <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-weight:bold;color:#b3d335;">
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

<main>
  <section class="productos-hero">
    <h1>Delicias de Nuestra Panadería</h1>
    <p class="subtitulo">Explora nuestros panes, pasteles, postres y productos frescos hechos con amor cada día.</p>
  </section>
  <div class="filtros">
    <button class="active" onclick="filtrar('Todos')">Todos</button>
    <?php foreach ($categorias as $cat): ?>
      <button onclick="filtrar('<?= htmlspecialchars($cat) ?>')"><?= htmlspecialchars($cat) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="catalogo" id="catalogo">
    <?php foreach ($productos as $p): ?>
      <div class="producto" data-categoria="<?= htmlspecialchars($p['categoria_nombre']) ?>">
        <span class="categoria-label"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
        <img src="otros/img/<?= htmlspecialchars($p['imagen_url']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
        <div class="contenido">
          <h2><?= htmlspecialchars($p['nombre']) ?></h2>
          <p class="descripcion"><?= htmlspecialchars($p['descripcion']) ?></p>
          <div class="precio-stock">
            <span class="precio"><strong>Precio:</strong> $<?= number_format($p['precio'], 0, '', '.') ?> COP</span>
            <span class="stock">Disponible <?= (int)$p['stock'] ?> unidades</span>
          </div>
          <div class="botones">
            <button class="boton btn-info" type="button"
              onclick="mostrarModal(
                `<?= addslashes($p['id']) ?>`,
                `<?= addslashes($p['nombre']) ?>`,
                `<?= $p['precio'] ?>`,
                `<?= $p['imagen_url'] ?>`,
                `<?= addslashes($p['descripcion_detallada'] ?? $p['descripcion']) ?>`,
                `<?= $p['categoria_nombre'] ?>`,
                `<?= (int)$p['stock'] ?>`
              )"
            >
              <i class="fas fa-info-circle"></i> Más Info
            </button>
            <button type="button" class="boton btn-carrito" style="width:100%;margin-top:10px;"
              onclick="mostrarModal(
                `<?= addslashes($p['id']) ?>`,
                `<?= addslashes($p['nombre']) ?>`,
                `<?= $p['precio'] ?>`,
                `<?= $p['imagen_url'] ?>`,
                `<?= addslashes($p['descripcion_detallada'] ?? $p['descripcion']) ?>`,
                `<?= $p['categoria_nombre'] ?>`,
                `<?= (int)$p['stock'] ?>`
              ); document.getElementById('modalCantidad').value = 1;"
            >
              <i class="fas fa-cart-plus"></i>Agregar al pedido
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>


<!-- MODAL SOLO PARA INFO DE PRODUCTO Y AGREGAR AL CARRITO -->
<div id="infoModal" class="modal">
  <div class="modal-content">
    <button class="close" onclick="cerrarModal()">&times;</button>
    <h2 id="modalNombre" class="modal-title"></h2>
    <div class="modal-body">
      <div class="modal-img">
        <img id="modalImagen" src="" alt="">
      </div>
      <div class="modal-details">
        <p id="modalCategoria" class="categoria-modal"></p>
        <p id="modalPrecio" class="precio-modal"></p>
        <p id="modalDescripcion" class="descripcion-modal"></p>
        <form id="modalCarritoForm" action="agregar_al_carrito.php" method="post" style="margin-top:1rem;">
          <input type="hidden" name="id" id="modalId">
          <div class="cantidad-control">
            <label for="modalCantidad">Cantidad:</label>
            <button type="button" onclick="cambiarCantidad(-1)">−</button>
            <input type="number" id="modalCantidad" name="cantidad" value="1" min="1" max="1">
            <button type="button" onclick="cambiarCantidad(1)">+</button>
          </div>
          <p style="margin-top: 1rem;">Total: <span id="modalTotal" class="precio-modal"></span></p>
          <button type="submit" class="boton btn-carrito" style="width:100%;margin-top:10px;">
            <i class="fas fa-cart-plus"></i>Agregar al pedido
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CONFIRMACIÓN AGREGADO -->
<div id="modalAgregado" class="modal">
  <div class="modal-content" style="max-width:350px;text-align:center;">
    <button class="close" onclick="cerrarModalAgregado()">&times;</button>
    <div style="font-size:2.5em;color:#b3d335;margin-bottom:10px;"><i class="fas fa-check-circle"></i></div>
    <div id="mensajeAgregado" style="font-size:1.1em;margin-bottom:10px;">¡Producto agregado al pedido!</div>
    <button onclick="cerrarModalAgregado()" class="boton btn-carrito" style="width:100%;margin-top:10px;">Seguir comprando</button>
    <button onclick="abrirCarrito()" class="boton btn-info" style="width:100%;margin-top:10px;">Ver pedido</button>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // --- MODAL INFO SOLO PARA VER DETALLES Y AGREGAR AL PEDIDO ---
  function mostrarModal(id, nombre, precio, imagen, descripcion, categoria, stock) {
    document.getElementById('modalNombre').textContent = nombre;
    document.getElementById('modalPrecio').textContent = `$${Number(precio).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0})} COP`;
    document.getElementById('modalImagen').src = "otros/img/" + imagen;
    document.getElementById('modalDescripcion').textContent = descripcion;
    document.getElementById('modalCategoria').textContent = `Categoría: ${categoria}`;
    document.getElementById('modalId').value = id;
    document.getElementById('modalCantidad').value = 1;
    document.getElementById('modalCantidad').max = stock;
    document.getElementById('modalCantidad').setAttribute('data-precio', precio);
    actualizarTotalModal();
    document.getElementById('infoModal').classList.add('active');
  }
  window.mostrarModal = mostrarModal;

  function cerrarModal() {
    document.getElementById('infoModal').classList.remove('active');
  }
  window.cerrarModal = cerrarModal;

  function cambiarCantidad(valor) {
    const input = document.getElementById('modalCantidad');
    let cantidad = parseInt(input.value) || 1;
    cantidad += valor;
    if (cantidad < 1) cantidad = 1;
    if (input.max && cantidad > parseInt(input.max)) cantidad = parseInt(input.max);
    input.value = cantidad;
    actualizarTotalModal();
  }
  window.cambiarCantidad = cambiarCantidad;

  function actualizarTotalModal() {
    const input = document.getElementById('modalCantidad');
    const precio = parseFloat(input.getAttribute('data-precio'));
    const cantidad = parseInt(input.value) || 1;
    document.getElementById('modalTotal').textContent = `$${(cantidad * precio).toLocaleString('es-CO', {minimumFractionDigits: 0, maximumFractionDigits: 0})} COP`;
  }

  document.getElementById('modalCantidad').addEventListener('input', actualizarTotalModal);

  // --- FILTRO CATEGORÍAS ---
  window.filtrar = function(categoria) {
    document.querySelectorAll('.filtros button').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');
    document.querySelectorAll('.producto').forEach(prod => {
      const cat = prod.getAttribute('data-categoria');
      if (categoria === 'Todos' || cat === categoria) {
        prod.style.display = 'flex';
        prod.style.flex = '1 1 260px';
        prod.style.maxWidth = '320px';
        prod.style.margin = '0 auto';
      } else {
        prod.style.display = 'none';
      }
    });
    // Ajustar alineación del contenedor para que no se estiren los productos
    const visibles = Array.from(document.querySelectorAll('.producto')).filter(p => p.style.display !== 'none');
    const catalogo = document.getElementById('catalogo');
    catalogo.style.justifyContent = 'flex-start';
  };

  document.getElementById('carritoNavBtn').addEventListener('click', function(e) {
    window.location.href = 'carrito.php';
  });
});

// --- AGREGAR AL PEDIDO DESDE MODAL (AJAX) ---
document.getElementById('modalCarritoForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = e.target;
  const datos = new FormData(form);
  fetch('agregar_al_carrito.php', {
    method: 'POST',
    body: datos
  })
  .then(response => response.json())
  .then(res => {
    cerrarModal();
    mostrarModalAgregado(res.mensaje || '¡Producto agregado al pedido!');
    actualizarContadorCarrito(res.contador);
  })
  .catch(() => {
    cerrarModal();
    mostrarModalAgregado('Ocurrió un error al agregar al pedido.');
  });
});
function actualizarContadorCarrito(contador) {
  var el = document.getElementById('carritoContador');
  if (el) {
    el.textContent = contador;
    el.style.display = (contador > 0) ? 'inline-block' : 'none';
  }
}
window.mostrarModalAgregado = function(mensaje) {
  document.getElementById('mensajeAgregado').textContent = mensaje || '¡Producto agregado al pedido!';
  document.getElementById('modalAgregado').classList.add('active');
};
window.cerrarModalAgregado = function() {
  document.getElementById('modalAgregado').classList.remove('active');
};
window.abrirCarrito = function() {
  document.getElementById('modalAgregado').classList.remove('active');
  window.location.href = 'carrito.php';
};
</script>

<footer class="footer-pan">
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
    Panadería Lilivan &copy; 2025 Todos los derechos reservados. Diseñado por <span style="color:#b3d335;font-weight:bold;">Janus</span>.
  </div>
</footer>
</body>
</html>