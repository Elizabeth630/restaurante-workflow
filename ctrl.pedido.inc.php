<?php
if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["mesa_id"]) || !isset($_GET["items"])) {
        die("Error: Faltan parámetros requeridos");
    }
    
    $mesa_id = (int)$_GET["mesa_id"];
    $items = mysqli_real_escape_string($con, $_GET["items"]);
    $observaciones = isset($_GET["observaciones"]) ? 
        mysqli_real_escape_string($con, $_GET["observaciones"]) : '';
    
    // Determinar el estado inicial según el flujo
    if ($flujo == 'F2') {
        $estado_inicial = 'en_cocina';  // F2 va directo a cocina
    } else {
        $estado_inicial = 'en_preparacion';  // F1 va a preparación primero
    }
    
    // Verificar si ya existe el pedido (para actualizar en lugar de insertar)
    $check_pedido = mysqli_query($con, "SELECT id FROM pedidos WHERE id = $ticket");
    
    if (mysqli_num_rows($check_pedido) > 0) {
        // Actualizar pedido existente
        $sql = "UPDATE pedidos SET mesa_id = ?, items = ?, observaciones = ?, estado = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "isssi", $mesa_id, $items, $observaciones, $estado_inicial, $ticket);
    } else {
        // Insertar nuevo pedido
        $sql = "INSERT INTO pedidos (id, mesa_id, items, observaciones, estado, mesero) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "iissss", $ticket, $mesa_id, $items, $observaciones, $estado_inicial, $_SESSION["usuario"]);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al guardar pedido: " . mysqli_error($con));
    }
    
    // Si es un nuevo pedido, actualizar el ticket en flujousuario
    if (mysqli_num_rows($check_pedido) == 0) {
        $update_sql = "UPDATE flujousuario SET ticket = ? 
            WHERE ticket = ? AND flujo = ? AND proceso = ? AND fechafinal IS NULL";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, "iiss", $ticket, $ticket, $flujo, $proceso);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            error_log("Advertencia: No se pudo actualizar el ticket: " . mysqli_error($con));
        }
    }
}
?>