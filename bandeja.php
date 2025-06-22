<?php
Include "conexion.inc.php";
$resultado = mysqli_query($con, "select * from flujousuario where usuario='".$_SESSION["usuario"]."' and fechafinal is null");
?>
<html>
    <head>
    <title>Bandeja de Entrada</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>
    <body>
    <header class="navbar">
        <a href="bandeja.php" class="navbar-brand">
        <i class="fas fa-utensils"></i> Workflow-Restaurante
        </a>
        <div class="navbar-actions">
            <?php if ($_SESSION["rol"] == 'mesero'): ?>
                <a href="admin_flujos.php" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Administrar Flujos
                </a>
            <?php endif; ?>
            <a href="login.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </header>
    <!-- Resto del código existente... -->
        
        <div class="container">
            <div class="bandeja-container">
                <div class="bandeja-menu">
                    <a href="bandeja.php" class="active">Bandeja de entrada</a>
                    <a href="bdsalida.php">Bandeja de salida</a>
                    <?php if ($_SESSION["rol"] == 'mesero'): ?>
                        <a href="nuevo_proceso.php">Iniciar Nuevo Proceso</a>
                    <?php endif; ?>
                </div>
                
                <div class="bandeja-content">
                    <h2>Procesos Pendientes</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Flujo</th>
                                <th>Proceso</th>
                                <th>Fecha Inicio</th>
                                <th>Operación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_array($resultado)): ?>
                                <tr>
                                    <td><?php echo $fila["flujo"]; ?></td>
                                    <td><?php echo $fila["proceso"]; ?></td>
                                    <td><?php echo $fila["fechainicial"]; ?></td>
                                    <td>
                                        <a href="inicial.php?flujo=<?php echo $fila["flujo"]; ?>&proceso=<?php echo $fila["proceso"]; ?>&ticket=<?php echo $fila["ticket"]; ?>" class="btn btn-secondary">
                                            Atender
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>