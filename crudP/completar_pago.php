<?php
include_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_pedido = $_POST['id_pedido'];
        $monto = $_POST['monto'];
        
        // Obtener el monto actual pagado
        $stmt = $conexionJ->prepare("SELECT monto_pagado, total_pedido FROM pedidos WHERE id_pedidos = :id_pedido");
        $stmt->bindParam(':id_pedido', $id_pedido);
        $stmt->execute();
        $pedido = $stmt->fetch();
        
        if ($pedido) {
            $nuevo_monto = $pedido['monto_pagado'] + $monto;
            $pago_completo = ($nuevo_monto >= $pedido['total_pedido']) ? 1 : 0;
            
            // Actualizar el pedido
            $update = $conexionJ->prepare("UPDATE pedidos SET monto_pagado = :monto_pagado, pago_completo = :pago_completo WHERE id_pedidos = :id_pedido");
            $update->bindParam(':monto_pagado', $nuevo_monto);
            $update->bindParam(':pago_completo', $pago_completo, PDO::PARAM_INT);
            $update->bindParam(':id_pedido', $id_pedido);
            $update->execute();
            
            // Redirigir de vuelta con mensaje de éxito
            header('Location: '.$_SERVER['HTTP_REFERER'].'?success=1');
            exit();
        }
    } catch(PDOException $e) {
        // Redirigir con mensaje de error
        header('Location: '.$_SERVER['HTTP_REFERER'].'?error='.urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit();
}
?>