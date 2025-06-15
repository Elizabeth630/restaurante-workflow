<?php
include "conexion.inc.php";
$resultado = mysqli_query($con, "select * from flujousuario where usuario='".$_SESSION["usuario"]."' and fechafinal is not null");
?>
<html>
    <head>
        <title>Bandeja de Salida</title>
        <link rel="stylesheet" href="estilos.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    </head>
    <body>
        <header class="navbar">
            <a href="bandeja.php" class="navbar-brand">
                <i class="fas fa-utensils"></i> Workflow-Restaurante
            </a>
            <div class="navbar-actions">
                <a href="login.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
            </div>
        </header>
        
        <div class="container">
            <div class="bandeja-container">
                <div class="bandeja-menu">
                    <a href="bandeja.php">Bandeja de entrada</a>
                    <a href="bdsalida.php" class="active">Bandeja de salida</a>
                    <?php if ($_SESSION["rol"] == 'mesero'): ?>
                        <a href="nuevo_proceso.php">Iniciar Nuevo Proceso</a>
                    <?php endif; ?>
                </div>
                
                <div class="bandeja-content">
                    <h2>Pedidos Completados</h2>
                    
                    <div class="card">
                        <table>
                            <thead>
                                <tr>
                                    <th>Flujo</th>
                                    <th>Proceso</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fila = mysqli_fetch_array($resultado)): ?>
                                    <tr>
                                        <td><?php echo $fila["flujo"]; ?></td>
                                        <td><?php echo $fila["proceso"]; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($fila["fechainicial"])); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($fila["fechafinal"])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (mysqli_num_rows($resultado) == 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i> No hay procesos completados recientemente.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>