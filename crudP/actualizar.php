<?php
include_once __DIR__ . '/conexion.php';

// Verificar si es una solicitud POST (envío del formulario)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que todos los campos requeridos estén presentes
    if(!isset($_POST['id_producto']) || empty($_POST['id_producto'])) {
        // Redirigir de vuelta al formulario con error
        header("Location: vistaActualizar.php?id=".$_POST['id_producto']."&error=ID de producto no especificado");
        exit();
    }

    // Recoger los datos del formulario
    $id_producto = $_POST['id_producto'];
    $datosActualizados = [
        'nombre' => $_POST['nombre_Producto'] ?? '',
        'descripcion' => $_POST['description_p'] ?? '',
        'valor' => $_POST['valor_p'] ?? 0,
        'cantidad' => $_POST['cantidad_p'] ?? 0,
        'categoria' => $_POST['categoria_p'] ?? '',
        'codigo' => $_POST['codigo_p'] ?? '',
        'diseno' => $_POST['diseño_p'] ?? '',
        'fecha' => $_POST['fecha_p'] ?? '',
        'motl' => $_POST['motl_p'] ?? '',
        'material' => $_POST['tipo_de_material'] ?? ''
    ];
    
    // Manejo de la imagen
    $foto_actual = $_POST['foto_actual'] ?? '';
    $nombreArchivo = $foto_actual; // Por defecto mantener la imagen actual
    
    // Si se subió una nueva imagen
    if(isset($_FILES['foto_producto']) && $_FILES['foto_producto']['error'] === UPLOAD_ERR_OK) {
        // Verificar que el directorio de uploads exista
        $directorioUploads = __DIR__ . '/../uploads/';
        if(!is_dir($directorioUploads)) {
            mkdir($directorioUploads, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($_FILES['foto_producto']['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '.' . $extension;
        $rutaDestino = $directorioUploads . $nombreArchivo;
        
        // Mover el archivo subido
        if(!move_uploaded_file($_FILES['foto_producto']['tmp_name'], $rutaDestino)) {
            header("Location: vistaActualizar.php?id=".$id_producto."&error=Error al subir la imagen");
            exit();
        }
        
        // Si había una imagen anterior y es diferente a la nueva, eliminarla
        if(!empty($foto_actual) && $foto_actual !== $nombreArchivo && file_exists($directorioUploads . $foto_actual)) {
            unlink($directorioUploads . $foto_actual);
        }
    }
    
    $datosActualizados['foto'] = $nombreArchivo;
    
    // Llamar a la función de actualización
    if(actualizarProducto($id_producto, $datosActualizados)) {
        // Redirigir de vuelta al formulario con mensaje de éxito
        header("Location: vistaActualizar.php?id=".$id_producto."&success=Producto actualizado correctamente");
        exit();
    } else {
        header("Location: vistaActualizar.php?id=".$id_producto."&error=Error al actualizar el producto");
        exit();
    }
}

function actualizarProducto($id_producto, $datosActualizados) {
    global $conexionJ;
    
    try {
        $sql = "UPDATE productos SET 
                nombre_Producto = :nombre, 
                description_p = :descripcion, 
                valor_p = :valor, 
                cantidad_p = :cantidad, 
                categoria_p = :categoria,
                codigo_p = :codigo,
                diseño_p = :diseno,
                fecha_p = :fecha,
                foto_producto = :foto,
                motl_p = :motl,
                tipo_de_material = :material
                WHERE id_producto = :id";
                
        $stmt = $conexionJ->prepare($sql);
        
        $stmt->bindParam(':id', $id_producto);
        $stmt->bindParam(':nombre', $datosActualizados['nombre']);
        $stmt->bindParam(':descripcion', $datosActualizados['descripcion']);
        $stmt->bindParam(':valor', $datosActualizados['valor']);
        $stmt->bindParam(':cantidad', $datosActualizados['cantidad']);
        $stmt->bindParam(':categoria', $datosActualizados['categoria']);
        $stmt->bindParam(':codigo', $datosActualizados['codigo']);
        $stmt->bindParam(':diseno', $datosActualizados['diseno']);
        $stmt->bindParam(':fecha', $datosActualizados['fecha']);
        $stmt->bindParam(':foto', $datosActualizados['foto']);
        $stmt->bindParam(':motl', $datosActualizados['motl']);
        $stmt->bindParam(':material', $datosActualizados['material']);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al actualizar producto: " . $e->getMessage());
        return false;
    }
}

// Si no es POST, redirigir
header("Location: ../ver_productos.php");
exit();
?>