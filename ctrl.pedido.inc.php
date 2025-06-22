<?php
// ==================== ARCHIVO: ctrl.pedido.inc.php (ESTRUCTURA BD CORRECTA) ====================

if (isset($_GET["Siguiente"])) {
    // Verificar parámetros requeridos
    if (!isset($_GET["mesa_id"]) || !isset($_GET["items"])) {
        error_log("Error: Faltan parámetros requeridos - mesa_id o items");
        die("Error: Faltan parámetros requeridos");
    }

    // Obtener contexto del flujo de manera dinámica
    $flujo_actual = $_GET["flujo_context"] ?? $flujo ?? '';
    $proceso_actual = $_GET["proceso_context"] ?? $proceso ?? '';
    $ticket_actual = (int)($_GET["ticket_context"] ?? $ticket ?? 0);
    
    // Si aún no tenemos contexto, intentar obtenerlo de flujousuario
    if (empty($flujo_actual) || empty($proceso_actual) || $ticket_actual <= 0) {
        // Buscar en flujousuario el registro activo más reciente para este usuario
        $context_query = "SELECT ticket, flujo, proceso FROM flujousuario 
                         WHERE usuario = '".$_SESSION["usuario"]."' 
                         AND fechafinal IS NULL 
                         ORDER BY fechainicial DESC 
                         LIMIT 1";
        $context_result = mysqli_query($con, $context_query);
        
        if ($context_result && mysqli_num_rows($context_result) > 0) {
            $context_row = mysqli_fetch_assoc($context_result);
            $flujo_actual = $context_row['flujo'];
            $proceso_actual = $context_row['proceso'];
            $ticket_actual = (int)$context_row['ticket'];
        }
    }
    
    // Validación final del contexto
    if (empty($flujo_actual) || empty($proceso_actual) || $ticket_actual <= 0) {
        error_log("Error crítico: No se pudo obtener contexto del flujo - Flujo: '$flujo_actual', Proceso: '$proceso_actual', Ticket: $ticket_actual");
        die("Error: No se pudo determinar el contexto del flujo actual");
    }

    // Log de depuración
    error_log("Procesando pedido - Flujo: $flujo_actual, Proceso: $proceso_actual, Ticket: $ticket_actual");

    $mesa_id = (int)$_GET["mesa_id"];
    $items = mysqli_real_escape_string($con, $_GET["items"]);
    $observaciones = isset($_GET["observaciones"]) ? 
        mysqli_real_escape_string($con, $_GET["observaciones"]) : '';

    // Iniciar transacción para asegurar consistencia
    mysqli_autocommit($con, false);
    
    try {
        // 1. Verificar si ya existe el pedido para este ticket
        $check_pedido = mysqli_query($con, "SELECT id FROM pedidos WHERE id = $ticket_actual");
        
        if (!$check_pedido) {
            throw new Exception("Error en consulta de verificación: " . mysqli_error($con));
        }
        
        $operacion = '';
        if (mysqli_num_rows($check_pedido) > 0) {
            // Actualizar pedido existente
            $sql = "UPDATE pedidos SET mesa_id = ?, items = ?, observaciones = ?, estado = 'pendiente' WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "issi", $mesa_id, $items, $observaciones, $ticket_actual);
            $operacion = "actualizado";
        } else {
            // Insertar nuevo pedido
            $sql = "INSERT INTO pedidos (id, mesa_id, items, observaciones, estado, mesero, fecha_creacion) 
                    VALUES (?, ?, ?, ?, 'pendiente', ?, NOW())";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "iisss", $ticket_actual, $mesa_id, $items, $observaciones, $_SESSION["usuario"]);
            $operacion = "creado";
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al guardar pedido: " . mysqli_error($con));
        }

        // 2. Verificar que el pedido se guardó correctamente
        $verify_pedido = mysqli_query($con, "SELECT id FROM pedidos WHERE id = $ticket_actual");
        if (!$verify_pedido || mysqli_num_rows($verify_pedido) == 0) {
            throw new Exception("El pedido no se guardó correctamente");
        }

        // 3. Obtener información del siguiente proceso en el flujo desde flujoproceso
        $siguiente_proceso_query = "SELECT siguiente, rol FROM flujoproceso 
                                   WHERE flujo = ? AND proceso = ?";
        $stmt_siguiente = mysqli_prepare($con, $siguiente_proceso_query);
        mysqli_stmt_bind_param($stmt_siguiente, "ss", $flujo_actual, $proceso_actual);
        mysqli_stmt_execute($stmt_siguiente);
        $siguiente_result = mysqli_stmt_get_result($stmt_siguiente);
        
        if ($siguiente_result && mysqli_num_rows($siguiente_result) > 0) {
            $siguiente_info = mysqli_fetch_assoc($siguiente_result);
            $proceso_siguiente = $siguiente_info['siguiente'];
            
            if (!empty($proceso_siguiente)) {
                // Obtener rol/usuario del siguiente proceso
                $usuario_siguiente_query = "SELECT rol FROM flujoproceso 
                                          WHERE flujo = ? AND proceso = ?";
                $stmt_usuario = mysqli_prepare($con, $usuario_siguiente_query);
                mysqli_stmt_bind_param($stmt_usuario, "ss", $flujo_actual, $proceso_siguiente);
                mysqli_stmt_execute($stmt_usuario);
                $usuario_result = mysqli_stmt_get_result($stmt_usuario);
                
                if ($usuario_result && mysqli_num_rows($usuario_result) > 0) {
                    $usuario_info = mysqli_fetch_assoc($usuario_result);
                    $rol_siguiente = $usuario_info['rol'];
                    
                    // Buscar un usuario disponible del rol correspondiente
                    $usuario_query = "SELECT username FROM usuarios WHERE rol = ? ORDER BY RAND() LIMIT 1";
                    $stmt_buscar_usuario = mysqli_prepare($con, $usuario_query);
                    mysqli_stmt_bind_param($stmt_buscar_usuario, "s", $rol_siguiente);
                    mysqli_stmt_execute($stmt_buscar_usuario);
                    $usuario_result_final = mysqli_stmt_get_result($stmt_buscar_usuario);
                    
                    if ($usuario_result_final && mysqli_num_rows($usuario_result_final) > 0) {
                        $usuario_final = mysqli_fetch_assoc($usuario_result_final);
                        $usuario_siguiente = $usuario_final['username'];
                    } else {
                        // Si no hay usuarios del rol, usar el rol como fallback
                        $usuario_siguiente = $rol_siguiente;
                        error_log("Advertencia: No se encontró usuario para el rol '$rol_siguiente'");
                    }
                    
                    // Verificar si ya existe el registro para el siguiente proceso
                    $check_siguiente = mysqli_query($con, 
                        "SELECT ticket FROM flujousuario 
                         WHERE ticket = $ticket_actual 
                         AND flujo = '$flujo_actual' 
                         AND proceso = '$proceso_siguiente'");
                    
                    if (!$check_siguiente || mysqli_num_rows($check_siguiente) == 0) {
                        // Crear registro para el siguiente proceso
                        $insert_siguiente = "INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial) 
                                           VALUES (?, ?, ?, ?, NOW())";
                        $stmt_insert = mysqli_prepare($con, $insert_siguiente);
                        mysqli_stmt_bind_param($stmt_insert, "isss", $ticket_actual, $usuario_siguiente, $flujo_actual, $proceso_siguiente);
                        
                        if (!mysqli_stmt_execute($stmt_insert)) {
                            error_log("Advertencia: No se pudo crear siguiente proceso: " . mysqli_error($con));
                        }
                    }
                }
            }
        }

        // 4. Marcar el proceso actual como completado
        $update_actual = "UPDATE flujousuario 
                         SET fechafinal = NOW() 
                         WHERE ticket = ? AND flujo = ? AND proceso = ? AND fechafinal IS NULL";
        $stmt_update = mysqli_prepare($con, $update_actual);
        mysqli_stmt_bind_param($stmt_update, "iss", $ticket_actual, $flujo_actual, $proceso_actual);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            error_log("Advertencia: No se pudo actualizar proceso actual: " . mysqli_error($con));
        }

        // Confirmar transacción
        mysqli_commit($con);
        
        // Log de éxito
        error_log("Pedido $operacion exitosamente - Ticket: $ticket_actual, Flujo: $flujo_actual");
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        mysqli_rollback($con);
        error_log("Error en procesamiento de pedido: " . $e->getMessage());
        die("Error al procesar pedido: " . $e->getMessage());
    } finally {
        // Restaurar autocommit
        mysqli_autocommit($con, true);
    }
}
?>