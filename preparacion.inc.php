<?php
// ==================== ARCHIVO: preparacion.inc.php (MODIFICADO) ====================
?>
<h2>Pedidos para Preparar</h2>
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
    <th>Estado</th>
</tr>
<tr>
    <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
    <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
    <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
    <td>Listo para enviar a cocina</td>
</tr>
</table>

<!-- Campos ocultos para el botón Siguiente -->
<input type="hidden" id="pedido_id" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">

<?php else: ?>
<p>No se encontró el pedido asociado a este proceso.</p>
<?php endif; ?>
