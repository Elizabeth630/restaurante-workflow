<?php
// --- ARCHIVO: ctrl.evaluacion.inc.php ---

if (isset($_GET["Siguiente"])) {
    // Debug: Mostrar todos los parámetros recibidos
    error_log("Parámetros recibidos en evaluacion: " . print_r($_GET, true));
    
    // Lista de parámetros requeridos con validación mejorada
    $parametrosRequeridos = [
        'flujo' => ['tipo' => 'string', 'requerido' => true],
        'proceso' => ['tipo' => 'string', 'requerido' => true], 
        'ticket' => ['tipo' => 'int', 'requerido' => true],
        'pedido_id' => ['tipo' => 'int', 'requerido' => true],
        'decision' => ['tipo' => 'string', 'requerido' => true]
    ];
    
    // Verificación exhaustiva con mensajes específicos
    $errores = [];
    foreach ($parametrosRequeridos as $param => $config) {
        if ($config['requerido'] && !isset($_GET[$param])) {
            $errores[] = "Falta parámetro: $param";
            error_log("EVALUACION ERROR: Falta parámetro requerido: $param");
        } elseif (isset($_GET[$param])) {
            // Validar tipo
            if ($config['tipo'] == 'int' && !is_numeric($_GET[$param])) {
                $errores[] = "El parámetro $param debe ser numérico (recibido: {$_GET[$param]})";
            } elseif ($config['tipo'] == 'string' && empty(trim($_GET[$param]))) {
                $errores[] = "El parámetro $param no puede estar vacío";
            }
        }
    }
    
    // Si hay errores, mostrarlos
    if (!empty($errores)) {
        $mensaje_error = "Errores en evaluación:\n" . implode("\n", $errores);
        error_log($mensaje_error);
        die("Error: " . implode(" | ", $errores));
    }
    
    // Validar consistencia ticket/pedido_id
    if ($_GET['ticket'] != $_GET['pedido_id']) {
        error_log("EVALUACION ERROR: Inconsistencia ticket={$_GET['ticket']} vs pedido_id={$_GET['pedido_id']}");
        die("Error: Inconsistencia entre ticket y pedido_id");
    }
    
    // Validar decisión
    $decisionValida = false;
    $tipo_preparacion = '';
    $nuevo_estado = '';
    
    if ($_GET["decision"] == 'verdad') {
        $tipo_preparacion = 'complejo';
        $nuevo_estado = 'en_preparacion_especial';
        $decisionValida = true;
        error_log("EVALUACION: Decisión VERDAD - Preparación especial");
    } elseif ($_GET["decision"] == 'falso') {
        $tipo_preparacion = 'simple';
        $nuevo_estado = 'en_preparacion_rapida';  
        $decisionValida = true;
        error_log("EVALUACION: Decisión FALSO - Preparación simple");
    }
    
    if (!$decisionValida) {
        error_log("EVALUACION ERROR: Decisión inválida: {$_GET['decision']}");
        die("Error: Valor de decisión no válido: {$_GET['decision']}");
    }
    
    // Transacción para actualizar base de datos
    mysqli_begin_transaction($con);
    
    try {
        // 1. Actualizar pedido
        $sqlPedido = "UPDATE pedidos SET 
                      tipo_preparacion = ?, 
                      estado = ? 
                      WHERE id = ?";
        $stmtPedido = mysqli_prepare($con, $sqlPedido);
        
        if (!$stmtPedido) {
            throw new Exception("Error preparando consulta: " . mysqli_error($con));
        }
        
        mysqli_stmt_bind_param($stmtPedido, "ssi", $tipo_preparacion, $nuevo_estado, $_GET['pedido_id']);
        
        if (!mysqli_stmt_execute($stmtPedido)) {
            throw new Exception("Error actualizando pedido: " . mysqli_stmt_error($stmtPedido));
        }
        
        // Verificar que se actualizó al menos una fila
        if (mysqli_stmt_affected_rows($stmtPedido) == 0) {
            throw new Exception("No se encontró el pedido con ID: {$_GET['pedido_id']}");
        }
        
        // 2. Registrar decisión en sesión para el proceso condicional
        $_SESSION["decision_condicion"] = $_GET["decision"];
        
        // 3. Log exitoso
        error_log("EVALUACION EXITOSA: Ticket={$_GET['ticket']}, Decisión={$_GET['decision']}, Estado=$nuevo_estado");
        
        mysqli_commit($con);
        
        // Mensaje de éxito opcional
        $_SESSION["mensaje"] = "Evaluación procesada correctamente";
        
    } catch (Exception $e) {
        mysqli_rollback($con);
        error_log("EVALUACION ERROR TRANSACCION: " . $e->getMessage());
        die("Error procesando evaluación: " . $e->getMessage());
    }
}
?>