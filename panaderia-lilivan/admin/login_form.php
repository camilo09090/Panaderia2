<?php
// filepath: c:\xampp\htdocs\Lilivan-v4\admin\login_form.php
session_start();
include_once("../conexion.php");

$registro_exitoso = false;
$errores_registro = [];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registro_cliente'])) {
    // Recibe los datos del formulario
    $nombre = trim($_POST['fullname']);
    $documento = trim($_POST['documento']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $direccion = trim($_POST['direccion']);
    $observaciones = trim($_POST['observaciones']);
    $password = trim($_POST['password']);

    // Validación básica
    if (empty($nombre) || empty($documento) || empty($telefono) || empty($email) || empty($direccion) || empty($password)) {
        $errores_registro[] = "Todos los campos son obligatorios.";
    } else {
        // Verifica si ya existe documento o email
        $stmt = $conn->prepare("SELECT id FROM clientes WHERE numero_documento = ? OR correo_electronico = ?");
        $stmt->bind_param("ss", $documento, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errores_registro[] = "El documento o correo ya está registrado.";
        } else {
            // Inserta el cliente
            $stmt2 = $conn->prepare("INSERT INTO clientes (nombre, numero_documento, telefono, correo_electronico, direccion, observaciones, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2->bind_param("sssssss", $nombre, $documento, $telefono, $email, $direccion, $observaciones, $hash);
            if ($stmt2->execute()) {
                $registro_exitoso = true;
            } else {
                $errores_registro[] = "Error al registrar. Intenta de nuevo.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Acceso Administrador | LILIVAN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Fuentes y iconos -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --verde: #b3d335;
      --gris: #f6f6f6;
      --gris-claro: #e9ecef;
      --texto: #333;
      --texto-sec: #7a7a7a;
    }
    body {
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
      background: var(--gris);
      color: var(--texto);
      min-height: 100vh;
    }
    
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
    .login-register-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      max-width: 900px;
      margin: 50px auto 0 auto;
      flex-wrap: wrap;
    }
    .login-box, .register-box {
      background-color: #fff;
      padding: 35px 30px;
      border-radius: 16px;
      box-shadow: 0 2px 12px #b3d33522;
      width: 100%;
      max-width: 340px;
      color: #333;
      text-align: center;
      border: 2px solid #e9ecef;
      margin-bottom: 30px;
    }
    .login-box .icon, .register-box .icon {
      background-color: #b3d335;
      color: #fff;
      font-size: 24px;
      padding: 18px;
      border-radius: 50%;
      display: inline-block;
      margin-bottom: 15px;
      box-shadow: 0 2px 8px #b3d33533;
    }
    .login-box h2, .register-box h2 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      color: #333;
      margin: 10px 0 8px;
    }
    .login-box p, .register-box p {
      font-size: 14px;
      color: #7a7a7a;
      margin-bottom: 25px;
      margin-top: 0;
    }
    form {
      text-align: left;
    }
    .input-group {
      margin-bottom: 18px;
    }
    .input-group label {
      font-size: 13px;
      color: #7a7a7a;
      margin-bottom: 6px;
      display: flex;
      align-items: center;
    }
    .input-group label i {
      margin-right: 6px;
    }
    .input-group input, .input-group textarea {
      width: 100%;
      padding: 12px;
      border-radius: 8px;
      background-color: #f6f6f6;
      color: #333;
      border: 2px solid #b3d335;
      font-size: 14px;
      box-sizing: border-box;
    }
    .input-group input::placeholder, .input-group textarea::placeholder {
      color: #b3d335;
    }
    button, .btn-submit {
      width: 100%;
      padding: 12px;
      background-color: #b3d335;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 15px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 2px 8px #b3d33533;
      margin-top: 10px;
    }
    button i, .btn-submit i {
      margin-right: 6px;
    }
    button:hover, .btn-submit:hover {
      background-color: #8bbf1f;
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
    .footer {
      margin-top: 20px;
      font-size: 12px;
      text-align: center;
      color: #7a7a7a;
    }
    .footer a {
      color: #b3d335;
      text-decoration: none;
    }
    .footer a:hover {
      text-decoration: underline;
    }
    .divider {
      margin: 24px 0 0 0;
      text-align: center;
    }
    .divider a {
      color: #a1a1aa;
      font-size: 0.85em;
      text-decoration: none;
    }
    .divider a:hover {
      color: #b3d335;
      text-decoration: underline;
    }
    .a-volver-inicio {
      color: #b3d335;
      text-decoration: none;
      transition: transform 0.2s, color 0.2s;
      display: inline-block;
      margin-top: 10px;
    }
    .a-volver-inicio:hover {
      transform: translateX(8px);
      color: #60a5fa;
      text-decoration: none;
    }
    @media (max-width: 900px) {
      .login-register-container {
        flex-direction: column;
        align-items: center;
        gap: 24px;
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
  </style>
</head>
<body>

<!-- Header principal -->
<header>
    <div class="nav-container">
        <div class="logo">
            <img src="../otros/img/logo.png" alt="Lilivan Logo">
            <span>DESDE 2001</span>
        </div>
        <nav>
            <a href="../index.php" class="nav-link">Inicio</a>
            <a href="../productos.php" class="nav-link">Productos</a>
            <button id="carritoNavBtn" class="btn-nav-carrito" onclick="window.location.href='carrito.php'">
        <i class="fas fa-shopping-basket"></i>
        Carrito
        <span id="carritoContador" style="background:#b3d335;color:#fff;border-radius:50%;padding:2px 8px;font-size:0.95em;margin-left:6px;display:none;">0</span>
      </button>
        </nav>
        <a href="login_form.php" class="btn-cuenta"><i class="fas fa-user"></i> Mi cuenta</a>
    </div>
</header>

<div class="login-register-container">
  <!-- Login -->
  <div class="login-box">
    <div class="icon"><i class="fas fa-wine-glass-alt"></i></div>
    <h2>Iniciar Sesión</h2>
    <p>Ingresa tus datos</p>
    <form action="login.php" method="POST">
      <div class="input-group">
        <label><i class="fas fa-envelope"></i>Email</label>
        <input type="email" name="email" placeholder="ejemplo@correo.com" required>
      </div>
      <div class="input-group">
        <label><i class="fas fa-lock"></i>Contraseña</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit"><i class="fas fa-sign-in-alt"></i> Ingresar</button>
    </form>
    <div class="footer">
      ¿Desea regresar al inicio? <a href="../index.php">Volver al inicio</a>.
    </div>
  </div>
  <!-- Registro -->
  <div class="register-box" id="register">
    <div class="icon"><i class="fas fa-user-plus"></i></div>
    <h2>Registrarme</h2>
    <p>Regístrate como cliente para comprar</p>
    <?php if (!empty($errores_registro)): ?>
      <div style="color:#e74c3c; margin-bottom:12px;">
        <?php foreach($errores_registro as $err) echo "<div>$err</div>"; ?>
      </div>
    <?php endif; ?>
    <form method="post" id="form-registro">
      <input type="hidden" name="registro_cliente" value="1">
      <div class="input-group">
        <label for="fullname"><i class="fas fa-user"></i>Nombre completo</label>
        <input type="text" id="fullname" name="fullname" required />
      </div>
      <div class="input-group">
        <label for="documento"><i class="fas fa-id-card"></i>Número de documento</label>
        <input type="text" id="documento" name="documento" required />
      </div>
      <div class="input-group">
        <label for="telefono"><i class="fas fa-phone"></i>Teléfono</label>
        <input type="tel" id="telefono" name="telefono" required />
      </div>
      <div class="input-group">
        <label for="email"><i class="fas fa-envelope"></i>Correo electrónico</label>
        <input type="email" id="email" name="email" required />
      </div>
      <div class="input-group">
        <label for="direccion"><i class="fas fa-map-marker-alt"></i>Dirección</label>
        <input type="text" id="direccion" name="direccion" required />
      </div>
      <div class="input-group">
        <label for="observaciones"><i class="fas fa-info-circle"></i>Observaciones</label>
        <textarea id="observaciones" name="observaciones" rows="2" placeholder="Observaciones generales fuera de su casa"></textarea>
      </div>
      <div class="input-group">
        <label for="password"><i class="fas fa-lock"></i>Contraseña</label>
        <input type="password" id="password" name="password" required minlength="6" />
      </div>
      <button class="btn-submit" type="submit">
        <i class="fas fa-user-plus"></i> Registrarme
      </button>
    </form>
    <div style="text-align:center; margin-top:12px;">
      <a href="../index.php" class="a-volver-inicio"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
    </div>
  </div>
</div>

<script>
<?php if ($registro_exitoso): ?>
Swal.fire({
  icon: 'success',
  title: '¡Registro exitoso!',
  text: 'Tu cuenta ha sido creada correctamente. Ahora puedes iniciar sesión.',
  confirmButtonColor: '#b3d335'
});
<?php endif; ?>
</script>
<!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-logo">
                <img src="./otros/img/logo-lilivan.png" alt="Lilivan Logo">
                <span>DESDE 2001</span>
                <p>La mejor y más tradicional tienda de bebidas y estanco del Huila, con el mismo sabor de siempre desde 2001.</p>
                <div class="footer-products">
                    <div class="footer-product"><img src="./otros/img/gaseosa.jpg" alt="Gaseosas"></div>
                    <div class="footer-product"><img src="./otros/img/cerveza.jpg" alt="Cervezas"></div>
                    <div class="footer-product"><img src="./otros/img/licor.jpg" alt="Licores"></div>
                    <div class="footer-product"><img src="./otros/img/cigarrillo.jpg" alt="Cigarrillo"></div>
                </div>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <div class="footer-title">Información</div>
                <ul class="footer-list">
                    <li><a href="#">Descuento de cumpleaños</a></li>
                    <li><a href="#">Autorización de tratamiento de datos personales</a></li>
                </ul>
            </div>
            <div>
                <div class="footer-title">Oficinas</div>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i> Calle 37#6-42, Granjas (Neiva-Huila)</li>
                    <li><i class="fas fa-phone"></i> 3163007815</li>
                    <li><i class="fas fa-envelope"></i> hercoleon@gmail.com</li>
                </ul>
                <button class="footer-btn"><i class="fas fa-phone"></i> Domicilios: 3163007815</button>
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
            Lilivan &copy; 2025 Todos los derechos reservados. Diseñado por <span style="color:var(--verde);font-weight:bold;">Janus</span>.
        </div>
    </footer>
</body>
</html>