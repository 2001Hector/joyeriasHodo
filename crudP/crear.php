<?php
// Archivo: crear.php
include_once __DIR__ . '/conexion.php';

function crearProducto($datos) {
    global $conexionJ;
    
    try {
        $query = "INSERT INTO productos (
            nombre_Producto, 
            description_p, 
            valor_p, 
            cantidad_p, 
            categoria_p, 
            codigo_p, 
            diseño_p, 
            fecha_p, 
            foto_producto, 
            motl_p, 
            tipo_de_material,
            personalizacionSN
        ) VALUES (
            :nombre, 
            :descripcion, 
            :valor, 
            :cantidad, 
            :categoria, 
            :codigo, 
            :diseno, 
            :fecha, 
            :foto, 
            :motl, 
            :material,
            :personalizacion
        )";
        
        $stmt = $conexionJ->prepare($query);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':valor', $datos['valor']);
        $stmt->bindParam(':cantidad', $datos['cantidad']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':codigo', $datos['codigo']);
        $stmt->bindParam(':diseno', $datos['diseno']);
        $stmt->bindParam(':fecha', $datos['fecha']);
        $stmt->bindParam(':foto', $datos['foto']);
        $stmt->bindParam(':motl', $datos['motl']);
        $stmt->bindParam(':material', $datos['material']);
        $stmt->bindParam(':personalizacion', $datos['personalizacion']);
        
        if($stmt->execute()) {
            return $conexionJ->lastInsertId();
        }
        return false;
    } catch(PDOException $e) {
        error_log("Error al crear producto: " . $e->getMessage());
        return false;
    }
}
?>