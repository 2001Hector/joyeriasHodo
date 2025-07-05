<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Joyería Hodo - Sobre Nosotros</title>
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navbar (solo se agregó el script de control aquí) -->
<nav class="bg-gradient-to-r from-black via-purple-900 to-black shadow-lg ">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-4">
                <i class="fas fa-gem text-white text-2xl"></i>
                <span class="text-white font-bold text-xl">Joyería hodo</span>
            </div>
            
            <!-- Menú para desktop (visible en md y arriba) -->
            <div class="hidden md:flex items-center space-x-8">  <!-- Este div faltaba -->
                <a href="../php/productos_generalesU.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                        <i class="fas fa-box-open mr-2"></i>Todos los productos
                    </a>
                    <a href="../php/categorias.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                        <i class="fas fa-info-circle mr-2"></i>Productos por categorias
                    </a>
                    <a href="../php/personalizar_producto.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                        <i class="fas fa-crown mr-2"></i>Personalización de joyas
                    </a>

                    <a href="../php/informacion.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                        <i class="fas fa-info-circle mr-2"></i>Garantías e información
                    </a>
            </div>
            
            <!-- Botón hamburguesa para móvil -->
            <div class="md:hidden flex items-center">
                <button id="menu-btn" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-2xl" id="menu-icon"></i>
                </button>
            </div>
        </div>
        
        <!-- Menú móvil (oculto por defecto) -->
        <div id="mobile-menu" class="hidden md:hidden pb-4">
            <div class="flex flex-col space-y-3 px-2 pt-2">
                <a href="../php/vistaUsuarios.php" class="text-white hover:text-gray-300 font-medium transition duration-300 px-3 py-2 rounded-md">
                    <i class="fas fa-box-open mr-2"></i>volver a ver productos
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Todo el resto de tu contenido PERMANECE EXACTAMENTE IGUAL -->
    <!-- Hero Section -->
    <div class="bg-black text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Nuestra Historia</h1>
            <p class="text-xl text-gray-300 max-w-3xl mx-auto">Más de 20 años creando piezas excepcionales con artesanía tradicional y diseño innovador</p>
        </div>
    </div>

    <!-- Sección de Información -->
    <div class="container mx-auto px-4 py-16">

        <!-- Misión, Visión, Valores -->
        <div class="grid md:grid-cols-3 gap-8 mb-16">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <div class="text-4xl text-black mb-4"><i class="fas fa-bullseye"></i></div>
                <h3 class="text-xl font-bold mb-3">Misión</h3>
                <p class="text-gray-700">Crear joyas excepcionales que capturen momentos especiales, combinando técnicas artesanales tradicionales con diseño contemporáneo, ofreciendo a nuestros clientes piezas únicas de la más alta calidad.</p>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-md">
                <div class="text-4xl text-black mb-4"><i class="fas fa-eye"></i></div>
                <h3 class="text-xl font-bold mb-3">Visión</h3>
                <p class="text-gray-700">Ser reconocidos como la joyería líder en diseño innovador y calidad excepcional en Colombia, expandiendo nuestro legado de excelencia a nuevas generaciones de amantes de la joyería fina.</p>
            </div>
            
            <div class="bg-white p-8 rounded-lg shadow-md">
                <div class="text-4xl text-black mb-4"><i class="fas fa-heart"></i></div>
                <h3 class="text-xl font-bold mb-3">Valores</h3>
                <ul class="text-gray-700 list-disc pl-5">
                    <li class="mb-2">Excelencia en cada detalle</li>
                    <li class="mb-2">Integridad y transparencia</li>
                    <li class="mb-2">Innovación constante</li>
                    <li class="mb-2">Pasión por el arte joyero</li>
                </ul>
            </div>
        </div>

        <!-- Garantías -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-16">
            <div class="md:flex">
                <div class="md:w-1/3 bg-black text-white p-8 flex items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-4">Nuestras Garantías</h2>
                        <i class="fas fa-certificate text-5xl opacity-20"></i>
                    </div>
                </div>
                <div class="md:w-2/3 p-8">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="flex items-start">
                            <div class="text-black mr-4 mt-1"><i class="fas fa-gem"></i></div>
                            <div>
                                <h4 class="font-bold mb-2">Calidad Premium</h4>
                                <p class="text-gray-700">Todos nuestros materiales son certificados y de la más alta pureza.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="text-black mr-4 mt-1"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <h4 class="font-bold mb-2">Garantía de 2 Años</h4>
                                <p class="text-gray-700">Cobertura contra defectos de fabricación en todas nuestras piezas.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="text-black mr-4 mt-1"><i class="fas fa-exchange-alt"></i></div>
                            <div>
                                <h4 class="font-bold mb-2">30 Días para Cambios</h4>
                                <p class="text-gray-700">Si no está satisfecho, puede cambiar su pieza dentro del primer mes.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="text-black mr-4 mt-1"><i class="fas fa-medal"></i></div>
                            <div>
                                <h4 class="font-bold mb-2">Autenticidad</h4>
                                <p class="text-gray-700">Certificado de autenticidad incluido con cada pieza de valor.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ubicación y Contacto -->
        <div class="grid md:grid-cols-2 gap-8 mb-16">
            <div>
                <h2 class="text-3xl font-bold mb-6">Nuestra Sede Principal</h2>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="flex items-start mb-4">
                        <i class="fas fa-map-marker-alt text-black mr-4 mt-1"></i>
                        <div>
                            <h4 class="font-bold">Dirección</h4>
                            <p class="text-gray-700">Carrera 15 #88-64, Bogotá, Colombia</p>
                            <p class="text-gray-500">Zona G - Chapinero Alto</p>
                        </div>
                    </div>
                    <div class="flex items-start mb-4">
                        <i class="fas fa-clock text-black mr-4 mt-1"></i>
                        <div>
                            <h4 class="font-bold">Horario de Atención</h4>
                            <p class="text-gray-700">Lunes a Viernes: 9:00 AM - 7:00 PM</p>
                            <p class="text-gray-700">Sábados: 10:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-phone-alt text-black mr-4 mt-1"></i>
                        <div>
                            <h4 class="font-bold">Contacto</h4>
                            <p class="text-gray-700">+57 1 555 1234</p>
                            <p class="text-gray-700">info@joyeriahodo.com</p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <div class="h-full bg-gray-200 rounded-lg overflow-hidden">
                    <!-- Mapa de Google Maps (reemplaza el src con tu embed real) -->
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3976.981156715988!2d-74.0561289256862!3d4.6695506413939!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3f9a3e2e5e5b5f%3A0x1a5b1b1b1b1b1b1b!2sZona%20G%2C%20Bogot%C3%A1!5e0!3m2!1ses!2sco!4v1620000000000!5m2!1ses!2sco" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy"
                            class="min-h-[300px]">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- Equipo -->
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold mb-2">Nuestros Artesanos</h2>
            <p class="text-gray-600 max-w-2xl mx-auto mb-8">Expertos joyeros con más de 15 años de experiencia en el diseño y fabricación de piezas exclusivas</p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-64 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user-tie text-6xl text-gray-400"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-1">Carlos Rodríguez</h3>
                        <p class="text-black font-medium mb-3">Maestro Joyero</p>
                        <p class="text-gray-700">Especialista en diamantes y piedras preciosas con 25 años de experiencia.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-64 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user-tie text-6xl text-gray-400"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-1">Ana Martínez</h3>
                        <p class="text-black font-medium mb-3">Diseñadora Principal</p>
                        <p class="text-gray-700">Creatividad e innovación en cada diseño, premiada internacionalmente.</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="h-64 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-user-tie text-6xl text-gray-400"></i>
                    </div>
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-1">Luis Gómez</h3>
                        <p class="text-black font-medium mb-3">Especialista en Metales</p>
                        <p class="text-gray-700">Dominio de técnicas ancestrales de fundición y aleación de metales.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-black text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0 flex items-center space-x-3">
                    <i class="fas fa-gem text-2xl text-white"></i>
                    <h3 class="text-xl font-bold">Joyería Hodo</h3>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-300 hover:text-white transition duration-300" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-white transition duration-300" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-300 hover:text-white transition duration-300" aria-label="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-6 pt-6 text-center text-gray-400 text-sm">
                <p>&copy; <?php echo date('Y'); ?> Joyería Hodo. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Script para el menú móvil (ÚNICO CAMBIO REALIZADO) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            
            menuBtn.addEventListener('click', function() {
                // Alternar visibilidad del menú
                mobileMenu.classList.toggle('hidden');
                
                // Cambiar icono
                if (mobileMenu.classList.contains('hidden')) {
                    menuIcon.classList.replace('fa-times', 'fa-bars');
                } else {
                    menuIcon.classList.replace('fa-bars', 'fa-times');
                }
            });
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>