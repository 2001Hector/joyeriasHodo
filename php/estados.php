<?php
// Incluir la conexión
include_once __DIR__ . '/../crudP/conexion.php';

// Configuración de paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6922726226939700"
     crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../src/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Joyería Hodo - Administración de Pedidos</title>
   
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <style>
        /* Estilos específicos para la versión móvil */
        @media (max-width: 767px) {
            .desktop-table {
                display: none;
            }
            .mobile-cards {
                display: block;
            }
        }
        @media (min-width: 768px) {
            .desktop-table {
                display: block;
            }
            .mobile-cards {
                display: none;
            }
        }
    </style>
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
                <div class="hidden md:flex items-center gap-2">
                    <a href="../php/index.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="../php/ver_productos.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap">
                        <i class="fas fa-eye mr-2"></i>Productos
                    </a>
                    <a href="../php/hacerP.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap">
                        <i class="fas fa-shopping-basket mr-2"></i>Pedidos
                    </a>
                    <a href="../php/productos_generalesU.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap">
                        <i class="fas fa-users mr-2"></i>Clientes
                    </a>
                    <a href="../php/estados.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap bg-yellow-700">
                        <i class="fas fa-truck mr-2"></i>Estado
                    </a>
                    <a href="../php/ver_personalizaciones.php" class="text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm whitespace-nowrap">
                        <i class="fas fa-paint-brush mr-2"></i>Personalizaciones
                    </a>
                </div>
                
                <!-- Botón hamburguesa móvil -->
                <div class="md:hidden flex items-center">
                    <button id="menu-btn" class="text-white focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Menú móvil -->
            <div id="mobile-menu" class="hidden md:hidden pb-4 space-y-2">
                <a href="../php/index.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-home mr-2"></i>Inicio
                </a>
                <a href="../php/ver_productos.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-eye mr-2"></i>Productos
                </a>
                <a href="../php/hacerP.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-shopping-basket mr-2"></i>Pedidos
                </a>
                <a href="../php/productos_generalesU.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-users mr-2"></i>Clientes
                </a>
                <a href="../php/estados.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm bg-yellow-700">
                    <i class="fas fa-truck mr-2"></i>Estado
                </a>
                <a href="../php/ver_personalizaciones.php" class="block text-white hover:text-yellow-200 font-medium transition duration-300 px-3 py-2 rounded-md text-sm">
                    <i class="fas fa-paint-brush mr-2"></i>Personalizaciones
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Administración de Pedidos</h1>
        
        <!-- Notificación de pedidos pendientes -->
        <?php
        try {
            $stmt_pendientes = $conexionJ->query("SELECT COUNT(*) AS total FROM pedidos WHERE pago_completo = 0");
            $num_pendientes = $stmt_pendientes->fetch()['total'];
            
            if ($num_pendientes > 0) {
                echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <strong class="font-bold">¡Atención! </strong>
                            <span class="ml-2">Tienes '.$num_pendientes.' pedido(s) con pago incompleto.</span>
                        </div>
                      </div>';
            }
            
            // Consulta principal
            $query = "SELECT 
                        p.id_pedidos, 
                        p.fecha_pedidos, 
                        p.estado_pedido,
                        p.monto_pagado,
                        p.total_pedido,
                        p.pago_completo,
                        u.nombre_u AS nombre_usuario,
                        u.numero_u AS telefono_usuario,
                        u.direccion_u AS direccion_usuario,
                        GROUP_CONCAT(pr.nombre_Producto SEPARATOR ', ') AS productos,
                        SUM(dp.cantidad_d) AS total_productos
                      FROM pedidos p
                      JOIN usuarios u ON p.id_usuario = u.id_u
                      JOIN detalles_pedidos dp ON p.id_pedidos = dp.id_pedidos
                      JOIN productos pr ON dp.id_producto = pr.id_producto
                      GROUP BY p.id_pedidos
                      ORDER BY p.pago_completo ASC, p.fecha_pedidos DESC
                      LIMIT :offset, :limit";
            
            $stmt = $conexionJ->prepare($query);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
            $stmt->execute();
            
            $total_registros = $conexionJ->query("SELECT COUNT(DISTINCT p.id_pedidos) AS total FROM pedidos p")->fetch()['total'];
            $total_paginas = ceil($total_registros / $registros_por_pagina);
        ?>
        
        <!-- ========== VERSIÓN DESKTOP (TABLA) ========== -->
        <div class="desktop-table bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cant.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if ($stmt->rowCount() > 0) {
                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $color = $row['pago_completo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                                $icon = $row['pago_completo'] ? 'fas fa-check-double' : 'fas fa-exclamation-triangle';
                                $estado = $row['pago_completo'] ? 'Completado' : 'Pago parcial';
                                $saldo_pendiente = $row['total_pedido'] - $row['monto_pagado'];
                                
                                echo '<tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">'.$row['id_pedidos'].'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">'.date('d/m/Y', strtotime($row['fecha_pedidos'])).'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">'.$row['nombre_usuario'].'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">'.($row['telefono_usuario'] ?? 'N/A').'</td>
                                        <td class="px-4 py-4 text-sm">'.($row['direccion_usuario'] ?? 'N/A').'</td>
                                        <td class="px-4 py-4 text-sm">'.$row['productos'].'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">'.$row['total_productos'].'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">$'.number_format($row['total_pedido'], 2).'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">$'.number_format($row['monto_pagado'], 2).'</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full '.$color.'">
                                                <i class="'.$icon.' mr-1"></i> '.$estado.'
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm">';
                                
                                if ($saldo_pendiente > 0) {
                                    echo '<button onclick="completarPago('.$row['id_pedidos'].', '.$saldo_pendiente.')" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs whitespace-nowrap">
                                            Completar pago
                                          </button>';
                                } else {
                                    echo '<span class="text-gray-500 text-xs">Completado</span>';
                                }
                                
                                echo '</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="11" class="px-4 py-4 text-center text-sm">No hay pedidos registrados</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación desktop -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo ($pagina_actual - 1); ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior
                        </a>
                    <?php endif; ?>
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo ($pagina_actual + 1); ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Siguiente
                        </a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando 
                            <span class="font-medium"><?php echo ($offset + 1); ?></span> 
                            a 
                            <span class="font-medium"><?php echo min($offset + $registros_por_pagina, $total_registros); ?></span> 
                            de 
                            <span class="font-medium"><?php echo $total_registros; ?></span> 
                            resultados
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <?php if ($pagina_actual > 1): ?>
                                <a href="?pagina=<?php echo ($pagina_actual - 1); ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Anterior</span>
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php 
                            $max_links = 5;
                            $start = max(1, $pagina_actual - floor($max_links / 2));
                            $end = min($total_paginas, $start + $max_links - 1);
                            
                            if ($start > 1) {
                                echo '<a href="?pagina=1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">1</a>';
                                if ($start > 2) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                $active = $i == $pagina_actual ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50';
                                echo '<a href="?pagina='.$i.'" class="'.$active.' relative inline-flex items-center px-4 py-2 border text-sm font-medium">'.$i.'</a>';
                            }
                            
                            if ($end < $total_paginas) {
                                if ($end < $total_paginas - 1) {
                                    echo '<span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">...</span>';
                                }
                                echo '<a href="?pagina='.$total_paginas.'" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">'.$total_paginas.'</a>';
                            }
                            ?>
                            
                            <?php if ($pagina_actual < $total_paginas): ?>
                                <a href="?pagina=<?php echo ($pagina_actual + 1); ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">Siguiente</span>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== VERSIÓN MÓVIL (TARJETAS) ========== -->
        <div class="mobile-cards space-y-4">
            <?php
            if ($stmt->rowCount() > 0) {
                // Re-ejecutar la consulta para el móvil
                $stmt->execute();
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $color = $row['pago_completo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    $icon = $row['pago_completo'] ? 'fas fa-check-double' : 'fas fa-exclamation-triangle';
                    $estado = $row['pago_completo'] ? 'Completado' : 'Pago parcial';
                    $saldo_pendiente = $row['total_pedido'] - $row['monto_pagado'];
                    
                    echo '<div class="bg-white p-4 rounded-lg shadow">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-gray-900">Pedido #'.$row['id_pedidos'].'</div>
                                    <div class="text-sm text-gray-500">'.date('d/m/Y', strtotime($row['fecha_pedidos'])).'</div>
                                </div>
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full '.$color.'">
                                    <i class="'.$icon.' mr-1"></i> '.$estado.'
                                </span>
                            </div>
                            
                            <div class="mt-3 space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Cliente:</span>
                                    <span class="font-medium">'.$row['nombre_usuario'].'</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Contacto:</span>
                                    <span>'.($row['telefono_usuario'] ?? 'N/A').'</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Productos:</span>
                                    <span class="text-right">'.$row['total_productos'].'</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-bold">$'.number_format($row['total_pedido'], 2).'</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Pagado:</span>
                                    <span>$'.number_format($row['monto_pagado'], 2).'</span>
                                </div>';
                    
                    if ($saldo_pendiente > 0) {
                        echo '<div class="pt-2 text-center">
                                <button onclick="completarPago('.$row['id_pedidos'].', '.$saldo_pendiente.')" 
                                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                    Completar pago ($'.number_format($saldo_pendiente, 2).')
                                </button>
                              </div>';
                    }
                    
                    echo '</div>
                        </div>';
                }
            } else {
                echo '<div class="bg-white p-4 rounded-lg shadow text-center text-gray-500">
                        No hay pedidos registrados
                      </div>';
            }
            ?>
            
            <!-- Paginación móvil -->
            <div class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between">
                    <?php if ($pagina_actual > 1): ?>
                        <a href="?pagina=<?php echo ($pagina_actual - 1); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                            <i class="fas fa-chevron-left mr-1"></i> Anterior
                        </span>
                    <?php endif; ?>
                    
                    <span class="px-4 py-2 text-sm text-gray-700">
                        Pág. <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>
                    </span>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <a href="?pagina=<?php echo ($pagina_actual + 1); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    <?php else: ?>
                        <span class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed">
                            Siguiente <i class="fas fa-chevron-right ml-1"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Script para completar pagos -->
        <script>
        function completarPago(idPedido, monto) {
            if(confirm(`¿Marcar el pedido ${idPedido} como pagado completo? Saldo a registrar: $${monto.toFixed(2)}`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../crudP/completar_pago.php';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id_pedido';
                inputId.value = idPedido;
                form.appendChild(inputId);
                
                const inputMonto = document.createElement('input');
                inputMonto.type = 'hidden';
                inputMonto.name = 'monto';
                inputMonto.value = monto;
                form.appendChild(inputMonto);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        </script>
        
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
        
        <?php
        } catch(PDOException $e) {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg" role="alert">
                    <p class="font-bold">Error:</p>
                    <p>No se pudieron cargar los pedidos. '.$e->getMessage().'</p>
                  </div>';
        }
        ?>
    </div>
</body>
</html>