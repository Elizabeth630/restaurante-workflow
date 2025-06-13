<?php
include "conexion.inc.php";
$resultado = mysqli_query($con, "select * from flujousuario where usuario='".$_SESSION["usuario"]."' and fechafinal is not null");
?>
<html>
    <body>
        <div style="width:200px; float:left">
            <a href="bandeja.php">Bandeja de entrada</a>
            <br>
            <a href="">Bandeja de salida</a>
        </div>
        <div style="width:600px; float:left">
            <h2>Pedidos Completados</h2>
            <table border="1">
                <tr>
                    <th>Flujo</th>
                    <th>Proceso</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                </tr>
                <?php
                while ($fila = mysqli_fetch_array($resultado)) {
                    echo "<tr>";
                    echo "<td>".$fila["flujo"]."</td>";
                    echo "<td>".$fila["proceso"]."</td>";
                    echo "<td>".$fila["fechainicial"]."</td>";
                    echo "<td>".$fila["fechafinal"]."</td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>
    </body>
</html>