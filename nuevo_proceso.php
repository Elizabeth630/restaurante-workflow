<?php
include "conexion.inc.php";

// Verificar sesión y rol
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION["rol"] != 'mesero') {
    $_SESSION["error"] = "Solo los meseros pueden iniciar nuevos procesos";
    header("Location: bandeja.php");
    exit();
}

// Obtener todos los flujos disponibles de la base de datos
$query_flujos = "SELECT DISTINCT flujo FROM flujoproceso ORDER BY flujo";
$result_flujos = mysqli_query($con, $query_flujos);
$flujos_disponibles = [];

if ($result_flujos && mysqli_num_rows($result_flujos) > 0) {
    while ($row = mysqli_fetch_assoc($result_flujos)) {
        $flujos_disponibles[] = $row['flujo'];
    }
}

// Descripciones e iconos para los flujos (pueden moverse a la BD si se desea)
$descripciones_flujos = [
    'F1' => [
        'nombre' => 'Proceso Normal',
        'icono' => 'fas fa-list-ol',
        'descripcion' => 'Pedido → Preparación → Cocina → Revisión → Factura'
    ],
    'F2' => [
        'nombre' => 'Proceso Rápido',
        'icono' => 'fas fa-bolt',
        'descripcion' => 'Pedido → Cocina → Factura'
    ],
    'F3' => [
        'nombre' => 'Proceso con Evaluación',
        'icono' => 'fas fa-clipboard-check',
        'descripcion' => 'Pedido → Evaluación → [Preparación Especial → Supervisión] o [Preparación Rápida] → Entrega'
    ]
];

// Para flujos no definidos, usar valores por defecto
foreach ($flujos_disponibles as $flujo) {
    if (!isset($descripciones_flujos[$flujo])) {
        $descripciones_flujos[$flujo] = [
            'nombre' => "Flujo $flujo",
            'icono' => 'fas fa-random',
            'descripcion' => 'Proceso personalizado'
        ];
    }
}
?>
<html>
    <head>
        <title>Nuevo Proceso</title>
        <link rel="stylesheet" href="estilos.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .process-container {
                max-width: 800px;
                margin: 30px auto;
                padding: 20px;
            }
            
            .process-title {
                color: var(--dark-color);
                text-align: center;
                margin-bottom: 30px;
                font-size: 2rem;
                position: relative;
            }
            
            .process-title:after {
                content: '';
                display: block;
                width: 100px;
                height: 4px;
                background: var(--primary-color);
                margin: 15px auto;
                border-radius: 2px;
            }
            
            .flujo-options {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            
            .flujo-option {
                background: white;
                border-radius: 8px;
                padding: 20px;
                cursor: pointer;
                transition: all 0.3s ease;
                border: 2px solid #e0e0e0;
                position: relative;
                overflow: hidden;
            }
            
            .flujo-option:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
                border-color: var(--accent-color);
            }
            
            .flujo-option h2 {
                color: var(--dark-color);
                margin-top: 0;
                display: flex;
                align-items: center;
            }
            
            .flujo-option h2 i {
                margin-right: 10px;
                color: var(--primary-color);
            }
            
            .flujo-option p {
                color: #555;
                margin-bottom: 10px;
                padding-left: 28px;
            }
            
            .flujo-option input[type="radio"] {
                position: absolute;
                opacity: 0;
            }
            
            .flujo-option label {
                display: inline-block;
                padding: 8px 15px;
                background-color: var(--light-color);
                color: white;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.3s;
                margin-top: 10px;
            }
            
            .flujo-option input[type="radio"]:checked + label {
                background-color: var(--primary-color);
            }
            
            .flujo-option input[type="radio"]:checked ~ * {
                color: var(--dark-color);
            }
            
            .submit-btn {
                display: block;
                width: 200px;
                margin: 30px auto 0;
                padding: 12px;
                background-color: var(--primary-color);
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .submit-btn:hover {
                background-color: #c1121f;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
            }
            
            .no-flujos {
                text-align: center;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 8px;
                color: #6c757d;
            }
        </style>
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
            <div class="process-container">
                <h1 class="process-title">Seleccione el Tipo de Proceso</h1>
                
                <form action="iniciar_proceso.php" method="post" class="flujo-options">
                    <?php if (!empty($flujos_disponibles)): ?>
                        <?php foreach ($flujos_disponibles as $index => $flujo): ?>
                            <div class="flujo-option">
                                <input type="radio" name="flujo" value="<?php echo $flujo; ?>" 
                                       id="flujo-<?php echo strtolower($flujo); ?>" 
                                       <?php echo $index === 0 ? 'checked' : ''; ?>>
                                <h2>
                                    <i class="<?php echo $descripciones_flujos[$flujo]['icono']; ?>"></i> 
                                    <?php echo $descripciones_flujos[$flujo]['nombre']; ?> (<?php echo $flujo; ?>)
                                </h2>
                                <p><?php echo $descripciones_flujos[$flujo]['descripcion']; ?></p>
                                <label for="flujo-<?php echo strtolower($flujo); ?>">Seleccionar</label>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-play-circle"></i> Iniciar Proceso
                        </button>
                    <?php else: ?>
                        <div class="no-flujos">
                            <i class="fas fa-exclamation-circle" style="font-size: 2rem; margin-bottom: 15px;"></i>
                            <h3>No hay flujos disponibles</h3>
                            <p>No se han configurado flujos de trabajo en el sistema.</p>
                            <a href="bandeja.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a la bandeja
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </body>
</html>