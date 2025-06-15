<?php
// ==================== ARCHIVO: ctrl.cocina_condicion.inc.php ====================

if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["pedido_id"]) || !isset($_GET["decision"])) {
        die("Error: Parámetros incompletos para la decisión");
    }
    
    $pedido_id = (int)$_GET["pedido_id"];
    $decision = mysqli_real_escape_string($con, $_GET["decision"]);
    
    // Actualizar estado del pedido según la decisión
    if ($decision == 'verdad') {
        // Requiere revisión del mesero
        $nuevo_estado = 'requiere_revision';
    } else {
        // Va directo a facturación
        $nuevo_estado = 'factura_directa';
    }
    
    // Actualizar estado del pedido
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al actualizar pedido: " . mysqli_error($con));
    }
    
    // Guardar la decisión en una variable de sesión para usar en controlador.php
    $_SESSION["decision_condicion"] = $decision;
    
    error_log("Decisión tomada para pedido $pedido_id: $decision -> estado: $nuevo_estado");
}
?>