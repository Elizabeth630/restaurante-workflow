<?php
// === ARCHIVO: ctrl.preparacion_especial.inc.php (VERSIÓN CORREGIDA) ===

if (isset($_GET["Siguiente"])) {
    error_log("=== CTRL PREPARACION ESPECIAL ===");
    error_log("Parámetros recibidos: " . print_r($_GET, true));
    
    // Validar parámetros requeridos
    $required_params = ['pedido_id', 'estado', 'flujo', 'proceso', 'ticket'];
    foreach ($required_params as $param) {
        if (!isset($_GET[$param])) {
            error_log("ERROR: Falta parámetro $param");
            die("Error: Falta parámetro requerido: $param");
        }
    }

    $pedido_id = (int)$_GET["pedido_id"];
    $estado = mysqli_real_escape_string($con, $_GET["estado"]);
    
    error_log("Actualizando pedido $pedido_id a estado: $estado");
    
    // Actualizar estado del pedido con mejor validación
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ? AND tipo_preparacion = 'complejo'";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($con));
        die("Error preparando consulta: " . mysqli_error($con));
    }
    
    mysqli_stmt_bind_param($stmt, "si", $estado, $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Error ejecutando actualización: " . mysqli_stmt_error($stmt));
        die("Error al actualizar pedido: " . mysqli_stmt_error($stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    error_log("Filas afectadas en actualización: $affected_rows");
    
    if ($affected_rows == 0) {
        error_log("ADVERTENCIA: No se actualizó ninguna fila para pedido $pedido_id");
        // Verificar si el pedido existe
        $check_query = "SELECT estado, tipo_preparacion FROM pedidos WHERE id = $pedido_id";
        $check_result = mysqli_query($con, $check_query);
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $check_data = mysqli_fetch_assoc($check_result);
            error_log("Pedido existe - Estado: {$check_data['estado']}, Tipo: {$check_data['tipo_preparacion']}");
        }
    }
    
    $_SESSION["mensaje"] = "Estado del pedido actualizado correctamente a: $estado";
    error_log("Proceso de preparación especial completado exitosamente");
}
?>