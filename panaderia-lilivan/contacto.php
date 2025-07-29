<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Contacto | Lilivan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Roboto&family=Montserrat:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f6f6f6;
      color: #333;
      margin: 0;
    }
    .contacto-container {
      max-width: 900px;
      margin: 50px auto 0 auto;
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 2px 12px #b3d33522;
      border: 1.5px solid #e9ecef;
      padding: 38px 32px 32px 32px;
    }
    h1 {
      font-family: 'Playfair Display', serif;
      color: #b3d335;
      text-align: center;
      margin-bottom: 10px;
    }
    .subtitulo {
      text-align: center;
      color: #7a7a7a;
      margin-bottom: 30px;
    }
    .contacto-form {
      display: flex;
      flex-direction: column;
      gap: 18px;
      max-width: 480px;
      margin: 0 auto;
    }
    .contacto-form input, .contacto-form textarea {
      border: 1.5px solid #b3d335;
      border-radius: 8px;
      padding: 12px;
      font-size: 1em;
      background: #f6f6f6;
      color: #333;
      font-family: 'Roboto', sans-serif;
      resize: none;
    }
    .contacto-form input:focus, .contacto-form textarea:focus {
      outline: none;
      border-color: #8bbf1f;
      background: #fff;
    }
    .contacto-form button {
      background: #b3d335;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 12px 0;
      font-weight: bold;
      font-size: 1.08em;
      cursor: pointer;
      box-shadow: 0 2px 8px #b3d33533;
      transition: background 0.2s;
    }
    .contacto-form button:hover {
      background: #8bbf1f;
    }
    .contacto-info {
      margin-top: 38px;
      text-align: center;
      color: #7a7a7a;
      font-size: 1.08em;
    }
    .contacto-info i {
      color: #b3d335;
      margin-right: 8px;
    }
    @media (max-width: 600px) {
      .contacto-container {
        padding: 18px 6px 18px 6px;
      }
      .contacto-form {
        padding: 0 2px;
      }
    }
  </style>
</head>
<body>
  <div class="contacto-container">
    <h1>Contacto</h1>
    <div class="subtitulo">¿Tienes dudas, sugerencias o quieres hacer un pedido especial?<br>¡Escríbenos!</div>
    <form class="contacto-form" method="post" action="#">
      <input type="text" name="nombre" placeholder="Tu nombre" required>
      <input type="email" name="email" placeholder="Tu correo electrónico" required>
      <textarea name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..." required></textarea>
      <button type="submit"><i class="fas fa-paper-plane"></i> Enviar mensaje</button>
    </form>
    <div class="contacto-info">
      <p><i class="fas fa-map-marker-alt"></i> Calle 37#6-42, Granjas (Neiva-Huila)</p>
      <p><i class="fas fa-phone"></i> 316 300 7815</p>
      <p><i class="fas fa-envelope"></i> hercoleon@gmail.com</p>
      <p><i class="fab fa-whatsapp"></i> <a href="https://wa.me/573163007815" style="color:#b3d335;text-decoration:none;font-weight:bold;">WhatsApp directo</a></p>
    </div>
  </div>
</body>
</html>
