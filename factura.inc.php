<?php
// --- ARCHIVO: facturacion.inc.php ---
?>
<h2>Facturación - Flujo 3</h2>
<?php
// Obtener el pedido actual
$pedido_actual = null;
if ($ticket > 0) {
    $query = "SELECT p.*, m.nombre as nombre_mesa, f.total, f.metodo_pago 
              FROM pedidos p
              JOIN mesas m ON p.mesa_id = m.id
              LEFT JOIN facturas f ON p.id = f.pedido_id
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
        <th>Total</th>
        <th>Método de Pago</th>
    </tr>
    <tr>
        <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
        <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
        <td>
            <input type="number" id="total" name="total" 
                   value="<?php echo isset($pedido_actual["total"]) ? htmlspecialchars($pedido_actual["total"]) : '0'; ?>" 
                   min="0" step="0.01" required>
        </td>
        <td>
            <select id="metodo_pago" name="metodo_pago" required>
                <option value="efectivo" <?php echo (isset($pedido_actual["metodo_pago"]) && $pedido_actual["metodo_pago"] == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                <option value="tarjeta" <?php echo (isset($pedido_actual["metodo_pago"]) && $pedido_actual["metodo_pago"] == 'tarjeta') ? 'selected' : ''; ?>>Tarjeta</option>
                <option value="transferencia" <?php echo (isset($pedido_actual["metodo_pago"]) && $pedido_actual["metodo_pago"] == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
            </select>
        </td>
    </tr>
</table>

<!-- Campos ocultos para el botón Siguiente -->
<input type="hidden" id="pedido_id" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">

<?php else: ?>
<div class="alert alert-warning">
    No hay pedidos listos para facturar en este proceso.
</div>
<?php endif; ?>