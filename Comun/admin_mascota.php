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
$usuario_id = $_SESSION['user_id'];

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT nombre FROM clientes WHERE id_cliente = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Procesar registro de nueva mascota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'nombre' => $_POST['nombre'],
            'especie' => $_POST['especie'],
            'raza' => $_POST['raza'],
            'fecha_nacimiento' => $_POST['fecha_nacimiento'],
            'id_seguro' => !empty($_POST['id_seguro']) ? $_POST['id_seguro'] : NULL
        ];

        $stmt = $conexion->prepare("
            INSERT INTO mascotas 
            (nombre, especie, raza, fecha_nacimiento, id_cliente, id_seguro)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssii", 
            $data['nombre'],
            $data['especie'],
            $data['raza'],
            $data['fecha_nacimiento'],
            $usuario_id,
            $data['id_seguro']
        );
        
        if ($stmt->execute()) {
            $success = "Mascota registrada exitosamente!";
        }
        
    } catch (Exception $e) {
        $error = "Error al registrar: " . $e->getMessage();
    }
}

// Obtener mascotas del usuario
try {
    $stmt = $conexion->prepare("
        SELECT m.*, s.tipo_seguro 
        FROM mascotas m
        LEFT JOIN seguros s ON m.id_seguro = s.id_seguro
        WHERE m.id_cliente = ?
        ORDER BY m.id_mascota DESC
    ");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $mascotas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    $error = "Error al cargar mascotas: " . $e->getMessage();
}

// Obtener seguros disponibles
$seguros = $conexion->query("SELECT id_seguro, tipo_seguro FROM seguros")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mis Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin_mascotas.css" rel="stylesheet">
    <link rel="stylesheet" href="css/nav.css">
     <link rel="stylesheet" href="css/footer.css">
</head>
<body>
    

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
               <?php include 'nav.php'; ?>
        <!-- Formulario de Registro -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Registrar Nueva Mascota</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label>Especie</label>
                            <select name="especie" class="form-select" required>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Conejo">Conejo</option>
                                <option value="Hurón">Hurón</option>
                                <option value="Pájaro">Pájaro</option>
                                <option value="Tortuga">Tortuga</option>
                                <option value="Pez">Pez</option>
                                <option value="Reptil">Reptil</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label>Raza</label>
                            <input type="text" name="raza" class="form-control">
                        </div>
                        
                        <div class="col-md-3">
                            <label>Fecha Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label>Seguro</label>
                            <select name="id_seguro" class="form-select">
                                <option value="">Sin seguro</option>
                                <?php foreach ($seguros as $seguro): ?>
                                    <option value="<?= $seguro['id_seguro'] ?>">
                                        <?= $seguro['tipo_seguro'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary w-100">
                                Registrar Mascota
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Listado -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Especie</th>
                                <th>Raza</th>
                                <th>Fecha Nacimiento</th>
                                <th>Seguro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mascotas as $m): ?>
                                <tr>
                                    <td><?= $m['nombre'] ?></td>
                                    <td><?= $m['especie'] ?></td>
                                    <td><?= $m['raza'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($m['fecha_nacimiento'])) ?></td>
                                    <td><?= $m['tipo_seguro'] ?? 'Sin seguro' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
     <?php include 'footer.php'; ?>
</body>
</html>