<?php
// --- ARCHIVO: entrega.inc.php ---
?>
<h2>Entrega del Pedido</h2>
<?php
// Obtener el pedido actual
$pedido_actual = null;
if ($ticket > 0) {
    $query = "SELECT p.*, m.nombre as nombre_mesa FROM pedidos p
    JOIN mesas m ON p.mesa_id = m.id
    WHERE p.id = $ticket";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $pedido_actual = mysqli_fetch_assoc($result);
    }
}

if ($pedido_actual):
?>
<table border="1">
    <tr>
        <th>Mesa</th>
        <th>Items</th>
        <th>Observaciones</th>
        <th>Tipo de Preparación</th>
        <th>Estado Actual</th>
        <th>Acción Final</th>
    </tr>
    <tr>
        <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
        <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
        <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
        <td><?php echo ucfirst(htmlspecialchars($pedido_actual["tipo_preparacion"])); ?></td>
        <td><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($pedido_actual["estado"]))); ?></td>
        <td>
            <input type="hidden" name="accion_final" value="entregado">
            <p>Pedido entregado con éxito</p>
        </td>
    </tr>
</table>

<!-- Campos ocultos para el botón Siguiente -->
<input type="hidden" id="pedido_id" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">
<?php else: ?>
<div class="alert alert-warning">
    No hay pedidos para entrega en este proceso.
</div>
<?php endif; ?>