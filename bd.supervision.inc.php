<?php
// --- ARCHIVO: bd.supervision.inc.php ---
$resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    WHERE p.id = $ticket AND p.estado = 'para_supervision' AND p.tipo_preparacion = 'complejo'");

if (!$resultado) {
    die("Error en consulta supervisión: " . mysqli_error($con));
}

$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}

error_log("Pedidos para supervisión encontrados para ticket $ticket: " . count($pedidos));
?>