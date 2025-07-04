<?php
include_once __DIR__ . '/../crudP/conexion.php';

if (!isset($conexionJ) || !$conexionJ) {
    die("Error: No se pudo establecer conexión con la base de datos");
}

try {
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
                
                <a href="../php/personalizar_producto.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
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
                <a href="../php/personalizar_producto.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
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

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Nuestros Productos</h1>
    
    <div class="mb-8 max-w-md mx-auto shadow-xl">
        <div class="relative">
            <input type="text" id="busqueda" placeholder="Buscar por nombre, código, categoría..." 
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-black focus:border-transparent">
            <button class="absolute right-3 top-2.5 text-gray-500">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 inset-shadow-sm" id="productos-container">
        <?php foreach ($productos as $producto): ?>
            <div class="producto bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300" 
                 data-nombre="<?php echo htmlspecialchars(strtolower($producto['nombre_Producto'])); ?>"
                 data-codigo="<?php echo htmlspecialchars(strtolower($producto['codigo_p'] ?? '')); ?>"
                 data-categoria="<?php echo htmlspecialchars(strtolower($producto['categoria_p'] ?? '')); ?>"
                 data-descripcion="<?php echo htmlspecialchars(strtolower($producto['description_p'] ?? '')); ?>"
                 data-id="<?php echo $producto['id_producto']; ?>">
                
                <div class="slick-carousel">
                    <?php 
                    if (!empty($producto['foto_producto'])) {
                        $imagenes = explode(',', $producto['foto_producto']);
                        $imagenesValidas = [];
                        
                        foreach ($imagenes as $imagen) {
                            $imagen = trim($imagen);
                            $imagenPath = '../uploads/' . $imagen;
                            if (file_exists($imagenPath)) {
                                $imagenesValidas[] = $imagenPath;
                            }
                        }
                        
                        if (!empty($imagenesValidas)) {
                            foreach ($imagenesValidas as $imagenPath) {
                                echo '<div>';
                                echo '<a href="'.$imagenPath.'" data-lg-group="producto-'.$producto['id_producto'].'">';
                                echo '<img src="'.$imagenPath.'" alt="'.htmlspecialchars($producto['nombre_Producto']).'" class="lg-zoom-in">';
                                echo '</a>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="no-image">';
                            echo '<i class="fas fa-image fa-3x mb-2"></i><br>';
                            echo '<span>Imágenes no encontradas</span>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-image">';
                        echo '<i class="fas fa-image fa-3x mb-2"></i><br>';
                        echo '<span>Sin imágenes</span>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <div class="p-4">
                    <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($producto['nombre_Producto']); ?></h3>
                    
                    <div class="text-gray-700 mb-2">
                        <span class="font-semibold">Código:</span> 
                        <?php echo htmlspecialchars($producto['codigo_p'] ?? 'N/A'); ?>
                    </div>
                    
                    <div class="text-gray-700 mb-2">
                        <span class="font-semibold">Categoría:</span> 
                        <?php echo htmlspecialchars($producto['categoria_p'] ?? 'N/A'); ?>
                    </div>
                    
                    <div class="text-gray-700 mb-2">
                        <span class="font-semibold">Diseño:</span> 
                        <?php echo htmlspecialchars($producto['diseño_p'] ?? 'N/A'); ?>
                    </div>
                    
                    <div class="text-gray-700 mb-2">
                        <span class="font-semibold">Material:</span> 
                        <?php echo htmlspecialchars($producto['tipo_de_material'] ?? 'N/A'); ?>
                    </div>
                    
                    <div class="text-gray-700 mb-2">
                        <span class="font-semibold">Precio:</span> 
                        <?php 
                        $precioFormateado = number_format($producto['valor_p'] ?? 0, 0, ',', '.');
                        echo '$'.$precioFormateado; 
                        ?>
                    </div>
                    
                    <?php if (!empty($producto['description_p'])): ?>
                        <div class="text-gray-700 mb-4">
                            <span class="font-semibold">Descripción:</span> 
                            <?php echo htmlspecialchars($producto['description_p']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-buttons">
                        <a href="https://wa.me/<?php echo $whatsappNumber; ?>?text=¡Hola! Estoy interesado en: <?php echo urlencode($producto['nombre_Producto']); ?>%0ACódigo: <?php echo urlencode($producto['codigo_p'] ?? ''); ?>%0APrecio: <?php echo urlencode('$'.$precioFormateado); ?>" 
                           target="_blank"
                           class="block bg-green-500 hover:bg-green-600 text-white text-center font-bold py-2 px-4 transition-colors duration-300 rounded-full">
                            <i class="fab fa-whatsapp mr-2"></i> Lo quiero
                        </a>
                        
                        <button class="btn-add-to-cart" 
                                data-id="<?php echo $producto['id_producto']; ?>"
                                data-name="<?php echo htmlspecialchars($producto['nombre_Producto']); ?>"
                                data-price="<?php echo $producto['valor_p'] ?? 0; ?>"
                                data-code="<?php echo htmlspecialchars($producto['codigo_p'] ?? ''); ?>"
                                data-image="<?php echo !empty($imagenesValidas) ? htmlspecialchars($imagenesValidas[0]) : ''; ?>">
                            <i class="fas fa-cart-plus"></i> Agregar
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div id="no-results" class="no-results col-span-full">
            <i class="fas fa-search fa-3x mb-4 text-gray-400"></i>
            <h3 class="text-xl font-bold text-gray-600">No se encontraron productos</h3><br>
            <p class="text-gray-500">Intenta con otros términos de búsqueda</p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/lightgallery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/plugins/zoom/lg-zoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.7.1/plugins/fullscreen/lg-fullscreen.min.js"></script>

<script>
    // Función para formatear a pesos colombianos
    function formatCOP(value) {
        return '$' + Math.round(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $(document).ready(function(){
        // Inicialización del carrusel Slick
        $('.slick-carousel').slick({
            dots: true,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            adaptiveHeight: true,
            autoplay: true,
            autoplaySpeed: 3000,
            arrows: true,
            prevArrow: '<button type="button" class="slick-prev"><i class="fas fa-chevron-left"></i></button>',
            nextArrow: '<button type="button" class="slick-next"><i class="fas fa-chevron-right"></i></button>'
        });

        // LightGallery
        document.querySelectorAll('.slick-carousel a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const groupId = this.getAttribute('data-lg-group');
                const galleryItems = [];
                
                document.querySelectorAll(`[data-lg-group="${groupId}"]`).forEach(el => {
                    galleryItems.push({
                        src: el.getAttribute('href'),
                        thumb: el.getAttribute('href')
                    });
                });
                
                const dynamicGallery = lightGallery(document.createElement('div'), {
                    dynamic: true,
                    dynamicEl: galleryItems,
                    download: false,
                    zoom: true,
                    fullScreen: true
                });
                
                const clickedIndex = Array.from(document.querySelectorAll(`[data-lg-group="${groupId}"]`)).indexOf(this);
                dynamicGallery.openGallery(clickedIndex);
            });
        });

        // Menú móvil
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

        // Búsqueda
        document.getElementById('busqueda').addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            const productos = document.querySelectorAll('.producto');
            const noResults = document.getElementById('no-results');
            let hasResults = false;
            
            productos.forEach(producto => {
                const nombre = producto.dataset.nombre;
                const codigo = producto.dataset.codigo;
                const categoria = producto.dataset.categoria;
                const descripcion = producto.dataset.descripcion;
                
                if (nombre.includes(searchTerm) || 
                    codigo.includes(searchTerm) || 
                    categoria.includes(searchTerm) || 
                    descripcion.includes(searchTerm)) {
                    producto.style.display = 'block';
                    hasResults = true;
                } else {
                    producto.style.display = 'none';
                }
            });
            
            noResults.style.display = hasResults ? 'none' : 'flex';
        });
        
        // Carrito de compras
        const cartIcon = document.getElementById('cart-icon');
        const cartIconMobile = document.getElementById('cart-icon-mobile');
        const cartModal = document.getElementById('cart-modal');
        const cartOverlay = document.getElementById('cart-overlay');
        const closeCart = document.getElementById('close-cart');
        const cartItemsContainer = document.getElementById('cart-items');
        const emptyCartMessage = document.getElementById('empty-cart-message');
        const cartSummary = document.getElementById('cart-summary');
        const cartTotal = document.getElementById('cart-total');
        const clearCartBtn = document.getElementById('clear-cart');
        const checkoutBtn = document.getElementById('checkout');
        const cartCount = document.getElementById('cart-count');
        const cartCountMobile = document.getElementById('cart-count-mobile');
        
        // Abrir carrito
        function openCart() {
            cartModal.classList.add('open');
            cartOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
            updateCartDisplay();
        }
        
        // Cerrar carrito
        function closeCartModal() {
            cartModal.classList.remove('open');
            cartOverlay.classList.remove('open');
            document.body.style.overflow = '';
        }
        
        cartIcon.addEventListener('click', openCart);
        cartIconMobile.addEventListener('click', openCart);
        cartOverlay.addEventListener('click', closeCartModal);
        closeCart.addEventListener('click', closeCartModal);
        
        // Obtener carrito del localStorage
        function getCart() {
            const cart = localStorage.getItem('cart');
            return cart ? JSON.parse(cart) : [];
        }
        
        // Guardar carrito en localStorage
        function saveCart(cart) {
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount();
        }
        
        // Actualizar contador del carrito
        function updateCartCount() {
            const cart = getCart();
            const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
            cartCount.textContent = totalItems;
            cartCountMobile.textContent = totalItems;
        }
        
        // Actualizar visualización del carrito
        function updateCartDisplay() {
            const cart = getCart();
            
            if (cart.length === 0) {
                emptyCartMessage.style.display = 'block';
                cartSummary.style.display = 'none';
                cartItemsContainer.innerHTML = '';
                cartItemsContainer.appendChild(emptyCartMessage);
            } else {
                emptyCartMessage.style.display = 'none';
                cartSummary.style.display = 'block';
                
                let total = 0;
                let itemsHTML = '';
                
                cart.forEach(item => {
                    total += item.price * item.quantity;
                    
                    itemsHTML += `
                        <div class="cart-item" data-id="${item.id}">
                            <img src="${item.image}" alt="${item.name}" class="cart-item-img">
                            <div class="cart-item-details">
                                <div class="cart-item-title">${item.name}</div>
                                <div class="cart-item-price">${formatCOP(item.price)} x ${item.quantity}</div>
                                <div class="cart-item-price">Código: ${item.code}</div>
                                <div class="cart-item-remove" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i> Eliminar
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                cartItemsContainer.innerHTML = itemsHTML;
                cartTotal.textContent = formatCOP(total);
            }
        }
        
        // Agregar producto al carrito
        function addToCart(productId, productName, productPrice, productCode, productImage) {
            const cart = getCart();
            const existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    code: productCode,
                    image: productImage,
                    quantity: 1
                });
            }
            
            saveCart(cart);
            
            // Mostrar notificación
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transition-opacity duration-500';
            notification.innerHTML = `<i class="fas fa-check-circle mr-2"></i> Producto agregado al carrito`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 2000);
        }
        
        // Eliminar producto del carrito
        window.removeFromCart = function(productId) {
            const cart = getCart();
            const updatedCart = cart.filter(item => item.id !== productId);
            saveCart(updatedCart);
            updateCartDisplay();
        }
        
        // Vaciar carrito
        clearCartBtn.addEventListener('click', function() {
            localStorage.removeItem('cart');
            updateCartCount();
            updateCartDisplay();
        });
        
        // Comprar por WhatsApp
        checkoutBtn.addEventListener('click', function() {
            const cart = getCart();
            if (cart.length === 0) return;
            
            let message = "¡Hola! Estoy interesado en los siguientes productos:\n\n";
            let total = 0;
            
            cart.forEach(item => {
                message += `- ${item.name} (Código: ${item.code})\n`;
                message += `  Cantidad: ${item.quantity}\n`;
                message += `  Precio unitario: ${formatCOP(item.price)}\n`;
                total += item.price * item.quantity;
            });
            
            message += `\nTOTAL: ${formatCOP(total)}`;
            
            const whatsappUrl = `https://wa.me/${<?php echo json_encode($whatsappNumber); ?>}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        });
        
        // Eventos para botones "Agregar al carrito"
        document.querySelectorAll('.btn-add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.getAttribute('data-id'));
                const productName = this.getAttribute('data-name');
                const productPrice = parseFloat(this.getAttribute('data-price'));
                const productCode = this.getAttribute('data-code');
                const productImage = this.getAttribute('data-image');
                
                addToCart(productId, productName, productPrice, productCode, productImage);
            });
        });
        
        // Inicializar contador del carrito
        updateCartCount();
    });
</script>

<!-- Footer -->
<footer class="bg-black text-white py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0 flex items-center space-x-3">
                <i class="fas fa-gem text-2xl text-white"></i>
                <h3 class="text-xl font-bold">Joyería Hodo</h3>
            </div>
            
            <div class="text-center md:text-right">
                <h4 class="font-semibold text-white mb-2">Ubicación</h4>
                <p><i class="fas fa-map-marker-alt mr-2"></i>Colombia, La Guajira, Maicao</p>
                <p>JOYERIASHODO</p>
            </div>
        </div>
        
        <div class="flex justify-center space-x-6 mt-6">
            <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="text-gray-300 hover:text-white transition duration-300">
                <i class="fab fa-whatsapp"></i>
            </a>
        </div>
        
        <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-400 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Joyería Hodo. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

</body>
</html>