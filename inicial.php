<?php
session_start();

// Parámetros
$flujo = $_GET["flujo"] ?? '';
$proceso = $_GET["proceso"] ?? '';
$ticket = isset($_GET["ticket"]) ? (int)$_GET["ticket"] : 0;

if (empty($flujo) || empty($proceso) || $ticket <= 0) {
    die("Error: Parámetros incompletos.");
}

include "conexion.inc.php";

// Verificar sesión
if (!isset($_SESSION["usuario"]) || !isset($_SESSION["rol"]) || !isset($_SESSION["nombre"])) {
    header("Location: login.php");
    exit();
}

// Obtener proceso actual y siguiente
$query = "SELECT fp.*, fp_next.rol AS rol_siguiente, fp_next.proceso AS proceso_siguiente
          FROM flujoproceso fp
          LEFT JOIN flujoproceso fp_next ON fp.flujo = fp_next.flujo AND fp.siguiente = fp_next.proceso
          WHERE fp.flujo = ? AND fp.proceso = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "ss", $flujo, $proceso);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("Error: Proceso no encontrado.");
}

$fila = mysqli_fetch_array($resultado);
$pantalla = $fila["pantalla"];
$rol_requerido = $fila["rol"];
$rol_siguiente = $fila["rol_siguiente"];
$proceso_siguiente = $fila["proceso_siguiente"];

// Validar rol
if ($_SESSION["rol"] !== $rol_requerido) {
    echo "<div class='alert alert-info'>
            <h3>Proceso en espera</h3>
            <p>Este paso requiere a un <strong>{$rol_requerido}</strong>.</p>
            <p>Tu rol: <strong>{$_SESSION['rol']}</strong>.</p>
            <a href='bandeja.php' class='btn btn-primary'>Volver a mi bandeja</a>
          </div>";
    exit();
}

// Verificar si proceso anterior fue completado (ej. para F1)
if ($flujo == 'F1' && $proceso != 'P1') {
    $query_anterior = "SELECT proceso FROM flujoproceso WHERE flujo='F1' AND siguiente='$proceso'";
    $res_anterior = mysqli_query($con, $query_anterior);
    if ($res_anterior && mysqli_num_rows($res_anterior)) {
        $anterior = mysqli_fetch_assoc($res_anterior)["proceso"];
        $verifica = mysqli_query($con, "SELECT * FROM flujousuario 
                                        WHERE flujo='$flujo' AND proceso='$anterior' 
                                        AND ticket=$ticket AND fechafinal IS NOT NULL");
        if (mysqli_num_rows($verifica) == 0) {
            die("Error: El proceso anterior ($anterior) no fue completado.");
        }
    }
}

// Mostrar botón anterior
$mostrar_boton_anterior = !($flujo == 'F1' && $proceso == 'P1') && !($flujo == 'F2' && $proceso == 'P1');

// Determinar botón siguiente
$texto_boton_siguiente = "Siguiente paso";
$accion_siguiente = "Siguiente";
$mostrar_boton_siguiente = true;

if ($proceso_siguiente && $rol_siguiente) {
    if ($rol_siguiente !== $_SESSION["rol"]) {
        $texto_boton_siguiente = "Enviar al " . ucfirst($rol_siguiente);
        $accion_siguiente = "Transferir";
    }

    // Verificar si ya fue transferido
    $check = mysqli_query($con, "SELECT * FROM flujousuario 
                                 WHERE flujo='$flujo' AND proceso='$proceso_siguiente' 
                                 AND ticket=$ticket");
    if (mysqli_num_rows($check) > 0) {
        $mostrar_boton_siguiente = false;
        echo "<div class='alert alert-warning'>
                <p>Este proceso ya ha sido enviado a <strong>{$rol_siguiente}</strong>.</p>
                <p>Espera a que complete su parte.</p>
              </div>";
    }
}

// Cargar lógica de datos
$bd_pantalla = "bd.$pantalla.inc.php";
if (!file_exists($bd_pantalla)) die("Error: Archivo de datos no encontrado ($bd_pantalla)");
include $bd_pantalla;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proceso: <?php echo htmlspecialchars($pantalla); ?></title>
    <style>
        .ticket-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .navigation-buttons { text-align: center; margin-top: 20px; }
        .nav-button { margin: 0 10px; padding: 10px 20px; font-size: 16px; }
        .alert { border: 1px solid #ccc; padding: 15px; margin: 15px 0; background: #fefefe; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="ticket-info">
        <strong>Ticket:</strong> #<?php echo $ticket; ?> |
        Flujo: <?php echo htmlspecialchars($flujo); ?> |
        Proceso: <?php echo htmlspecialchars($proceso); ?> |
        Usuario: <?php echo htmlspecialchars($_SESSION["nombre"]); ?> (<?php echo htmlspecialchars($_SESSION["rol"]); ?>)
    </div>

    <h2><?php echo ucfirst(htmlspecialchars($pantalla)); ?></h2>

    <?php 
    // Pantalla visual
    $pantalla_inc = "$pantalla.inc.php";
    if (file_exists($pantalla_inc)) include $pantalla_inc;
    else die("Error: Pantalla no encontrada ($pantalla_inc)");
    ?>

    <div class="navigation-buttons">
        <?php if ($mostrar_boton_anterior): ?>
            <form action="controlador.php" method="GET" style="display:inline;">
                <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                <input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
                <input type="submit" name="Anterior" value="Anterior" class="nav-button">
            </form>
        <?php endif; ?>

        <?php if ($mostrar_boton_siguiente): ?>
            <button type="button" onclick="prepararFormulario()" class="nav-button">
                <?php echo $texto_boton_siguiente; ?>
            </button>
            <input type="hidden" id="accion_siguiente" value="<?php echo $accion_siguiente; ?>">
        <?php endif; ?>
    </div>

    <script>
    function prepararFormulario() {
        const elementos = document.querySelectorAll('input[type="hidden"][id], select[id], input[type="number"][id], textarea[id]');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = 'controlador.php';

        form.appendChild(crearCampoOculto('flujo', '<?php echo htmlspecialchars($flujo); ?>'));
        form.appendChild(crearCampoOculto('proceso', '<?php echo htmlspecialchars($proceso); ?>'));
        form.appendChild(crearCampoOculto('ticket', '<?php echo $ticket; ?>'));
        form.appendChild(crearCampoOculto(document.getElementById('accion_siguiente').value, '1'));

        elementos.forEach(el => {
            if (el.name && el.name !== '') {
                form.appendChild(crearCampoOculto(el.name, el.value));
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    function crearCampoOculto(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    }
    </script>
</body>
</html>
