<?php
// --- ARCHIVO: ctrl.entrega.inc.php ---
if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["pedido_id"]) || !isset($_GET["accion_final"])) {
        die("Error: Parámetros incompletos");
    }

    $pedido_id = (int)$_GET["pedido_id"];
    $accion_final = mysqli_real_escape_string($con, $_GET["accion_final"]);
    
    // Eliminar la lógica condicional y dejar solo:
    $nuevo_estado = 'completado'; // Siempre marca como completado

    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $nuevo_estado, $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al actualizar pedido: " . mysqli_error($con));
    }
    
    // Si estamos retrocediendo, no marcar como completado en flujousuario
    if (!isset($_GET["Anterior"])) {
        // Marcar proceso actual como completado
        $sql_update = "UPDATE flujousuario SET fechafinal = NOW()
        WHERE ticket = $pedido_id AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL";
        
        if (!mysqli_query($con, $sql_update)) {
            die("Error al actualizar proceso actual: " . mysqli_error($con));
        }
    }
}
?>