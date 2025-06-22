<?php
// ==================== ARCHIVO: controlador.php (VERSIÓN GENÉRICA) ====================

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
if (!isset($_GET["flujo"]) || !isset($_GET["proceso"]) || !isset($_GET["ticket"]) || $_GET["ticket"] == 0) {
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
    
    // ===== LÓGICA PARA CONDICIONALES =====
    // Verificar si el proceso actual tiene condicionales
    $query_condicion = "SELECT * FROM flujoProcesoCondicion WHERE flujo='$flujo' AND proceso='$proceso'";
    $resultado_condicion = mysqli_query($con, $query_condicion);
    
    if ($resultado_condicion && mysqli_num_rows($resultado_condicion) > 0) {
        // Proceso con condicional
        $fila_condicion = mysqli_fetch_array($resultado_condicion);
        
        // Obtener la decisión tomada
        $decision = isset($_SESSION["decision_condicion"]) ? $_SESSION["decision_condicion"] : 
                   (isset($_GET["decision"]) ? $_GET["decision"] : null);
        
        if (!$decision) {
            die("Error: No se ha tomado una decisión para el proceso condicional");
        }
        
        // Determinar siguiente proceso según la decisión
        if ($decision == 'verdad') {
            $siguiente_proceso = $fila_condicion["verdad"];
        } else {
            $siguiente_proceso = $fila_condicion["falso"];
        }
        
        // Limpiar la decisión de la sesión
        unset($_SESSION["decision_condicion"]);
        
        error_log("Proceso condicional: flujo=$flujo, proceso=$proceso, decisión=$decision, siguiente=$siguiente_proceso");
        
    } else {
        // Lógica genérica para cualquier flujo
        $siguiente_proceso = $fila_proceso["siguiente"];
    }
    
    // Si no hay siguiente proceso - completar
    if (empty($siguiente_proceso)) {
        mysqli_query($con, "UPDATE flujousuario SET fechafinal = NOW() 
                           WHERE ticket = $ticket AND flujo = '$flujo' 
                           AND proceso = '$proceso' AND fechafinal IS NULL");
        
        // Marcar pedido como completado si el proceso actual es el último del flujo
        $es_proceso_final = true;
        $query_check_final = "SELECT 1 FROM flujoproceso WHERE flujo='$flujo' AND siguiente='$proceso'";
        $result_check_final = mysqli_query($con, $query_check_final);
        if ($result_check_final && mysqli_num_rows($result_check_final) > 0) {
            $es_proceso_final = false;
        }
        
        if ($es_proceso_final) {
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
                                          WHERE ticket = $ticket AND flujo = '$flujo' 
                                          AND proceso = '$siguiente_proceso'");
    
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
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW() 
                   WHERE ticket = $ticket AND flujo = '$flujo' 
                   AND proceso = '$proceso' AND fechafinal IS NULL";
    
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }
    
    // Insertar siguiente proceso
    if ($rol_siguiente != $_SESSION["rol"]) {
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
                   VALUES ($ticket, '".mysqli_real_escape_string($con, $usuario_siguiente)."', 
                          '$flujo', '$siguiente_proceso', NOW())";
    
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
    // --- LÓGICA PARA EL BOTÓN ANTERIOR (VERSIÓN GENÉRICA) ---
    function encontrarProcesoAnterior($flujo, $proceso_actual, $con, $ticket) {
        // Primero verificar si hay un condicional que apunte a este proceso
        $query_condicional = "SELECT proceso FROM flujoProcesoCondicion 
                             WHERE flujo='$flujo' AND (verdad='$proceso_actual' OR falso='$proceso_actual')";
        $result_condicional = mysqli_query($con, $query_condicional);
        
        if ($result_condicional && mysqli_num_rows($result_condicional) > 0) {
            return mysqli_fetch_assoc($result_condicional)['proceso'];
        }
        
        // Si no es un destino condicional, buscar el proceso anterior normal
        $query = "SELECT proceso FROM flujoproceso 
                  WHERE flujo='$flujo' AND siguiente='$proceso_actual'";
        $resultado = mysqli_query($con, $query);
        
        if ($resultado && mysqli_num_rows($resultado) > 0) {
            return mysqli_fetch_assoc($resultado)['proceso'];
        }
        
        // Si no encontramos un proceso anterior definido, buscar en el historial
        $query_historial = "SELECT proceso FROM flujousuario 
                           WHERE flujo='$flujo' AND ticket=$ticket 
                           AND proceso != '$proceso_actual'
                           ORDER BY fechainicial DESC LIMIT 1";
        $result_historial = mysqli_query($con, $query_historial);
        
        if ($result_historial && mysqli_num_rows($result_historial) > 0) {
            return mysqli_fetch_assoc($result_historial)['proceso'];
        }
        
        return null;
    }
    
    $proceso_anterior = encontrarProcesoAnterior($flujo, $proceso, $con, $ticket);
    
    if ($proceso_anterior) {
        $check_anterior = mysqli_query($con, "SELECT * FROM flujousuario 
                                             WHERE ticket = $ticket AND flujo = '$flujo' 
                                             AND proceso = '$proceso_anterior'");
        
        if (mysqli_num_rows($check_anterior) > 0) {
            // Eliminar el proceso actual de flujousuario
            mysqli_query($con, "DELETE FROM flujousuario 
                               WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso'");
            
            // Marcar el proceso anterior como no completado
            mysqli_query($con, "UPDATE flujousuario SET fechafinal = NULL 
                               WHERE ticket = $ticket AND flujo = '$flujo' AND proceso = '$proceso_anterior'");
            
            // Actualizar el estado del pedido según el proceso anterior (si aplica)
            $query_estado_anterior = "SELECT pantalla FROM flujoproceso 
                                    WHERE flujo='$flujo' AND proceso='$proceso_anterior'";
            $result_estado = mysqli_query($con, $query_estado_anterior);
            
            if ($result_estado && mysqli_num_rows($result_estado) > 0) {
                $pantalla_anterior = mysqli_fetch_assoc($result_estado)['pantalla'];
                
                // Mapeo genérico de pantallas a estados
                $estados = [
                    'pedido' => 'pendiente',
                    'preparacion' => 'en_preparacion',
                    'cocina' => 'en_cocina',
                    'revision' => 'para_revision',
                    'factura' => 'para_facturar',
                    'evaluacion' => 'pendiente',
                    'preparacion_especial' => 'en_preparacion_especial',
                    'preparacion_rapida' => 'en_preparacion_rapida',
                    'supervision' => 'para_supervision',
                    'entrega' => 'para_entrega'
                ];
                
                if (isset($estados[$pantalla_anterior])) {
                    mysqli_query($con, "UPDATE pedidos SET estado='".$estados[$pantalla_anterior]."' WHERE id=$ticket");
                }
            }
            
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