<?php
if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["pedido_id"]) || !isset($_GET["estado"])) {
        die("Error: Parámetros incompletos");
    }
    
    $pedido_id = (int)$_GET["pedido_id"];
    $estado = mysqli_real_escape_string($con, $_GET["estado"]);
    
    // Para F2, cuando se marca como listo va directo a para_facturar
    if ($flujo == 'F2' && $estado == 'para_revision') {
        $estado = 'para_facturar';
    }
    
    $sql = "UPDATE pedidos SET estado = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "si", $estado, $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al actualizar pedido: " . mysqli_error($con));
    }
}
?>