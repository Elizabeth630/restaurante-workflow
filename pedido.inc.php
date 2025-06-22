<?php
// ==================== ARCHIVO: pedido.inc.php (ESTRUCTURA BD CORRECTA) ====================
?>
<h2>Nuevo Pedido</h2>
<?php
// Buscar si ya existe un pedido para este ticket
$pedido_existente = null;
if ($ticket > 0) {
    $query = "SELECT * FROM pedidos WHERE id = $ticket";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $pedido_existente = mysqli_fetch_assoc($result);
    }
}
?>

<!-- Mantener contexto del flujo actual de forma dinámica -->
<input type="hidden" name="flujo_context" value="<?php echo htmlspecialchars($flujo ?? ''); ?>">
<input type="hidden" name="proceso_context" value="<?php echo htmlspecialchars($proceso ?? ''); ?>">
<input type="hidden" name="ticket_context" value="<?php echo (int)($ticket ?? 0); ?>">

<table border="1">
<tr>
    <th>Mesa</th>
    <th>Items</th>
    <th>Observaciones</th>
</tr>
<tr>
    <td>
        <select id="mesa_id" name="mesa_id" required>
            <?php
            $resultado = mysqli_query($con, "SELECT * FROM mesas");
            while ($fila = mysqli_fetch_array($resultado)) {
                $selected = ($pedido_existente && $fila["id"] == $pedido_existente["mesa_id"]) ? 'selected' : '';
                echo "<option value='".(int)$fila["id"]."' $selected>".htmlspecialchars($fila["nombre"])."</option>";
            }
            ?>
        </select>
    </td>
    <td>
        <textarea id="items" name="items" rows="5" cols="50" placeholder="Ingrese los items del pedido" required><?php
            echo $pedido_existente ? htmlspecialchars($pedido_existente["items"]) : '';
        ?></textarea>
    </td>
    <td>
        <textarea id="observaciones" name="observaciones" rows="3" cols="50" placeholder="Alergias, preferencias, etc."><?php
            echo $pedido_existente ? htmlspecialchars($pedido_existente["observaciones"]) : '';
        ?></textarea>
    </td>
</tr>
</table>

<?php
$resultado = mysqli_query($con, "SELECT * FROM pedidos WHERE estado='pendiente' AND mesero='".$_SESSION["usuario"]."'");
$pedidos = [];
while ($fila = mysqli_fetch_array($resultado)) {
    $pedidos[] = $fila;
}
?>