<?php
$resultado = mysqli_query($con, "SELECT p.*, m.nombre as nombre_mesa
    FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    WHERE p.id = $ticket AND p.estado = 'para_revision'");

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($con));
}

$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}

error_log("Pedidos para revisión encontrados para ticket $ticket: " . count($pedidos));
?>