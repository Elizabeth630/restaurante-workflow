<?php
// === ARCHIVO: bd.preparacion_especial.inc.php (VERSIÓN DEBUG) ===

// Debug: Verificar el estado actual del pedido
error_log("=== DEBUG PREPARACION ESPECIAL ===");
error_log("Ticket recibido: $ticket");

// Primero verificamos qué estado tiene realmente el pedido
$debug_query = "SELECT p.*, m.nombre as nombre_mesa 
                FROM pedidos p
                JOIN mesas m ON p.mesa_id = m.id  
                WHERE p.id = $ticket";
$debug_result = mysqli_query($con, $debug_query);

if ($debug_result && mysqli_num_rows($debug_result) > 0) {
    $debug_pedido = mysqli_fetch_assoc($debug_result);
    error_log("Estado actual del pedido: " . $debug_pedido["estado"]);
    error_log("Tipo preparación actual: " . $debug_pedido["tipo_preparacion"]);
    error_log("Mesa: " . $debug_pedido["nombre_mesa"]);
} else {
    error_log("ERROR: No se encontró el pedido con ID $ticket");
}

// Consulta principal con múltiples opciones de estado
$resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa 
    FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    WHERE p.id = $ticket 
    AND (p.estado IN ('en_preparacion_especial', 'en_preparacion', 'para_preparacion_especial'))
    AND (p.tipo_preparacion = 'complejo' OR p.tipo_preparacion IS NULL)");

if (!$resultado) {
    error_log("Error en consulta preparación especial: " . mysqli_error($con));
    die("Error en consulta preparación especial: " . mysqli_error($con));
}

$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
    error_log("Pedido encontrado - Estado: " . $fila["estado"] . ", Tipo: " . $fila["tipo_preparacion"]);
}

error_log("Pedidos en preparación especial encontrados para ticket $ticket: " . count($pedidos));

// Si no encontramos con la consulta principal, buscar con cualquier estado
if (count($pedidos) == 0) {
    error_log("No se encontraron pedidos, buscando con cualquier estado...");
    $resultado_cualquier = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa 
        FROM pedidos p
        JOIN mesas m ON p.mesa_id = m.id
        WHERE p.id = $ticket");
    
    if ($resultado_cualquier && mysqli_num_rows($resultado_cualquier) > 0) {
        $pedido_cualquier = mysqli_fetch_assoc($resultado_cualquier);
        error_log("Pedido existe pero con estado: " . $pedido_cualquier["estado"]);
        error_log("Tipo preparación: " . $pedido_cualquier["tipo_preparacion"]);
        
        // Si el pedido existe pero con estado incorrecto, lo corregimos
        if ($pedido_cualquier["tipo_preparacion"] == 'complejo') {
            error_log("Corrigiendo estado del pedido...");
            mysqli_query($con, "UPDATE pedidos SET estado = 'en_preparacion_especial' WHERE id = $ticket");
            
            // Volver a cargar los pedidos
            $resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa 
                FROM pedidos p
                JOIN mesas m ON p.mesa_id = m.id
                WHERE p.id = $ticket AND p.estado = 'en_preparacion_especial'");
            
            $pedidos = [];
            while ($fila = mysqli_fetch_array($resultado)) {
                $pedidos[] = $fila;
            }
            error_log("Después de corrección, pedidos encontrados: " . count($pedidos));
        }
    }
}
?>