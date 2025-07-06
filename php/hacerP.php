<?php
// Incluir la conexión
include_once __DIR__ . '/../crudP/conexion.php';

// Variables de estado
$paso = isset($_GET['paso']) ? (int)$_GET['paso'] : 1;
$id_usuario = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;

// Procesar Paso 1: Registro de Usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_usuario'])) {
    try {
        // Validar campos obligatorios
        if (empty($_POST['nombre_u']) || empty($_POST['correo_u']) || empty($_POST['numero_u'])) {
            throw new Exception("Todos los campos marcados con * son obligatorios");
        }

        // Recoger datos del formulario
        $datosUsuario = [
            'nombre_u' => htmlspecialchars(trim($_POST['nombre_u'])),
            'correo_u' => filter_var(trim($_POST['correo_u']), FILTER_SANITIZE_EMAIL),
            'direccion_u' => isset($_POST['direccion_u']) ? htmlspecialchars(trim($_POST['direccion_u'])) : null,
            'numero_u' => htmlspecialchars(trim($_POST['numero_u'])),
            'observacion_usuario' => isset($_POST['observacion_usuario']) ? htmlspecialchars(trim($_POST['observacion_usuario'])) : null
        ];

        // Validar formato de correo
        if (!filter_var($datosUsuario['correo_u'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no tiene un formato válido");
        }

        // Insertar usuario
        $stmt = $conexionJ->prepare("INSERT INTO usuarios 
                                    (nombre_u, correo_u, direccion_u, numero_u, observacion_usuario, fecha_u) 
                                    VALUES (:nombre_u, :correo_u, :direccion_u, :numero_u, :observacion_usuario, CURDATE())");
        $stmt->execute($datosUsuario);
        $id_usuario = $conexionJ->lastInsertId();

        // Redirigir al paso 2
        header("Location: hacerP.php?paso=2&id_usuario=$id_usuario");
        exit();

    } catch(PDOException $e) {
        $errorUsuario = "Error al guardar usuario: " . $e->getMessage();
    } catch(Exception $e) {
        $errorUsuario = $e->getMessage();
    }
}

// Procesar Paso 2: Creación de Pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_pedido'])) {
    try {
        // Validar que existan productos
        if (!isset($_POST['productos']) || !is_array($_POST['productos']) || count($_POST['productos']) === 0) {
            throw new Exception("No se han seleccionado productos para el pedido");
        }

        // Validar pago
        $pago_completo = isset($_POST['pago_completo']) && $_POST['pago_completo'] === '1';
        $monto_pagado = isset($_POST['monto_pagado']) ? (float)$_POST['monto_pagado'] : 0;
        $total_pedido = 0;

        // Calcular total del pedido
        foreach ($_POST['productos'] as $producto) {
            $total_pedido += (float)$producto['precio_d'] * (int)$producto['cantidad_d'];
        }

        // Validar monto pagado
        if ($pago_completo) {
            $monto_pagado = $total_pedido;
        } else {
            if ($monto_pagado <= 0 || $monto_pagado > $total_pedido) {
                throw new Exception("El monto pagado debe ser mayor que 0 y menor o igual al total del pedido");
            }
        }

        // Determinar estado según pago
         if ($pago_completo) {
            $monto_pagado = $total_pedido;
            $estado_pedido = 'Completado y pago completo el articulo';
        } else {
            if ($monto_pagado <= 0) {
                throw new Exception("El monto pagado debe ser mayor que 0");
            }
            
            if ($monto_pagado > $total_pedido) {
                throw new Exception("El monto pagado no puede ser mayor al total del pedido");
            }
            
            // Determinar estado según el monto pagado
            if ($monto_pagado >= ($total_pedido / 2)) {
                $estado_pedido = 'Completado y pago mitad del costo del articulo';
            } else {
                $estado_pedido = 'Solo cancelo una parte de articulo';
            }
        }

        // Iniciar transacción
        $conexionJ->beginTransaction();

        // Validar y preparar datos del pedido
        $datosPedido = [
            'id_usuario' => $id_usuario,
            'estado_pedido' => $estado_pedido,
            'fecha_pedidos' => htmlspecialchars(trim($_POST['fecha_pedidos'])),
            'pago_completo' => $pago_completo ? 1 : 0,
            'monto_pagado' => $monto_pagado,
            'total_pedido' => $total_pedido
        ];

        // Validar fecha
        if (!DateTime::createFromFormat('Y-m-d', $datosPedido['fecha_pedidos'])) {
            throw new Exception("La fecha del pedido no es válida");
        }

        // Insertar pedido
        $stmt = $conexionJ->prepare("INSERT INTO pedidos 
                                    (id_usuario, estado_pedido, fecha_pedidos, pago_completo, monto_pagado, total_pedido) 
                                    VALUES (:id_usuario, :estado_pedido, :fecha_pedidos, :pago_completo, :monto_pagado, :total_pedido)");
        $stmt->execute($datosPedido);
        $id_pedidos = $conexionJ->lastInsertId();

        // Procesar cada producto del pedido
        foreach ($_POST['productos'] as $producto) {
            // Validar datos del producto
            if (!isset($producto['id_producto'], $producto['cantidad_d'], $producto['precio_d'])) {
                throw new Exception("Datos del producto incompletos");
            }

            $id_producto = (int)$producto['id_producto'];
            $cantidad = (int)$producto['cantidad_d'];
            $precio = (float)$producto['precio_d'];

            if ($id_producto <= 0 || $cantidad <= 0 || $precio <= 0) {
                throw new Exception("Datos del producto inválidos");
            }

            // Verificar cantidad disponible
            $stmt = $conexionJ->prepare("SELECT cantidad_p FROM productos WHERE id_producto = :id_producto FOR UPDATE");
            $stmt->execute(['id_producto' => $id_producto]);
            $cantidad_disponible = $stmt->fetchColumn();

            if ($cantidad_disponible === false) {
                throw new Exception("Producto no encontrado (ID: $id_producto)");
            }

            // Si cantidad_p es NULL, tratarlo como 0
            $cantidad_disponible = $cantidad_disponible === null ? 0 : (int)$cantidad_disponible;

            if ($cantidad_disponible < $cantidad) {
                throw new Exception("No hay suficiente cantidad disponible para el producto ID: $id_producto (Disponible: $cantidad_disponible, Solicitado: $cantidad)");
            }

            // Insertar detalle del pedido
            $datosDetalle = [
                'id_pedidos' => $id_pedidos,
                'id_producto' => $id_producto,
                'cantidad_d' => $cantidad,
                'precio_d' => $precio
            ];

            $stmt = $conexionJ->prepare("INSERT INTO detalles_pedidos 
                                        (id_pedidos, id_producto, cantidad_d, precio_d) 
                                        VALUES (:id_pedidos, :id_producto, :cantidad_d, :precio_d)");
            $stmt->execute($datosDetalle);

            // Actualizar cantidad del producto (evitando valores negativos)
            $stmt = $conexionJ->prepare("UPDATE productos SET cantidad_p = GREATEST(0, cantidad_p - :cantidad) WHERE id_producto = :id_producto");
            $stmt->execute([
                'cantidad' => $cantidad,
                'id_producto' => $id_producto
            ]);
        }

        // Confirmar transacción
        $conexionJ->commit();

        // Redirigir a página de éxito
        header("Location: hacerP.php?paso=3&id_pedidos=$id_pedidos");
        exit();

    } catch(PDOException $e) {
        // Revertir transacción en caso de error
        if (isset($conexionJ) && $conexionJ->inTransaction()) {
            $conexionJ->rollBack();
        }
        $errorPedido = "Error al crear el pedido: " . $e->getMessage();
    } catch(Exception $e) {
        // Revertir transacción en caso de error
        if (isset($conexionJ) && $conexionJ->inTransaction()) {
            $conexionJ->rollBack();
        }
        $errorPedido = $e->getMessage();
    }
}

// Obtener productos para selección (incluyendo imagen)
$productos = [];
try {
    $stmt = $conexionJ->query("SELECT id_producto, codigo_p, nombre_Producto, valor_p as precio, cantidad_p, foto_producto FROM productos WHERE cantidad_p > 0 OR cantidad_p IS NULL ORDER BY nombre_Producto");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorProductos = "Error al obtener productos: " . $e->getMessage();
}

// Obtener clientes existentes (para el modal)
$clientes = [];
try {
    $stmt = $conexionJ->query("SELECT id_u, nombre_u, correo_u, numero_u FROM usuarios ORDER BY nombre_u");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errorClientes = "Error al obtener clientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hacer Pedido - Joyería Hodo</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .producto-preview {
            display: none;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }
        .producto-preview img {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 0.5rem;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .producto-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .bg-yellow-600 {
            background-color: #d97706;
        }
        .hover\:bg-yellow-700:hover {
            background-color: #b45309;
        }
        .from-yellow-600 {
            --tw-gradient-from: #d97706;
            --tw-gradient-to: rgba(217, 119, 6, 0);
            --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
        }
        .to-yellow-800 {
            --tw-gradient-to: #92400e;
        }
        #total-pedido {
            font-size: 1.25rem;
            font-weight: bold;
        }
        #monto-pagado-container {
            display: none;
        }
    </style>
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
                    <span class="text-white font-bold text-xl">Joyería Hodo</span>
                </div>
                
                <!-- Menú para desktop -->
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
                
                <!-- Botón hamburguesa para móvil -->
                <div class="md:hidden flex items-center">
                    <button id="menu-btn" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Menú móvil -->
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <div class="flex flex-col space-y-3 px-2 pt-2">
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

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 py-8">
        <!-- Paso 1: Registro de Cliente -->
        <?php if ($paso === 1): ?>
            <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center mb-6">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold mr-4">1</div>
                    <h2 class="text-2xl font-bold text-gray-800">Registro de Cliente</h2>
                </div>

                <?php if (isset($errorUsuario)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($errorUsuario) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="nombre_u" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo*</label>
                        <input type="text" id="nombre_u" name="nombre_u" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               value="<?= isset($_POST['nombre_u']) ? htmlspecialchars($_POST['nombre_u']) : '' ?>">
                    </div>
                    
                    <div>
                        <label for="correo_u" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico*</label>
                        <input type="email" id="correo_u" name="correo_u" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               value="<?= isset($_POST['correo_u']) ? htmlspecialchars($_POST['correo_u']) : '' ?>">
                    </div>
                    
                    <div>
                        <label for="direccion_u" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input type="text" id="direccion_u" name="direccion_u"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               value="<?= isset($_POST['direccion_u']) ? htmlspecialchars($_POST['direccion_u']) : '' ?>">
                    </div>
                    
                    <div>
                        <label for="numero_u" class="block text-sm font-medium text-gray-700 mb-1">Teléfono (WhatsApp)*</label>
                        <input type="text" id="numero_u" name="numero_u" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"
                               value="<?= isset($_POST['numero_u']) ? htmlspecialchars($_POST['numero_u']) : '' ?>">
                    </div>
                    
                    <div>
                        <label for="observacion_usuario" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <textarea id="observacion_usuario" name="observacion_usuario" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500"><?= isset($_POST['observacion_usuario']) ? htmlspecialchars($_POST['observacion_usuario']) : '' ?></textarea>
                    </div>
                    
                    <div class="flex justify-between pt-4">
                        <button type="submit" name="guardar_usuario" class="bg-yellow-600 text-white px-6 py-2 rounded-md hover:bg-yellow-700 transition flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar Cliente 
                        </button>
                        <button type="button" id="btn-cliente-existente" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition flex items-center">
                            <i class="fas fa-user-check mr-2"></i> Cliente ya existe
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modal para cliente existente -->
            <!-- Modal para cliente existente -->
<div id="modalCliente" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="modal-content bg-white rounded-lg shadow-lg w-full max-w-4xl mx-4" style="max-height: 90vh; display: flex; flex-direction: column;">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-800">Seleccionar Cliente Existente</h3>
            <span class="close text-gray-500 hover:text-gray-700 cursor-pointer text-2xl">&times;</span>
        </div>
        
        <div class="p-6 pt-0 flex-1 overflow-hidden flex flex-col">
            <!-- Contenedor del buscador fijo en la parte superior -->
            <div class="mb-4 sticky top-0 bg-white pt-2 pb-4 z-10">
                <input type="text" id="buscarCliente" placeholder="Buscar cliente..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 outline-none">
            </div>
            
            <!-- Contenedor de la tabla con scroll -->
            <div class="flex-1 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="listaClientes" class="bg-white divide-y divide-gray-200">
                        <?php 
                        // Configuración de paginación
                        $clientesPorPagina = 10;
                        $paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                        $totalClientes = count($clientes);
                        $totalPaginas = ceil($totalClientes / $clientesPorPagina);
                        $inicio = ($paginaActual - 1) * $clientesPorPagina;
                        $clientesPagina = array_slice($clientes, $inicio, $clientesPorPagina);
                        
                        foreach ($clientesPagina as $cliente): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($cliente['nombre_u']) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 truncate max-w-xs"><?= htmlspecialchars($cliente['correo_u']) ?></td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600 font-mono"><?= htmlspecialchars($cliente['numero_u'] ?? 'N/A') ?></td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button type="button" class="seleccionar-cliente bg-yellow-600 text-white px-3 py-1.5 rounded-md hover:bg-yellow-700 transition text-sm font-medium whitespace-nowrap" 
                                            data-id="<?= $cliente['id_u'] ?>">
                                        Seleccionar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación funcional que no cierra el modal -->
            <div class="px-6 py-4 border-t border-gray-200 bg-white sticky bottom-0">
                <div class="flex items-center justify-between">
                    <div class="hidden sm:block">
                        <p class="text-sm text-gray-700">
                            Mostrando <span class="font-medium"><?= $inicio + 1 ?></span> a <span class="font-medium"><?= min($inicio + $clientesPorPagina, $totalClientes) ?></span> de <span class="font-medium"><?= $totalClientes ?></span> resultados
                        </p>
                    </div>
                    <div class="flex-1 flex justify-between sm:justify-end">
                        <?php if ($paginaActual > 1): ?>
                            <button onclick="cambiarPagina(<?= $paginaActual - 1 ?>)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Anterior
                            </button>
                        <?php else: ?>
                            <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                                Anterior
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($paginaActual < $totalPaginas): ?>
                            <button onclick="cambiarPagina(<?= $paginaActual + 1 ?>)" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Siguiente
                            </button>
                        <?php else: ?>
                            <span class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                                Siguiente
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Números de página -->
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-center mt-4">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php 
                        // Mostrar números de página (máximo 5)
                        $inicioPaginas = max(1, min($paginaActual - 2, $totalPaginas - 4));
                        $finPaginas = min($totalPaginas, $inicioPaginas + 4);
                        
                        if ($inicioPaginas > 1): ?>
                            <button onclick="cambiarPagina(1)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                1
                            </button>
                            <?php if ($inicioPaginas > 2): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                    ...
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php for ($i = $inicioPaginas; $i <= $finPaginas; $i++): ?>
                            <button onclick="cambiarPagina(<?= $i ?>)" class="<?= $i == $paginaActual ? 'z-10 bg-yellow-50 border-yellow-500 text-yellow-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                        
                        <?php if ($finPaginas < $totalPaginas): ?>
                            <?php if ($finPaginas < $totalPaginas - 1): ?>
                                <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                    ...
                                </span>
                            <?php endif; ?>
                            <button onclick="cambiarPagina(<?= $totalPaginas ?>)" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <?= $totalPaginas ?>
                            </button>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Manejo del modal de clientes
    const modal = document.getElementById("modalCliente");
    const btn = document.getElementById("btn-cliente-existente");
    const span = document.getElementsByClassName("close")[0];
    const buscarCliente = document.getElementById("buscarCliente");
    const listaClientes = document.getElementById("listaClientes");
    
    // Abrir modal
    if (btn) {
        btn.onclick = function() {
            modal.style.display = "flex";
            document.body.style.overflow = "hidden"; // Evita scroll en el fondo
        }
    }
    
    // Cerrar modal
    if (span) {
        span.onclick = function() {
            modal.style.display = "none";
            document.body.style.overflow = "auto"; // Restaura scroll
        }
    }
    
    // Cerrar al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.style.overflow = "auto"; // Restaura scroll
        }
    }
    
    // Buscar clientes (en la página actual)
    if (buscarCliente) {
        buscarCliente.addEventListener('input', function() {
            const termino = this.value.toLowerCase();
            const filas = listaClientes.querySelectorAll('tr');
            
            filas.forEach(fila => {
                const textoFila = fila.textContent.toLowerCase();
                fila.style.display = textoFila.includes(termino) ? '' : 'none';
            });
        });
    }
    
    // Seleccionar cliente
    if (listaClientes) {
        listaClientes.addEventListener('click', function(e) {
            if (e.target.classList.contains('seleccionar-cliente')) {
                const idCliente = e.target.dataset.id;
                window.location.href = `hacerP.php?paso=2&id_usuario=${idCliente}`;
            }
        });
    }
    
    // Función para cambiar de página sin cerrar el modal
    function cambiarPagina(pagina) {
        const url = new URL(window.location.href);
        url.searchParams.set('pagina', pagina);
        
        // Mostrar loader
        const loader = document.createElement('div');
        loader.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loader.innerHTML = '<div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-yellow-500"></div>';
        document.body.appendChild(loader);
        
        // Usamos fetch para cargar los datos sin recargar la página
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Parseamos el HTML para extraer solo la tabla y paginación
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTable = doc.getElementById('listaClientes');
                const newPagination = doc.querySelector('.px-6.py-4.border-t');
                
                if (newTable && listaClientes) {
                    listaClientes.innerHTML = newTable.innerHTML;
                }
                
                if (newPagination) {
                    const currentPagination = document.querySelector('.px-6.py-4.border-t');
                    if (currentPagination) {
                        currentPagination.innerHTML = newPagination.innerHTML;
                    }
                }
                
                // Restaurar el valor de búsqueda
                if (buscarCliente) {
                    const currentSearch = buscarCliente.value;
                    if (currentSearch) {
                        const filas = listaClientes.querySelectorAll('tr');
                        filas.forEach(fila => {
                            const textoFila = fila.textContent.toLowerCase();
                            fila.style.display = textoFila.includes(currentSearch.toLowerCase()) ? '' : 'none';
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Error al cargar la página:', error);
                // Fallback: recargar la página normalmente
                window.location.href = url;
            })
            .finally(() => {
                // Ocultar loader
                if (loader.parentNode) {
                    loader.parentNode.removeChild(loader);
                }
            });
    }
</script>
        <?php endif; ?>
<!-- Paso 2: Creación de Pedido -->

        <!-- Paso 3: Creación de Pedido -->
        <?php if ($paso === 2 && $id_usuario): ?>
            <?php
            // Obtener datos del usuario
            $stmt = $conexionJ->prepare("SELECT * FROM usuarios WHERE id_u = ?");
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$usuario) {
                header("Location: hacerP.php?paso=1");
                exit();
            }
            ?>

            <div class="max-w-6xl mx-auto">
                <div class="flex items-center mb-6">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-yellow-600 text-white font-bold mr-4">2</div>
                    <h2 class="text-2xl font-bold text-gray-800">Creación de Pedido</h2>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Información del Cliente</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Nombre</p>
                                    <p class="font-medium"><?= htmlspecialchars($usuario['nombre_u']) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Correo</p>
                                    <p class="font-medium"><?= htmlspecialchars($usuario['correo_u']) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Teléfono</p>
                                    <p class="font-medium"><?= htmlspecialchars($usuario['numero_u'] ?? 'N/A') ?></p>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($usuario['numero_u'])): ?>
                            <a href="https://wa.me/57<?= preg_replace('/[^0-9]/', '', $usuario['numero_u']) ?>" 
                               target="_blank"
                               class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition flex items-center">
                                <i class="fab fa-whatsapp mr-2"></i> Contactar por WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (isset($errorPedido)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($errorPedido) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errorProductos)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?= htmlspecialchars($errorProductos) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="form-pedido" class="bg-white rounded-lg shadow-md p-6">
                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label for="fecha_pedidos" class="block text-sm font-medium text-gray-700 mb-1">Fecha del pedido*</label>
                            <input type="date" id="fecha_pedidos" name="fecha_pedidos" required
                                   value="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado del pedido*</label>
                            <div class="mt-1">
                                <input type="hidden" name="estado_pedido" id="estado-pedido-input" value="">
                                <p class="text-sm text-gray-900" id="estado-pedido-text">Se determinará automáticamente según el pago</p>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Productos del Pedido</h3>

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagen</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody id="productos-seleccionados" class="bg-white divide-y divide-gray-200">
                                <tr id="sin-productos">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay productos agregados</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right font-medium">Total del Pedido:</td>
                                    <td class="px-6 py-4 font-bold" id="total-pedido">$0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <h4 class="text-md font-semibold text-gray-700 mb-3">Agregar Producto</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <label for="buscar-producto" class="block text-sm font-medium text-gray-700 mb-1">Buscar por código*</label>
                                <input type="text" id="buscar-producto" placeholder="Ingrese código del producto"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            <div>
                                <label for="cantidad-producto" class="block text-sm font-medium text-gray-700 mb-1">Cantidad*</label>
                                <input type="number" id="cantidad-producto" min="1" value="1"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" id="agregar-producto" class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 transition w-full">
                                    <i class="fas fa-plus mr-2"></i> Agregar
                                </button>
                            </div>
                        </div>
                        
                        <!-- Lista de productos filtrados -->
                        <div id="lista-productos" class="mt-4 hidden">
                            <select id="select-producto" size="5" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <?php foreach ($productos as $producto): ?>
                                    <option value="<?= $producto['id_producto'] ?>" 
                                            data-precio="<?= $producto['precio'] ?>"
                                            data-codigo="<?= htmlspecialchars($producto['codigo_p']) ?>"
                                            data-nombre="<?= htmlspecialchars($producto['nombre_Producto']) ?>"
                                            data-cantidad_d="<?= $producto['cantidad_p'] ?? '' ?>"
                                            data-foto_producto="<?= htmlspecialchars($producto['foto_producto'] ?? '') ?>">
                                        <?= htmlspecialchars($producto['codigo_p']) ?> - <?= htmlspecialchars($producto['nombre_Producto']) ?> - $<?= number_format($producto['precio'], 2) ?>
                                        <?php if(isset($producto['cantidad_p'])): ?>
                                            (Disponible: <?= $producto['cantidad_p'] ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Sección de pago -->
                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <h4 class="text-md font-semibold text-gray-700 mb-3">Información de Pago</h4>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">¿El pago es completo?</label>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center">
                                    <input id="pago-completo-si" name="pago_completo" type="radio" value="1" 
                                           class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300" checked
                                           onchange="toggleMontoPagado(true)">
                                    <label for="pago-completo-si" class="ml-2 block text-sm text-gray-900">Sí, pago completo</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="pago-completo-no" name="pago_completo" type="radio" value="0" 
                                           class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300"
                                           onchange="toggleMontoPagado(false)">
                                    <label for="pago-completo-no" class="ml-2 block text-sm text-gray-900">No, pago parcial</label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="monto-pagado-container" class="mt-4">
                            <label for="monto-pagado" class="block text-sm font-medium text-gray-700 mb-1">Monto pagado*</label>
                            <input type="number" id="monto-pagado" name="monto_pagado" min="0" step="0.01"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <p class="mt-1 text-sm text-gray-500" id="saldo-pendiente-text"></p>
                        </div>
                    </div>

                    <div class="flex justify-between pt-4">
                        <a href="hacerP.php?paso=1" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i> Volver
                        </a>
                        <button type="submit" name="crear_pedido" id="crear-pedido" disabled
                                class="bg-yellow-600 text-white px-6 py-2 rounded-md hover:bg-yellow-700 transition flex items-center">
                            <i class="fas fa-check-circle mr-2"></i> Finalizar Pedido
                        </button>
                    </div>
                </form>
            </div>

            <!-- Plantilla para productos seleccionados (hidden) -->
            <template id="template-producto">
                <tr class="producto-item">
                    <td class="px-6 py-4">
                        <img src="" class="producto-img" alt="Imagen del producto">
                    </td>
                    <td class="px-6 py-4 producto-nombre"></td>
                    <td class="px-6 py-4 producto-precio"></td>
                    <td class="px-6 py-4 producto-cantidad"></td>
                    <td class="px-6 py-4 producto-subtotal"></td>
                    <td class="px-6 py-4">
                        <button type="button" class="eliminar-producto text-red-600 hover:text-red-800">
                            <i class="fas fa-trash-alt mr-1"></i> Eliminar
                        </button>
                    </td>
                    <input type="hidden" name="productos[][id_producto]" class="input-id">
                    <input type="hidden" name="productos[][cantidad_d]" class="input-cantidad">
                    <input type="hidden" name="productos[][precio_d]" class="input-precio">
                </tr>
            </template>

            <!-- Script para manejar productos del pedido -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const buscarProducto = document.getElementById('buscar-producto');
                    const listaProductos = document.getElementById('lista-productos');
                    const selectProducto = document.getElementById('select-producto');
                    const cantidadProducto = document.getElementById('cantidad-producto');
                    const btnAgregar = document.getElementById('agregar-producto');
                    const tbodyProductos = document.getElementById('productos-seleccionados');
                    const sinProductos = document.getElementById('sin-productos');
                    const btnCrearPedido = document.getElementById('crear-pedido');
                    const template = document.getElementById('template-producto');
                    const totalPedidoElement = document.getElementById('total-pedido');
                    const estadoPedidoInput = document.getElementById('estado-pedido-input');
                    const estadoPedidoText = document.getElementById('estado-pedido-text');
                    const montoPagadoInput = document.getElementById('monto-pagado');
                    const saldoPendienteText = document.getElementById('saldo-pendiente-text');
                    
                    let productos = [];
                    let totalPedido = 0;
                    
                    // Buscar productos por código o nombre
                    buscarProducto.addEventListener('input', function() {
                        const termino = this.value.toLowerCase();
                        const opciones = selectProducto.options;
                        
                        if (termino.length >= 1) {  // Cambiado a 1 carácter mínimo para búsqueda
                            listaProductos.classList.remove('hidden');
                            
                            for (let i = 0; i < opciones.length; i++) {
                                const codigo = opciones[i].dataset.codigo.toLowerCase();
                                const nombre = opciones[i].dataset.nombre.toLowerCase();
                                if (codigo.includes(termino) || nombre.includes(termino)) {
                                    opciones[i].style.display = '';
                                    // Seleccionar automáticamente el primer resultado que coincida
                                    if (opciones[i].style.display !== 'none' && selectProducto.selectedIndex === -1) {
                                        selectProducto.selectedIndex = i;
                                    }
                                } else {
                                    opciones[i].style.display = 'none';
                                }
                            }
                            
                            // Si solo hay una opción visible, seleccionarla automáticamente
                            const visibleOptions = Array.from(opciones).filter(opt => opt.style.display !== 'none');
                            if (visibleOptions.length === 1) {
                                selectProducto.selectedIndex = Array.from(opciones).indexOf(visibleOptions[0]);
                            }
                        } else {
                            listaProductos.classList.add('hidden');
                            selectProducto.selectedIndex = -1;
                        }
                    });

                    // Seleccionar producto al hacer clic en la lista
                    selectProducto.addEventListener('click', function(e) {
                        if (e.target.tagName === 'OPTION') {
                            buscarProducto.value = e.target.dataset.codigo;
                            listaProductos.classList.add('hidden');
                        }
                    });

                    // Agregar producto al pedido
                    btnAgregar.addEventListener('click', function() {
                        const codigo = buscarProducto.value.trim();
                        const cantidad = parseInt(cantidadProducto.value);
                        
                        if (!codigo || isNaN(cantidad) || cantidad <= 0) {
                            alert('Por favor ingrese un código válido que contengan prodcutos');
                            return;
                        }
                        
                        // Buscar el producto seleccionado en el select
                        const selectedOption = selectProducto.options[selectProducto.selectedIndex];
                        
                        if (!selectedOption || selectedOption.style.display === 'none') {
                            alert('Por favor seleccione un producto válido de la lista');
                            return;
                        }
                        
                        const productoSeleccionado = {
                            id: selectedOption.value,
                            codigo: selectedOption.dataset.codigo,
                            nombre: selectedOption.dataset.nombre,
                            precio: parseFloat(selectedOption.dataset.precio),
                            cantidad_disponible: selectedOption.dataset.cantidad_d ? parseInt(selectedOption.dataset.cantidad_d) : null,
                            foto: selectedOption.dataset.foto_producto || '../assets/img/sin-imagen.jpg',
                            cantidad: cantidad
                        };
                        
                        // Validar cantidad disponible
                        if (productoSeleccionado.cantidad_disponible !== null && productoSeleccionado.cantidad > productoSeleccionado.cantidad_disponible) {
                            alert(`No hay suficiente cantidad disponible. Disponible: ${productoSeleccionado.cantidad_disponible}`);
                            return;
                        }
                        
                        // Verificar si el producto ya está agregado
                        const productoExistenteIndex = productos.findIndex(p => p.id === productoSeleccionado.id);
                        
                        if (productoExistenteIndex >= 0) {
                            // Actualizar cantidad si ya existe
                            productos[productoExistenteIndex].cantidad += cantidad;
                        } else {
                            // Agregar nuevo producto
                            productos.push(productoSeleccionado);
                        }
                        
                        actualizarListaProductos();
                        
                        // Limpiar campos
                        buscarProducto.value = '';
                        cantidadProducto.value = '1';
                        listaProductos.classList.add('hidden');
                        selectProducto.selectedIndex = -1;
                    });

                    // Actualizar la lista visual de productos
                    function actualizarListaProductos() {
                        // Limpiar tabla
                        tbodyProductos.innerHTML = '';
                        
                        if (productos.length === 0) {
                            tbodyProductos.appendChild(sinProductos);
                            btnCrearPedido.disabled = true;
                            totalPedido = 0;
                            actualizarTotalPedido();
                            return;
                        }
                        
                        // Habilitar botón de crear pedido
                        btnCrearPedido.disabled = false;
                        
                        // Calcular total del pedido
                        totalPedido = productos.reduce((sum, producto) => sum + (producto.precio * producto.cantidad), 0);
                        
                        // Actualizar total en la interfaz
                        actualizarTotalPedido();
                        
                        // Agregar cada producto a la tabla
                        productos.forEach((producto, index) => {
                            const clone = template.content.cloneNode(true);
                            const row = clone.querySelector('tr');
                            
                            // Configurar datos visuales
                            const img = clone.querySelector('.producto-img');
                            img.src = producto.foto;
                            img.alt = producto.nombre;
                            img.onerror = function() {
                                this.src = '../uploads/pr.png'; // Imagen por defecto si falla la carga
                            };
                            
                            clone.querySelector('.producto-nombre').textContent = producto.nombre;
                            clone.querySelector('.producto-precio').textContent = `$${producto.precio.toFixed(2)}`;
                            clone.querySelector('.producto-cantidad').textContent = producto.cantidad;
                            clone.querySelector('.producto-subtotal').textContent = `$${(producto.precio * producto.cantidad).toFixed(2)}`;
                            
                            // Configurar inputs hidden
                            clone.querySelector('.input-id').value = producto.id;
                            clone.querySelector('.input-id').name = `productos[${index}][id_producto]`;
                            
                            clone.querySelector('.input-cantidad').value = producto.cantidad;
                            clone.querySelector('.input-cantidad').name = `productos[${index}][cantidad_d]`;
                            
                            clone.querySelector('.input-precio').value = producto.precio;
                            clone.querySelector('.input-precio').name = `productos[${index}][precio_d]`;
                            
                            // Configurar botón eliminar
                            const btnEliminar = clone.querySelector('.eliminar-producto');
                            btnEliminar.addEventListener('click', () => {
                                productos = productos.filter(p => p.id !== producto.id);
                                actualizarListaProductos();
                            });
                            
                            tbodyProductos.appendChild(clone);
                        });
                    }
                    
                    // Actualizar el total del pedido en la interfaz
                    function actualizarTotalPedido() {
                        totalPedidoElement.textContent = `$${totalPedido.toFixed(2)}`;
                        
                        // Actualizar monto máximo permitido para pago parcial
                        if (montoPagadoInput) {
                            montoPagadoInput.max = totalPedido;
                            
                            // Si ya hay un monto ingresado, actualizar el saldo pendiente
                            if (montoPagadoInput.value) {
                                const montoPagado = parseFloat(montoPagadoInput.value);
                                const saldoPendiente = totalPedido - montoPagado;
                                saldoPendienteText.textContent = `Saldo pendiente: $${saldoPendiente.toFixed(2)}`;
                            }
                        }
                    }
                    
                    // Permitir agregar producto con Enter
                    buscarProducto.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            btnAgregar.click();
                        }
                    });

                    // Prevenir envío del formulario si no hay productos
                    document.getElementById('form-pedido').addEventListener('submit', function(e) {
                        if (productos.length === 0) {
                            e.preventDefault();
                            alert('Debe agregar al menos un producto al pedido');
                            return;
                        }
                        
                        // Validar monto pagado si es pago parcial
                        const pagoCompleto = document.querySelector('input[name="pago_completo"]:checked').value === '1';
                        
                        if (!pagoCompleto) {
                            const montoPagado = parseFloat(document.getElementById('monto-pagado').value);
                            
                            if (isNaN(montoPagado) || montoPagado <= 0 || montoPagado > totalPedido) {
                                e.preventDefault();
                                alert('El monto pagado debe ser mayor que 0 y menor o igual al total del pedido');
                                return;
                            }
                        }
                        
                        // Establecer el estado del pedido según el pago
                        const estadoPedido = pagoCompleto ? 
                            'Completado y pago completo el articulo' : 
                            'Completado y pago mitad del costo del articulo';
                            
                        document.getElementById('estado-pedido-input').value = estadoPedido;
                    });
                    
                    // Manejar cambios en el monto pagado
                    if (montoPagadoInput) {
                        montoPagadoInput.addEventListener('input', function() {
                            const montoPagado = parseFloat(this.value) || 0;
                            const saldoPendiente = totalPedido - montoPagado;
                            saldoPendienteText.textContent = `Saldo pendiente: $${saldoPendiente.toFixed(2)}`;
                        });
                    }
                });
                
                // Función para mostrar/ocultar campo de monto pagado
                function toggleMontoPagado(pagoCompleto) {
                    const montoPagadoContainer = document.getElementById('monto-pagado-container');
                    const estadoPedidoText = document.getElementById('estado-pedido-text');
                    
                    if (pagoCompleto) {
                        montoPagadoContainer.style.display = 'none';
                        estadoPedidoText.textContent = 'Completado y pago completo el articulo';
                    } else {
                        montoPagadoContainer.style.display = 'block';
                        estadoPedidoText.textContent = 'Completado y pago mitad del costo del articulo';
                        
                        // Actualizar saldo pendiente
                        const totalPedido = parseFloat(document.getElementById('total-pedido').textContent.replace('$', '')) || 0;
                        const montoPagado = parseFloat(document.getElementById('monto-pagado').value) || 0;
                        const saldoPendiente = totalPedido - montoPagado;
                        document.getElementById('saldo-pendiente-text').textContent = `Saldo pendiente: $${saldoPendiente.toFixed(2)}`;
                    }
                }
            </script>
        <?php endif; ?>

        <!-- Paso 3: Confirmación de Pedido -->
        <?php if ($paso === 3 && isset($_GET['id_pedidos'])): ?>
            <?php
            $id_pedidos = (int)$_GET['id_pedidos'];
            
            // Obtener información del pedido
            $stmt = $conexionJ->prepare("
                SELECT p.*, u.nombre_u, u.correo_u, u.numero_u, u.direccion_u, u.observacion_usuario 
                FROM pedidos p
                JOIN usuarios u ON p.id_usuario = u.id_u
                WHERE p.id_pedidos = ?
            ");
            $stmt->execute([$id_pedidos]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                header("Location: hacerP.php?paso=1");
                exit();
            }
            
            // Obtener detalles del pedido
            $stmt = $conexionJ->prepare("
                SELECT d.*, pr.nombre_Producto, pr.foto_producto 
                FROM detalles_pedidos d
                JOIN productos pr ON d.id_producto = pr.id_producto
                WHERE d.id_pedidos = ?
            ");
            $stmt->execute([$id_pedidos]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular total
            $total = 0;
            foreach ($detalles as $detalle) {
                $total += $detalle['precio_d'] * $detalle['cantidad_d'];
            }
            ?>
            
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">¡Pedido creado con éxito!</h2>
                    <p class="text-gray-600">El pedido #<?= $id_pedidos ?> ha sido registrado correctamente.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Información del Pedido</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Número de Pedido</p>
                                <p class="font-medium">#<?= $id_pedidos ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fecha</p>
                                <p class="font-medium"><?= date('d/m/Y', strtotime($pedido['fecha_pedidos'])) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Estado</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['estado_pedido']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="font-medium text-xl">$<?= number_format($pedido['total_pedido'], 2) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Monto Pagado</p>
                                <p class="font-medium">$<?= number_format($pedido['monto_pagado'], 2) ?></p>
                            </div>
                            <?php if (!$pedido['pago_completo']): ?>
                            <div>
                                <p class="text-sm text-gray-500">Saldo Pendiente</p>
                                <p class="font-medium">$<?= number_format($pedido['total_pedido'] - $pedido['monto_pagado'], 2) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Información del Cliente</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Nombre</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['nombre_u']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Correo</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['correo_u']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Teléfono</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['numero_u']) ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Dirección</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['direccion_u']) ?></p>
                            </div>
                            <?php if (!empty($pedido['observacion_usuario'])): ?>
                            <div>
                                <p class="text-sm text-gray-500">Observación</p>
                                <p class="font-medium"><?= htmlspecialchars($pedido['observacion_usuario']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Productos</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($detalles as $detalle): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full object-cover" src="<?= htmlspecialchars($detalle['foto_producto'] ?? '../assets/img/sin-imagen.jpg') ?>" alt="<?= htmlspecialchars($detalle['nombre_Producto']) ?>">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($detalle['nombre_Producto']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            $<?= number_format($detalle['precio_d'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= $detalle['cantidad_d'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            $<?= number_format($detalle['precio_d'] * $detalle['cantidad_d'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right font-medium text-gray-500">Total</td>
                                    <td class="px-6 py-4 font-bold">$<?= number_format($pedido['total_pedido'], 2) ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right font-medium text-gray-500">Monto Pagado</td>
                                    <td class="px-6 py-4">$<?= number_format($pedido['monto_pagado'], 2) ?></td>
                                </tr>
                                <?php if (!$pedido['pago_completo']): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right font-medium text-gray-500">Saldo Pendiente</td>
                                    <td class="px-6 py-4">$<?= number_format($pedido['total_pedido'] - $pedido['monto_pagado'], 2) ?></td>
                                </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
                
                <div class="flex justify-between pt-4">
                    <a href="hacerP.php?paso=1" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 transition flex items-center">
                        <i class="fas fa-plus mr-2"></i> Nuevo Pedido
                    </a>
                    <div class="space-x-4">
                        <a href="../php/ver_pedidos.php" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 transition flex items-center">
                            <i class="fas fa-list mr-2"></i> Ver Todos los Pedidos
                        </a>
                        <a href="../php/index.php" class="bg-yellow-600 text-white px-6 py-2 rounded-md hover:bg-yellow-700 transition flex items-center">
                            <i class="fas fa-home mr-2"></i> Ir al Inicio
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Script para el menú móvil -->
    <script>
        const menuBtn = document.getElementById('menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        menuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>