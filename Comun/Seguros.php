<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conexion = conectar();
$error = '';
$success = '';
$mascotas = [];
$seguros = [];

// Obtener email del usuario desde la base de datos
$stmt = $conexion->prepare("SELECT email, id_cliente FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener todos los seguros disponibles
$seguros_query = $conexion->query("SELECT * FROM seguros ORDER BY tipo_seguro");
$seguros = $seguros_query->fetch_all(MYSQLI_ASSOC);

// Obtener mascotas del cliente automáticamente
try {
    $stmt = $conexion->prepare("
        SELECT m.id_mascota, m.nombre, m.id_seguro, s.tipo_seguro 
        FROM mascotas m
        LEFT JOIN seguros s ON m.id_seguro = s.id_seguro
        WHERE m.id_cliente = ?
    ");
    $stmt->bind_param("i", $usuario['id_cliente']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mascotas = $result->fetch_all(MYSQLI_ASSOC);
    }

} catch (Exception $e) {
    $error = $e->getMessage();
}

// Manejar asignación/actualización de seguro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_seguro'])) {
    try {
        $mascota_id = $_POST['mascota_id'];
        $seguro_id = $_POST['seguro_id'];

        if (!is_numeric($mascota_id)) {
            throw new Exception("ID de mascota inválido");
        }

        $seguro_id = !empty($seguro_id) ? $seguro_id : null;

        $stmt = $conexion->prepare("UPDATE mascotas SET id_seguro = ? WHERE id_mascota = ?");
        $stmt->bind_param("ii", $seguro_id, $mascota_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar seguro: " . $conexion->error);
        }
        
        $success = "¡Seguro actualizado correctamente!";
        
        // Actualizar lista de mascotas
        $stmt = $conexion->prepare("
            SELECT m.id_mascota, m.nombre, m.id_seguro, s.tipo_seguro 
            FROM mascotas m
            LEFT JOIN seguros s ON m.id_seguro = s.id_seguro
            WHERE m.id_cliente = ?
        ");
        $stmt->bind_param("i", $usuario['id_cliente']);
        $stmt->execute();
        $result = $stmt->get_result();
        $mascotas = $result->fetch_all(MYSQLI_ASSOC);

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Seguros</title>
    <link rel="stylesheet" href="css/nav.css">
       <link rel="stylesheet" href="css/footer.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/seguros.css"> 
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="dashboard-content">
    <?php

if(isset($usuario) && is_array($usuario)){
    $userEmail = $usuario['email'] ?? 'No registrado';
}else{
    $userEmail = 'Invitado';
}
?>

<h1 class="mb-4">🛡️ Seguros de <?= htmlspecialchars($userEmail) ?></h1>
        <?php if ($error) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Lista de mascotas -->
        <?php if (!empty($mascotas)) : ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mascota</th>
                        <th>Seguro Actual</th>
                        <th>Actualizar Seguro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mascotas as $mascota) : ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($mascota['nombre']) ?></td>
                        <td><?= $mascota['tipo_seguro'] ?? 'Sin seguro' ?></td>
                        <td>
                            <form method="post" class="row g-2">
                                <div class="col-8">
                                    <select name="seguro_id" class="form-select">
                                        <option value="">Eliminar seguro</option>
                                        <?php foreach ($seguros as $seguro) : ?>
                                        <option value="<?= $seguro['id_seguro'] ?>"
                                            <?= (isset($mascota['id_seguro']) && $seguro['id_seguro'] == $mascota['id_seguro']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($seguro['tipo_seguro']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="hidden" name="mascota_id" value="<?= $mascota['id_mascota'] ?>">
                                    <button type="submit" name="assign_seguro" class="btn btn-warning w-100">
                                        Actualizar
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            No tienes mascotas registradas. 
        </div>
        <?php endif; ?>

     
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>