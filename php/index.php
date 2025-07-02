<!DOCTYPE html>
<?php
// Incluir la conexión (sube un nivel porque crudP está fuera de php)
include_once __DIR__ . '/../crudP/conexion.php';

// Función para generar código único
function generarCodigoUnico($conexion) {
    $anio = date('Y'); // Obtener el año actual
    
    do {
        $numero = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        $codigo = 'JOV-'. $anio . '-'. $numero;

        // Verificar que no exista en la base de datos
        $query = "SELECT COUNT(*) FROM productos WHERE codigo_p = :codigo";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

    } while ($existe > 0); // Repetir si ya existe

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
    
    // Unir nombres en un solo string separado por comas
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
        'personalizacionSN' => $_POST['tiene_personalizacion']
    ];
    
    // Procesar personalizaciones si existen
    $personalizaciones = [];
    if ($_POST['tiene_personalizacion'] === 'si' && isset($_POST['personalizacion_nombre']) && is_array($_POST['personalizacion_nombre'])) {
        foreach ($_POST['personalizacion_nombre'] as $index => $nombre) {
            if (!empty($nombre)) {
                $personalizacion = [
                    'nombre' => $nombre,
                    'descripcion' => $_POST['personalizacion_descripcion'][$index] ?? '',
                    'imagenes' => []
                ];
                
                // Procesar múltiples imágenes de personalización
                if (isset($_FILES['personalizacion_imagen']['name'][$index])) {
                    $uploadDir = __DIR__ . '/../uploads/personalizaciones/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Procesar cada imagen de esta personalización
                    foreach ($_FILES['personalizacion_imagen']['name'][$index] as $imgIndex => $imgName) {
                        if ($_FILES['personalizacion_imagen']['error'][$index][$imgIndex] === UPLOAD_ERR_OK) {
                            $uniqueName = uniqid() . '_' . basename($imgName);
                            $uploadFile = $uploadDir . $uniqueName;
                            if (move_uploaded_file($_FILES['personalizacion_imagen']['tmp_name'][$index][$imgIndex], $uploadFile)) {
                                $personalizacion['imagenes'][] = [
                                    'nombre' => $_POST['personalizacion_descripcion_imagen'][$index][$imgIndex] ?? '',
                                    'archivo' => $uniqueName
                                ];
                            }
                        }
                    }
                }
                
                $personalizaciones[] = $personalizacion;
            }
        }
    }
    
    // Agregar personalizaciones a los datos del producto
    $datosProducto['personalizaciones'] = $personalizaciones;
    
    if (crearProducto($datosProducto)) {
        $mensajeExito = "Producto creado exitosamente!";
        // Generar nuevo código para el próximo producto
        $codigoUnico = generarCodigoUnico($conexionJ);
    } else {
        $mensajeError = "Error al crear el producto.";
    }
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Joyería hodo - Administración</title>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        .personalizacion-imagen-item {
            transition: all 0.3s ease;
        }
        .personalizacion-imagen-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 py-8">
        <!-- Mensajes de éxito/error -->
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
                                    <option value="dijes">Dijes</option>
                                    <option value="broches">Broches</option>
                                    <option value="relojes">Relojes</option>
                                    <option value="piercings">Piercings</option>
                                    <option value="gemelos">Gemelos</option>
                                    <option value="tiaras">Tiaras</option>
                                    <option value="alfileres">Alfileres</option>
                                    <option value="joyeria_para_hombre">Joyería para hombre</option>
                                    <option value="joyeria_para_ninos">Joyería para niños</option>
                                    <option value="set_de_joyeria">Set de joyería</option>
                                    <option value="accesorios">Accesorios</option>
                                    <option value="cadenas">Cadenas</option>
                                    <option value="medallas">Medallas</option>
                                    <option value="charms">Charms</option>
                                    <option value="pendientes">Pendientes</option>
                                    <option value="joyas_personalizadas">Joyas personalizadas</option>
                                    <option value="joyeria_religiosa">Joyería religiosa</option>
                                    <option value="joyeria_artesanal">Joyería artesanal</option>
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
                        </div>
                        
                        <!-- Campo de fotos del producto -->
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
                        
                        <!-- Sección de personalización -->
                        <div class="md:col-span-2">
                            <div class="mb-4">
                                <label class="block text-gray-700 font-medium mb-2">¿Tiene personalización?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tiene_personalizacion" value="si" class="h-5 w-5 text-yellow-600" id="personalizacion_si">
                                        <span class="ml-2 text-gray-700">Sí</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tiene_personalizacion" value="no" class="h-5 w-5 text-yellow-600" checked id="personalizacion_no">
                                        <span class="ml-2 text-gray-700">No</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div id="personalizacionContainer" class="hidden space-y-6">
                                <!-- Contenedor de personalizaciones -->
                                <div id="personalizacionItemsContainer">
                                    <!-- Las personalizaciones se agregarán aquí -->
                                </div>
                                
                                <!-- Botón para agregar nueva personalización -->
                                <button type="button" id="agregarPersonalizacion" 
                                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Agregar nueva personalización
                                </button>
                                
                                <!-- Previsualización de personalizaciones -->
                                <div id="previewPersonalizaciones" class="mt-6 hidden">
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">Previsualización de Personalizaciones</h3>
                                    <div id="previewPersonalizacionesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <!-- Aquí se mostrarán las previsualizaciones -->
                                    </div>
                                </div>
                                
                                <!-- Botón para aplicar personalizaciones -->
                                <button type="button" id="aplicarPersonalizaciones" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center">
                                    <i class="fas fa-check mr-2"></i> Aplicar Personalizaciones
                                </button>
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

    <!-- Plantilla para nueva personalización (hidden) -->
    <template id="personalizacionTemplate">
        <div class="border border-gray-200 rounded-lg p-4 personalizacion-item bg-gray-50 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700">Personalización #<span class="personalizacion-number"></span></h3>
                <button type="button" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 transition flex items-center remove-personalizacion">
                    <i class="fas fa-trash mr-2"></i> Eliminar
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Nombre de la personalización</label>
                    <input type="text" name="personalizacion_nombre[]" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Descripción general</label>
                    <input type="text" name="personalizacion_descripcion[]" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                </div>
            </div>
            
            <!-- Sección de imágenes de personalización -->
            <div class="mt-4">
                <label class="block text-gray-700 font-medium mb-2">Imágenes de la personalización</label>
                
                <!-- Contenedor de imágenes para esta personalización -->
                <div class="personalizacion-imagenes-container space-y-4">
                    <!-- Las imágenes se agregarán aquí -->
                </div>
                
                <!-- Botón para agregar más imágenes -->
                <button type="button" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition flex items-center agregar-imagen-personalizacion">
                    <i class="fas fa-plus mr-2"></i> Agregar otra imagen
                </button>
            </div>
        </div>
    </template>

    <!-- Plantilla para nueva imagen de personalización (hidden) -->
    <template id="imagenPersonalizacionTemplate">
        <div class="border border-gray-200 rounded-lg p-4 personalizacion-imagen-item bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Descripción de la imagen</label>
                    <input type="text" name="personalizacion_descripcion_imagen[][][]" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Imagen</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                        <input type="file" name="personalizacion_imagen[][][]" accept="image/*" 
                               class="hidden personalizacion-imagen-input">
                        <label class="cursor-pointer personalizacion-imagen-label">
                            <i class="fas fa-cloud-upload-alt text-3xl text-yellow-500 mb-2"></i>
                            <p class="text-gray-600 personalizacion-imagen-text">Haz clic o arrastra una imagen</p>
                            <p class="text-sm text-gray-400">Formatos: JPG, PNG (Max. 5MB)</p>
                        </label>
                    </div>
                    <div class="personalizacion-imagen-preview mt-4 flex justify-center hidden">
                        <img src="#" alt="Preview" class="max-h-40 rounded border border-gray-200">
                        <button type="button" class="ml-2 px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 personalizacion-remove-imagen">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Scripts -->
    <script>
        // Previsualización de imágenes del producto
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
        
        // Mostrar/ocultar sección de personalización
        const personalizacionSi = document.getElementById('personalizacion_si');
        const personalizacionNo = document.getElementById('personalizacion_no');
        const personalizacionContainer = document.getElementById('personalizacionContainer');
        
        function togglePersonalizacion() {
            if (personalizacionSi.checked) {
                personalizacionContainer.classList.remove('hidden');
                // Si no hay personalizaciones, agregar una automáticamente
                if (document.querySelectorAll('.personalizacion-item').length === 0) {
                    agregarNuevaPersonalizacion();
                }
            } else {
                personalizacionContainer.classList.add('hidden');
            }
        }
        
        personalizacionSi.addEventListener('change', togglePersonalizacion);
        personalizacionNo.addEventListener('change', togglePersonalizacion);
        
        // Contador para personalizaciones
        let personalizacionCounter = 0;
        
        // Plantillas
        const personalizacionTemplate = document.getElementById('personalizacionTemplate');
        const imagenPersonalizacionTemplate = document.getElementById('imagenPersonalizacionTemplate');
        
        // Función para agregar nueva personalización
        function agregarNuevaPersonalizacion() {
            personalizacionCounter++;
            
            const clone = personalizacionTemplate.content.cloneNode(true);
            const personalizacionItem = clone.querySelector('.personalizacion-item');
            const personalizacionNumber = clone.querySelector('.personalizacion-number');
            
            personalizacionNumber.textContent = personalizacionCounter;
            
            // Configurar eventos para esta personalización
            setupPersonalizacionEvents(personalizacionItem);
            
            // Agregar al contenedor
            document.getElementById('personalizacionItemsContainer').appendChild(clone);
            
            // Mostrar botones de acción
            document.getElementById('aplicarPersonalizaciones').classList.remove('hidden');
            
            // Agregar una imagen por defecto
            agregarNuevaImagenPersonalizacion(personalizacionItem.querySelector('.personalizacion-imagenes-container'));
        }
        
        // Función para agregar nueva imagen a una personalización
        function agregarNuevaImagenPersonalizacion(contenedor) {
            const clone = imagenPersonalizacionTemplate.content.cloneNode(true);
            const imagenItem = clone.querySelector('.personalizacion-imagen-item');
            
            // Configurar eventos para esta imagen
            setupImagenPersonalizacionEvents(imagenItem);
            
            // Agregar al contenedor
            contenedor.appendChild(clone);
        }
        
        // Función para configurar eventos de personalización
        function setupPersonalizacionEvents(item) {
            // Botón para agregar más imágenes
            const agregarImagenBtn = item.querySelector('.agregar-imagen-personalizacion');
            const imagenesContainer = item.querySelector('.personalizacion-imagenes-container');
            
            agregarImagenBtn.addEventListener('click', () => {
                agregarNuevaImagenPersonalizacion(imagenesContainer);
            });
            
            // Botón para eliminar personalización
            const removeBtn = item.querySelector('.remove-personalizacion');
            removeBtn.addEventListener('click', function() {
                if (confirm('¿Estás seguro de eliminar esta personalización?')) {
                    item.remove();
                    updatePersonalizacionNumbers();
                    
                    // Ocultar botones si no hay personalizaciones
                    if (document.querySelectorAll('.personalizacion-item').length === 0) {
                        document.getElementById('aplicarPersonalizaciones').classList.add('hidden');
                        document.getElementById('previewPersonalizaciones').classList.add('hidden');
                    }
                }
            });
        }
        
        // Función para configurar eventos de imagen de personalización
        function setupImagenPersonalizacionEvents(item) {
            const fileInput = item.querySelector('.personalizacion-imagen-input');
            const fileLabel = item.querySelector('.personalizacion-imagen-label');
            const fileText = item.querySelector('.personalizacion-imagen-text');
            const preview = item.querySelector('.personalizacion-imagen-preview');
            const previewImg = preview.querySelector('img');
            const removeBtn = item.querySelector('.personalizacion-remove-imagen');
            
            // Asignar ID único al input y label
            const uniqueId = 'imagen-' + Date.now();
            fileInput.id = uniqueId;
            fileLabel.htmlFor = uniqueId;
            
            // Manejar selección de archivo
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    fileText.textContent = this.files[0].name;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Manejar eliminación de imagen
            removeBtn.addEventListener('click', function() {
                if (confirm('¿Estás seguro de eliminar esta imagen?')) {
                    item.remove();
                }
            });
        }
        
        // Función para actualizar números de personalización
        function updatePersonalizacionNumbers() {
            const items = document.querySelectorAll('.personalizacion-item');
            items.forEach((item, index) => {
                const title = item.querySelector('.personalizacion-number');
                if (title) {
                    title.textContent = index + 1;
                }
            });
            personalizacionCounter = items.length;
        }
        
        // Botón para agregar nueva personalización
        document.getElementById('agregarPersonalizacion').addEventListener('click', agregarNuevaPersonalizacion);
        
        // Botón para aplicar personalizaciones (previsualización)
        document.getElementById('aplicarPersonalizaciones').addEventListener('click', function() {
            const previewContainer = document.getElementById('previewPersonalizacionesContainer');
            previewContainer.innerHTML = '';
            
            document.querySelectorAll('.personalizacion-item').forEach((item, index) => {
                const nombre = item.querySelector('input[name="personalizacion_nombre[]"]').value;
                const descripcion = item.querySelector('input[name="personalizacion_descripcion[]"]').value;
                const imagenes = item.querySelectorAll('.personalizacion-imagen-preview img');
                
                if (nombre) {
                    const previewCard = document.createElement('div');
                    previewCard.className = 'bg-white rounded-lg shadow-md p-4 border border-gray-200';
                    previewCard.innerHTML = `
                        <h4 class="font-medium text-gray-800 mb-2">${nombre}</h4>
                        <p class="text-gray-600 text-sm mb-3">${descripcion}</p>
                        <div class="grid grid-cols-2 gap-2">
                    `;
                    
                    imagenes.forEach(img => {
                        if (img.src && !img.src.endsWith('#')) {
                            previewCard.innerHTML += `
                                <div class="border rounded p-2">
                                    <img src="${img.src}" alt="Preview" class="w-full h-24 object-contain">
                                </div>
                            `;
                        }
                    });
                    
                    previewCard.innerHTML += `</div>`;
                    previewContainer.appendChild(previewCard);
                }
            });
            
            // Mostrar la previsualización
            document.getElementById('previewPersonalizaciones').classList.remove('hidden');
        });
    </script>
</body>
</html>