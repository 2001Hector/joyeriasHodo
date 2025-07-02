<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$basedatos = "joyeria_db";

try {
    $conexionJ = new PDO("mysql:host=$servidor;dbname=$basedatos", $usuario, $contrasena);
    $conexionJ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>