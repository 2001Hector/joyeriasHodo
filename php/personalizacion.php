<?php
// Archivo: personalizacion.php
include_once __DIR__ . '/../crudP/conexion.php';

if (!isset($_GET['id_producto'])) {
    header("Location: ../php/index.php");
    exit();
}

$idProducto = $_GET['id_producto'];

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Insertar la personalización
    $query = "INSERT INTO personalizaciones (id_producto, nombre_personalizacion, descripcion) 
              VALUES (:id_producto, :nombre, :descripcion)";
    $stmt = $conexionJ->prepare($query);
    $stmt->bindParam(':id_producto', $idProducto);
    $stmt->bindParam(':nombre', $_POST['nombre_personalizacion']);
    $stmt->bindParam(':descripcion', $_POST['descripcion']);
    
    if ($stmt->execute()) {
        $idPersonalizacion = $conexionJ->lastInsertId();
        
        // Procesar imágenes de personalización
        if (!empty($_FILES['imagenes_personalizacion']['name'][0])) {
            $uploadDir = __DIR__ . '/../uploads/personalizaciones/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            foreach ($_FILES['imagenes_personalizacion']['name'] as $index => $name) {
                if ($_FILES['imagenes_personalizacion']['error'][$index] === UPLOAD_ERR_OK) {
                    $uniqueName = uniqid() . '_' . basename($name);
                    $uploadFile = $uploadDir . $uniqueName;
                    
                    if (move_uploaded_file($_FILES['imagenes_personalizacion']['tmp_name'][$index], $uploadFile)) {
                        // Insertar en la tabla imagenes_personalizacion
                        $queryImg = "INSERT INTO imagenes_personalizacion 
                                    (id_personalizacion, imagenP, descripcion_imagen) 
                                    VALUES (:id_personalizacion, :imagen, :descripcion)";
                        $stmtImg = $conexionJ->prepare($queryImg);
                        $stmtImg->bindParam(':id_personalizacion', $idPersonalizacion);
                        $stmtImg->bindParam(':imagen', $uniqueName);
                        $stmtImg->bindParam(':descripcion', $_POST['descripcion_imagen'][$index]);
                        $stmtImg->execute();
                    }
                }
            }
        }
        
        // Preguntar si desea agregar otra personalización
        if (isset($_POST['agregar_otra']) && $_POST['agregar_otra'] === 'Si') {
            // Recargar la página con el mismo id_producto para agregar otra
            header("Location: personalizacion.php?id_producto=" . $idProducto . "&success=1");
            exit();
        } else {
            // Redirigir al listado de productos
            header("Location: ../php/index.php?success_personalizacion=1");
            exit();
        }
    } else {
        $error = "Error al guardar la personalización.";
    }
}

// Obtener información del producto
$queryProducto = "SELECT * FROM productos WHERE id_producto = :id_producto";
$stmtProducto = $conexionJ->prepare($queryProducto);
$stmtProducto->bindParam(':id_producto', $idProducto);
$stmtProducto->execute();
$producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    header("Location: ../php/index.php");
    exit();
}

// Obtener las personalizaciones existentes para este producto
$queryPersonalizaciones = "SELECT * FROM personalizaciones WHERE id_producto = :id_producto";
$stmtPersonalizaciones = $conexionJ->prepare($queryPersonalizaciones);
$stmtPersonalizaciones->bindParam(':id_producto', $idProducto);
$stmtPersonalizaciones->execute();
$personalizaciones = $stmtPersonalizaciones->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6922726226939700"
     crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalización de Producto</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar (copiar el mismo de index.php) -->
    
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Personalización para: <?= htmlspecialchars($producto['nombre_Producto']) ?></h2>
                <p class="text-yellow-100 text-sm">Código: <?= htmlspecialchars($producto['codigo_p']) ?></p>
            </div>
            
            <div class="p-6">
                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Personalización guardada correctamente. Puedes agregar otra.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Mostrar personalizaciones existentes -->
                <?php if (count($personalizaciones) > 0): ?>
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Personalizaciones existentes:</h3>
                        <div class="space-y-4">
                            <?php foreach ($personalizaciones as $personalizacion): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-800"><?= htmlspecialchars($personalizacion['nombre_personalizacion']) ?></h4>
                                    <p class="text-gray-600"><?= nl2br(htmlspecialchars($personalizacion['descripcion'])) ?></p>
                                    <a href="detalle_personalizacion.php?id=<?= $personalizacion['id_personalizacion'] ?>" 
                                       class="inline-block mt-2 text-yellow-600 hover:text-yellow-800 text-sm">
                                        <i class="fas fa-eye mr-1"></i> Ver detalles
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Formulario para nueva personalización -->
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="id_producto" value="<?= $idProducto ?>">
                    
                    <div>
                        <label for="nombre_personalizacion" class="block text-gray-700 font-medium mb-2">Nombre de la Personalización</label>
                        <input type="text" id="nombre_personalizacion" name="nombre_personalizacion" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                    </div>
                    
                    <div>
                        <label for="descripcion" class="block text-gray-700 font-medium mb-2">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Imágenes de Personalización</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                            <input type="file" name="imagenes_personalizacion[]" id="imagenesPersonalizacion" 
                                   accept="image/*" multiple class="hidden">
                            <label for="imagenesPersonalizacion" class="cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-3xl text-yellow-500 mb-2"></i>
                                <p class="text-gray-600">Haz clic o arrastra imágenes de la personalización</p>
                                <p class="text-sm text-gray-400">Puedes subir múltiples imágenes</p>
                            </label>
                        </div>
                        
                        <div id="previewContainerPersonalizacion" class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Aquí aparecerán las previsualizaciones -->
                        </div>
                        
                        <div id="descripcionesContainer" class="mt-4 space-y-4">
                            <!-- Aquí aparecerán los campos para descripciones de imágenes -->
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 font-medium mb-2">¿Desea agregar otra personalización después de esta?</label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="agregar_otra" value="Si" class="text-yellow-600 focus:ring-yellow-500">
                                    <span class="ml-2">Sí</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="agregar_otra" value="No" checked class="text-yellow-600 focus:ring-yellow-500">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <a href="../php/index.php" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                                <i class="fas fa-arrow-left mr-2"></i> Volver sin guardar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition">
                                <i class="fas fa-save mr-2"></i> Guardar Personalización
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('imagenesPersonalizacion').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('previewContainerPersonalizacion');
            const descripcionesContainer = document.getElementById('descripcionesContainer');
            
            previewContainer.innerHTML = '';
            descripcionesContainer.innerHTML = '';
            
            if (this.files.length > 0) {
                Array.from(this.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            // Previsualización
                            const previewDiv = document.createElement('div');
                            previewDiv.className = 'relative group';
                            previewDiv.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded border border-gray-200">
                                <span class="absolute bottom-0 left-0 bg-black bg-opacity-50 text-white text-xs px-2 py-1 w-full">${file.name}</span>
                            `;
                            previewContainer.appendChild(previewDiv);
                            
                            // Campo de descripción
                            const descDiv = document.createElement('div');
                            descDiv.className = 'bg-gray-50 p-3 rounded';
                            descDiv.innerHTML = `
                                <label class="block text-gray-700 text-sm font-medium mb-1">Descripción para ${file.name}</label>
                                <input type="text" name="descripcion_imagen[]" class="w-full px-3 py-2 border border-gray-300 rounded">
                            `;
                            descripcionesContainer.appendChild(descDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>