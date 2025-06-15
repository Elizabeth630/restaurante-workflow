<?php
// --- ARCHIVO: evaluacion.inc.php ---
?>
<h2>Evaluación del Pedido</h2>

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
<!-- Formulario con campos identificables -->
<div id="formulario-evaluacion">
    <table border="1">
        <tr>
            <th>Mesa</th>
            <th>Items</th>
            <th>Observaciones</th>
            <th>Decisión</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($pedido_actual["nombre_mesa"]); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido_actual["items"])); ?></td>
            <td><?php echo nl2br(htmlspecialchars($pedido_actual["observaciones"])); ?></td>
            <td>
                <div style="margin: 10px 0;">
                    <p><strong>¿El pedido requiere preparación especial?</strong></p>
                    <label>
                        <input type="radio" name="decision" value="verdad" required>
                        Sí, requiere preparación especial del cocinero
                    </label>
                    <br><br>
                    <label>
                        <input type="radio" name="decision" value="falso" required>
                        No, es un pedido simple que puedo preparar yo
                    </label>
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Campos ocultos necesarios -->
    <input type="hidden" name="pedido_id" value="<?php echo $ticket; ?>">
    <input type="hidden" name="mesa_id" value="<?php echo $pedido_actual['mesa_id']; ?>">
</div>

<script>
// document.addEventListener('DOMContentLoaded', function() {
//     // Actualizar texto del botón según selección
//     const radios = document.querySelectorAll('input[name="decision"]');
//     const botonSiguiente = document.querySelector('.nav-button:last-child');
    
//     radios.forEach(radio => {
//         radio.addEventListener('change', function() {
//             if (botonSiguiente) {
//                 botonSiguiente.textContent = this.value === 'verdad' 
//                     ? 'Enviar a Cocina (Preparación Especial)'
//                     : 'Preparar Yo (Pedido Simple)';
//             }
//         });
//     });
// });

// Override de la función prepararFormulario para este formulario específico
function prepararFormulario() {
    // Validar selección de decisión
    const decisionSeleccionada = document.querySelector('input[name="decision"]:checked');
    if (!decisionSeleccionada) {
        alert('Por favor seleccione una opción antes de continuar');
        return;
    }
    
    // Crear formulario con todos los datos necesarios
    const form = document.createElement('form');
    form.method = 'GET';
    form.action = 'controlador.php';
    
    // Campos obligatorios
    const campos = {
        'flujo': '<?php echo htmlspecialchars($flujo); ?>',
        'proceso': '<?php echo htmlspecialchars($proceso); ?>',
        'ticket': '<?php echo $ticket; ?>',
        'pedido_id': '<?php echo $ticket; ?>',
        'mesa_id': '<?php echo $pedido_actual["mesa_id"]; ?>',
        'decision': decisionSeleccionada.value,
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
    
    // Debug
    console.log('Enviando evaluación con datos:', campos);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php else: ?>
<div class="alert alert-warning">
    No hay pedidos para evaluar en este proceso.
</div>
<?php endif; ?>