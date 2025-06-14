<?php
// ==================== ARCHIVO: inicial.php (MODIFICADO) ====================
?>
<?php
// Obtener parámetros de la URL
$flujo = isset($_GET["flujo"]) ? $_GET["flujo"] : '';
$proceso = isset($_GET["proceso"]) ? $_GET["proceso"] : '';
$ticket = isset($_GET["ticket"]) ? (int)$_GET["ticket"] : 0;

// Verificar parámetros esenciales
if (empty($flujo) || empty($proceso) || $ticket <= 0) {
    die("Error: Parámetros incompletos en la URL. Se requieren flujo, proceso y ticket válido.");
}

include "conexion.inc.php";

// Obtener información del proceso actual
$query = "SELECT * FROM flujoproceso WHERE flujo=? AND proceso=?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ss", $flujo, $proceso);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("Error: Proceso no encontrado para flujo $flujo y proceso $proceso");
}

$fila = mysqli_fetch_array($resultado);
$pantalla = $fila["pantalla"];

// Determinar si mostrar botón anterior
$mostrar_boton_anterior = true;
if ($flujo == 'F1' && $proceso == 'P1') {
    $mostrar_boton_anterior = false;
} else if ($flujo == 'F2' && $proceso == 'P1') {
    $mostrar_boton_anterior = false;
}

// Incluir archivo de datos de la pantalla
$bd_pantalla = "bd.".$pantalla.".inc.php";
if (!file_exists($bd_pantalla)) {
    die("Error: Archivo de datos no encontrado para $pantalla");
}
include $bd_pantalla;
?>

<html>
<head>
    <title>Sistema de Pedidos - <?php echo htmlspecialchars($pantalla); ?></title>
    <style>
        .ticket-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .navigation-buttons {
            text-align: center;
            margin-top: 20px;
        }
        .nav-button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 16px;
        }
    </style>
    <script>
        function prepararFormulario() {
            // Obtener todos los elementos de input de la pantalla actual
            var elementos = document.querySelectorAll('input[type="hidden"][id], select[id], input[type="number"][id], textarea[id]');
            
            // Crear un formulario temporal
            var form = document.createElement('form');
            form.method = 'GET';
            form.action = 'controlador.php';
            
            // Agregar campos básicos
            form.appendChild(crearCampoOculto('flujo', '<?php echo htmlspecialchars($flujo); ?>'));
            form.appendChild(crearCampoOculto('proceso', '<?php echo htmlspecialchars($proceso); ?>'));
            form.appendChild(crearCampoOculto('ticket', '<?php echo $ticket; ?>'));
            form.appendChild(crearCampoOculto('Siguiente', '1'));
            
            // Agregar todos los elementos específicos de la pantalla
            elementos.forEach(function(elemento) {
                if (elemento.name && elemento.name !== '') {
                    form.appendChild(crearCampoOculto(elemento.name, elemento.value));
                }
            });
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function crearCampoOculto(name, value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            return input;
        }
    </script>
</head>
<body>
    <div class="ticket-info">
        <strong>Ticket #<?php echo $ticket; ?></strong> |
        Flujo: <?php echo htmlspecialchars($flujo); ?> |
        Proceso: <?php echo htmlspecialchars($proceso); ?>
    </div>
    
    <h1><?php echo ucfirst(htmlspecialchars($pantalla)); ?></h1>
    
    <!-- Contenido específico de la pantalla -->
    <?php
    $pantalla_inc = $pantalla.".inc.php";
    if (file_exists($pantalla_inc)) {
        include $pantalla_inc;
    } else {
        die("Error: Pantalla no encontrada ($pantalla_inc)");
    }
    ?>
    
    <!-- Botones de navegación -->
    <div class="navigation-buttons">
        <?php if ($mostrar_boton_anterior): ?>
            <form action="controlador.php" method="GET" style="display: inline;">
                <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                <input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
                <input type="submit" value="Anterior" name="Anterior" class="nav-button">
            </form>
        <?php endif; ?>
        
        <button type="button" onclick="prepararFormulario()" class="nav-button">Siguiente</button>
    </div>
</body>
</html>