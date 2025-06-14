<?php
if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["pedido_id"])) {
        die("Error: Falta el parámetro pedido_id");
    }
    
    $pedido_id = (int)$_GET["pedido_id"];
    
    // Actualizar estado del pedido a 'para_facturar'
    $sql = "UPDATE pedidos SET estado='para_facturar' WHERE id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
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