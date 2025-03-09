<?php
session_start();
require_once 'conexion.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conexion = conectar();
$error = '';
$success = '';
$mascotas = [];
$nombre_completo = '';

// Obtener datos del cliente
try {
    // Datos del cliente
    $stmt = $conexion->prepare("SELECT nombre, apellidos FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        throw new Exception("Cliente no encontrado");
    }

    $cliente = $resultado->fetch_assoc();
    $nombre_completo = htmlspecialchars(trim($cliente['nombre'] . ' ' . $cliente['apellidos']));

    // Obtener mascotas del cliente
    $stmt = $conexion->prepare("SELECT id_mascota, nombre FROM mascotas WHERE id_cliente = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $mascotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = "Error al cargar datos: " . $e->getMessage();
}

// Procesar formulario de cita
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_mascota = $_POST['mascota'];
        $servicio = htmlspecialchars(trim($_POST['servicio']));
        $fecha = $_POST['fecha'];
        $hora = $_POST['hora'];

        // Validaciones
        if (empty($id_mascota) || empty($servicio) || empty($fecha) || empty($hora)) {
            throw new Exception("Todos los campos son obligatorios");
        }

        // Insertar cita
        $stmt = $conexion->prepare("INSERT INTO citas (id_mascota, servicio, fecha, hora, id_cliente) 
                                   VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", 
            $id_mascota,
            $servicio,
            $fecha,
            $hora,
            $_SESSION['user_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al agendar la cita: " . $stmt->error);
        }

        $success = "Cita agendada exitosamente";
        $_POST = array(); // Limpiar formulario

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - Dogtor</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
     <link rel="stylesheet" href="css/citas.css"> 
      <link rel="stylesheet" href="css/nav.css"> 
</head>
<body>
     <?php include 'nav.php'; ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-calendar-check"></i> Agendar Nueva Cita</h4>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <!-- Dueño -->
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Dueño</label>
                            <input type="text" class="form-control" value="<?= $nombre_completo ?>" readonly>
                        </div>

                        <!-- Selección de mascota -->
                        <div class="form-group">
                            <label><i class="fas fa-paw"></i> Mascota</label>
                            <select class="form-control" name="mascota" required>
                                <option value="">Seleccione una mascota</option>
                                <?php foreach ($mascotas as $m): ?>
                                    <option value="<?= $m['id_mascota'] ?>">
                                        <?= htmlspecialchars($m['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Servicio -->
                        <div class="form-group">
                            <label><i class="fas fa-stethoscope"></i> Servicio</label>
                            <select class="form-control" name="servicio" required>
                                <option value="">Seleccione un servicio</option>
                                <option value="Consulta General">Consulta General</option>
                                <option value="Vacunación">Vacunación</option>
                                <option value="Estética">Estética</option>
                            </select>
                        </div>

                        <!-- Fecha y Hora -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-calendar-day"></i> Fecha</label>
                                <input type="date" class="form-control" name="fecha" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label><i class="fas fa-clock"></i> Hora</label>
                                <select class="form-control" name="hora" required>
                                    <option value="">Seleccione hora</option>
                                    <option value="09:00">09:00 AM</option>
                                    <option value="10:00">10:00 AM</option>
                                    <option value="11:00">11:00 AM</option>
                                    <option value="15:00">03:00 PM</option>
                                    <option value="16:00">04:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-save"></i> Guardar Cita
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>