<?php
// ==================== ARCHIVO: cocina_condicion.inc.php ====================
?>
<h2>Cocina - Decisión de Flujo</h2>
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
<table border="1">
    <tr>
        <th>Mesa</th>
        <th>Items</th>
        <th>Observaciones</th>
        <th>Estado Actual</th>
        <th>Decisión</th>
    </tr>
    <tr>
        <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
        <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
        <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
        <td><?php echo ucfirst(str_replace('_', ' ', $pedido_actual["estado"])); ?></td>
        <td>
            <div style="margin: 10px 0;">
                <p><strong>¿El pedido requiere revisión adicional del mesero?</strong></p>
                <label>
                    <input type="radio" id="decision_verdad" name="decision" value="verdad" required>
                    Sí, requiere revisión del mesero
                </label>
                <br><br>
                <label>
                    <input type="radio" id="decision_falso" name="decision" value="falso" required>
                    No, puede ir directo a facturación
                </label>
            </div>
        </td>
    </tr>
</table>

<!-- Campos ocultos para el botón Siguiente -->
<input type="hidden" id="pedido_id" name="pedido_id" value="<?php echo (int)$pedido_actual["id"]; ?>">

<script>
// Actualizar el botón siguiente según la decisión
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="decision"]');
    const botonSiguiente = document.querySelector('.nav-button:last-child');
    
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'verdad') {
                botonSiguiente.textContent = 'Enviar a Revisión (Mesero)';
            } else {
                botonSiguiente.textContent = 'Enviar a Facturación Directa';
            }
        });
    });
});
</script>

<?php else: ?>
<div class="alert alert-warning">
    No hay pedidos en cocina para evaluar.
</div>
<?php endif; ?>