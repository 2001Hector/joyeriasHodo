<?php
// Archivo: ver_personalizaciones.php
include_once __DIR__ . '/../crudP/conexion.php';

$query = "SELECT p.*, pr.nombre_Producto 
          FROM personalizaciones p
          JOIN productos pr ON p.id_producto = pr.id_producto
          ORDER BY p.id_personalizacion DESC";
$stmt = $conexionJ->prepare($query);
$stmt->execute();
$personalizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6922726226939700"
     crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizaciones</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navbar (copiar el mismo de index.php) -->
    
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Personalizaciones Registradas</h2>
            </div>
            
            <div class="p-6 overflow-x-auto">
                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        Personalización creada exitosamente!
                    </div>
                <?php endif; ?>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre Personalización</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($personalizaciones as $personalizacion): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $personalizacion['id_personalizacion'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($personalizacion['nombre_Producto']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($personalizacion['nombre_personalizacion']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars(substr($personalizacion['descripcion'], 0, 50)) ?>...</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="detalle_personalizacion.php?id=<?= $personalizacion['id_personalizacion'] ?>" 
                                   class="text-yellow-600 hover:text-yellow-800 mr-3">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>