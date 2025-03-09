<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id'])) {
   header('Location: login.php');
   exit;
   
}

// Get user data (corrected)
$conexion = conectar();
$stmt = $conexion->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

// Get the result first
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    // Handle error case
    die("Error: Usuario no encontrado");
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Dogtor</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/footer.css">
</head>
<body>
  <?php include 'nav.php'; ?>

  <section class="banner">
    <div class="banner-content">
        <h1 class="main-title">Bienvenido a <span class="highlight">Dogtor</span><br>tu veterinaria de confianza</h1>
    </div>
</section>
    <?php include 'footer.php'; ?>
</body>

</html>