<?php
$servidor = "mysql.hostinger.com";
$usuario = "Hector_jose";
$contrasena = "2001Chamorro";
$basedatos = "u680910350_joyeriaHodo";

try {
    $conexionJ = new PDO("mysql:host=$servidor;dbname=$basedatos", $usuario, $contrasena);
    $conexionJ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>