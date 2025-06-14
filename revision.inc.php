<h2>Pedidos para Revisión</h2>
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
    <form method="GET" action="controlador.php">
        <table border="1">
            <tr>
                <th>Mesa</th>
                <th>Items</th>
                <th>Observaciones</th>
                <th>Acción</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
                <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
                <td>
                    <input type="hidden" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">
                    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
                    <button type="submit" name="Siguiente">Marcar para Facturar</button>
                </td>
            </tr>
        </table>
    </form>
<?php else: ?>
    <div class="alert alert-warning">
        No hay pedidos para revisión en este proceso.
    </div>
<?php endif; ?>