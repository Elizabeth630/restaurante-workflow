<h2>Facturación</h2>
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
    // Verificar si ya existe una factura para este pedido
    $factura_existente = null;
    $query_factura = "SELECT * FROM facturas WHERE pedido_id = $ticket";
    $result_factura = mysqli_query($con, $query_factura);
    if ($result_factura && mysqli_num_rows($result_factura) > 0) {
        $factura_existente = mysqli_fetch_assoc($result_factura);
    }
?>
    <form method="GET" action="controlador.php">
        <table border="1">
            <tr>
                <th>Mesa</th>
                <th>Items</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Acción</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
                <td>
                    <input type="number" name="total" value="<?php echo $factura_existente ? htmlspecialchars($factura_existente["total"]) : '0'; ?>" min="0" step="0.01" required>
                </td>
                <td>
                    <select name="metodo_pago" required>
                        <option value="efectivo" <?php echo ($factura_existente && $factura_existente["metodo_pago"] == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="tarjeta" <?php echo ($factura_existente && $factura_existente["metodo_pago"] == 'tarjeta') ? 'selected' : ''; ?>>Tarjeta</option>
                        <option value="transferencia" <?php echo ($factura_existente && $factura_existente["metodo_pago"] == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">
                    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                    <button type="submit" name="Siguiente">Generar Factura</button>
                </td>
            </tr>
        </table>
    </form>
<?php else: ?>
    <div class="alert alert-warning">
        No hay pedidos listos para facturar en este proceso.
    </div>
<?php endif; ?>