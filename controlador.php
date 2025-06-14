<?php
// ==================== ARCHIVO: controlador.php ====================
include "conexion.inc.php";

// Verificar sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// Depuración: Mostrar parámetros recibidos (solo para desarrollo)
error_log("Parámetros recibidos: " . print_r($_GET, true));

// Verificar parámetros requeridos
if (!isset($_GET["flujo"]) || !isset($_GET["proceso"]) || !isset($_GET["ticket"]) || $_GET["ticket"] == 0) {
    die("Error: Faltan parámetros requeridos en la URL");
}

$flujo = mysqli_real_escape_string($con, $_GET["flujo"]);
$proceso = mysqli_real_escape_string($con, $_GET["proceso"]);
$ticket = (int)$_GET["ticket"];

// Verificar que el ticket pertenece al usuario actual
$verificar_ticket = mysqli_query($con, "SELECT * FROM flujousuario 
    WHERE ticket = $ticket AND usuario = '".$_SESSION["usuario"]."' AND fechafinal IS NULL");
if (mysqli_num_rows($verificar_ticket) == 0) {
    die("Ticket no válido o no pertenece al usuario actual");
}

// Obtener información del proceso actual
$query = "SELECT * FROM flujoproceso WHERE flujo='$flujo' AND proceso='$proceso'";
$resultado = mysqli_query($con, $query);
if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("Proceso no encontrado para flujo $flujo y proceso $proceso");
}
$fila = mysqli_fetch_array($resultado);
$pantalla = $fila["pantalla"];
$siguiente = $fila["siguiente"];

// Incluir el controlador específico si existe
$controlador_path = "ctrl.".$pantalla.".inc.php";
if (file_exists($controlador_path)) {
    include $controlador_path;
}

// ==================== NAVEGACIÓN ====================

if (isset($_GET["Siguiente"])) {
    // Determinar siguiente proceso
    if ($flujo == 'F2') {
        // Flujo rápido: secuencia fija
        switch ($proceso) {
            case 'P1': $siguiente_proceso = 'P2'; break;
            case 'P2': $siguiente_proceso = 'P3'; break;
            case 'P3': $siguiente_proceso = null; break;
            default: $siguiente_proceso = null;
        }
    } else {
        // Flujo normal (F1): usa base de datos
        $siguiente_proceso = $fila["siguiente"];
    }

    // Si no hay siguiente proceso → bandeja
    if (empty($siguiente_proceso)) {
        // Marcar actual como completado
        mysqli_query($con, "UPDATE flujousuario SET fechafinal = NOW() 
            WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL");

        $_SESSION["mensaje"] = "Proceso completado exitosamente!";
        header("Location: bandeja.php");
        exit();
    }

    // Verificar si el siguiente proceso ya está registrado
    $check_completed = mysqli_query($con, "SELECT * FROM flujousuario 
        WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$siguiente_proceso'");
    if (mysqli_num_rows($check_completed) > 0) {
        // Redirigir sin duplicar registro
        header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
        exit();
    }

    // Marcar proceso actual como completado
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW() 
        WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL";
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }

    // Insertar siguiente proceso
    $sql_insert = "INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial)
        VALUES ($ticket, '".mysqli_real_escape_string($con, $_SESSION["usuario"])."', '$flujo', '$siguiente_proceso', NOW())";
    if (!mysqli_query($con, $sql_insert)) {
        die("Error al crear nuevo registro de proceso: " . mysqli_error($con));
    }

    // Redirigir
    header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
    exit();

} else if (isset($_GET["Anterior"])) {
    // Obtener proceso anterior
    if ($flujo == 'F1') {
        $query_anterior = "SELECT proceso FROM flujoproceso WHERE flujo='F1' AND siguiente='$proceso'";
        $resultado_anterior = mysqli_query($con, $query_anterior);
        if (!$resultado_anterior || mysqli_num_rows($resultado_anterior) == 0) {
            die("No se puede retroceder más en el flujo");
        }
        $fila_anterior = mysqli_fetch_array($resultado_anterior);
        $proceso_anterior = $fila_anterior["proceso"];
    } else { // F2: mapeo manual
        switch ($proceso) {
            case 'P2': $proceso_anterior = 'P1'; break;
            case 'P3': $proceso_anterior = 'P2'; break;
            default: die("No se puede retroceder desde $proceso");
        }
    }

    // No se marca el actual como finalizado al retroceder
    header("Location: inicial.php?flujo=$flujo&proceso=$proceso_anterior&ticket=$ticket");
    exit();
    
} else {
    // Mostrar el formulario (pantalla)
    include $pantalla.".inc.php";
}
?>
