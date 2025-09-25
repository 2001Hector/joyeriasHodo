<?php
session_start();
include_once __DIR__ . '/../crudP/conexion.php';

if (!isset($conexionJ) || !$conexionJ) {
    die("Error de conexión a la base de datos");
}

// Configuración
$whatsappNumber = "573003539845";
$costoPersonalizacion = 8000;

// Manejar operaciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'count' => 0, 'cart' => []];

    switch ($_POST['action']) {
        case 'add':
            if (isset($_POST['id'], $_POST['nombre'], $_POST['precio_producto'], $_POST['precio'])) {
                $item = [
                    'id_producto' => $_POST['id'],
                    'nombre' => $_POST['nombre'],
                    'precio' => floatval($_POST['precio']),
                    'quantity' => 1,
                    'imagen' => $_POST['imagen'] ?? '',
                    'precio_producto' => floatval($_POST['precio_producto']),
                    'costo_personalizacion' => isset($_POST['costo_personalizacion']) ? floatval($_POST['costo_personalizacion']) : 0,
                    'personalizaciones' => isset($_POST['personalizaciones']) ? json_decode($_POST['personalizaciones'], true) : []
                ];

                $_SESSION['carrito'] = $_SESSION['carrito'] ?? [];

                $encontrado = false;
                foreach ($_SESSION['carrito'] as &$i) {
                    if (
                        $i['id_producto'] == $item['id_producto'] &&
                        json_encode($i['personalizaciones']) == json_encode($item['personalizaciones'])
                    ) {
                        $i['quantity']++;
                        $encontrado = true;
                        break;
                    }
                }

                if (!$encontrado) {
                    $_SESSION['carrito'][] = $item;
                }

                $response['success'] = true;
                $response['message'] = 'Producto agregado al carrito';
            }
            break;

        case 'update':
            if (isset($_POST['id'], $_POST['quantity'])) {
                $id = $_POST['id'];
                $quantity = intval($_POST['quantity']);

                foreach ($_SESSION['carrito'] as &$item) {
                    if ($item['id_producto'] == $id) {
                        $item['quantity'] = $quantity;
                        $response['success'] = true;
                        break;
                    }
                }
            }
            break;

        case 'remove':
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                foreach ($_SESSION['carrito'] as $key => $item) {
                    if ($item['id_producto'] == $id) {
                        unset($_SESSION['carrito'][$key]);
                        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                        $response['success'] = true;
                        $response['message'] = 'Producto eliminado del carrito';
                        break;
                    }
                }
            }
            break;

        case 'clear_cart':
            unset($_SESSION['carrito']);
            $response['success'] = true;
            $response['message'] = 'Carrito vaciado';
            break;

        case 'get_cart':
            $response['success'] = true;
            break;
    }

    $response['count'] = array_sum(array_column($_SESSION['carrito'] ?? [], 'quantity'));
    $response['cart'] = $_SESSION['carrito'] ?? [];
    $response['total'] = array_sum(array_map(function ($item) {
        return $item['precio'] * $item['quantity'];
    }, $_SESSION['carrito'] ?? []));

    echo json_encode($response);
    exit;
}

$productoId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$modoPersonalizacion = ($productoId > 0);

// Obtener datos
if ($modoPersonalizacion) {
    $stmt = $conexionJ->prepare("SELECT * FROM productos WHERE id_producto = ?");
    $stmt->execute([$productoId]);
    $producto = $stmt->fetch();

    if (!$producto) die("Producto no encontrado");

    $stmt = $conexionJ->prepare("SELECT * FROM personalizaciones WHERE id_producto = ?");
    $stmt->execute([$productoId]);
    $personalizaciones = $stmt->fetchAll();

    foreach ($personalizaciones as &$p) {
        $stmt = $conexionJ->prepare("SELECT * FROM imagenes_personalizacion WHERE id_personalizacion = ?");
        $stmt->execute([$p['id_personalizacion']]);
        $p['imagenes'] = $stmt->fetchAll();
    }
    unset($p);
} else {
    $productos = $conexionJ->query("SELECT * FROM productos WHERE personalizacionSN = 'Si'")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6922726226939700"
     crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modoPersonalizacion ? "Personalizar {$producto['nombre_Producto']}" : "Productos Personalizables" ?> | Joyería Hodo</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] {
            display: none !important;
        }

        .option-img {
            transition: all 0.2s ease;
        }

        .option-img.selected {
            box-shadow: 0 0 0 2px #10B981;
            transform: scale(1.05);
        }

        .cart-item-enter {
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 767px) {
            #cart-sidebar {
                width: 90%;
            }

            .cart-item {
                flex-direction: column;
            }

            .cart-item-img {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }

        .btn-saved {
            background-color: #10B981 !important;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-black via-purple-900 to-black text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-gem text-xl"></i>
                    <span class="font-bold text-lg">Joyería Hodo</span>
                </div>

                <div class="hidden md:flex items-center gap">
                    <a href="../php/productos_generalesU.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                        <i class="fas fa-box-open mr-2"></i>Todos los productos
                    </a>
                    <a href="../php/categorias.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                        <i class="fas fa-boxes mr-2"></i>Productos por categorías
                    </a>
                    <a href="../php/personalizar_producto.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                        <i class="fas fa-crown mr-2"></i>Personalización de joyas
                    </a>
                    <a href="../php/informacion.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                        <i class="fas fa-info-circle mr-2"></i>Garantías e información
                    </a>
                    <button id="cart-btn" class="relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= array_sum(array_column($_SESSION['carrito'] ?? [], 'quantity')) ?>
                        </span>
                    </button>
                </div>

                <div class="md:hidden flex items-center space-x-4">
                    <button id="cart-btn-mobile" class="relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span id="cart-count-mobile" class="absolute -top-2 -right-2 bg-yellow-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?= array_sum(array_column($_SESSION['carrito'] ?? [], 'quantity')) ?>
                        </span>
                    </button>
                    <button id="mobile-menu-btn" class="text-xl">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>

            <div id="mobile-menu" class="hidden md:hidden pb-3 space-y-2">
                <!-- Menú con fondo negro y hover blanco completo -->
                <a href="../php/productos_generalesU.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                    <i class="fas fa-box-open mr-2"></i>Todos los productos
                </a>
                <a href="../php/categorias.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                    <i class="fas fa-boxes mr-2"></i>Productos por categorías
                </a>
                <a href="../php/personalizar_producto.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                    <i class="fas fa-crown mr-2"></i>Personalización de joyas
                </a>
                <a href="../php/informacion.php" class="block text-white hover:bg-white hover:text-black px-3 py-2 rounded transition duration-300">
                    <i class="fas fa-info-circle mr-2"></i>Garantías e información
                </a>
            </div>

        </div>
    </nav>

    <!-- Overlay y Sidebar del Carrito -->
    <div id="cart-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    <div id="cart-sidebar" class="fixed top-0 right-0 h-full w-full md:w-96 bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out z-50 overflow-y-auto">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold">
                <i class="fas fa-shopping-cart mr-2"></i> Tu Carrito
            </h3>
            <button id="close-cart" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="cart-items" class="p-4 space-y-4">
            <?php if (!empty($_SESSION['carrito'])): ?>
                <?php foreach ($_SESSION['carrito'] as $item): ?>
                    <div class="cart-item flex items-start border-b border-gray-200 pb-4" data-id="<?= $item['id_producto'] ?>">
                        <div class="cart-item-img w-20 h-20 bg-gray-100 rounded overflow-hidden mr-4">
                            <?php if (!empty($item['imagen']) && file_exists($item['imagen'])): ?>
                                <img src="<?= $item['imagen'] ?>" alt="<?= $item['nombre'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium"><?= $item['nombre'] ?></h4>
                            <p class="text-gray-600 text-sm">
                                Precio: $<?= number_format($item['precio_producto'], 0, ',', '.') ?>
                            </p>
                            <p class="text-gray-600 text-sm">
                                Cantidad: <?= $item['quantity'] ?>
                            </p>
                            <?php if (!empty($item['personalizaciones'])): ?>
                                <div class="text-xs text-gray-500 mt-1">
                                    <p class="font-semibold">Personalizaciones:</p>
                                    <?php foreach ($item['personalizaciones'] as $p): ?>
                                        <p>- <?= $p['nombre'] ?></p>
                                    <?php endforeach; ?>
                                    <p class="font-semibold mt-1">Costo personalización: $<?= number_format($item['costo_personalizacion'], 0, ',', '.') ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="flex items-center mt-1">
                                <button class="change-quantity bg-gray-200 px-2 rounded" data-action="decrease" data-id="<?= $item['id_producto'] ?>">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span class="quantity mx-2"><?= $item['quantity'] ?></span>
                                <button class="change-quantity bg-gray-200 px-2 rounded" data-action="increase" data-id="<?= $item['id_producto'] ?>">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <button class="remove-item text-red-500 ml-4" data-id="<?= $item['id_producto'] ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Tu carrito está vacío</p>
                </div>
            <?php endif; ?>

        </div>

        <div class="p-4 border-t border-gray-200">
            <div class="flex justify-between mb-4">
                <span class="font-semibold">Total:</span>
                <span id="cart-total" class="font-bold">
                    $<?= number_format(array_sum(array_map(function ($item) {
                            return $item['precio'] * $item['quantity'];
                        }, $_SESSION['carrito'] ?? [])), 0, ',', '.') ?>
                </span>
            </div>

            <div class="payment-methods">
                <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                    <i class="fas fa-credit-card mr-2"></i> Métodos de pago
                </h4>
                <div class="text-sm text-blue-700">
                    <p class="mb-1"><i class="fas fa-check-circle text-green-500 mr-1"></i>Transferencias bancarias💳</p>
                    <p><i class="fas fa-check-circle text-green-500 mr-1"></i> Pago contra entrega 💰</p>
                    <p><i class="fas fa-check-circle text-green-500 mr-1"></i> Costo de envío incluido 🚚</p>
                </div>
            </div>
            <br><br>
            <button id="whatsapp-checkout" class="block w-full bg-green-500 hover:bg-green-600 text-white text-center py-2 rounded-lg font-medium">
                <i class="fab fa-whatsapp mr-2"></i> Comprar
            </button>

        </div>
    </div>

    <!-- Contenido principal -->
    <main class="flex-grow container mx-auto px-4 py-6">
        <?php if ($modoPersonalizacion): ?>
            <!-- Vista de personalización -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="md:flex">
                    <!-- Imagen del producto -->
                    <div class="md:w-1/3 p-4">
                        <?php if (!empty($producto['foto_producto'])): ?>
                            <?php
                            $imagen = '../uploads/' . trim(explode(',', $producto['foto_producto'])[0]);
                            if (file_exists($imagen)): ?>
                                <img src="<?= $imagen ?>" alt="<?= $producto['nombre_Producto'] ?>" class="w-full h-auto rounded-lg">
                            <?php else: ?>
                                <div class="bg-gray-100 rounded-lg flex items-center justify-center h-64">
                                    <i class="fas fa-image text-4xl text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-gray-100 rounded-lg flex items-center justify-center h-64">
                                <i class="fas fa-image text-4xl text-gray-400"></i>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4">
                            <h2 class="text-xl font-bold"><?= $producto['nombre_Producto'] ?></h2>
                            <p class="text-gray-600 mt-1"><?= $producto['description_p'] ?? 'Sin descripción' ?></p>
                            <div class="mt-3">
                                <p class="text-lg font-semibold">Precio: $<?= number_format($producto['valor_p'], 0, ',', '.') ?></p>
                                <?php if (!empty($personalizaciones)): ?>
                                    <p class="text-sm">+ $<?= number_format($costoPersonalizacion, 0, ',', '.') ?> por personalización</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Opciones de personalización -->
                    <div class="md:w-2/3 p-4 border-t md:border-t-0 md:border-l border-gray-200">
                        <?php if (empty($personalizaciones)): ?>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                                <p class="text-yellow-700">Este producto no tiene opciones de personalización disponibles.</p>
                            </div>
                            <a href="personalizar_producto.php" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-arrow-left mr-2"></i> Volver a productos
                            </a>
                        <?php else: ?>
                            <h2 class="text-2xl font-bold mb-4">Personaliza tu joya</h2>

                            <div id="personalizaciones" class="space-y-6">
                                <?php foreach ($personalizaciones as $p): ?>
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <h3 class="font-semibold text-lg mb-3"><?= $p['nombre_personalizacion'] ?></h3>
                                        <?php if (!empty($p['descripcion'])): ?>
                                            <p class="text-gray-600 mb-3"><?= $p['descripcion'] ?></p>
                                        <?php endif; ?>

                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                            <?php foreach ($p['imagenes'] as $img): ?>
                                                <div class="cursor-pointer" onclick="seleccionarOpcion(this, <?= $p['id_personalizacion'] ?>, <?= $img['id_imagen_personalizacion'] ?>, '<?= addslashes($img['descripcion_imagen'] ?? '') ?>')">
                                                    <img src="../uploads/personalizaciones/<?= $img['imagenP'] ?>"
                                                        alt="<?= $img['descripcion_imagen'] ?? '' ?>"
                                                        class="option-img w-full h-24 object-cover rounded border border-gray-200">
                                                    <?php if (!empty($img['descripcion_imagen'])): ?>
                                                        <p class="text-sm text-center mt-1"><?= $img['descripcion_imagen'] ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Resumen y acciones -->
                            <div id="resumen" class="mt-6 bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-lg mb-3">Tu selección</h3>
                                <div id="opciones-seleccionadas" class="space-y-2"></div>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex justify-between">
                                        <span>Precio producto:</span>
                                        <span>$<?= number_format($producto['valor_p'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Costo personalización:</span>
                                        <span>$<?= number_format($costoPersonalizacion, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="flex justify-between font-bold text-lg mt-2">
                                        <span>Total:</span>
                                        <span id="precio-total">$<?= number_format($producto['valor_p'] + $costoPersonalizacion, 0, ',', '.') ?></span>
                                    </div>
                                </div>

                                <!-- Campos ocultos con los datos del producto -->
                                <input type="hidden" id="producto-id" value="<?= $producto['id_producto'] ?>">
                                <input type="hidden" id="producto-nombre" value="<?= htmlspecialchars($producto['nombre_Producto']) ?>">
                                <input type="hidden" id="producto-precio" value="<?= $producto['valor_p'] ?>">
                                <input type="hidden" id="producto-imagen" value="<?= !empty($producto['foto_producto']) ? '../uploads/' . trim(explode(',', $producto['foto_producto'])[0]) : '' ?>">
                                <input type="hidden" id="costo-personalizacion" value="<?= $costoPersonalizacion ?>">

                                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <button id="add-to-cart" class="bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg">
                                        <i class="fas fa-check-circle mr-2"></i> Guardar en carrito
                                    </button>
                                    <a href="personalizar_producto.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 rounded-lg text-center">
                                        <i class="fas fa-arrow-left mr-2"></i> Seguir comprando
                                    </a>
                                    <button id="whatsapp-btn" class="bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg md:col-span-2">
                                        <i class="fab fa-whatsapp mr-2"></i> Consultar por WhatsApp
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Vista de lista de productos -->
            <h1 class="text-2xl md:text-3xl font-bold text-center mb-6">Productos Personalizables</h1>

            <!-- Buscador -->
            <div class="max-w-md mx-auto mb-8 relative">
                <input type="text" id="search-input" placeholder="Buscar productos..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
            </div>

            <!-- Lista de productos -->
            <div id="productos" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($productos as $p): ?>
                    <?php
                    $imagen = !empty($p['foto_producto']) ? '../uploads/' . trim(explode(',', $p['foto_producto'])[0]) : '';
                    $imagenValida = $imagen && file_exists($imagen);
                    ?>

                    <div class="producto bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                        data-nombre="<?= strtolower($p['nombre_Producto']) ?>"
                        data-codigo="<?= strtolower($p['codigo_p'] ?? '') ?>"
                        data-categoria="<?= strtolower($p['categoria_p'] ?? '') ?>">
                        <div class="h-48 bg-gray-100 overflow-hidden">
                            <?php if ($imagenValida): ?>
                                <img src="<?= $imagen ?>" alt="<?= $p['nombre_Producto'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image fa-3x"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-lg"><?= $p['nombre_Producto'] ?></h3>
                            <div class="text-gray-600 text-sm space-y-1 mt-2">
                                <p><span class="font-semibold">Código:</span> <?= $p['codigo_p'] ?? 'N/A' ?></p>
                                <p><span class="font-semibold">Categoría:</span> <?= $p['categoria_p'] ?? 'N/A' ?></p>
                                <p><span class="font-semibold">Precio:</span> $<?= number_format($p['valor_p'], 0, ',', '.') ?></p>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <a href="https://wa.me/<?= $whatsappNumber ?>?text=<?= urlencode("Hola, estoy interesado en el producto:\n\n*{$p['nombre_Producto']}*\nCódigo: {$p['codigo_p']}\nPrecio: \$" . number_format($p['valor_p'], 0, ',', '.')) ?>"
                                    target="_blank"
                                    class="bg-green-500 hover:bg-green-600 text-white text-center py-2 rounded text-sm">
                                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                                </a>
                                <a href="personalizar_producto.php?id=<?= $p['id_producto'] ?>"
                                    class="bg-purple-500 hover:bg-purple-600 text-white text-center py-2 rounded text-sm">
                                    <i class="fas fa-crown mr-1"></i> Personalizar
                                </a>
                                <button class="add-to-cart bg-blue-500 hover:bg-blue-600 text-white py-2 rounded sm:col-span-2 text-sm"
                                    data-id="<?= $p['id_producto'] ?>"
                                    data-nombre="<?= $p['nombre_Producto'] ?>"
                                    data-precio_producto="<?= $p['valor_p'] ?>"
                                    data-imagen="<?= $imagenValida ? $imagen : '' ?>">
                                    <i class="fas fa-check-circle mr-1"></i> Agregar al carrito
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Sin resultados -->
            <div id="no-results" class="hidden text-center py-12">
                <i class="fas fa-search fa-3x text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600">No se encontraron productos</h3>
                <p class="text-gray-500">Prueba con otros términos de búsqueda</p>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0 flex items-center space-x-2">
                    <i class="fas fa-gem"></i>
                    <span class="font-bold">Joyería Hodo</span>
                </div>
                <div class="text-center md:text-right">
                    <p><i class="fas fa-map-marker-alt mr-1"></i> Colombia, La Guajira, Maicao</p>
                    <p>JOYERIASHODO</p>
                </div>
            </div>
            <div class="flex justify-center space-x-6 mt-6">
                <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="text-gray-300 hover:text-white"><i class="fab fa-instagram"></i></a>
                <a href="https://wa.me/<?= $whatsappNumber ?>" class="text-gray-300 hover:text-white"><i class="fab fa-whatsapp"></i></a>
            </div>
            <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; <?= date('Y') ?> Joyería Hodo. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Variables globales
        let selecciones = {};
        <?php if ($modoPersonalizacion): ?>
            const precioProducto = <?= $producto['valor_p'] ?>;
            const costoPersonalizacion = <?= $costoPersonalizacion ?>;
        <?php endif; ?>

        // Funciones comunes
        function actualizarContadorCarrito() {
            $.post('personalizar_producto.php', {
                action: 'get_cart'
            }, function(res) {
                $('#cart-count, #cart-count-mobile').text(res.count || 0);
            }, 'json');
        }

        function mostrarNotificacion(mensaje, tipo = 'success') {
            const tipos = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500'
            };

            const notificacion = $(`
                <div class="fixed top-4 right-4 ${tipos[tipo]} text-white px-4 py-2 rounded-lg shadow-lg animate-fade-in">
                    <i class="fas fa-${tipo === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i> ${mensaje}
                </div>
            `);

            $('body').append(notificacion);
            setTimeout(() => notificacion.remove(), 3000);
        }

        // Carrito
        $(document).on('click', '#cart-btn, #cart-count, #cart-btn-mobile, #cart-count-mobile', function(e) {
            e.preventDefault();
            $('#cart-sidebar').removeClass('translate-x-full');
            $('#cart-overlay').removeClass('hidden');
            actualizarCarrito();
        });

        $('#close-cart, #cart-overlay').on('click', function() {
            $('#cart-sidebar').addClass('translate-x-full');
            $('#cart-overlay').addClass('hidden');
        });

        // Actualizar carrito
        function actualizarCarrito() {
            $.post('personalizar_producto.php', {
                action: 'get_cart'
            }, function(data) {
                if (data.cart && data.cart.length > 0) {
                    let html = '';

                    data.cart.forEach(item => {
                        html += `
                            <div class="cart-item flex items-start border-b border-gray-200 pb-4" data-id="${item.id_producto}">
                                <div class="cart-item-img w-20 h-20 bg-gray-100 rounded overflow-hidden mr-4">
                                    ${item.imagen ? 
                                        `<img src="${item.imagen}" alt="${item.nombre}" class="w-full h-full object-cover">` : 
                                        `<div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image"></i>
                                        </div>`}
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium">${item.nombre}</h4>
                                    <p class="text-gray-600 text-sm">
                                        Precio: $${item.precio_producto.toLocaleString('es-CO')}
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        Cantidad: ${item.quantity}
                                    </p>
                                    ${item.personalizaciones && item.personalizaciones.length > 0 ? `
                                        <div class="text-xs text-gray-500 mt-1">
                                            <p class="font-semibold">Personalizaciones:</p>
                                            ${item.personalizaciones.map(p => `<p>- ${p.nombre}</p>`).join('')}
                                            <p class="font-semibold mt-1">Costo personalización: $${item.costo_personalizacion.toLocaleString('es-CO')}</p>
                                        </div>
                                    ` : ''}
                                    <div class="flex items-center mt-1">
                                        <button class="change-quantity bg-gray-200 px-2 rounded" data-action="decrease" data-id="${item.id_producto}">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span class="quantity mx-2">${item.quantity}</span>
                                        <button class="change-quantity bg-gray-200 px-2 rounded" data-action="increase" data-id="${item.id_producto}">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <button class="remove-item text-red-500 ml-4" data-id="${item.id_producto}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    });

                    $('#cart-items').html(html);
                    $('#cart-total').text('$' + data.total.toLocaleString('es-CO'));
                } else {
                    $('#cart-items').html(`
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Tu carrito está vacío</p>
                        </div>
                    `);
                    $('#cart-total').text('$0');
                }

                $('#cart-count, #cart-count-mobile').text(data.count || 0);
            }, 'json');
        }

        // Manejar eventos del carrito
        $(document).on('click', '.change-quantity', function() {
            const action = $(this).data('action');
            const id = $(this).data('id');
            const $quantity = $(this).siblings('.quantity');
            let newQuantity = parseInt($quantity.text());

            if (action === 'increase') {
                newQuantity++;
            } else if (action === 'decrease' && newQuantity > 1) {
                newQuantity--;
            }

            $.post('personalizar_producto.php', {
                action: 'update',
                id: id,
                quantity: newQuantity
            }, function(data) {
                if (data.success) {
                    $quantity.text(newQuantity);
                    $('#cart-total').text('$' + data.total.toLocaleString('es-CO'));
                    $('#cart-count, #cart-count-mobile').text(data.count);
                }
            }, 'json');
        });

        $(document).on('click', '.remove-item', function() {
            const $item = $(this).closest('.cart-item');
            const id = $(this).data('id');

            $.post('personalizar_producto.php', {
                action: 'remove',
                id: id
            }, function(data) {
                if (data.success) {
                    mostrarNotificacion(data.message);
                    $item.remove();
                    $('#cart-total').text('$' + data.total.toLocaleString('es-CO'));
                    $('#cart-count, #cart-count-mobile').text(data.count);

                    if (data.count === 0) {
                        $('#cart-items').html(`
                            <div class="text-center py-8">
                                <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">Tu carrito está vacío</p>
                            </div>
                        `);
                    }

                    // Actualizar botones "Agregar al carrito" en la lista de productos
                    $('.add-to-cart').each(function() {
                        const btnId = $(this).data('id');
                        if (btnId == id) {
                            $(this).html('<i class="fas fa-check-circle mr-1"></i> Agregar al carrito');
                            $(this).removeClass('btn-saved');
                        }
                    });
                }
            }, 'json');
        });

        // WhatsApp Checkout
        $('#whatsapp-checkout').on('click', function() {
            $.post('personalizar_producto.php', {
                action: 'get_cart'
            }, function(data) {
                if (!data.cart || data.cart.length === 0) {
                    mostrarNotificacion('Tu carrito está vacío', 'error');
                    return;
                }

                let mensaje = "Hola, estoy interesado en los siguientes productos:\n\n";

                data.cart.forEach(item => {
                    mensaje += `*${item.nombre}*\n`;
                    mensaje += `- Precio base: $${item.precio_producto.toLocaleString('es-CO')}\n`;
                    mensaje += `- Cantidad: ${item.quantity}\n`;

                    if (item.personalizaciones && item.personalizaciones.length > 0) {
                        mensaje += `- Personalizaciones:\n`;
                        item.personalizaciones.forEach(p => {
                            mensaje += `  • ${p.nombre}\n`;
                        });
                        mensaje += `- Costo personalización: $${item.costo_personalizacion.toLocaleString('es-CO')}\n`;
                    }

                    mensaje += `- Subtotal: $${(item.precio * item.quantity).toLocaleString('es-CO')}\n\n`;
                });

                mensaje += `\n*TOTAL: $${data.total.toLocaleString('es-CO')}*\n\n`;
                mensaje += "¿Podrían confirmarme disponibilidad y forma de pago?";

                window.open(`https://wa.me/<?= $whatsappNumber ?>?text=${encodeURIComponent(mensaje)}`, '_blank');
            }, 'json');
        });

        // Personalización
        <?php if ($modoPersonalizacion): ?>

            // ...existing code...
            function seleccionarOpcion(elemento, idPersonalizacion, idOpcion, nombreOpcion) {
                const $img = $(elemento).find('.option-img');
                const yaSeleccionada = $img.hasClass('selected');

                // Si ya está seleccionada, deselecciona
                if (yaSeleccionada) {
                    $img.removeClass('selected');
                    delete selecciones[idPersonalizacion];
                } else {
                    // Selecciona esta y deselecciona las demás
                    $(elemento).closest('.bg-gray-50').find('.option-img').removeClass('selected');
                    $img.addClass('selected');
                    selecciones[idPersonalizacion] = {
                        id: idOpcion,
                        nombre: nombreOpcion,
                        personalizacion: $(elemento).closest('.bg-gray-50').find('h3').text()
                    };
                }

                actualizarResumen();
            }
            // ...existing code...
            function actualizarResumen() {
                const $resumen = $('#resumen');
                const $opciones = $('#opciones-seleccionadas');

                if (Object.keys(selecciones).length === 0) {
                    $resumen.addClass('hidden');
                    return;
                }

                $opciones.empty();
                let html = '';

                for (const id in selecciones) {
                    html += `
                    <div class="flex justify-between items-center bg-white p-2 rounded">
                        <span class="font-medium">${selecciones[id].personalizacion}:</span>
                        <span>${selecciones[id].nombre}</span>
                    </div>
                `;
                }

                $opciones.html(html);

                const precioTotal = precioProducto + (Object.keys(selecciones).length > 0 ? costoPersonalizacion : 0);
                $('#precio-total').text('$' + precioTotal.toLocaleString('es-CO'));
                $resumen.removeClass('hidden');
            }

            $('#add-to-cart').on('click', function() {
                if (Object.keys(selecciones).length === 0) {
                    mostrarNotificacion('Selecciona al menos una opción de personalización', 'error');
                    return;
                }

                const $btn = $(this);
                $btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');
                $btn.prop('disabled', true);

                $.post('personalizar_producto.php', {
                    action: 'add',
                    id: $('#producto-id').val(),
                    nombre: $('#producto-nombre').val(),
                    precio_producto: $('#producto-precio').val(),
                    precio: parseFloat($('#producto-precio').val()) + parseFloat($('#costo-personalizacion').val()),
                    imagen: $('#producto-imagen').val(),
                    personalizaciones: JSON.stringify(selecciones),
                    costo_personalizacion: $('#costo-personalizacion').val()
                }, function(response) {
                    if (response.success) {
                        mostrarNotificacion(response.message);
                        $('#cart-count, #cart-count-mobile').text(response.cart_count);
                        $btn.html('<i class="fas fa-check mr-2"></i> Guardado');
                        $btn.removeClass('bg-blue-500 hover:bg-blue-600');
                        $btn.addClass('bg-green-500 hover:bg-green-600');

                        if (!$('#cart-sidebar').hasClass('translate-x-full')) {
                            actualizarCarrito();
                        }
                    } else {
                        mostrarNotificacion('Error al agregar al carrito', 'error');
                        $btn.html('<i class="fas fa-check-circle mr-2"></i> Guardar en carrito');
                        $btn.prop('disabled', false);
                    }
                }, 'json');
            });

            $('#whatsapp-btn').on('click', function() {
                if (Object.keys(selecciones).length === 0) {
                    mostrarNotificacion('Selecciona al menos una opción de personalización', 'error');
                    return;
                }

                let mensaje = `Hola, estoy interesado en personalizar el producto:\n\n`;
                mensaje += `*${'<?= $producto['nombre_Producto'] ?>'}*\n`;
                mensaje += `Precio base: $${precioProducto.toLocaleString('es-CO')}\n`;
                mensaje += `Costo personalización: $${costoPersonalizacion.toLocaleString('es-CO')}\n\n`;
                mensaje += `*Personalizaciones seleccionadas:*\n`;

                for (const id in selecciones) {
                    mensaje += `- ${selecciones[id].personalizacion}: ${selecciones[id].nombre}\n`;
                }

                const precioTotal = precioProducto + costoPersonalizacion;
                mensaje += `\n*Precio total:* $${precioTotal.toLocaleString('es-CO')}\n\n`;
                mensaje += `¿Podrían confirmarme disponibilidad?`;

                window.open(`https://wa.me/<?= $whatsappNumber ?>?text=${encodeURIComponent(mensaje)}`, '_blank');
            });
        <?php else: ?>
            // Agregar al carrito (modo lista)
            $(document).on('click', '.add-to-cart', function() {
                const $btn = $(this);
                const id = $btn.data('id');
                const nombre = $btn.data('nombre');
                const precio_producto = $btn.data('precio_producto');
                const imagen = $btn.data('imagen');

                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');
                $btn.prop('disabled', true);

                $.post('personalizar_producto.php', {
                    action: 'add',
                    id: id,
                    nombre: nombre,
                    precio_producto: precio_producto,
                    precio: precio_producto, // Mismo valor ya que no hay personalización
                    imagen: imagen
                }, function(response) {
                    if (response.success) {
                        mostrarNotificacion(response.message);
                        $('#cart-count, #cart-count-mobile').text(response.count);
                        $btn.html('<i class="fas fa-check mr-1"></i> Guardado');
                        $btn.addClass('btn-saved');
                        $btn.prop('disabled', true);

                        if (!$('#cart-sidebar').hasClass('translate-x-full')) {
                            actualizarCarrito();
                        }
                    } else {
                        mostrarNotificacion('Error al agregar al carrito', 'error');
                        $btn.html('<i class="fas fa-check-circle mr-1"></i> Agregar al carrito');
                        $btn.prop('disabled', false);
                    }
                }, 'json');
            });
        <?php endif; ?>

        // Menú móvil
        $('#mobile-menu-btn').on('click', function() {
            $('#mobile-menu').toggleClass('hidden');
            $(this).find('i').toggleClass('fa-bars fa-times');
        });

        // Verificar estado del carrito al cargar la página
        function verificarEstadoCarrito() {
            $.post('personalizar_producto.php', {
                action: 'get_cart'
            }, function(data) {
                if (data.cart && data.cart.length > 0) {
                    data.cart.forEach(item => {
                        $(`.add-to-cart[data-id="${item.id_producto}"]`).each(function() {
                            $(this).html('<i class="fas fa-check mr-1"></i> Guardado');
                            $(this).addClass('btn-saved');
                            $(this).prop('disabled', true);
                        });
                    });
                }
            }, 'json');
        }

        // Inicialización
        $(document).ready(function() {
            actualizarContadorCarrito();
            verificarEstadoCarrito();

            // Buscador de productos
            $('#search-input').on('input', function() {
                const term = $(this).val().toLowerCase();
                let resultados = 0;

                $('.producto').each(function() {
                    const nombre = $(this).data('nombre');
                    const codigo = $(this).data('codigo');
                    const categoria = $(this).data('categoria');

                    if (nombre.includes(term) || codigo.includes(term) || categoria.includes(term)) {
                        $(this).show();
                        resultados++;
                    } else {
                        $(this).hide();
                    }
                });

                if (resultados === 0) {
                    $('#no-results').removeClass('hidden');
                } else {
                    $('#no-results').addClass('hidden');
                }
            });
        });
    </script>
</body>

</html>