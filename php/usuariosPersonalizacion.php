<?php
include_once __DIR__ . '/../crudP/conexion.php';

if (!isset($conexionJ) || !$conexionJ) {
    die("Error: No se pudo establecer conexión con la base de datos");
}

try {
    // Consulta para productos personalizables (personalizacionSN = 'Si')
    $queryPersonalizables = "SELECT * FROM productos WHERE personalizacionSN = 'Si'";
    $stmtPersonalizables = $conexionJ->query($queryPersonalizables);
    $productosPersonalizables = $stmtPersonalizables->fetchAll(PDO::FETCH_ASSOC);
    
    // Consulta para todos los productos
    $query = "SELECT * FROM productos";
    $stmt = $conexionJ->query($query);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error al obtener productos: " . $e->getMessage());
}

$whatsappNumber = "573208320246"; // Reemplaza con tu número
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joyería Hodo - Productos</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/css/lightgallery-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

    <style>
        /* Estilos del carrusel */
        .slick-carousel {
            position: relative;
            margin-bottom: 20px;
        }
        .slick-carousel .slick-prev,
        .slick-carousel .slick-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .slick-carousel .slick-prev:hover,
        .slick-carousel .slick-next:hover {
            background: rgba(0,0,0,0.8);
        }
        .slick-carousel .slick-prev {
            left: 10px;
        }
        .slick-carousel .slick-next {
            right: 10px;
        }
        .slick-carousel img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .slick-carousel .slick-dots {
            bottom: 10px;
        }
        .slick-carousel .slick-dots li button:before {
            color: white;
            font-size: 10px;
        }
        
        .no-image {
            height: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7280;
        }
        
        .producto {
            transition: all 0.3s ease;
        }
        .producto:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            display: none;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #f59e0b;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .cart-modal {
            display: none;
            position: fixed;
            top: 0;
            right: 0;
            width: 100%;
            max-width: 400px;
            height: 100%;
            background-color: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .cart-modal.open {
            display: block;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cart-items {
            padding: 1rem;
        }
        
        .cart-item {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 1rem;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .cart-item-price {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .cart-item-remove {
            color: #ef4444;
            cursor: pointer;
        }
        
        .cart-summary {
            padding: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .payment-methods {
            background-color: #f8fafc;
            border: 1px solid #e0e7ff;
            border-left: 4px solid #4f46e5;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .cart-actions {
            display: flex;
            gap: 1rem;
        }
        
        .cart-btn {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            border-radius: 0.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .cart-btn-checkout {
            background-color: #10b981;
            color: white;
        }
        
        .cart-btn-clear {
            background-color: #ef4444;
            color: white;
        }
        
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
        
        .overlay.open {
            display: block;
        }
        
        .product-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .product-buttons a, .product-buttons button {
            flex: 1;
        }
        
        .btn-add-to-cart {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-add-to-cart:hover {
            background-color: #2563eb;
        }
        
        /* Estilos para el modal de personalización */
        .personalizacion-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 1100;
            overflow-y: auto;
            padding: 20px;
        }
        
        .personalizacion-content {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            max-width: 800px;
            margin: 30px auto;
        }
        
        .personalizacion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .personalizacion-title {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .close-personalizacion {
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }
        
        .personalizacion-body {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .personalizacion-producto {
            flex: 1;
            min-width: 300px;
        }
        
        .personalizacion-producto img {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
        }
        
        .personalizacion-opciones {
            flex: 2;
            min-width: 300px;
        }
        
        .opcion-personalizacion {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .opcion-titulo {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .opcion-imagenes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .opcion-imagen {
            width: 80px;
            height: 80px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
            object-fit: cover;
        }
        
        .opcion-imagen:hover, .opcion-imagen.selected {
            border-color: #3b82f6;
        }
        
        .personalizacion-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .personalizacion-precio {
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .personalizacion-botones {
            display: flex;
            gap: 10px;
        }
        
        .btn-personalizacion {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-personalizacion-cancelar {
            background-color: #f1f1f1;
            color: #333;
        }
        
        .btn-personalizacion-agregar {
            background-color: #3b82f6;
            color: white;
        }
        
        .btn-personalizacion-comprar {
            background-color: #10b981;
            color: white;
        }
        
        /* Estilos para la sección de productos personalizables */
        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 30px 0 20px;
            text-align: center;
            color: #333;
        }
        
        .costo-personalizacion {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .personalizacion-nota {
            font-size: 0.9rem;
            color: #3b82f6;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navbar -->
<nav class="bg-gradient-to-r from-black via-purple-900 to-black shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
                <i class="fas fa-gem text-white text-2xl"></i>
                <span class="text-white font-bold text-xl">Joyería hodo</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="../php/categorias.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>Categorías
                </a>   
                
                <a href="../php/usuariosPersonalizacion.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-crown mr-2"></i>Personalización de joyas
                </a>

                <a href="../php/informacion.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>Garantías e información
                </a>
                <div class="cart-icon text-white cursor-pointer relative" id="cart-icon">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="cart-count" id="cart-count">0</span>
                </div>
            </div>
            
            <div class="md:hidden flex items-center space-x-4">
                <div class="cart-icon text-white cursor-pointer relative" id="cart-icon-mobile">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <span class="cart-count" id="cart-count-mobile">0</span>
                </div>
                <button id="menu-btn" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <div class="flex flex-col space-y-3 px-2 pt-2">
                <a href="../php/categorias.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>Categorías
                </a>
                <a href="../php/usuariosPersonalizacion.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-crown mr-2"></i>Personalización de joyas
                </a>

                <a href="../php/informacion.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-info-circle mr-2"></i>Garantías e información
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Modal del carrito -->
<div class="overlay" id="cart-overlay"></div>
<div class="cart-modal" id="cart-modal">
    <div class="cart-header">
        <h3 class="text-xl font-bold">Tu Carrito</h3>
        <button id="close-cart" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <div class="cart-items" id="cart-items">
        <div class="text-center py-8 text-gray-500" id="empty-cart-message">
            <i class="fas fa-shopping-cart fa-3x mb-4"></i>
            <p>Tu carrito está vacío</p>
        </div>
    </div>
    
    <div class="cart-summary" id="cart-summary" style="display: none;">
        <div class="payment-methods">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-credit-card mr-2"></i> Métodos de pago
            </h4>
            <div class="text-sm text-blue-700">
                <p class="mb-1"><i class="fas fa-check-circle text-green-500 mr-1"></i> Transferencias bancarias</p>
                <p><i class="fas fa-check-circle text-green-500 mr-1"></i> Pago contra entrega</p>
            </div>
        </div>
        
        <div class="cart-total">
            <span class="font-semibold">Total:</span>
            <span id="cart-total" class="text-xl font-bold text-blue-800">$0</span>
        </div>
        
        <div class="cart-actions">
            <button class="cart-btn cart-btn-clear" id="clear-cart">
                <i class="fas fa-trash-alt mr-2"></i> Vaciar
            </button>
            <button class="cart-btn cart-btn-checkout" id="checkout">
                <i class="fab fa-whatsapp mr-2"></i> Comprar
            </button>
        </div>
    </div>
</div>

<!-- Modal de personalización -->
<div class="personalizacion-modal" id="personalizacion-modal">
    <div class="personalizacion-content">
        <div class="personalizacion-header">
            <div class="personalizacion-title">Personalizar Producto</div>
            <div class="close-personalizacion" id="close-personalizacion">&times;</div>
        </div>
        <div class="personalizacion-body" id="personalizacion-body">
            <!-- Contenido dinámico se cargará aquí -->
        </div>
        <div class="personalizacion-footer">
            <div class="personalizacion-precio">
                Precio total: <span id="personalizacion-precio-total">$0</span>
                <div class="costo-personalizacion">(Incluye $8,000 COP de costo de personalización)</div>
            </div>
            <div class="personalizacion-botones">
                <button class="btn-personalizacion btn-personalizacion-cancelar" id="cancelar-personalizacion">Cancelar</button>
                <button class="btn-personalizacion btn-personalizacion-agregar" id="agregar-personalizacion">Agregar al carrito</button>
                <button class="btn-personalizacion btn-personalizacion-comprar" id="comprar-personalizacion">Comprar ahora</button>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="container mx-auto px-4 py-8">
    <?php if (!empty($productosPersonalizables)): ?>
        <h2 class="section-title">Productos Personalizables</h2>
        <p class="personalizacion-nota">¡Personaliza tu joya por solo $8,000 COP adicionales!</p>
        <div class="slick-carousel">
            <?php foreach ($productosPersonalizables as $producto): ?>
                <div class="px-2">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden producto">
                        <?php if (!empty($producto['foto_producto'])): ?>
                            <img src="<?php echo htmlspecialchars($producto['foto_producto']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_Producto']); ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <span>Imagen no disponible</span>
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($producto['nombre_Producto']); ?></h3>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($producto['description_p']); ?></p>
                            <p class="text-gray-900 font-bold">$<?php echo number_format($producto['valor_p'], 0, ',', '.'); ?> COP</p>
                            <div class="product-buttons mt-4">
                                <button class="btn-add-to-cart" onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>, '<?php echo htmlspecialchars($producto['nombre_Producto']); ?>', <?php echo $producto['valor_p']; ?>, '<?php echo htmlspecialchars($producto['foto_producto']); ?>')">
                                    <i class="fas fa-cart-plus mr-2"></i>Agregar
                                </button>
                                <button class="btn-add-to-cart bg-purple-600 hover:bg-purple-700" onclick="abrirPersonalizacion(<?php echo $producto['id_producto']; ?>)">
                                    <i class="fas fa-paint-brush mr-2"></i>Personalizar (+$8,000)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script>
    // Carrusel de productos personalizables
    $(document).ready(function(){
        $('.slick-carousel').slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 3,
            slidesToScroll: 1,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 1
                    }
                },
                {
                    breakpoint: 600,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1
                    }
                }
            ]
        });
    });

    // Variables globales
    let carrito = [];
    const COSTO_PERSONALIZACION = 8000; // 8,000 COP
    
    // Funciones del carrito
    function actualizarCarrito() {
        const cartCount = carrito.reduce((total, item) => total + item.cantidad, 0);
        document.getElementById('cart-count').textContent = cartCount;
        document.getElementById('cart-count-mobile').textContent = cartCount;
        
        const cartItems = document.getElementById('cart-items');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const cartSummary = document.getElementById('cart-summary');
        
        if (carrito.length === 0) {
            emptyCartMessage.style.display = 'block';
            cartSummary.style.display = 'none';
            
            // Limpiar los items del carrito (excepto el mensaje de vacío)
            while (cartItems.firstChild && cartItems.firstChild !== emptyCartMessage) {
                cartItems.removeChild(cartItems.firstChild);
            }
        } else {
            emptyCartMessage.style.display = 'none';
            cartSummary.style.display = 'block';
            
            // Limpiar los items del carrito (excepto el mensaje de vacío)
            while (cartItems.firstChild && cartItems.firstChild !== emptyCartMessage) {
                cartItems.removeChild(cartItems.firstChild);
            }
            
            let total = 0;
            
            carrito.forEach((item, index) => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.imagen || ''}" alt="${item.nombre}" class="cart-item-img">
                    <div class="cart-item-details">
                        <div class="cart-item-title">${item.nombre}</div>
                        <div class="cart-item-price">$${item.precio.toLocaleString('es-CO')} COP</div>
                        ${item.personalizacion ? `<div class="text-sm text-gray-600">Personalización: ${item.personalizacion}</div>` : ''}
                        ${item.personalizacion ? `<div class="text-sm text-blue-600">(+ $${COSTO_PERSONALIZACION.toLocaleString('es-CO')} COP por personalización)</div>` : ''}
                        <div class="flex items-center mt-1">
                            <button onclick="cambiarCantidad(${index}, -1)" class="text-gray-500 px-2">-</button>
                            <span class="mx-2">${item.cantidad}</span>
                            <button onclick="cambiarCantidad(${index}, 1)" class="text-gray-500 px-2">+</button>
                        </div>
                    </div>
                    <div class="cart-item-remove" onclick="eliminarDelCarrito(${index})">
                        <i class="fas fa-trash"></i>
                    </div>
                `;
                cartItems.insertBefore(cartItem, emptyCartMessage);
                
                total += item.precio * item.cantidad;
            });
            
            document.getElementById('cart-total').textContent = `$${total.toLocaleString('es-CO')} COP`;
        }
    }
    
    function agregarAlCarrito(id, nombre, precio, imagen, personalizacion = null) {
        const itemExistente = carrito.find(item => 
            item.id === id && 
            (!item.personalizacion && !personalizacion || item.personalizacion === personalizacion)
        );
        
        if (itemExistente) {
            itemExistente.cantidad += 1;
        } else {
            carrito.push({
                id,
                nombre,
                precio: personalizacion ? precio + COSTO_PERSONALIZACION : precio,
                imagen,
                cantidad: 1,
                personalizacion
            });
        }
        
        actualizarCarrito();
        // Mostrar notificación
        alert(`Producto ${personalizacion ? 'personalizado' : ''} agregado al carrito`);
    }
    
    function eliminarDelCarrito(index) {
        carrito.splice(index, 1);
        actualizarCarrito();
    }
    
    function cambiarCantidad(index, cambio) {
        const nuevaCantidad = carrito[index].cantidad + cambio;
        
        if (nuevaCantidad > 0) {
            carrito[index].cantidad = nuevaCantidad;
            actualizarCarrito();
        } else {
            eliminarDelCarrito(index);
        }
    }
    
    function vaciarCarrito() {
        carrito = [];
        actualizarCarrito();
    }
    
    function comprarPorWhatsApp() {
        if (carrito.length === 0) return;
        
        let mensaje = "¡Hola! Estoy interesado en los siguientes productos:\n\n";
        let total = 0;
        
        carrito.forEach(item => {
            mensaje += `- ${item.nombre}`;
            if (item.personalizacion) {
                mensaje += ` (Personalizado: ${item.personalizacion})`;
                mensaje += ` (+ $${COSTO_PERSONALIZACION.toLocaleString('es-CO')} COP)`;
            }
            mensaje += ` x${item.cantidad}: $${(item.precio * item.cantidad).toLocaleString('es-CO')} COP\n`;
            total += item.precio * item.cantidad;
        });
        
        mensaje += `\nTotal: $${total.toLocaleString('es-CO')} COP\n\n`;
        mensaje += "Por favor, indíqueme cómo proceder con la compra. ¡Gracias!";
        
        const url = `https://wa.me/<?php echo $whatsappNumber; ?>?text=${encodeURIComponent(mensaje)}`;
        window.open(url, '_blank');
    }
    
    // Funciones del modal de carrito
    document.getElementById('cart-icon').addEventListener('click', () => {
        document.getElementById('cart-modal').classList.add('open');
        document.getElementById('cart-overlay').classList.add('open');
    });
    
    document.getElementById('cart-icon-mobile').addEventListener('click', () => {
        document.getElementById('cart-modal').classList.add('open');
        document.getElementById('cart-overlay').classList.add('open');
    });
    
    document.getElementById('close-cart').addEventListener('click', () => {
        document.getElementById('cart-modal').classList.remove('open');
        document.getElementById('cart-overlay').classList.remove('open');
    });
    
    document.getElementById('cart-overlay').addEventListener('click', () => {
        document.getElementById('cart-modal').classList.remove('open');
        document.getElementById('cart-overlay').classList.remove('open');
        document.getElementById('personalizacion-modal').style.display = 'none';
    });
    
    document.getElementById('clear-cart').addEventListener('click', vaciarCarrito);
    document.getElementById('checkout').addEventListener('click', comprarPorWhatsApp);
    
    // Funciones del modal de personalización
    let productoActual = null;
    let opcionesSeleccionadas = {};
    
    async function abrirPersonalizacion(idProducto) {
        try {
            // Obtener información del producto
            const responseProducto = await fetch(`obtener_producto.php?id=${idProducto}`);
            const producto = await responseProducto.json();
            
            // Obtener opciones de personalización
            const responsePersonalizaciones = await fetch(`obtener_personalizaciones.php?id_producto=${idProducto}`);
            const personalizaciones = await responsePersonalizaciones.json();
            
            // Obtener imágenes de personalización
            const imagenesPromises = personalizaciones.map(p => 
                fetch(`obtener_imagenes_personalizacion.php?id_personalizacion=${p.id_personalizacion}`)
                    .then(res => res.json())
                    .then(imagenes => ({...p, imagenes}))
            );
            
            const personalizacionesConImagenes = await Promise.all(imagenesPromises);
            
            // Mostrar el modal
            productoActual = producto;
            opcionesSeleccionadas = {};
            
            const modalBody = document.getElementById('personalizacion-body');
            modalBody.innerHTML = `
                <div class="personalizacion-producto">
                    <h3 class="font-bold text-lg mb-2">${producto.nombre_Producto}</h3>
                    ${producto.foto_producto ? `<img src="${producto.foto_producto}" alt="${producto.nombre_Producto}">` : '<div class="no-image"><i class="fas fa-image fa-3x"></i></div>'}
                    <p class="mt-2">${producto.description_p || 'Sin descripción'}</p>
                    <p class="font-bold mt-2">Precio base: $${parseFloat(producto.valor_p).toLocaleString('es-CO')} COP</p>
                    <p class="font-bold text-blue-600 mt-1">+ Costo de personalización: $${COSTO_PERSONALIZACION.toLocaleString('es-CO')} COP</p>
                </div>
                <div class="personalizacion-opciones" id="personalizacion-opciones">
                    ${personalizacionesConImagenes.map(p => `
                        <div class="opcion-personalizacion" data-id="${p.id_personalizacion}">
                            <div class="opcion-titulo">${p.nombre_personalizacion}</div>
                            <p class="text-sm text-gray-600 mb-2">${p.descripcion || ''}</p>
                            <div class="opcion-imagenes">
                                ${p.imagenes.map(img => `
                                    <img src="../uploads/personalizaciones/${img.imagenP}" alt="${img.descripcion_imagen || p.nombre_personalizacion}" 
                                         class="opcion-imagen" 
                                         data-id="${img.id_imagen_personalizacion}"
                                         data-descripcion="${img.descripcion_imagen || p.nombre_personalizacion}"
                                         onclick="seleccionarOpcion(${p.id_personalizacion}, ${img.id_imagen_personalizacion}, '${img.descripcion_imagen || p.nombre_personalizacion}')">
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
            
            // Actualizar precio total
            actualizarPrecioPersonalizacion();
            
            document.getElementById('personalizacion-modal').style.display = 'block';
            document.getElementById('cart-overlay').classList.add('open');
            
        } catch (error) {
            console.error('Error al cargar personalizaciones:', error);
            alert('Error al cargar las opciones de personalización');
        }
    }
    
    function seleccionarOpcion(idPersonalizacion, idImagen, descripcion) {
        // Remover selección previa para esta personalización
        const opciones = document.querySelectorAll(`.opcion-personalizacion[data-id="${idPersonalizacion}"] .opcion-imagen`);
        opciones.forEach(img => img.classList.remove('selected'));
        
        // Marcar como seleccionada
        const imagenSeleccionada = document.querySelector(`.opcion-imagen[data-id="${idImagen}"]`);
        if (imagenSeleccionada) {
            imagenSeleccionada.classList.add('selected');
        }
        
        // Guardar selección
        opcionesSeleccionadas[idPersonalizacion] = {
            idImagen,
            descripcion
        };
        
        actualizarPrecioPersonalizacion();
    }
    
    function actualizarPrecioPersonalizacion() {
        if (!productoActual) return;
        
        const precioBase = parseFloat(productoActual.valor_p);
        const precioTotal = precioBase + COSTO_PERSONALIZACION;
        
        document.getElementById('personalizacion-precio-total').textContent = `$${precioTotal.toLocaleString('es-CO')} COP`;
    }
    
    function cerrarPersonalizacion() {
        document.getElementById('personalizacion-modal').style.display = 'none';
        document.getElementById('cart-overlay').classList.remove('open');
    }
    
    function agregarPersonalizacionAlCarrito() {
        if (!productoActual) return;
        
        // Verificar que se hayan seleccionado todas las opciones
        const personalizaciones = document.querySelectorAll('.opcion-personalizacion');
        let todasSeleccionadas = true;
        let descripcionPersonalizacion = '';
        
        personalizaciones.forEach(p => {
            const idPersonalizacion = p.getAttribute('data-id');
            if (!opcionesSeleccionadas[idPersonalizacion]) {
                todasSeleccionadas = false;
            } else {
                if (descripcionPersonalizacion) descripcionPersonalizacion += ', ';
                descripcionPersonalizacion += opcionesSeleccionadas[idPersonalizacion].descripcion;
            }
        });
        
        if (!todasSeleccionadas) {
            alert('Por favor selecciona todas las opciones de personalización');
            return;
        }
        
        // Agregar al carrito
        agregarAlCarrito(
            productoActual.id_producto,
            productoActual.nombre_Producto,
            parseFloat(productoActual.valor_p),
            productoActual.foto_producto,
            descripcionPersonalizacion
        );
        
        cerrarPersonalizacion();
    }
    
        function comprarPersonalizacionAhora() {
        agregarPersonalizacionAlCarrito();
        document.getElementById('cart-modal').classList.add('open');
    }
    
    // Event listeners para el modal de personalización
    document.getElementById('close-personalizacion').addEventListener('click', cerrarPersonalizacion);
    document.getElementById('cancelar-personalizacion').addEventListener('click', cerrarPersonalizacion);
    document.getElementById('agregar-personalizacion').addEventListener('click', agregarPersonalizacionAlCarrito);
    document.getElementById('comprar-personalizacion').addEventListener('click', comprarPersonalizacionAhora);
    
    // Menú móvil
    document.getElementById('menu-btn').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
    
    // Inicializar carrito
    actualizarCarrito();
</script>

<!-- Sección de todos los productos -->

<!-- Footer -->
<footer class="bg-gradient-to-r from-black via-purple-900 to-black text-white py-8">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-4">Joyería Hodo</h3>
                <p class="mb-4">Creando piezas únicas que cuentan tu historia.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-white hover:text-gray-300"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white hover:text-gray-300"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white hover:text-gray-300"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Contacto</h3>
                <p class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i> Bogotá, Colombia</p>
                <p class="mb-2"><i class="fas fa-phone mr-2"></i> +57 320 832 0246</p>
                <p class="mb-2"><i class="fas fa-envelope mr-2"></i> info@joyeriahodo.com</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Horario</h3>
                <p class="mb-2">Lunes - Viernes: 9:00 AM - 6:00 PM</p>
                <p class="mb-2">Sábados: 10:00 AM - 4:00 PM</p>
                <p>Domingos: Cerrado</p>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
            <p>&copy; <?php echo date('Y'); ?> Joyería Hodo. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<!-- Script para filtros -->
<script>
    // Filtrado de productos
    document.getElementById('search-input').addEventListener('input', filtrarProductos);
    document.getElementById('filter-category').addEventListener('change', filtrarProductos);
    document.getElementById('filter-price').addEventListener('change', filtrarProductos);
    
    function filtrarProductos() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const categoryFilter = document.getElementById('filter-category').value;
        const priceFilter = document.getElementById('filter-price').value;
        
        const productos = document.querySelectorAll('#productos-container .producto');
        let visibleCount = 0;
        
        productos.forEach(producto => {
            const nombre = producto.querySelector('h3').textContent.toLowerCase();
            const descripcion = producto.querySelector('p.text-gray-700').textContent.toLowerCase();
            const categoria = producto.getAttribute('data-category');
            const precio = parseFloat(producto.getAttribute('data-price'));
            
            // Aplicar filtros
            const matchesSearch = nombre.includes(searchTerm) || descripcion.includes(searchTerm);
            const matchesCategory = !categoryFilter || categoria === categoryFilter;
            const matchesPrice = !priceFilter || (
                priceFilter === '0-50000' && precio < 50000 ||
                priceFilter === '50000-100000' && precio >= 50000 && precio <= 100000 ||
                priceFilter === '100000-200000' && precio >= 100000 && precio <= 200000 ||
                priceFilter === '200000-500000' && precio >= 200000 && precio <= 500000 ||
                priceFilter === '500000' && precio > 500000
            );
            
            if (matchesSearch && matchesCategory && matchesPrice) {
                producto.style.display = 'block';
                visibleCount++;
            } else {
                producto.style.display = 'none';
            }
        });
        
        // Mostrar mensaje si no hay resultados
        const noResults = document.getElementById('no-results');
        if (visibleCount === 0) {
            noResults.style.display = 'block';
        } else {
            noResults.style.display = 'none';
        }
    }
</script>

</body>
</html>