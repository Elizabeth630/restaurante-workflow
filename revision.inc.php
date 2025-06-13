<h2>Pedido para Revisión</h2>

<?php if (empty($pedidos)): ?>
    <div class="alert alert-warning">
        No hay pedidos listos para revisión en este proceso.
        <?php
        // Debug adicional
        $check_pedido = mysqli_fetch_assoc(mysqli_query($con, 
            "SELECT estado FROM pedidos WHERE id = $ticket"));
        $count_para_revision = mysqli_fetch_assoc(mysqli_query($con, 
            "SELECT COUNT(*) as total FROM pedidos WHERE estado='para_revision'"))['total'];
        echo "<br>(Estado actual del pedido #$ticket: " . ($check_pedido['estado'] ?? 'no encontrado') . ")";
        echo "<br>(Total de pedidos para revisión en sistema: $count_para_revision)";
        ?>
    </div>
<?php else: ?>
<form method="GET" action="controlador.php">
    <table border="1">
        <thead>
            <tr>
                <th>Mesa</th>
                <th>Items</th>
                <th>Observaciones</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
            <tr>
                <td><?php echo htmlspecialchars($pedido['nombre_mesa']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido['items'])); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido['observaciones'])); ?></td>
                <td>
                    <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido['id']; ?>">
                    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                    <button type="submit" name="Siguiente">Marcar para Facturar</button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</form>
<?php endif; ?>