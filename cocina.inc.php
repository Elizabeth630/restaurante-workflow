<h2>Pedido en Cocina</h2>
<?php if (empty($pedidos)): ?>
    <div class="alert alert-warning">
        No hay pedidos en cocina para este proceso.
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
                <th>Estado</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido["nombre_mesa"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido["items"])); ?></td>
                <td>
                    <select name="estado" required>
                        <option value="en_cocina" <?php echo $pedido["estado"] == 'en_cocina' ? 'selected' : ''; ?>>En preparación</option>
                        <?php if ($flujo == 'F1'): ?>
                            <option value="para_revision" <?php echo $pedido["estado"] == 'para_revision' ? 'selected' : ''; ?>>Listo para servir</option>
                        <?php else: ?>
                            <option value="para_revision" <?php echo $pedido["estado"] == 'para_revision' ? 'selected' : ''; ?>>Listo para facturar</option>
                        <?php endif; ?>
                    </select>
                </td>
                <td>
                    <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido["id"]; ?>">
                    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                    <button type="submit" name="Siguiente">Actualizar Estado</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </form>
<?php endif; ?>