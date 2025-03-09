<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'conexion.php';

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Error de seguridad');
        }

        // Recoger  datos
        $datos = [
            'dni' => trim($_POST['dni']),
            'nombre' => trim($_POST['nombre']),
            'apellidos' => trim($_POST['apellidos']),
            'email' => strtolower(trim($_POST['email'])),
            'password' => $_POST['password'],
            'direccion' => trim($_POST['direccion']),
            'telefono' => trim($_POST['telefono'])
        ];

        // Validaciones básicas
        $camposRequeridos = ['dni', 'nombre', 'apellidos', 'email', 'password', 'direccion'];
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo " . ucfirst($campo) . " es obligatorio");
            }
        }

        // Verificar si DNI o Email ya existen
        $conexion = conectar();
        $stmt = $conexion->prepare("SELECT id_cliente FROM clientes WHERE dni = ? OR email = ?");
        $stmt->bind_param("ss", $datos['dni'], $datos['email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("El DNI o Email ya están registrados");
        }

        // Hash de contraseña
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);

        // Insertar nuevo usuario
        $stmt = $conexion->prepare("INSERT INTO clientes 
            (dni, nombre, apellidos, email, password, direccion, telefono) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssss", 
            $datos['dni'],
            $datos['nombre'],
            $datos['apellidos'],
            $datos['email'],
            $password_hash,
            $datos['direccion'],
            $datos['telefono']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Error al crear el usuario: ' . $stmt->error);
        }

        // Iniciar sesión automáticamente
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['user_email'] = $datos['email'];

        header('Location: dashboard.php');
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registro Completo</title>
    <link rel="stylesheet" href="css/dogtor.css">
    <script src="JavaScript/registro.js" defer></script>
</head>
<body>
    <div class="container">
        <?php if(isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateForm()">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <h2>Registro de Usuario</h2>
            
            <div class="form-group">
                <label>DNI:</label>
                <input type="text" name="dni" pattern="\d{8}[A-Za-z]" 
                       title="8 dígitos + letra" required>
                <span class="error-mensaje"></span>
            </div>

            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,50}" 
                       title="Solo letras y espacios (2-50 caracteres)" required>
                <span class="error-mensaje"></span>
            </div>

            <div class="form-group">
                <label>Apellidos:</label>
                <input type="text" name="apellidos" 
                       pattern="[A-Za-záéíóúÁÉÍÓÚñÑ\s]{2,100}" 
                       title="Solo letras y espacios (2-100 caracteres)" required>
                <span class="error-mensaje"></span>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
                <span class="error-mensaje"></span>
            </div>
            
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" 
                       minlength="8" required>
                <span class="error-mensaje"></span>
            </div>

            <div class="form-group">
                <label>Dirección:</label>
                <input type="text" name="direccion" required>
                <span class="error-mensaje"></span>
            </div>
            
            <div class="form-group">
                <label>Teléfono:</label>
                <input type="tel" name="telefono"
                       pattern="[+]?[0-9\s-]{9,15}">
                <span class="error-mensaje"></span>
            </div>
            
            <button type="submit">Registrarse</button>
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </form>
    </div>
</body>
</html>