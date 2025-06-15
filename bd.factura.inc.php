<?php
$resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa, f.total, f.metodo_pago 
    FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    LEFT JOIN facturas f ON p.id = f.pedido_id
    WHERE p.id = $ticket AND (p.estado = 'para_facturar' OR p.estado = 'completado')");

if (!$resultado) {
    die("Error en consulta facturación: ". mysqli_error($con));
}

$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}

error_log("Pedidos para facturación encontrados para ticket $ticket: ". count($pedidos));
?>