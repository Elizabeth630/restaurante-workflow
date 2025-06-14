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

// ========== NUEVA LÓGICA PARA BOTÓN ANTERIOR ==========
// Determinar si mostrar botón anterior
$mostrar_boton_anterior = true;

// Excepciones para F1 y F2
if ($flujo == 'F1' && $proceso == 'P1') {
    $mostrar_boton_anterior = false; // No se puede retroceder desde P1 en F1
} else if ($flujo == 'F2' && $proceso == 'P1') {
    $mostrar_boton_anterior = false; // No se puede retroceder desde P1 en F2
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
</style>
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

<!-- ========== BOTONES DE NAVEGACIÓN MODIFICADOS ========== -->
<div style="text-align: center; margin-top: 20px;">
    <form action="controlador.php" method="GET" style="display: inline;">
        <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
        <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
        <input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
        <?php if ($mostrar_boton_anterior): ?>
            <input type="submit" value="Anterior" name="Anterior">
        <?php endif; ?>
    </form>
    
    <form action="controlador.php" method="GET" style="display: inline;">
        <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
        <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
        <input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
        <input type="submit" value="Siguiente" name="Siguiente">
    </form>
</div>

</body>
</html>