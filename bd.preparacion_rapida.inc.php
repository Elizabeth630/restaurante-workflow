<?php
// --- ARCHIVO: bd.preparacion_rapida.inc.php ---
$resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    WHERE p.id = $ticket AND p.estado = 'en_preparacion_rapida' AND p.tipo_preparacion = 'simple'");

if (!$resultado) {
    die("Error en consulta preparación rápida: " . mysqli_error($con));
}

$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}

error_log("Pedidos en preparación rápida encontrados para ticket $ticket: " . count($pedidos));
?>