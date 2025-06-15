<?php
// === ARCHIVO: preparacion_especial.inc.php (VERSIÓN CORREGIDA) ===
?>
<h2>Preparación Especial</h2>

<?php
// Debug información
echo "<div style='background:#f0f0f0; padding:10px; margin:10px 0; border:1px solid #ccc;'>";
echo "<strong>DEBUG INFO:</strong><br>";
echo "Ticket: $ticket<br>";

// Verificar estado actual del pedido
$debug_query = "SELECT * FROM pedidos WHERE id = $ticket";
$debug_result = mysqli_query($con, $debug_query);
if ($debug_result && mysqli_num_rows($debug_result) > 0) {
    $debug_pedido = mysqli_fetch_assoc($debug_result);
    echo "Estado actual: " . htmlspecialchars($debug_pedido["estado"]) . "<br>";
    echo "Tipo preparación: " . htmlspecialchars($debug_pedido["tipo_preparacion"]) . "<br>";
}
echo "</div>";

// Obtener el pedido actual con consulta más flexible
$pedido_actual = null;
if ($ticket > 0) {
    // Primero intentar con estado esperado
    $query = "SELECT p.*, m.nombre as nombre_mesa FROM pedidos p
              JOIN mesas m ON p.mesa_id = m.id
              WHERE p.id = $ticket AND p.estado = 'en_preparacion_especial' 
              AND p.tipo_preparacion = 'complejo'";
    $result = mysqli_query($con, $query);
    
    // Si no encuentra, buscar con tipo_preparacion = complejo sin importar estado
    if (!$result || mysqli_num_rows($result) == 0) {
        $query = "SELECT p.*, m.nombre as nombre_mesa FROM pedidos p
                  JOIN mesas m ON p.mesa_id = m.id
                  WHERE p.id = $ticket AND p.tipo_preparacion = 'complejo'";
        $result = mysqli_query($con, $query);
        
        // Si lo encuentra pero con estado incorrecto, corregir
        if ($result && mysqli_num_rows($result) > 0) {
            mysqli_query($con, "UPDATE pedidos SET estado = 'en_preparacion_especial' WHERE id = $ticket");
        }
    }
    
    if ($result && mysqli_num_rows($result) > 0) {
        $pedido_actual = mysqli_fetch_assoc($result);
    }
}

if ($pedido_actual):
?>
<div id="formulario-preparacion-especial">
    <table border="1">
        <tr>
            <th>Mesa</th>
            <th>Items</th>
            <th>Observaciones</th>
            <th>Tipo Preparación</th>
            <th>Estado Actual</th>
            <th>Nuevo Estado</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
            <td><?php echo htmlspecialchars($pedido_actual["tipo_preparacion"]); ?></td>
            <td><?php echo htmlspecialchars($pedido_actual["estado"]); ?></td>
            <td>
                <select name="estado" required>
                    <option value="en_preparacion_especial" <?php echo ($pedido_actual["estado"] == 'en_preparacion_especial') ? 'selected' : ''; ?>>En preparación</option>
                    <option value="para_supervision">Listo para supervisión</option>
                    
                </select>
            </td>
        </tr>
    </table>

    <!-- Campos ocultos necesarios -->
    <input type="hidden" name="pedido_id" value="<?php echo $ticket; ?>">
    <input type="hidden" name="mesa_id" value="<?php echo $pedido_actual['mesa_id']; ?>">
</div>

<script>
// Override de prepararFormulario para preparación especial
function prepararFormulario() {
    const estadoSelect = document.querySelector('select[name="estado"]');
    if (!estadoSelect || !estadoSelect.value) {
        alert('Por favor seleccione un estado');
        return;
    }
    
    // Crear formulario
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = 'controlador.php';
    
    // Campos necesarios
    const campos = {
        'flujo': '<?php echo htmlspecialchars($flujo); ?>',
        'proceso': '<?php echo htmlspecialchars($proceso); ?>',
        'ticket': '<?php echo $ticket; ?>',
        'pedido_id': '<?php echo $ticket; ?>',
        'mesa_id': '<?php echo $pedido_actual["mesa_id"]; ?>',
        'estado': estadoSelect.value,
        'Siguiente': '1'
    };
    
    // Crear campos del formulario
    for (const [nombre, valor] of Object.entries(campos)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
    }
    
    console.log('Enviando preparación especial con datos:', campos);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php else: ?>
<div class="alert alert-warning">
    <h3>No hay pedidos para preparación especial</h3>
    <p>Posibles causas:</p>
    <ul>
        <li>El pedido no tiene tipo_preparacion = 'complejo'</li>
        <li>El estado del pedido no es 'en_preparacion_especial'</li>
        <li>El ticket <?php echo $ticket; ?> no existe</li>
    </ul>
    
    <?php
    // Mostrar información de debug
    $debug_query = "SELECT estado, tipo_preparacion FROM pedidos WHERE id = $ticket";
    $debug_result = mysqli_query($con, $debug_query);
    if ($debug_result && mysqli_num_rows($debug_result) > 0) {
        $debug_info = mysqli_fetch_assoc($debug_result);
        echo "<p><strong>Estado actual del pedido $ticket:</strong> " . htmlspecialchars($debug_info["estado"]) . "</p>";
        echo "<p><strong>Tipo preparación:</strong> " . htmlspecialchars($debug_info["tipo_preparacion"]) . "</p>";
    } else {
        echo "<p><strong>El pedido $ticket no existe en la base de datos</strong></p>";
    }
    ?>
</div>
<?php endif; ?>