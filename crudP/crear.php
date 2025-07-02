<?php
include_once __DIR__ . '/conexion.php';



function crearProducto($datosProducto) {
    global $conexionJ;
    
    try {
        $sql = "INSERT INTO productos (
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
                tipo_de_material
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
                :material)";
        
        $stmt = $conexionJ->prepare($sql);
        
        $stmt->bindParam(':nombre', $datosProducto['nombre']);
        $stmt->bindParam(':descripcion', $datosProducto['descripcion']);
        $stmt->bindParam(':valor', $datosProducto['valor']);
        $stmt->bindParam(':cantidad', $datosProducto['cantidad']);
        $stmt->bindParam(':categoria', $datosProducto['categoria']);
        $stmt->bindParam(':codigo', $datosProducto['codigo']);
        $stmt->bindParam(':diseno', $datosProducto['diseno']);
        $stmt->bindParam(':fecha', $datosProducto['fecha']);
        $stmt->bindParam(':foto', $datosProducto['foto']);
        $stmt->bindParam(':motl', $datosProducto['motl']);
        $stmt->bindParam(':material', $datosProducto['material']);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al crear producto: " . $e->getMessage());
        return false;
    }
}
?>