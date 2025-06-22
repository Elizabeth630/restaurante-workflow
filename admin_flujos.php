<?php
// Verificar si la sesión no está activa antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "conexion.inc.php";

// Verificar permisos
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != 'mesero') {
    header("Location: login.php");
    exit();
}

// Operaciones CRUD
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['crear'])) {
        $flujo = mysqli_real_escape_string($con, $_POST['flujo']);
        $proceso = mysqli_real_escape_string($con, $_POST['proceso']);
        $siguiente = mysqli_real_escape_string($con, $_POST['siguiente']);
        $pantalla = mysqli_real_escape_string($con, $_POST['pantalla']);
        $rol = mysqli_real_escape_string($con, $_POST['rol']);
        
        $sql = "INSERT INTO flujoproceso (flujo, proceso, siguiente, pantalla, rol) 
                VALUES ('$flujo', '$proceso', '$siguiente', '$pantalla', '$rol')";
        mysqli_query($con, $sql);
    } 
    elseif (isset($_POST['actualizar'])) {
        // Verifica que el id exista
        
        $flujo = mysqli_real_escape_string($con, $_POST['flujo']);
        $proceso = mysqli_real_escape_string($con, $_POST['proceso']);
        $siguiente = mysqli_real_escape_string($con, $_POST['siguiente']);
        $pantalla = mysqli_real_escape_string($con, $_POST['pantalla']);
        $rol = mysqli_real_escape_string($con, $_POST['rol']);
        
        $sql = "UPDATE flujoproceso SET 
                flujo='$flujo', 
                proceso='$proceso', 
                siguiente='$siguiente', 
                pantalla='$pantalla', 
                rol='$rol' 
                WHERE flujo='".$_POST['flujo_original']."' AND proceso='".$_POST['proceso_original']."'";
        mysqli_query($con, $sql);
    }
    elseif (isset($_POST['eliminar'])) {
        $flujo = mysqli_real_escape_string($con, $_POST['flujo']);
        $proceso = mysqli_real_escape_string($con, $_POST['proceso']);
        
        $sql = "DELETE FROM flujoproceso WHERE flujo='$flujo' AND proceso='$proceso'";
        mysqli_query($con, $sql);
    }
}

// Obtener todos los flujos
$flujos = mysqli_query($con, "SELECT * FROM flujoproceso ORDER BY flujo, proceso");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Administrar Flujos</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <a href="bandeja.php" class="navbar-brand">
            <i class="fas fa-utensils"></i> Workflow-Restaurante
        </a>
        <div class="navbar-actions">
            <a href="bandeja.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Bandeja
            </a>
            <a href="login.php" class="btn btn-logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </header>

    <div class="container">
        <h1>Administración de Flujos</h1>
        
        <!-- Formulario para crear/editar -->
        <div class="card">
            <h2><?php echo isset($_GET['editar']) ? 'Editar Proceso' : 'Agregar Nuevo Proceso'; ?></h2>
            <form method="POST">
                <?php
                $editar = isset($_GET['editar']);
                $flujo_editar = '';
                $proceso_editar = '';
                $siguiente_editar = '';
                $pantalla_editar = '';
                $rol_editar = '';
                
                if ($editar) {
                    $result = mysqli_query($con, "SELECT * FROM flujoproceso WHERE flujo='".$_GET['flujo']."' AND proceso='".$_GET['proceso']."'");
                    $row = mysqli_fetch_assoc($result);
                    $flujo_editar = $row['flujo'];
                    $proceso_editar = $row['proceso'];
                    $siguiente_editar = $row['siguiente'];
                    $pantalla_editar = $row['pantalla'];
                    $rol_editar = $row['rol'];
                }
                ?>
                
                <input type="hidden" name="flujo_original" value="<?php echo $flujo_editar; ?>">
                <input type="hidden" name="proceso_original" value="<?php echo $proceso_editar; ?>">
                
                <div class="form-group">
                    <label>Flujo:</label>
                    <input type="text" name="flujo" value="<?php echo $flujo_editar; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Proceso:</label>
                    <input type="text" name="proceso" value="<?php echo $proceso_editar; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Siguiente Proceso:</label>
                    <input type="text" name="siguiente" value="<?php echo $siguiente_editar; ?>">
                </div>
                
                <div class="form-group">
                    <label>Pantalla:</label>
                    <input type="text" name="pantalla" value="<?php echo $pantalla_editar; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Rol:</label>
                    <select name="rol" required>
                        <option value="mesero" <?php echo ($rol_editar == 'mesero') ? 'selected' : ''; ?>>Mesero</option>
                        <option value="cocinero" <?php echo ($rol_editar == 'cocinero') ? 'selected' : ''; ?>>Cocinero</option>
                        <option value="admin" <?php echo ($rol_editar == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <?php if ($editar): ?>
                        <button type="submit" name="actualizar" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar
                        </button>
                        <a href="admin_flujos.php" class="btn btn-secondary">Cancelar</a>
                    <?php else: ?>
                        <button type="submit" name="crear" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Tabla de flujos existentes -->
        <div class="card">
            <h2>Flujos Existentes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Flujo</th>
                        <th>Proceso</th>
                        <th>Siguiente</th>
                        <th>Pantalla</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($flujo = mysqli_fetch_assoc($flujos)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flujo['flujo']); ?></td>
                            <td><?php echo htmlspecialchars($flujo['proceso']); ?></td>
                            <td><?php echo htmlspecialchars($flujo['siguiente']); ?></td>
                            <td><?php echo htmlspecialchars($flujo['pantalla']); ?></td>
                            <td><?php echo htmlspecialchars($flujo['rol']); ?></td>
                            <td>
                                <a href="admin_flujos.php?editar=1&flujo=<?php echo $flujo['flujo']; ?>&proceso=<?php echo $flujo['proceso']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="flujo" value="<?php echo $flujo['flujo']; ?>">
                                    <input type="hidden" name="proceso" value="<?php echo $flujo['proceso']; ?>">
                                    <button type="submit" name="eliminar" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este proceso?');">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>