<?php
if (isset($_GET["Siguiente"])) {
    if (!isset($_GET["pedido_id"]) || !isset($_GET["total"]) || !isset($_GET["metodo_pago"])) {
        die("Error: Parámetros incompletos");
    }
    
    $pedido_id = (int)$_GET["pedido_id"];
    $total = (float)$_GET["total"];
    $metodo_pago = mysqli_real_escape_string($con, $_GET["metodo_pago"]);
    
    // Verificar si ya existe factura para actualizar en lugar de insertar
    $check_factura = mysqli_query($con, "SELECT id FROM facturas WHERE pedido_id = $pedido_id");
    
    if (mysqli_num_rows($check_factura) > 0) {
        // Actualizar factura existente
        $sql = "UPDATE facturas SET total = ?, metodo_pago = ?, fecha = NOW() WHERE pedido_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "dsi", $total, $metodo_pago, $pedido_id);
    } else {
        // Insertar nueva factura
        $sql = "INSERT INTO facturas (pedido_id, total, metodo_pago, fecha)
            VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ids", $pedido_id, $total, $metodo_pago);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al guardar factura: " . mysqli_error($con));
    }
    
    // Actualizar estado del pedido a 'completado'
    $sql = "UPDATE pedidos SET estado='completado' WHERE id=?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $pedido_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        die("Error al actualizar pedido: " . mysqli_error($con));
    }
}
?>