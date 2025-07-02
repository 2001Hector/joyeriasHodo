<?php
include_once __DIR__ . '/conexion.php';

// Verificar si se recibió el ID del producto
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    try {
        $sql = "SELECT * FROM productos WHERE id_producto = :id";
        $stmt = $conexionJ->prepare($sql);
        $stmt->bindParam(':id', $id_producto);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$producto) {
            header("Location: /vistaActualizar.php?error=Producto no encontrado");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: /vistaActualizar.php?error=Error en la base de datos: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: /vistaActualizar.php?error=ID de producto no especificado");
    exit();
}

// Mostrar mensajes de éxito o error
$mensaje = '';
$tipoMensaje = '';

if(isset($_GET['success'])) {
    $mensaje = $_GET['success'];
    $tipoMensaje = 'success';
} elseif(isset($_GET['error'])) {
    $mensaje = $_GET['error'];
    $tipoMensaje = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Producto</title>
  <link rel="stylesheet" href="../src/output.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
 <!-- Agrega esto para el menú móvil -->
  <!-- Tailwind CSS -->

  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar mejorado con menú móvil -->
  <nav class="bg-gradient-to-r from-yellow-600 to-yellow-800 shadow-lg">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-4">
          <i class="fas fa-gem text-white text-2xl"></i>
          <span class="text-white font-bold text-xl">Joyería hodo</span>
        </div>
        
        <!-- Menú para desktop (visible en md y arriba) -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300">
            <i class="fas fa-home mr-2"></i>Inicio
          </a>
          <a href="../php/ver_productos.php" class="text-white hover:text-yellow-200 font-medium transition duration-300">
            <i class="fas fa-eye mr-2"></i>Ver Productos
          </a>
          <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300">
            <i class="fas fa-chart-line mr-2"></i>Reportes
          </a>
          <a href="#" class="text-white hover:text-yellow-200 font-medium transition duration-300">
            <i class="fas fa-cog mr-2"></i>Configuración
          </a>
        </div>
        
        <!-- Botón hamburguesa para móvil (visible en md abajo) -->
        <div class="md:hidden flex items-center">
          <button id="menu-btn" class="text-white focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
          </button>
        </div>
      </div>
      
      <!-- Menú móvil (oculto por defecto esto se guarda automaticamente si no esta en movil) -->
      <div id="mobile-menu" class="hidden md:hidden pb-4">
        <div class="flex flex-col space-y-3 px-2 pt-2">
          <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md">
            <i class="fas fa-home mr-2"></i>Inicio
          </a>
          <a href="../php/ver_productos.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md">
            <i class="fas fa-eye mr-2"></i>Ver Productos
          </a>
          <a href="#" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md">
            <i class="fas fa-chart-line mr-2"></i>Reportes
          </a>
          <a href="#" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md">
            <i class="fas fa-cog mr-2"></i>Configuración
          </a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Script para el menú móvil -->
  <script>
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      // Cambiar el ícono entre hamburguesa y X
      const icon = menuBtn.querySelector('i');
      if (mobileMenu.classList.contains('hidden')) {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
      } else {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
      }
    });
  </script>
<!-- asta ca termina el escrip para movil -->

<!-- Mostrar mensajes -->
<?php if(!empty($mensaje)): ?>
<div class="container mx-auto px-4 mt-4">
    <div class="p-4 rounded-md <?php echo $tipoMensaje === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'; ?>">
        <div class="flex">
            <div class="flex-shrink-0">
                <?php if($tipoMensaje === 'success'): ?>
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                <?php else: ?>
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                <?php endif; ?>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">
                    <?php echo htmlspecialchars($mensaje); ?>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Contenido principal -->
<div class="container mx-auto px-4 py-8">
  <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-yellow-500">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Producto</h2>
    
    <form action="actualizar.php" method="POST" enctype="multipart/form-data" class="space-y-6">
      <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($producto['id_producto']); ?>">
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Columna izquierda -->
        <div class="space-y-4">
          <div>
            <label for="nombre_Producto" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
            <input type="text" id="nombre_Producto" name="nombre_Producto" 
                   value="<?php echo htmlspecialchars($producto['nombre_Producto']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
          </div>
          
          <div>
            <label for="codigo_p" class="block text-sm font-medium text-gray-700">Código</label>
            <input type="text" id="codigo_p" name="codigo_p" 
                   value="<?php echo htmlspecialchars($producto['codigo_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
          </div>
          
          <div>
            <label for="categoria_p" class="block text-sm font-medium text-gray-700">Categoría</label>
            <select id="categoria_p" name="categoria_p" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
              <option value="Anillo" <?php echo $producto['categoria_p'] === 'Anillo' ? 'selected' : ''; ?>>Anillo</option>
              <option value="Collar" <?php echo $producto['categoria_p'] === 'Collar' ? 'selected' : ''; ?>>Collar</option>
              <option value="Pulsera" <?php echo $producto['categoria_p'] === 'Pulsera' ? 'selected' : ''; ?>>Pulsera</option>
              <option value="Aretes" <?php echo $producto['categoria_p'] === 'Aretes' ? 'selected' : ''; ?>>Aretes</option>
            </select>
          </div>
          
          <div>
            <label for="valor_p" class="block text-sm font-medium text-gray-700">Valor</label>
            <input type="number" id="valor_p" name="valor_p" step="0.01" min="0"
                   value="<?php echo htmlspecialchars($producto['valor_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
          </div>
          
          <div>
            <label for="cantidad_p" class="block text-sm font-medium text-gray-700">Cantidad</label>
            <input type="number" id="cantidad_p" name="cantidad_p" min="0"
                   value="<?php echo htmlspecialchars($producto['cantidad_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" required>
          </div>
        </div>
        
        <!-- Columna derecha -->
        <div class="space-y-4">
          <div>
            <label for="diseño_p" class="block text-sm font-medium text-gray-700">Diseño</label>
            <input type="text" id="diseño_p" name="diseño_p" 
                   value="<?php echo htmlspecialchars($producto['diseño_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
          </div>
          
          <div>
            <label for="fecha_p" class="block text-sm font-medium text-gray-700">Fecha</label>
            <input type="date" id="fecha_p" name="fecha_p" 
                   value="<?php echo htmlspecialchars($producto['fecha_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
          </div>
          
          <div>
            <label for="tipo_de_material" class="block text-sm font-medium text-gray-700">Tipo de Material</label>
            <input type="text" id="tipo_de_material" name="tipo_de_material" 
                   value="<?php echo htmlspecialchars($producto['tipo_de_material']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
          </div>
          
          <div>
            <label for="motl_p" class="block text-sm font-medium text-gray-700">Marca o Tipo de Lote</label>
            <input type="text" id="motl_p" name="motl_p" 
                   value="<?php echo htmlspecialchars($producto['motl_p']); ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
          </div>
          
          <div>
            <label for="foto_producto" class="block text-sm font-medium text-gray-700">Imagen del Producto</label>
            <input type="file" id="foto_producto" name="foto_producto" 
                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
            <?php if(!empty($producto['foto_producto'])): ?>
              <p class="mt-1 text-sm text-gray-500">Imagen actual: <?php echo htmlspecialchars($producto['foto_producto']); ?></p>
              <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($producto['foto_producto']); ?>">
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div>
        <label for="description_p" class="block text-sm font-medium text-gray-700">Descripción</label>
        <textarea id="description_p" name="description_p" rows="3" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"><?php echo htmlspecialchars($producto['description_p']); ?></textarea>
      </div>
      
      <div class="flex justify-end space-x-4">
        <a href="../php/ver_productos.php" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
          Cancelar
        </a>
        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md text-sm font-medium hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
          Guardar Cambios
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-12">
  <div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-center">
      <div class="mb-4 md:mb-0">
        <h3 class="text-xl font-bold">Joyería hodo</h3>
        <p class="text-gray-400">Calidad y elegancia en cada pieza</p>
      </div>
      <div class="flex space-x-6">
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
      </div>
    </div>
    <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
      &copy; <?php echo date('Y'); ?> Joyería hodo. Todos los derechos reservados.
    </div>
  </div>
</footer>

</body>
</html>