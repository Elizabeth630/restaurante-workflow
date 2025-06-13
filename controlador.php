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
if (!isset($_GET["flujo"])) {
    die("Error: Falta el parámetro 'flujo' en la URL");
}
if (!isset($_GET["proceso"])) {
    die("Error: Falta el parámetro 'proceso' en la URL");
}
if (!isset($_GET["ticket"]) || $_GET["ticket"] == 0) {
    die("Error: Falta el parámetro 'ticket' o es inválido");
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

// Obtener información del proceso
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

// Manejar navegación
if (isset($_GET["Siguiente"])) {
    // Actualizar el proceso actual como completado
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW() 
        WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL";
    
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }
    
    if (mysqli_affected_rows($con) == 0) {
        die("Error: No se pudo marcar el proceso como completado. Posibles causas: proceso ya finalizado o ticket incorrecto.");
    }
    
    // Determinar siguiente proceso
    $siguiente_proceso = null;
    
    if ($flujo == 'F2') {
        // Flujo rápido: P1 -> P2 -> P3 -> FIN
        switch ($proceso) {
            case 'P1': $siguiente_proceso = 'P2'; break;
            case 'P2': $siguiente_proceso = 'P3'; break;
            case 'P3': $siguiente_proceso = null; break;
        }
    } else {
        // Flujo normal: usar la configuración de la base de datos
        $siguiente_proceso = $fila["siguiente"];
    }
    
    // Si no hay siguiente proceso, redirigir a bandeja
    if (empty($siguiente_proceso)) {
        $_SESSION["mensaje"] = "Proceso completado exitosamente!";
        header("Location: bandeja.php");
        exit();
    }
    
    // Crear registro para el siguiente proceso
    $sql_insert = "INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial)
        VALUES ($ticket, '".mysqli_real_escape_string($con, $_SESSION["usuario"])."', '$flujo', '$siguiente_proceso', NOW())";
    
    if (!mysqli_query($con, $sql_insert)) {
        die("Error al crear nuevo registro de proceso: " . mysqli_error($con));
    }
    
    // Redireccionar al siguiente proceso
    header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
    exit();
    
} else if (isset($_GET["Anterior"])) {
    // Lógica para retroceder
    $query_anterior = "SELECT proceso FROM flujoproceso WHERE flujo='$flujo' AND siguiente='$proceso'";
    $resultado_anterior = mysqli_query($con, $query_anterior);
    
    if (!$resultado_anterior || mysqli_num_rows($resultado_anterior) == 0) {
        die("No se puede retroceder más en el flujo");
    }
    
    $fila_anterior = mysqli_fetch_array($resultado_anterior);
    $proceso_anterior = $fila_anterior["proceso"];
    
    // Actualizar el proceso actual
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW() 
        WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL";
    
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }
    
    // Redireccionar al proceso anterior
    header("Location: inicial.php?flujo=$flujo&proceso=$proceso_anterior&ticket=$ticket");
    exit();
} else {
    // Mostrar el formulario normalmente
    include $pantalla.".inc.php";
}