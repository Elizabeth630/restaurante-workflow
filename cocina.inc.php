<?php
// ==================== ARCHIVO: cocina.inc.php (MODIFICADO) ====================
?>
<h2>Pedido en Cocina</h2>
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
    <th>Estado Actual</th>
    <th>Nuevo Estado</th>
</tr>
<tr>
    <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
    <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
    <td><?php echo ucfirst(str_replace('_', ' ', $pedido_actual["estado"])); ?></td>
    <td>
        <select id="estado" name="estado" required>
            <option value="en_cocina" <?php echo $pedido_actual["estado"] == 'en_cocina' ? 'selected' : ''; ?>>En preparación</option>
            <?php if ($flujo == 'F1'): ?>
                <option value="para_revision" <?php echo $pedido_actual["estado"] == 'para_revision' ? 'selected' : ''; ?>>Listo para servir</option>
            <?php else: ?>
                <option value="para_revision" <?php echo $pedido_actual["estado"] == 'para_revision' ? 'selected' : ''; ?>>Listo para facturar</option>
            <?php endif; ?>
        </select>
    </td>
</tr>
</table>

<!-- Campos ocultos para el botón Siguiente -->
<input type="hidden" id="pedido_id" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">

<?php else: ?>
<div class="alert alert-warning">
    No hay pedidos en cocina para este proceso.
</div>
<?php endif; ?>