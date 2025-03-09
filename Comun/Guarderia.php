<?php
session_start();
require 'conexion.php';
$conexion = conectar();
$mascotas = [];
$mascotas_alojadas = [];
$error = '';
$success = '';

try {
    // Obtener mascotas del usuario
    $stmt = $conexion->prepare("SELECT id_mascota, nombre FROM mascotas WHERE id_cliente = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $mascotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Obtener estancias activas (modificado para mejor precisión)
    $stmt = $conexion->prepare("
        SELECT g.*, m.nombre 
        FROM guarderia g
        JOIN mascotas m ON g.id_mascota = m.id_mascota
        WHERE g.id_cliente = ? 
        AND (g.fecha_salida > CURDATE() OR (g.fecha_salida = CURDATE() AND g.hora_salida > CURTIME()))
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $mascotas_alojadas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = "Error al cargar los datos: " . $e->getMessage();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['solicitar_guarderia'])) {
    try {
        // Validar campos requeridos
        $required = ['mascota_id', 'fecha_entrada', 'hora_entrada', 'fecha_salida', 'hora_salida'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) throw new Exception("Todos los campos son obligatorios");
        }

        // Convertir fechas a objetos DateTime
        $entrada = new DateTime($_POST['fecha_entrada'] . ' ' . $_POST['hora_entrada']);
        $salida = new DateTime($_POST['fecha_salida'] . ' ' . $_POST['hora_salida']);
        
        // Validar fechas
        if ($salida <= $entrada) throw new Exception("La salida debe ser posterior a la entrada");
        
        // Nueva validación: Verificar solapamiento de fechas
        $stmt_check = $conexion->prepare("
            SELECT id 
            FROM guarderia 
            WHERE id_mascota = ? 
            AND (
                (fecha_entrada <= ? AND fecha_salida >= ?) OR 
                (fecha_entrada BETWEEN ? AND ?) OR 
                (fecha_salida BETWEEN ? AND ?)
            )
        ");
        
        $fecha_entrada_str = $_POST['fecha_entrada'];
        $fecha_salida_str = $_POST['fecha_salida'];
        
        $stmt_check->bind_param("issssss",
            $_POST['mascota_id'],
            $fecha_salida_str,  // fecha_entrada <= ? (fecha_salida nueva reserva)
            $fecha_entrada_str, // fecha_salida >= ? (fecha_entrada nueva reserva)
            $fecha_entrada_str, // BETWEEN start
            $fecha_salida_str,  // BETWEEN end
            $fecha_entrada_str, // BETWEEN start
            $fecha_salida_str   // BETWEEN end
        );
        
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            throw new Exception("Ya tienes esa mascota en el guarderia");
        }

        // Calcular precio (manteniendo tu lógica actual)
        $intervalo = $entrada->diff($salida);
        $horas = ($intervalo->days * 24) + $intervalo->h;
        $precio = $horas * 1;

        // Insertar en BD
        $stmt = $conexion->prepare("
            INSERT INTO guarderia 
            (id_mascota, fecha_entrada, hora_entrada, fecha_salida, hora_salida, precio, id_cliente)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("issssdi",
            $_POST['mascota_id'],
            $_POST['fecha_entrada'],
            $_POST['hora_entrada'],
            $_POST['fecha_salida'],
            $_POST['hora_salida'],
            $precio,
            $_SESSION['user_id']
        );
        
        if (!$stmt->execute()) throw new Exception("Error al guardar la reserva");
        
        $success = "Reserva exitosa! Precio: {$precio}€";
        header("Refresh:2"); // Recargar para actualizar datos
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guardería Canina</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/guarderia.css">
    <link rel="stylesheet" href="css/nav.css"</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="dashboard-content">
        <h1 class="mb-4">🐶 Guardería de Mascotas</h1>

        <?php if ($error) : ?>
        <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success) : ?>
        <div class="alert alert-success mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($mascotas_alojadas)) : ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                🏠 Estancias Activas
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mascota</th>
                                <th>Fecha Entrada</th>
                                <th>Hora Entrada</th>
                                <th>Fecha Salida</th>
                                <th>Hora Salida</th>
                                <th>Horas</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mascotas_alojadas as $registro) : 
                                $entrada = new DateTime($registro['fecha_entrada'] . ' ' . $registro['hora_entrada']);
                                $salida = new DateTime($registro['fecha_salida'] . ' ' . $registro['hora_salida']);
                                $intervalo = $entrada->diff($salida);
                                $horas = ($intervalo->days * 24) + $intervalo->h;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($registro['nombre']) ?></td>
                                <td><?= date('d/m/Y', strtotime($registro['fecha_entrada'])) ?></td>
                                <td><?= $registro['hora_entrada'] ?></td>
                                <td><?= date('d/m/Y', strtotime($registro['fecha_salida'])) ?></td>
                                <td><?= $registro['hora_salida'] ?></td>
                                <td><?= $horas ?>h</td>
                                <td><?= $horas ?>€</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-success text-white">
                🛎️ Nueva Reserva
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mascota</label>
                            <select name="mascota_id" class="form-select" required>
                                <?php foreach ($mascotas as $mascota) : ?>
                                <option value="<?= $mascota['id_mascota'] ?>">
                                    <?= htmlspecialchars($mascota['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Fecha Entrada</label>
                            <input type="date" name="fecha_entrada" 
                                   class="form-control" 
                                   min="<?= date('Y-m-d') ?>" 
                                   required>
                            <div class="mt-2">
                                <label class="form-label">Hora Entrada</label>
                                <select name="hora_entrada" class="form-select" required>
                                    <?php for ($h = 8; $h <= 20; $h++): ?>
                                    <option value="<?= sprintf("%02d:00", $h) ?>">
                                        <?= $h ?>:00
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Fecha Salida</label>
                            <input type="date" name="fecha_salida" 
                                   class="form-control" 
                                   min="<?= date('Y-m-d') ?>" 
                                   required>
                            <div class="mt-2">
                                <label class="form-label">Hora Salida</label>
                                <select name="hora_salida" class="form-select" required>
                                    <?php for ($h = 9; $h <= 21; $h++): ?>
                                    <option value="<?= sprintf("%02d:00", $h) ?>">
                                        <?= $h ?>:00
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12 bg-light p-3 rounded">
                            <h5 class="mb-0">💰 Precio estimado: 
                                <span id="precioEstimado">-</span>
                            </h5>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" name="solicitar_guarderia" 
                                    class="btn btn-success w-100">
                                📅 Reservar Ahora
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
   
   <script src="JavaScript/guarderia.js"></script>
    
    
</body>
</html>

