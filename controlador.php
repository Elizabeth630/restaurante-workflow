<?php
// ==================== ARCHIVO: controlador.php (MODIFICADO CON CONTROL DE ROLES) ====================

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

// Verificar que el ticket pertenece al usuario actual o que el proceso puede ser continuado
$verificar_ticket = mysqli_query($con, "SELECT * FROM flujousuario 
                                      WHERE ticket = $ticket 
                                      AND flujo = '$flujo' 
                                      AND proceso = '$proceso' 
                                      AND fechafinal IS NULL");
if (mysqli_num_rows($verificar_ticket) == 0) {
    // Para flujo F1, verificar si el usuario anterior completó su parte
    if ($flujo == 'F1') {
        $verificar_anterior = mysqli_query($con, "SELECT fu.*, fp.rol 
                                                FROM flujousuario fu
                                                JOIN flujoproceso fp ON fu.flujo = fp.flujo AND fu.proceso = fp.proceso
                                                WHERE fu.ticket = $ticket 
                                                AND fu.flujo = '$flujo' 
                                                AND fu.fechafinal IS NOT NULL
                                                ORDER BY fu.fechafinal DESC 
                                                LIMIT 1");
        if (mysqli_num_rows($verificar_anterior) == 0) {
            die("Ticket no válido o proceso anterior no completado");
        }
    } else {
        die("Ticket no válido o no pertenece al usuario actual");
    }
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
$rol_requerido = $fila["rol"];

// Verificar rol del usuario
if ($_SESSION["rol"] != $rol_requerido) {
    die("Error: No tienes permisos para realizar esta acción. Rol requerido: $rol_requerido");
}

// Incluir el controlador específico si existe
$controlador_path = "ctrl.".$pantalla.".inc.php";
if (file_exists($controlador_path)) {
    include $controlador_path;
}

// --- NAVEGACIÓN ---
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

    // Si no hay siguiente proceso - bandeja
    if (empty($siguiente_proceso)) {
        // Marcar actual como completado
        mysqli_query($con, "UPDATE flujousuario SET fechafinal = NOW()
                          WHERE ticket = $ticket 
                          AND flujo = '$flujo' 
                          AND proceso = '$proceso' 
                          AND fechafinal IS NULL");

        $_SESSION["mensaje"] = "Proceso completado exitosamente!";
        header("Location: bandeja.php");
        exit();
    }

    // Obtener información del siguiente proceso
    $query_siguiente = "SELECT * FROM flujoproceso 
                       WHERE flujo='$flujo' 
                       AND proceso='$siguiente_proceso'";
    $resultado_siguiente = mysqli_query($con, $query_siguiente);
    if (!$resultado_siguiente || mysqli_num_rows($resultado_siguiente) == 0) {
        die("Error: No se puede determinar el siguiente proceso");
    }
    $fila_siguiente = mysqli_fetch_array($resultado_siguiente);
    $rol_siguiente = $fila_siguiente["rol"];

    // Verificar si el siguiente proceso ya está registrado
    $check_completed = mysqli_query($con, "SELECT * FROM flujousuario
                                         WHERE ticket = $ticket 
                                         AND flujo = '$flujo' 
                                         AND proceso = '$siguiente_proceso'");
    if (mysqli_num_rows($check_completed) > 0) {
        // Redirigir sin duplicar registro
        header("Location: inicial.php?flujo=$flujo&proceso=$siguiente_proceso&ticket=$ticket");
        exit();
    }

    // Marcar proceso actual como completado
    $sql_update = "UPDATE flujousuario SET fechafinal = NOW()
                  WHERE ticket = $ticket 
                  AND flujo = '$flujo' 
                  AND proceso = '$proceso' 
                  AND fechafinal IS NULL";
    if (!mysqli_query($con, $sql_update)) {
        die("Error al actualizar proceso actual: " . mysqli_error($con));
    }

    // Insertar siguiente proceso
    // Para flujo F1, el usuario del siguiente proceso será determinado por el rol requerido
    if ($flujo == 'F1') {
        // Buscar un usuario con el rol requerido
        $query_usuario = "SELECT username FROM usuarios WHERE rol='$rol_siguiente' LIMIT 1";
        $resultado_usuario = mysqli_query($con, $query_usuario);
        
        if (!$resultado_usuario || mysqli_num_rows($resultado_usuario) == 0) {
            die("Error: No hay usuarios disponibles con el rol $rol_siguiente para continuar el proceso");
        }
        
        $fila_usuario = mysqli_fetch_array($resultado_usuario);
        $usuario_siguiente = $fila_usuario["username"];
    } else {
        // Para flujo F2, el mismo usuario continúa
        $usuario_siguiente = $_SESSION["usuario"];
    }

    $sql_insert = "INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial)
                  VALUES ($ticket, '".mysqli_real_escape_string($con, $usuario_siguiente)."', '$flujo', '$siguiente_proceso', NOW())";
    if (!mysqli_query($con, $sql_insert)) {
        die("Error al crear nuevo registro de proceso: " . mysqli_error($con));
    }

    // Redirigir al siguiente proceso
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
    } else {
        // F2: mapeo manual
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