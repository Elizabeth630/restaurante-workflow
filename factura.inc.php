<h2>Facturación</h2>
<?php if (empty($pedidos)): ?>
    <div class="alert alert-warning">
        No hay pedidos listos para facturar en este proceso.
        <?php
        $check_pedido = mysqli_fetch_assoc(mysqli_query($con, "SELECT estado FROM pedidos WHERE id = $ticket"));
        echo "<br>(Estado actual del pedido #$ticket: " . ($check_pedido['estado'] ?? 'no encontrado') . ")";
        ?>
    </div>
<?php else: ?>
    <form method="GET" action="controlador.php">
        <table border="1">
            <tr>
                <th>Mesa</th>
                <th>Items</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido["nombre_mesa"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido["items"])); ?></td>
                <td>
                    <input type="number" name="total" value="0" min="0" step="0.01" required>
                </td>
                <td>
                    <select name="metodo_pago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido["id"]; ?>">
                    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                    <button type="submit" name="Siguiente">Generar Factura</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </form>
<?php endif; ?>