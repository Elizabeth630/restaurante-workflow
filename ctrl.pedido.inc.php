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
    
    $sql = "INSERT INTO pedidos (mesa_id, items, observaciones, estado, mesero) 
        VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $mesa_id, $items, $observaciones, $estado_inicial, $_SESSION["usuario"]);
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al crear pedido: " . mysqli_error($con));
    }
    
    // Obtener el ID del pedido creado
    $pedido_id = mysqli_insert_id($con);
    
    // Actualizar el ticket en flujousuario para asociarlo con el pedido
    $update_sql = "UPDATE flujousuario SET ticket = ? 
        WHERE ticket = ? AND flujo = ? AND proceso = ? AND fechafinal IS NULL";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, "iiss", $pedido_id, $ticket, $flujo, $proceso);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        error_log("Advertencia: No se pudo actualizar el ticket: " . mysqli_error($con));
    }
    
    // Actualizar la variable ticket para el resto del proceso
    $ticket = $pedido_id;
}
?>
