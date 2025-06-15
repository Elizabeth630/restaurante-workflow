<?php
include "conexion.inc.php";
$resultado = mysqli_query($con, "select * from flujousuario where usuario='".$_SESSION["usuario"]."' and fechafinal is null");
?>
<html>
    <body>
        <div style="width:200px; float:left">
            <a href="">Bandeja de entrada</a>
            <br>
            <a href="bdsalida.php">Bandeja de salida</a>
            <br>
            <?php if ($_SESSION["rol"] == 'mesero'): ?>
                <a href="nuevo_proceso.php">Iniciar Nuevo Proceso</a>
                <br>
            <?php endif; ?>
            
            <a href="login.php">Volver al login</a>
        </div>
        <div style="width:600px; float:left">
            <h2>Procesos Pendientes</h2>
            <table border="1">
                <tr>
                    <th>Flujo</th>
                    <th>Proceso</th>
                    <th>Fecha Inicio</th>
                    <th>Operación</th>
                </tr>
                <?php
                while ($fila = mysqli_fetch_array($resultado)) {
                    echo "<tr>";
                    echo "<td>".$fila["flujo"]."</td>";
                    echo "<td>".$fila["proceso"]."</td>";
                    echo "<td>".$fila["fechainicial"]."</td>";
                    echo "<td><a href='inicial.php?flujo=".$fila["flujo"]."&proceso=".$fila["proceso"]."&ticket=".$fila["ticket"]."'>Atender</a></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </body>
</html>