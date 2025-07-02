<?php
include_once __DIR__ . '/conexion.php';

// Función para redireccionar con mensajes flash
function redirectWithMessage($type, $message, $redirectUrl) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $redirectUrl");
    exit();
}



if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    try {
        // 1. Obtener información del producto incluyendo nombre para el mensaje
        $sql_select = "SELECT nombre_Producto, foto_producto FROM productos WHERE id_producto = :id";
        $stmt_select = $conexionJ->prepare($sql_select);
        $stmt_select->bindParam(':id', $id_producto);
        $stmt_select->execute();
        $producto = $stmt_select->fetch(PDO::FETCH_ASSOC);
        
        if(!$producto) {
            redirectWithMessage('error', 'El producto no existe en la base de datos', '../php/ver_productos.php');
        }

        // 2. Eliminar imágenes asociadas
        if(!empty($producto['foto_producto'])) {
            $imagenes = explode(',', $producto['foto_producto']);
            $deletedImages = 0;
            
            foreach($imagenes as $imagen) {
                $ruta_imagen = __DIR__ . '/../uploads/' . trim($imagen);
                if(file_exists($ruta_imagen)) {
                    if(unlink($ruta_imagen)) {
                        $deletedImages++;
                    }
                }
            }
        }
        
        // 3. Eliminar el producto de la base de datos
        $sql_delete = "DELETE FROM productos WHERE id_producto = :id";
        $stmt_delete = $conexionJ->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $id_producto);
        
        if($stmt_delete->execute()) {
            $message = sprintf(
                "Producto <strong>'%s'</strong> eliminado exitosamente. %s",
                htmlspecialchars($producto['nombre_Producto']),
                isset($deletedImages) ? "($deletedImages imágenes eliminadas)" : ""
            );
            redirectWithMessage('success', $message, '../php/ver_productos.php');
        } else {
            redirectWithMessage('error', 'No se pudo completar la eliminación del producto', '../php/ver_productos.php');
        }
        
    } catch(PDOException $e) {
        error_log("Error al eliminar producto: " . $e->getMessage());
        redirectWithMessage('error', 'Ocurrió un error técnico al procesar la solicitud', '../php/ver_productos.php');
    }
} else {
    redirectWithMessage('error', 'No se especificó el producto a eliminar', '../php/ver_productos.php');
}
?>