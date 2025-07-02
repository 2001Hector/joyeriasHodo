<?php
session_start();
include_once __DIR__ . '/../crudP/conexion.php';

$productos = [];
$mensajeError = '';
$filtroCodigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';

// Mostrar mensajes flash de sesión
if(isset($_SESSION['flash_message'])) {
    $flashType = $_SESSION['flash_message']['type'];
    $flashMessage = $_SESSION['flash_message']['message'];
    
    echo '<div class="'.($flashType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 text-red-700').' px-4 py-3 mb-6 rounded">'
        .$flashMessage.
        '</div>';
    
    unset($_SESSION['flash_message']);
}

try {
    $sql = "SELECT * FROM productos";
    
    if(!empty($filtroCodigo)) {
        $sql .= " WHERE codigo_p LIKE :codigo";
    }
    
    $sql .= " ORDER BY id_producto DESC";
    
    $stmt = $conexionJ->prepare($sql);
    
    if(!empty($filtroCodigo)) {
        $paramCodigo = '%'.$filtroCodigo.'%';
        $stmt->bindParam(':codigo', $paramCodigo);
    }
    
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $mensajeError = "Error al cargar los productos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../src/output.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css"/>
  <title>Joyería hodo - Productos</title>
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
          <a href="#" class="text-white hover:text-yellow-200 font-medium transition duration-300">
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
      
      <!-- Menú móvil (oculto por defecto) -->
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

<!-- Contenido principal -->
<div class="container mx-auto px-4 py-8">

  <!-- Mensaje de error -->
  <?php if (!empty($mensajeError)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
      <?php echo htmlspecialchars($mensajeError); ?>
    </div>
  <?php endif; ?>

  <!-- Filtro y título -->
  <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-yellow-500">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <h2 class="text-2xl font-bold text-gray-800">Productos Registrados</h2>
      
      <div class="w-full md:w-auto">
        <form method="GET" class="flex gap-2">
          <input type="text" name="codigo" placeholder="Filtrar por código" 
                 class="flex-grow p-2 border rounded focus:ring-yellow-500 focus:border-yellow-500"
                 value="<?php echo htmlspecialchars($filtroCodigo); ?>">
          <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>
      
      <a href="index.php" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i> Nuevo Producto
      </a>
    </div>
  </div>

  <!-- Grid de productos -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php if (!empty($productos)): ?>
      <?php foreach ($productos as $producto): ?>
        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden flex flex-col h-full">
          <!-- Carrusel de imágenes -->
          <div class="product-images">
            <?php 
              $imagenes = explode(',', $producto['foto_producto']);
              $primeraImagen = !empty($imagenes[0]) ? $imagenes[0] : '';
            ?>
            <?php if (!empty($primeraImagen)): ?>
              <div class="slick-carousel">
                <?php foreach ($imagenes as $imagen): ?>
                  <?php if (!empty($imagen)): ?>
                    <div>
                      <img src="../uploads/<?php echo htmlspecialchars(trim($imagen)); ?>" 
                           alt="<?php echo htmlspecialchars($producto['nombre_Producto']); ?>" 
                           class="w-full h-48 object-cover">
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                <i class="fas fa-gem text-gray-400 text-4xl"></i>
              </div>
            <?php endif; ?>
          </div>
          
          <!-- Detalles del producto -->
          <div class="p-4 flex-grow flex flex-col">
            <div class="flex justify-between items-start mb-2">
              <h3 class="text-lg font-bold text-gray-800 truncate">
                <?php echo htmlspecialchars($producto['nombre_Producto']); ?>
              </h3>
              <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                <?php echo htmlspecialchars($producto['categoria_p']); ?>
              </span>
            </div>
            
            <div class="mb-2">
              <p class="text-sm text-gray-500"><?php echo htmlspecialchars($producto['codigo_p']); ?></p>
              <?php if (!empty($producto['descripcion_p'])): ?>
                <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars($producto['descripcion_p']); ?></p>
              <?php endif; ?>
            </div>
            
            <div class="mt-auto">
              <div class="flex justify-between items-center mb-3">
                <span class="text-lg font-bold text-gray-900">
                  $<?php echo number_format($producto['valor_p'], 2); ?>
                </span>
                <span class="text-sm text-gray-600">
                  <?php echo htmlspecialchars($producto['cantidad_p']); ?> en stock
                </span>
              </div>
              
              <!-- Acciones -->
              <div class="flex justify-between border-t pt-3">
                <a href="../crudP/vistaActualizar.php?id=<?php echo $producto['id_producto']; ?>" 
                   class="text-yellow-600 hover:text-yellow-800 flex items-center">
                  <i class="fas fa-edit mr-1"></i> Editar
                </a>
                <a href="../crudP/eliminar.php?id=<?php echo $producto['id_producto']; ?>" 
                   class="text-red-600 hover:text-red-800 flex items-center"
                   onclick="return confirm('¿Estás seguro de eliminar este producto?');">
                  <i class="fas fa-trash-alt mr-1"></i> Eliminar
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-span-full text-center py-10">
        <i class="fas fa-box-open text-4xl text-gray-400 mb-3"></i>
        <p class="text-gray-500 text-lg">No hay productos registrados<?php echo !empty($filtroCodigo) ? ' con ese código' : ''; ?>.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer class="bg-gray-800 text-white py-6 mt-12">
  <div class="container mx-auto px-4">
    <div class="flex flex-col md:flex-row justify-between items-center">
      <div class="mb-4 md:mb-0">
        <h3 class="text-xl font-bold">Joyería Shodo</h3>
        <p class="text-gray-400">Calidad y elegancia en cada pieza</p>
      </div>
      <div class="flex space-x-6">
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
    <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
      &copy; <?php echo date('Y'); ?> Joyería Shodo. Todos los derechos reservados.
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
  $(document).ready(function(){
    $('.slick-carousel').slick({
      dots: true,
      infinite: true,
      speed: 300,
      slidesToShow: 1,
      adaptiveHeight: true,
      autoplay: true,
      autoplaySpeed: 3000
    });
  });
</script>
</body>
</html>