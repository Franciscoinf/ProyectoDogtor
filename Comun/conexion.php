<?php
function conectar() {
    // Configuración de la conexión a la base de datos
    $host = "localhost";      
    $usuario = "root";        
    $contraseña = "";         
    $base_datos = "veterinaria"; 

    // Crear una nueva conexión a la base de datos
    $conexion = new mysqli($host, $usuario, $contraseña, $base_datos);

    // Verificar si hubo un error en la conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    return $conexion; 
}
?>
