<h2>Nuevo Pedido</h2>
<form method="GET" action="controlador.php">
    <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
    <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
    <input type="hidden" name="ticket" value="<?php echo (int)$ticket; ?>">
    
    <label>Mesa:</label>
    <select name="mesa_id" required>
        <?php
        $resultado = mysqli_query($con, "SELECT * FROM mesas");
        while ($fila = mysqli_fetch_array($resultado)) {
            echo "<option value='".(int)$fila["id"]."'>".htmlspecialchars($fila["nombre"])."</option>";
        }
        ?>
    </select>
    <br><br>

    <label>Items:</label><br>
    <textarea name="items" rows="5" cols="50" placeholder="Ingrese los items del pedido" required></textarea>
    <br><br>

    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="3" cols="50" placeholder="Alergias, preferencias, etc."></textarea>
    <br><br>
    
    <button type="submit" name="Siguiente">Guardar Pedido</button>
</form>