<?php
session_start();

// Si ya está logueado, redirige al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require 'conexion.php';

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Error de seguridad');
        }

        $email = strtolower(trim($_POST['email']));
        $password = $_POST['password'];

        // Buscar usuario en la base de datos
        $conexion = conectar();
        $stmt = $conexion->prepare("SELECT id_cliente, password FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception('Usuario no registrado');
        }

        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Contraseña incorrecta');
        }

        // Crear sesión
        $_SESSION['user_id'] = $user['id_cliente'];
        $_SESSION['user_email'] = $email;

        // Redirigir al dashboard
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
    <title>Login</title>
    <link rel="stylesheet" href="css/dogtor.css">
</head>
<body>
    <div class="container">
        <?php if(isset($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <h2>Iniciar Sesión</h2>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Entrar</button>
            <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
        </form>
    </div>
</body>
</html>