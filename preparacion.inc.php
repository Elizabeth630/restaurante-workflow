<h2>Pedido para Preparar</h2>
<?php if (empty($pedidos)): ?>
    <div class="alert alert-warning">
        No se encontró el pedido asociado a este proceso.
        <?php
        // Debug adicional
        $check_pedido = mysqli_fetch_assoc(mysqli_query($con, 
            "SELECT estado FROM pedidos WHERE id = $ticket"));
        echo "(Estado actual del pedido #$ticket: " . ($check_pedido['estado'] ?? 'no encontrado') . ")";
        ?>
    </div>
<?php else: ?>
<form method="GET" action="controlador.php">
    <table border="1">
        <tr>
            <th>Mesa</th>
            <th>Items</th>
            <th>Observaciones</th>
            <th>Acción</th>
        </tr>
        <?php foreach ($pedidos as $pedido): ?>
        <tr>
            <td><?php echo htmlspecialchars($pedido["nombre_mesa"]); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido["items"])); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido["observaciones"])); ?></td>
            <td>
                <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido["id"]; ?>">
                <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                <button type="submit" name="Siguiente">Enviar a Cocina</button>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</form>
<?php endif; ?>