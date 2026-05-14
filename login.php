<?php
/**
 * Script de Autenticación
 * Procesa el login y valida credenciales contra la BD
 */

session_start();

require_once 'config.php';
require_once 'db.php';

// Verificar si ya hay sesión activa
if (!empty($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$exito = false;

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $error = 'Por favor ingresa email y contraseña';
    } else {
        try {
            $db = new Database();

            // Buscar el usuario por email
            $query = "SELECT id, nombre, email, password FROM usuarios WHERE email = ? LIMIT 1";
            $stmt = $db->conexion->prepare($query);

            if (!$stmt) {
                throw new Exception('Error en la BD: ' . $db->conexion->error);
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            $stmt->close();

            // Verificar si el usuario existe
            if (!$usuario) {
                $error = 'Email o contraseña incorrectos';
            } else {
                $password_guardado = $usuario['password'];
                $login_valido = false;
                $rehash_necesario = false;

                // Flujo principal: contraseñas almacenadas como hash bcrypt.
                if (password_verify($password, $password_guardado)) {
                    $login_valido = true;
                    $rehash_necesario = password_needs_rehash(
                        $password_guardado,
                        PASSWORD_BCRYPT,
                        ['cost' => BCRYPT_COST]
                    );
                // Compatibilidad con instalaciones viejas donde la contraseña
                // pudo guardarse en texto plano por error.
                } elseif ($password === $password_guardado) {
                    $login_valido = true;
                    $rehash_necesario = true;
                }

                if ($login_valido) {
                    if ($rehash_necesario) {
                        $nuevo_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                        $update = $db->conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");

                        if ($update) {
                            $update->bind_param('si', $nuevo_hash, $usuario['id']);
                            $update->execute();
                            $update->close();
                        }
                    }

                    // Login exitoso - crear sesión
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nombre'] = $usuario['nombre'];
                    $_SESSION['usuario_email'] = $usuario['email'];
                    $_SESSION['login_time'] = time();

                    $exito = true;

                    // Redirigir al dashboard
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error = 'Email o contraseña incorrectos';
                }
            }

            $db->cerrar();

        } catch (Exception $e) {
            $error = 'Error al procesar login: ' . $e->getMessage();
        }
    }
}

// Si llega aquí es porque hubo error o es GET
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TechPlace | Acceso</title>
  <link rel="icon" href="assets/img/logos/Techplace logos-05.png" type="image/png">

  <!-- TailwindCSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

  <style>
    html,
    body {
      background: #09090f !important;
      color: #fff;
      font-family: 'Inter', sans-serif;
    }

    .video-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      object-fit: cover;
      z-index: 0;
      filter: brightness(0.50) blur(1.5px);
      background: linear-gradient(120deg, rgba(10, 10, 20, 0.6) 60%, rgba(50, 17, 80, 0.5) 100%);
    }

    .login-container {
      position: relative;
      z-index: 10;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .login-card {
      background: rgba(26, 26, 46, 0.8);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(192, 132, 252, 0.2);
      border-radius: 20px;
      padding: 3rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    }

    .error-alert {
      background: rgba(239, 68, 68, 0.1);
      border-left: 4px solid #ef4444;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      color: #fca5a5;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .error-alert i {
      font-size: 1.25rem;
    }

    input {
      transition: all 0.3s ease !important;
    }

    input:focus {
      border-color: #c084fc !important;
      box-shadow: 0 0 15px rgba(192, 132, 252, 0.3) !important;
    }

    button {
      transition: all 0.3s ease;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 30px rgba(192, 132, 252, 0.4);
    }

    button:active {
      transform: translateY(0);
    }

    .nav-link {
      transition: all 0.3s ease;
    }

    .nav-link:hover {
      color: #c084fc !important;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-card {
      animation: slideIn 0.5s ease-out;
    }
  </style>
</head>

<body>
  <!-- Fondo -->
  <div class="video-bg"></div>

  <!-- Contenedor de login -->
  <div class="login-container">
    <div class="login-card">

      <!-- Logo + título -->
      <div class="flex flex-col items-center mb-8">
        <img src="assets/img/logos/Techplace logos-05.webp" alt="TechPlace" class="h-20 mb-3 drop-shadow-lg" onerror="this.style.display='none'">
        <h2 class="text-3xl font-extrabold text-purple-200 tracking-wide text-center">
          <i class="fas fa-rocket mr-2"></i>Acceso TechPlace
        </h2>
        <p class="text-purple-300 text-sm mt-2">Dashboard de Usuarios</p>
      </div>

      <!-- Alerta de error -->
      <?php if (!empty($error)): ?>
        <div class="error-alert">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <!-- Formulario -->
      <form action="login.php" method="POST" class="space-y-5">

        <!-- E-mail -->
        <div class="relative">
          <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-purple-400"></i>
          <input type="email" name="email" id="email" required 
                 placeholder="Tu Email" 
                 class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-900 bg-opacity-60
                 text-purple-100 placeholder-purple-400
                 border border-purple-700 focus:border-purple-400
                 focus:ring focus:ring-purple-500/40 outline-none transition" />
        </div>

        <!-- Contraseña -->
        <div class="relative">
          <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-purple-400"></i>
          <input type="password" name="password" id="password" required 
                 placeholder="Contraseña" 
                 class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-900 bg-opacity-60
                 text-purple-100 placeholder-purple-400
                 border border-purple-700 focus:border-purple-400
                 focus:ring focus:ring-purple-500/40 outline-none transition" />
        </div>

        <!-- Botón -->
        <button type="submit" class="w-full py-3 rounded-xl font-bold text-white relative
               bg-gradient-to-r from-purple-700 via-indigo-600 to-purple-500
               hover:from-purple-600 hover:to-indigo-500
               shadow-lg transform transition duration-200 hover:-translate-y-0.5">
          <span class="inline-flex items-center gap-2">
            <i class="fas fa-sign-in-alt"></i> Entrar al Dashboard
          </span>
        </button>
      </form>

      <!-- Links auxiliares -->
      <div class="flex justify-between items-center mt-6 text-sm">
        <a href="#" class="text-purple-300 hover:text-purple-400 hover:underline transition nav-link">¿Olvidaste tu contraseña?</a>
        <a href="index.html" class="text-gray-300 hover:text-gray-100 hover:underline transition nav-link">Regresar</a>
      </div>

      <!-- Datos de prueba -->
      <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(192, 132, 252, 0.2);">
        <p class="text-xs text-gray-400 mb-2">📝 Credenciales de Prueba:</p>
        <div class="text-xs text-gray-400 space-y-1">
          <p>Email: <span class="text-purple-300 font-mono">admin@techplace.com</span></p>
          <p>Contraseña: <span class="text-purple-300 font-mono">123456</span></p>
        </div>
      </div>

    </div>
  </div>

</body>

</html>
<?php
