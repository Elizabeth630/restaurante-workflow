<?php
include "conexion.inc.php";

// Verificar sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

$flujo = $_POST["flujo"];
$usuario = $_SESSION["usuario"];

// Obtener el primer proceso del flujo seleccionado
if ($flujo == 'F2') {
    // Secuencia específica para flujo rápido
    $proceso_inicial = 'P1'; // Siempre empieza en P1 para F2
} else {
    // Consulta normal para F1
    $resultado = mysqli_query($con, "SELECT fp1.proceso 
                                   FROM flujoproceso fp1 
                                   LEFT JOIN flujoproceso fp2 ON fp1.flujo = fp2.flujo AND fp1.proceso = fp2.siguiente
                                   WHERE fp1.flujo = '$flujo' AND fp2.proceso IS NULL");
    if (!$resultado || mysqli_num_rows($resultado) == 0) {
        die("Error: No se pudo determinar el proceso inicial para este flujo");
    }
    $fila = mysqli_fetch_array($resultado);
    $proceso_inicial = $fila["proceso"];
}

// Crear nuevo ticket de proceso
$sql = "INSERT INTO flujousuario (usuario, flujo, proceso, fechainicial) 
        VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "sss", $usuario, $flujo, $proceso_inicial);

if (!mysqli_stmt_execute($stmt)) {
    die("Error al crear nuevo proceso: " . mysqli_error($con));
}

$ticket = mysqli_insert_id($con);

// Redirigir al proceso inicial
header("Location: inicial.php?flujo=$flujo&proceso=$proceso_inicial&ticket=$ticket");
exit();
?>