<?php
include_once __DIR__ . '/conexion.php';

function crearProducto($datosProducto) {
    global $conexionJ;
    
    // Validación básica (opcional)
    if (!isset($datosProducto['nombre']) || empty($datosProducto['nombre'])) {
        throw new InvalidArgumentException("El nombre del producto es requerido");
    }

    try {
        $conexionJ->beginTransaction();
        
        // Insertar producto principal
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
                :personalizacionSN)";
        
        $stmt = $conexionJ->prepare($sql);
        
        $stmt->bindParam(':nombre', $datosProducto['nombre'], PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $datosProducto['descripcion'], PDO::PARAM_STR);
        $stmt->bindParam(':valor', $datosProducto['valor'], PDO::PARAM_STR);
        $stmt->bindParam(':cantidad', $datosProducto['cantidad'], PDO::PARAM_INT);
        $stmt->bindParam(':categoria', $datosProducto['categoria'], PDO::PARAM_STR);
        $stmt->bindParam(':codigo', $datosProducto['codigo'], PDO::PARAM_STR);
        $stmt->bindParam(':diseno', $datosProducto['diseno'], PDO::PARAM_STR);
        $stmt->bindParam(':fecha', $datosProducto['fecha'], PDO::PARAM_STR);
        $stmt->bindParam(':foto', $datosProducto['foto'], PDO::PARAM_STR);
        $stmt->bindParam(':motl', $datosProducto['motl'], PDO::PARAM_STR);
        $stmt->bindParam(':material', $datosProducto['material'], PDO::PARAM_STR);
        $stmt->bindParam(':personalizacionSN', $datosProducto['personalizacionSN'], PDO::PARAM_STR);
        
        $stmt->execute();
        $productoId = $conexionJ->lastInsertId();

        // Insertar personalizaciones si existen
        if (!empty($datosProducto['personalizaciones']) && is_array($datosProducto['personalizaciones'])) {
            $sqlPersonalizacion = "INSERT INTO personalizaciones (
                                  id_producto, 
                                  nombre, 
                                  descripcion, 
                                  imagen
                                  ) VALUES (
                                  :id_producto, 
                                  :nombre, 
                                  :descripcion, 
                                  :imagen)";
            
            $stmtPersonalizacion = $conexionJ->prepare($sqlPersonalizacion);

            foreach ($datosProducto['personalizaciones'] as $personalizacion) {
                if (empty($personalizacion['nombre']) || empty($personalizacion['imagen'])) {
                    continue; // O podrías lanzar una excepción
                }
                
                $stmtPersonalizacion->bindValue(':producto_id', $productoId, PDO::PARAM_INT);
                $stmtPersonalizacion->bindValue(':nombre', $personalizacion['nombre'], PDO::PARAM_STR);
                $stmtPersonalizacion->bindValue(':descripcion', $personalizacion['descripcion'] ?? '', PDO::PARAM_STR);
                $stmtPersonalizacion->bindValue(':imagen', $personalizacion['imagen'], PDO::PARAM_STR);
                $stmtPersonalizacion->execute();
            }
        }
        
        $conexionJ->commit();
        return $productoId; // Devuelve el ID del producto creado
        
    } catch(PDOException $e) {
        $conexionJ->rollBack();
        error_log("Error al crear producto: " . $e->getMessage());
        throw $e; // Relanza la excepción para manejo superior
    }
}
?>