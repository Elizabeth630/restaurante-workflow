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

// Cargar lógica de datos (esto debe ir ANTES de usarse)
$bd_pantalla = "bd.$pantalla.inc.php";
if (!file_exists($bd_pantalla)) die("Error: Archivo de datos no encontrado ($bd_pantalla)");
include $bd_pantalla;

// Definir pantalla_inc aquí, después de cargar bd_pantalla
$pantalla_inc = "$pantalla.inc.php";

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proceso: <?php echo htmlspecialchars($pantalla); ?></title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .ticket-info { 
            background: var(--accent-color); 
            padding: 15px;
            margin-bottom: 20px; 
            border-radius: 8px;
            color: var(--dark-color);
            font-weight: 600;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <header class="navbar">
        <a href="bandeja.php" class="navbar-brand">
            <i class="fas fa-utensils"></i> Workflow-Restaurante
        </a>
        <div class="navbar-actions">
            <a href="login.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </header>
    
    <div class="container">
        <div class="ticket-info">
            <span><strong>Ticket:</strong> #<?php echo $ticket; ?></span>
            <span><strong>Flujo:</strong> <?php echo htmlspecialchars($flujo); ?></span>
            <span><strong>Proceso:</strong> <?php echo htmlspecialchars($proceso); ?></span>
            <span><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
        </div>

        <?php 
        if (file_exists($pantalla_inc)) {
            include $pantalla_inc;
        } else {
            echo "<div class='alert alert-danger'>
                    <i class='fas fa-exclamation-triangle'></i> Pantalla no encontrada: $pantalla_inc
                  </div>";
        }
        ?>

        <div class="navigation-buttons">
            <!-- Botón Volver a Bandeja -->
            <a href="bandeja.php" class="nav-button" style="background-color: var(--light-color); color: white;">
                Volver a Bandeja
            </a>

            <?php if ($mostrar_boton_anterior): ?>
            <form action="controlador.php" method="GET" style="display:inline;">
                <input type="hidden" name="flujo" value="<?php echo htmlspecialchars($flujo); ?>">
                <input type="hidden" name="proceso" value="<?php echo htmlspecialchars($proceso); ?>">
                <input type="hidden" name="ticket" value="<?php echo $ticket; ?>">
                <button type="submit" name="Anterior" class="nav-button" style="background-color: var(--warning-color); color: white;">
                    Anterior
                </button>
            </form>
            <?php endif; ?>
            
            <?php if ($mostrar_boton_siguiente): ?>
            <button type="button" onclick="prepararFormulario()" class="nav-button" style="background-color: var(--success-color); color: white;">
                <?php echo $texto_boton_siguiente; ?>
            </button>
            <input type="hidden" id="accion_siguiente" value="<?php echo $accion_siguiente; ?>">
            <?php endif; ?>
        </div>
    </div>

    <script>
    function prepararFormulario() {
        // Buscar elementos por NAME en lugar de solo por ID
        const elementos = document.querySelectorAll('input[name]:not([name="flujo"]):not([name="proceso"]):not([name="ticket"]), select[name], textarea[name]');
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = 'controlador.php';

        // Campos base obligatorios
        form.appendChild(crearCampoOculto('flujo', '<?php echo htmlspecialchars($flujo); ?>'));
        form.appendChild(crearCampoOculto('proceso', '<?php echo htmlspecialchars($proceso); ?>'));
        form.appendChild(crearCampoOculto('ticket', '<?php echo $ticket; ?>'));
        form.appendChild(crearCampoOculto(document.getElementById('accion_siguiente').value, '1'));

        // Añadir todos los demás elementos del formulario
        elementos.forEach(el => {
            if (el.name && el.name !== '') {
                let valor = el.value;
                
                if (el.type === 'radio' && !el.checked) return;
                if (el.type === 'checkbox' && !el.checked) return;
                
                form.appendChild(crearCampoOculto(el.name, valor));
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