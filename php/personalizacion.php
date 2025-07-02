<?php
// Incluir la conexión (sube un nivel porque crudP está fuera de php)
include_once __DIR__ . '/../crudP/conexion.php';

// Función para formatear moneda en formato colombiano
function formato_moneda_colombiano($monto) {
    return number_format($monto, 2, ',', '.');
}

// Obtener productos personalizables
$productos_personalizables = [];
try {
    $stmt = $conexionJ->query("SELECT * FROM productos WHERE personalizable = 'si' AND cantidad_p > 0");
    $productos_personalizables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatear precios para visualización
    foreach ($productos_personalizables as &$producto) {
        $producto['valor_formateado'] = formato_moneda_colombiano($producto['valor_p']);
        $producto['costo_personalizacion_formateado'] = formato_moneda_colombiano($producto['costo_personalizacion']);
    }
    unset($producto); // Romper la referencia
    
} catch(PDOException $e) {
    $error = "Error al obtener productos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personaliza tu joya - Joyería Hodo</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .personalizacion-option {
            transition: all 0.3s ease;
        }
        .personalizacion-option:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .personalizacion-option.selected {
            border: 3px solid #d97706;
            transform: scale(1.05);
        }
        .preview-personalizacion {
            min-height: 300px;
            background-color: #f9fafb;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Contenido principal -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">Personaliza tu joya</h1>
            <p class="text-gray-600 mb-8">Selecciona un producto y personalízalo a tu gusto. Los productos personalizados tienen un costo adicional.</p>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($productos_personalizables)): ?>
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    Actualmente no hay productos disponibles para personalizar.
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Lista de productos personalizables -->
                    <div class="md:col-span-1">
                        <h2 class="text-xl font-semibold text-gray-700 mb-4">Nuestros productos personalizables</h2>
                        <div class="space-y-4">
                            <?php foreach ($productos_personalizables as $producto): ?>
                                <div class="producto-item bg-white p-4 rounded-lg shadow cursor-pointer hover:shadow-md transition"
                                     data-id="<?= $producto['id_producto'] ?>"
                                     data-nombre="<?= htmlspecialchars($producto['nombre_Producto']) ?>"
                                     data-precio="<?= $producto['valor_p'] ?>"
                                     data-precio-formateado="<?= $producto['valor_formateado'] ?>"
                                     data-costo-personalizacion="<?= $producto['costo_personalizacion'] ?>"
                                     data-costo-personalizacion-formateado="<?= $producto['costo_personalizacion_formateado'] ?>"
                                     data-descripcion="<?= htmlspecialchars($producto['descripcion_personalizacion']) ?>"
                                     data-imagen-principal="<?= htmlspecialchars($producto['foto_producto']) ?>"
                                     data-imagen-1="<?= htmlspecialchars($producto['imagen_1']) ?>"
                                     data-imagen-2="<?= htmlspecialchars($producto['imagen_2']) ?>">
                                    <div class="flex items-center space-x-4">
                                        <img src="<?= htmlspecialchars($producto['foto_producto']) ?>" 
                                             alt="<?= htmlspecialchars($producto['nombre_Producto']) ?>" 
                                             class="w-16 h-16 object-cover rounded">
                                        <div>
                                            <h3 class="font-medium text-gray-800"><?= htmlspecialchars($producto['nombre_Producto']) ?></h3>
                                            <p class="text-sm text-gray-600">$<?= $producto['valor_formateado'] ?></p>
                                            <?php if ($producto['costo_personalizacion'] > 0): ?>
                                                <p class="text-xs text-yellow-600">+ $<?= $producto['costo_personalizacion_formateado'] ?> por personalización</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Área de personalización -->
                    <div class="md:col-span-2">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div id="sin-seleccion" class="text-center py-12">
                                <i class="fas fa-gem text-gray-300 text-5xl mb-4"></i>
                                <h3 class="text-xl font-medium text-gray-500">Selecciona un producto para personalizar</h3>
                                <p class="text-gray-400 mt-2">Elige uno de nuestros productos personalizables</p>
                            </div>

                            <div id="contenedor-personalizacion" class="hidden">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 id="nombre-producto" class="text-xl font-semibold text-gray-800"></h2>
                                    <div>
                                        <span class="text-gray-600">Precio base: </span>
                                        <span id="precio-base" class="font-medium"></span>
                                        <span id="costo-personalizacion" class="ml-2 text-yellow-600"></span>
                                    </div>
                                </div>

                                <!-- Preview de la personalización -->
                                <div class="preview-personalizacion mb-6 p-4 flex items-center justify-center">
                                    <img id="imagen-preview" src="" alt="Vista previa" class="max-h-64">
                                </div>

                                <!-- Opciones de personalización -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-700 mb-3">Opciones de personalización</h3>
                                    <p id="descripcion-personalizacion" class="text-gray-600 mb-4"></p>
                                    
                                    <div class="grid grid-cols-2 gap-4 mb-6">
                                        <div class="personalizacion-option p-4 border rounded-lg cursor-pointer text-center"
                                             data-tipo="imagen" data-valor="1">
                                            <img src="" id="opcion-imagen-1" class="w-full h-24 object-contain mb-2">
                                            <p class="text-sm font-medium">Opción 1</p>
                                        </div>
                                        <div class="personalizacion-option p-4 border rounded-lg cursor-pointer text-center"
                                             data-tipo="imagen" data-valor="2">
                                            <img src="" id="opcion-imagen-2" class="w-full h-24 object-contain mb-2">
                                            <p class="text-sm font-medium">Opción 2</p>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label for="texto-personalizado" class="block text-sm font-medium text-gray-700 mb-2">Texto personalizado (máx. 20 caracteres)</label>
                                        <input type="text" id="texto-personalizado" maxlength="20" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                    </div>

                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Color del metal</label>
                                        <div class="flex space-x-4">
                                            <div class="personalizacion-option color-option p-4 rounded-full cursor-pointer bg-yellow-300 border-2 border-transparent"
                                                 data-tipo="color" data-valor="oro" title="Oro"></div>
                                            <div class="personalizacion-option color-option p-4 rounded-full cursor-pointer bg-gray-300 border-2 border-transparent"
                                                 data-tipo="color" data-valor="plata" title="Plata"></div>
                                            <div class="personalizacion-option color-option p-4 rounded-full cursor-pointer bg-rose-300 border-2 border-transparent"
                                                 data-tipo="color" data-valor="rose" title="Oro Rosado"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resumen y total -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-lg font-medium text-gray-700 mb-3">Resumen de tu pedido</h3>
                                    <div class="space-y-2 mb-4">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Producto:</span>
                                            <span id="resumen-producto" class="font-medium"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Personalización:</span>
                                            <span id="resumen-personalizacion" class="font-medium">-</span>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                                            <span class="text-gray-600">Total:</span>
                                            <span id="resumen-total" class="font-bold text-lg"></span>
                                        </div>
                                    </div>

                                    <button id="btn-agregar-carrito" class="w-full bg-yellow-600 text-white py-3 rounded-md hover:bg-yellow-700 transition flex items-center justify-center">
                                        <i class="fas fa-shopping-cart mr-2"></i> Agregar al carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contenedorPersonalizacion = document.getElementById('contenedor-personalizacion');
            const sinSeleccion = document.getElementById('sin-seleccion');
            const nombreProducto = document.getElementById('nombre-producto');
            const precioBase = document.getElementById('precio-base');
            const costoPersonalizacion = document.getElementById('costo-personalizacion');
            const imagenPreview = document.getElementById('imagen-preview');
            const descripcionPersonalizacion = document.getElementById('descripcion-personalizacion');
            const opcionImagen1 = document.getElementById('opcion-imagen-1');
            const opcionImagen2 = document.getElementById('opcion-imagen-2');
            const resumenProducto = document.getElementById('resumen-producto');
            const resumenPersonalizacion = document.getElementById('resumen-personalizacion');
            const resumenTotal = document.getElementById('resumen-total');
            const btnAgregarCarrito = document.getElementById('btn-agregar-carrito');
            const textoPersonalizado = document.getElementById('texto-personalizado');
            
            let productoSeleccionado = null;
            let opcionesSeleccionadas = {
                imagen: null,
                color: null,
                texto: null
            };
            let costoTotal = 0;

            // Seleccionar producto
            document.querySelectorAll('.producto-item').forEach(item => {
                item.addEventListener('click', function() {
                    productoSeleccionado = {
                        id: this.dataset.id,
                        nombre: this.dataset.nombre,
                        precio: parseFloat(this.dataset.precio),
                        precioFormateado: this.dataset.precioFormateado,
                        costoPersonalizacion: parseFloat(this.dataset.costoPersonalizacion),
                        costoPersonalizacionFormateado: this.dataset.costoPersonalizacionFormateado,
                        descripcion: this.dataset.descripcion,
                        imagenPrincipal: this.dataset.imagenPrincipal,
                        imagen1: this.dataset.imagen1,
                        imagen2: this.dataset.imagen2
                    };

                    // Mostrar sección de personalización
                    sinSeleccion.classList.add('hidden');
                    contenedorPersonalizacion.classList.remove('hidden');

                    // Actualizar información del producto
                    nombreProducto.textContent = productoSeleccionado.nombre;
                    precioBase.textContent = `$${productoSeleccionado.precioFormateado}`;
                    
                    if (productoSeleccionado.costoPersonalizacion > 0) {
                        costoPersonalizacion.textContent = `(+ $${productoSeleccionado.costoPersonalizacionFormateado} por personalización)`;
                    } else {
                        costoPersonalizacion.textContent = '(Personalización incluida)';
                    }
                    
                    descripcionPersonalizacion.textContent = productoSeleccionado.descripcion || 'Personaliza este producto a tu gusto.';
                    imagenPreview.src = productoSeleccionado.imagenPrincipal;
                    
                    // Cargar opciones de imágenes
                    opcionImagen1.src = productoSeleccionado.imagen1 || productoSeleccionado.imagenPrincipal;
                    opcionImagen2.src = productoSeleccionado.imagen2 || productoSeleccionado.imagenPrincipal;
                    
                    // Actualizar resumen
                    resumenProducto.textContent = productoSeleccionado.nombre;
                    calcularTotal();
                });
            });

            // Seleccionar opciones de personalización
            document.querySelectorAll('.personalizacion-option').forEach(option => {
                option.addEventListener('click', function() {
                    const tipo = this.dataset.tipo;
                    const valor = this.dataset.valor;
                    
                    // Quitar selección anterior del mismo tipo
                    document.querySelectorAll(`.personalizacion-option[data-tipo="${tipo}"]`).forEach(el => {
                        el.classList.remove('selected');
                        if (el.classList.contains('color-option')) {
                            el.classList.remove('border-yellow-600');
                            el.classList.add('border-transparent');
                        }
                    });
                    
                    // Marcar como seleccionado
                    this.classList.add('selected');
                    if (this.classList.contains('color-option')) {
                        this.classList.remove('border-transparent');
                        this.classList.add('border-yellow-600');
                    }
                    
                    // Guardar selección
                    opcionesSeleccionadas[tipo] = valor;
                    
                    // Actualizar vista previa según selección
                    if (tipo === 'imagen') {
                        if (valor === '1') {
                            imagenPreview.src = productoSeleccionado.imagen1 || productoSeleccionado.imagenPrincipal;
                        } else {
                            imagenPreview.src = productoSeleccionado.imagen2 || productoSeleccionado.imagenPrincipal;
                        }
                    }
                    
                    // Actualizar resumen
                    actualizarResumenPersonalizacion();
                    calcularTotal();
                });
            });

            // Escuchar cambios en el texto personalizado
            textoPersonalizado.addEventListener('input', function() {
                opcionesSeleccionadas.texto = this.value.trim();
                actualizarResumenPersonalizacion();
                calcularTotal();
            });

            // Función para actualizar el resumen de personalización
            function actualizarResumenPersonalizacion() {
                let detalles = [];
                
                if (opcionesSeleccionadas.imagen) {
                    detalles.push(`Diseño: Opción ${opcionesSeleccionadas.imagen}`);
                }
                
                if (opcionesSeleccionadas.color) {
                    detalles.push(`Color: ${opcionesSeleccionadas.color.charAt(0).toUpperCase() + opcionesSeleccionadas.color.slice(1)}`);
                }
                
                if (opcionesSeleccionadas.texto && opcionesSeleccionadas.texto !== '') {
                    detalles.push(`Texto: "${opcionesSeleccionadas.texto}"`);
                }
                
                resumenPersonalizacion.textContent = detalles.join(', ') || '-';
            }

            // Función para calcular el total
            function calcularTotal() {
                costoTotal = productoSeleccionado.precio;
                
                // Agregar costo de personalización si hay al menos una opción seleccionada
                if (opcionesSeleccionadas.imagen || opcionesSeleccionadas.color || (opcionesSeleccionadas.texto && opcionesSeleccionadas.texto !== '')) {
                    costoTotal += productoSeleccionado.costoPersonalizacion;
                }
                
                // Formatear total en formato colombiano
                const formattedTotal = costoTotal.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                resumenTotal.textContent = `$${formattedTotal}`;
            }

            // Agregar al carrito
            btnAgregarCarrito.addEventListener('click', function() {
                if (!productoSeleccionado) return;
                
                // Validar que haya al menos una opción de personalización seleccionada
                if (!opcionesSeleccionadas.imagen && !opcionesSeleccionadas.color && (!opcionesSeleccionadas.texto || opcionesSeleccionadas.texto === '')) {
                    alert('Por favor selecciona al menos una opción de personalización');
                    return;
                }
                
                // Crear objeto con los datos del producto personalizado
                const productoPersonalizado = {
                    id: productoSeleccionado.id,
                    nombre: productoSeleccionado.nombre,
                    precio: productoSeleccionado.precio,
                    costo_personalizacion: productoSeleccionado.costoPersonalizacion,
                    total: costoTotal,
                    personalizacion: opcionesSeleccionadas
                };
                
                // Aquí normalmente enviarías los datos al servidor o los guardarías en el carrito
                console.log('Producto personalizado:', productoPersonalizado);
                
                // Mostrar mensaje de éxito
                alert('Producto personalizado agregado al carrito');
                
                // Opcional: Redirigir al carrito o a otra página
                // window.location.href = 'carrito.php';
            });
        });
    </script>
</body>
</html>