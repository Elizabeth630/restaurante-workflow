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
}
?>