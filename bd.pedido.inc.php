<?php
$resultado = mysqli_query($con, "SELECT * FROM pedidos WHERE estado='pendiente' AND mesero='".$_SESSION["usuario"]."'");
$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}
?>