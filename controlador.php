<?php
// Verificar si la sesión no está activa antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "conexion.inc.php";

// Verificar sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// Verificar parámetros requeridos
if (!isset($_GET["flujo"]) || !isset($_GET["proceso"]) || 
    !isset($_GET["ticket"]) || $_GET["ticket"] == 0) {
    die("Error: Faltan parámetros requeridos en la URL");
}

$flujo = mysqli_real_escape_string($con, $_GET["flujo"]);
$proceso = mysqli_real_escape_string($con, $_GET["proceso"]);
$ticket = (int)$_GET["ticket"];

// Obtener datos del proceso actual
$query_proceso = "SELECT * FROM flujoproceso WHERE flujo='$flujo' AND proceso='$proceso'";
$resultado_proceso = mysqli_query($con, $query_proceso);

if (!$resultado_proceso || mysqli_num_rows($resultado_proceso) == 0) {
    die("Proceso no encontrado para flujo $flujo y proceso $proceso");
}

$fila_proceso = mysqli_fetch_array($resultado_proceso);

if ($_SESSION["rol"] != $fila_proceso["rol"]) {
    $_SESSION["error"] = "No tienes permiso para acceder a este proceso";
    header("Location: bandeja.php");
    exit();
}

// Incluir el controlador específico si existe
$controlador_path = "ctrl.".$fila_proceso["pantalla"].".inc.php";
if (file_exists($controlador_path)) {
    include $controlador_path;
}

// --- NAVEGACIÓN ---
if (isset($_GET["Siguiente"]) || isset($_GET["Transferir"])) {
    // Determinar siguiente proceso
    if ($flujo == 'F2') {
        switch ($proceso) {
            case 'P1': $siguiente_proceso = 'P2'; break;
            case 'P2': $siguiente_proceso = 'P3'; break;
            case 'P3': $siguiente_proceso = null; break;
            default: $siguiente_proceso = null;
        }
    } else {
        $siguiente_proceso = $fila_proceso["siguiente"];
    }

    // Si no hay siguiente proceso - completar
    if (empty($siguiente_proceso)) {
        mysqli_query($con, "UPDATE flujousuario SET fechafinal = NOW() 
                           WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' 
                           AND fechafinal IS NULL");
        
        if ($flujo == 'F1' && $proceso == 'P5') {
            mysqli_query($con, "UPDATE pedidos SET estado='completado' WHERE id=$ticket");
        }
        
        $_SESSION["mensaje"] = "¡Proceso completado exitosamente!";
        header("Location: bandeja.php");
        exit();
    }

    // Obtener información del siguiente proceso
    $query_siguiente = "SELECT * FROM flujoproceso WHERE flujo='$flujo' AND proceso='$siguiente_proceso'";
    $resultado_siguiente = mysqli_query($con, $query_siguiente);
    
    if (!$resultado_siguiente || mysqli_num_rows($resultado_siguiente) == 0) {
        die("Error: No se puede determinar el siguiente proceso");
    }
    
    $fila_siguiente = mysqli_fetch_array($resultado_siguiente);
    $rol_siguiente = $fila_siguiente["rol"];

    // Verificar si el siguiente proceso ya está registrado
    $check_completed = mysqli_query($con, "SELECT * FROM flujousuario 
                                          WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$siguiente_proceso'");
    
    if (mysqli_num_rows($check_completed) > 0) {
        if ($rol_siguiente == $_SESSION["rol"]) {
            header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
        } else {
            $_SESSION["mensaje"] = "El proceso ya ha sido enviado al $rol_siguiente";
            header("Location: bandeja.php");
        }
        exit();
    }

    // Marcar proceso actual como completado
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW() WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso' AND fechafinal IS NULL";
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }

    // Insertar siguiente proceso
    if ($flujo == 'F1' && $rol_siguiente != $_SESSION["rol"]) {
        $query_usuario = "SELECT username FROM usuarios WHERE rol='$rol_siguiente' LIMIT 1";
        $resultado_usuario = mysqli_query($con, $query_usuario);
        
        if (!$resultado_usuario || mysqli_num_rows($resultado_usuario) == 0) {
            die("Error: No hay usuarios disponibles con el rol $rol_siguiente para continuar el proceso");
        }
        
        $fila_usuario = mysqli_fetch_array($resultado_usuario);
        $usuario_siguiente = $fila_usuario["username"];
        $_SESSION["mensaje"] = "El proceso ha sido enviado al $rol_siguiente";
        $redirigir_a_bandeja = true;
    } else {
        $usuario_siguiente = $_SESSION["usuario"];
        $redirigir_a_bandeja = false;
    }

    $sql_insert = "INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial) 
                   VALUES ($ticket, '".mysqli_real_escape_string($con, $usuario_siguiente)."', '$flujo', '$siguiente_proceso', NOW())";
    
    if (!mysqli_query($con, $sql_insert)) {
        die("Error al crear nuevo registro de proceso: " . mysqli_error($con));
    }

    if ($redirigir_a_bandeja) {
        header("Location: bandeja.php");
    } else {
        header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
    }
    exit();

} else if (isset($_GET["Anterior"])) {
    // --- LÓGICA PARA EL BOTÓN ANTERIOR ---
    
    // Función para encontrar el proceso anterior
    function encontrarProcesoAnterior($flujo, $proceso_actual, $con) {
        if ($flujo == 'F2') {
            // Para F2: P1 <- P2 <- P3
            switch ($proceso_actual) {
                case 'P2': return 'P1';
                case 'P3': return 'P2';
                default: return null;
            }
        } else {
            // Para F1: buscar en la tabla flujoproceso
            $query = "SELECT proceso FROM flujoproceso WHERE flujo='$flujo' AND siguiente='$proceso_actual'";
            $resultado = mysqli_query($con, $query);
            
            if ($resultado && mysqli_num_rows($resultado) > 0) {
                $fila = mysqli_fetch_array($resultado);
                return $fila["proceso"];
            }
            return null;
        }
    }
    
    $proceso_anterior = encontrarProcesoAnterior($flujo, $proceso, $con);
    
    if ($proceso_anterior) {
        // Verificar si el proceso anterior existe en flujousuario
        $check_anterior = mysqli_query($con, "SELECT * FROM flujousuario 
                                             WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso_anterior'");
        
        if (mysqli_num_rows($check_anterior) > 0) {
            // Eliminar el proceso actual de flujousuario (para poder volver atrás)
            mysqli_query($con, "DELETE FROM flujousuario 
                               WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso'");
            
            // Marcar el proceso anterior como no completado (quitar fechafinal)
            mysqli_query($con, "UPDATE flujousuario SET fechafinal = NULL 
                               WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso_anterior'");
            
            // Actualizar el estado del pedido según el proceso anterior
            switch ($proceso_anterior) {
                case 'P1': // Volver a pedido
                    mysqli_query($con, "UPDATE pedidos SET estado='pendiente' WHERE id=$ticket");
                    break;
                case 'P2': // Volver a preparación
                    mysqli_query($con, "UPDATE pedidos SET estado='en_preparacion' WHERE id=$ticket");
                    break;
                case 'P3': // Volver a cocina
                    mysqli_query($con, "UPDATE pedidos SET estado='en_cocina' WHERE id=$ticket");
                    break;
                case 'P4': // Volver a revisión
                    mysqli_query($con, "UPDATE pedidos SET estado='para_revision' WHERE id=$ticket");
                    break;
                case 'P5': // Volver a facturación
                    mysqli_query($con, "UPDATE pedidos SET estado='para_facturar' WHERE id=$ticket");
                    break;
            }
            
            // Redirigir al proceso anterior
            header("Location: inicial.php?flujo=$flujo&proceso=$proceso_anterior&ticket=$ticket");
            exit();
        } else {
            $_SESSION["error"] = "No se puede retroceder: el proceso anterior no existe en el historial";
            header("Location: bandeja.php");
            exit();
        }
    } else {
        $_SESSION["error"] = "No se puede retroceder desde el primer proceso";
        header("Location: bandeja.php");
        exit();
    }

} else {
    // Mostrar el formulario (pantalla)
    include $fila_proceso["pantalla"].".inc.php";
}
?>