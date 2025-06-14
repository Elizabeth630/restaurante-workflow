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

<form method="GET" action="controlador.php">
    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">

    <label>Mesa:</label>
    <select name="mesa_id" required>
        <?php
        $resultado = mysqli_query($con, "SELECT * FROM mesas");
        while ($fila = mysqli_fetch_array($resultado)) {
            $selected = ($pedido_existente && $fila["id"] == $pedido_existente["mesa_id"]) ? 'selected' : '';
            echo "<option value='".(int)$fila["id"]."' $selected>".htmlspecialchars($fila["nombre"])."</option>";
        }
        ?>
    </select>
    <br>
    
    <label>Items:</label><br>
    <textarea name="items" rows="5" cols="50" placeholder="Ingrese los items del pedido" required><?php 
        echo $pedido_existente ? htmlspecialchars($pedido_existente["items"]) : ''; 
    ?></textarea>
    <br>
    
    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3" cols="50" placeholder="Alergias, preferencias, etc."><?php 
        echo $pedido_existente ? htmlspecialchars($pedido_existente["observaciones"]) : ''; 
    ?></textarea>
    <br>
    
    <button type="submit" name="Siguiente">Guardar Pedido</button>
</form>