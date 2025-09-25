<!DOCTYPE html>
<?php
// Incluir la conexión
include_once __DIR__ . '/../crudP/conexion.php';

// Función para generar código único
function generarCodigoUnico($conexion) {
    $anio = date('Y');
    
    do {
        $numero = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $codigo = 'JOV-'. $anio . '-'. $numero;

        $query = "SELECT COUNT(*) FROM productos WHERE codigo_p = :codigo";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

    } while ($existe > 0);

    return $codigo;
}

// Generar código único al cargar la página
$codigoUnico = generarCodigoUnico($conexionJ);

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../crudP/crear.php';
    
    // Procesar varias imágenes
    $fotosNombres = [];
    if (!empty($_FILES['fotos']['name'][0])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        foreach ($_FILES['fotos']['name'] as $index => $name) {
            if ($_FILES['fotos']['error'][$index] === UPLOAD_ERR_OK) {
                $uniqueName = uniqid() . '_' . basename($name);
                $uploadFile = $uploadDir . $uniqueName;
                if (move_uploaded_file($_FILES['fotos']['tmp_name'][$index], $uploadFile)) {
                    $fotosNombres[] = $uniqueName;
                }
            }
        }
    }
    
    $fotosTexto = implode(',', $fotosNombres);
    
    $datosProducto = [
        'nombre' => $_POST['nombre'],
        'descripcion' => $_POST['descripcion'],
        'valor' => $_POST['valor'],
        'cantidad' => $_POST['cantidad'],
        'categoria' => $_POST['categoria'],
        'codigo' => $_POST['codigo'],
        'diseno' => $_POST['diseno'],
        'fecha' => $_POST['fecha'],
        'foto' => $fotosTexto,
        'motl' => $_POST['motl'],
        'material' => $_POST['material'],
        'personalizacion' => $_POST['personalizacion'] ?? 'No'
    ];
    
    $idProducto = crearProducto($datosProducto);
    
    if ($idProducto) {
        if ($datosProducto['personalizacion'] === 'Si') {
            header("Location: personalizacion.php?id_producto=" . $idProducto);
            exit();
        } else {
            $mensajeExito = "Producto creado exitosamente!";
            $codigoUnico = generarCodigoUnico($conexionJ);
        }
    } else {
        $mensajeError = "Error al crear el producto.";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6922726226939700"
     crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Joyería hodo - Administración</title>
    <script>
        tailwind.config = {
          darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">

  <!-- Navbar -->
  <nav class="bg-gradient-to-r from-yellow-600 to-yellow-800 shadow-lg">
    <div class="container mx-auto px-4">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-4">
          <i class="fas fa-gem text-white text-2xl"></i>
          <span class="text-white font-bold text-xl">Joyería hodo</span>
        </div>
        
        <!-- Menú desktop -->
        <div class="hidden md:flex items-center gap">
          <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-home mr-2"></i>Inicio agregar producto
          </a>
          <a href="../php/ver_productos.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-eye mr-2"></i>Ver Productos
          </a>
          <a href="../php/hacerP.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 text-sm">
            <i class="fas fa-shopping-basket mr-2"></i>hacer Pedidos
          </a>
          <a href="../php/productos_generalesU.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-users mr-2"></i>Vista de clientes
          </a>
          <a href="../php/estados.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-truck mr-2"></i>Estado de pedidos
          </a>
          <a href="../php/ver_personalizaciones.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-paint-brush mr-2"></i>Personalizaciones
          </a>
        </div>
        
        <!-- Botón móvil -->
        <div class="md:hidden flex items-center">
          <button id="menu-btn" class="text-white focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
          </button>
        </div>
      </div>
      
      <!-- Menú móvil -->
      <div id="mobile-menu" class="hidden md:hidden pb-4">
        <div class="flex flex-col space-y-3 px-2 pt-2">
          <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-home mr-2"></i>Inicio agregar producto
          </a>
          <a href="../php/ver_productos.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-eye mr-2"></i>Ver Productos
          </a>
          <a href="../php/hacerP.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 text-sm">
            <i class="fas fa-shopping-basket mr-2"></i>hacer Pedidos
          </a>
          <a href="../php/productos_generalesU.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-users mr-2"></i>Vista de clientes
          </a>
          <a href="../php/estados.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-truck mr-2"></i>Estado de pedidos
          </a>
          <a href="../php/ver_personalizaciones.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
            <i class="fas fa-paint-brush mr-2"></i>Personalizaciones
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

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 py-8">
        <!-- Mensajes -->
        <?php if(isset($mensajeExito)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 animate-fade-in" role="alert">
                <span class="block sm:inline"><?php echo $mensajeExito; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <i class="fas fa-times-circle cursor-pointer" onclick="this.parentElement.parentElement.style.display='none'"></i>
                </span>
            </div>
        <?php endif; ?>
        
        <?php if(isset($mensajeError)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 animate-fade-in" role="alert">
                <span class="block sm:inline"><?php echo $mensajeError; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <i class="fas fa-times-circle cursor-pointer" onclick="this.parentElement.parentElement.style.display='none'"></i>
                </span>
            </div>
        <?php endif; ?>

        <!-- Tarjeta de bienvenida -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8 border-l-4 border-yellow-500">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Bienvenido al Panel de Administración</h2>
            <p class="text-gray-600">Gestiona tus productos de joyería y personalizaciones.</p>
        </div>

        <!-- Formulario para crear producto -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Agregar Nuevo Producto</h2>
            </div>
            <div class="p-6">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna izquierda -->
                        <div class="space-y-4">
                            <div>
                                <label for="nombre" class="block text-gray-700 font-medium mb-2">Nombre del Producto</label>
                                <input type="text" id="nombre" name="nombre" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="descripcion" class="block text-gray-700 font-medium mb-2">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="3" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition"></textarea>
                            </div>
                            
                            <div>
                                <label for="valor" class="block text-gray-700 font-medium mb-2">Valor ($)</label>
                                <input type="number" id="valor" name="valor" min="0" step="0.01" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="cantidad" class="block text-gray-700 font-medium mb-2">Cantidad Disponible</label>
                                <input type="number" id="cantidad" name="cantidad" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                        </div>
                        
                        <!-- Columna derecha -->
                        <div class="space-y-4">
                            <div>
                                <label for="categoria" class="block text-gray-700 font-medium mb-2">Categoría</label>
                                <select id="categoria" name="categoria" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                                    <option value="">Seleccione una categoría</option>
                                    <option value="anillos">Anillos</option>
                                    <option value="collares">Collares</option>
                                    <option value="pulseras">Pulseras</option>
                                    <option value="aretes">Aretes</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="codigo" class="block text-gray-700 font-medium mb-2">Código del Producto</label>
                                <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($codigoUnico); ?>" readonly
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition bg-gray-100 cursor-not-allowed">
                            </div>
                            
                            <div>
                                <label for="diseno" class="block text-gray-700 font-medium mb-2">Diseño</label>
                                <input type="text" id="diseno" name="diseno"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="fecha" class="block text-gray-700 font-medium mb-2">Fecha de Creación</label>
                                <input type="date" id="fecha" name="fecha" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="motl" class="block text-gray-700 font-medium mb-2">Modelo o Talla</label>
                                <input type="text" id="motl" name="motl"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="material" class="block text-gray-700 font-medium mb-2">Material</label>
                                <input type="text" id="material" name="material"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                            </div>
                            
                            <div>
                                <label for="personalizacion" class="block text-gray-700 font-medium mb-2">¿Requiere personalización?</label>
                                <select id="personalizacion" name="personalizacion" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                                    <option value="No">No</option>
                                    <option value="Si">Sí</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Campo de fotos -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-medium mb-2">Fotos del Producto</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                <input type="file" name="fotos[]" id="fileInput" accept="image/*" multiple 
                                       class="hidden">
                                <label for="fileInput" class="cursor-pointer">
                                    <i class="fas fa-cloud-upload-alt text-3xl text-yellow-500 mb-2"></i>
                                    <p class="text-gray-600" id="fileLabel">Haz clic o arrastra imágenes</p>
                                    <p class="text-sm text-gray-400">Formatos: JPG, PNG (Max. 5MB cada una)</p>
                                </label>
                            </div>
                            <div id="previewContainer" class="mt-4 flex flex-wrap gap-2">
                                <!-- Aquí se mostrarán las previsualizaciones -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-4 pt-4">
                        <button type="reset" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition">
                            Limpiar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-yellow-600 text-white font-medium rounded-lg hover:bg-yellow-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
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
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> Joyería hodo. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Script para el input de archivo -->
    <script>
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const fileLabel = document.getElementById('fileLabel');
            const previewContainer = document.getElementById('previewContainer');
            
            if (this.files.length > 0) {
                fileLabel.textContent = this.files.length > 1 
                    ? `${this.files.length} archivos seleccionados` 
                    : this.files[0].name;
                
                previewContainer.innerHTML = '';
                previewContainer.classList.remove('hidden');
                
                Array.from(this.files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const preview = document.createElement('div');
                            preview.className = 'w-24 h-24 relative group';
                            preview.innerHTML = `
                                <img src="${e.target.result}" alt="Preview" class="w-full h-full object-cover rounded border border-gray-200">
                                <button type="button" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            `;
                            previewContainer.appendChild(preview);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                fileLabel.textContent = 'Haz clic o arrastra imágenes';
                previewContainer.classList.add('hidden');
            }
        });
    </script>
</body>
</html>