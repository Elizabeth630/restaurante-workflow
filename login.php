<?php
include "conexion.inc.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = sha1($_POST["password"]);
    $resultado = mysqli_query($con, "SELECT * FROM usuarios WHERE username='$username' AND password='$password'");
    if (mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_array($resultado);
        $_SESSION["usuario"] = $fila["username"];
        $_SESSION["rol"] = $fila["rol"];
        $_SESSION["nombre"] = $fila["nombre"];
        header("Location: bandeja.php");
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<html>
<head>
    <title>Login - Sistema de Pedidos</title>
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 70px);
            background-color: var(--secondary-color);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .login-logo {
            color: var(--primary-color);
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .login-title {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: 700;
        }
        
        .login-form input {
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .login-form input:focus {
            border-color: var(--accent-color);
        }
        
        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            background-color: #c1121f;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230, 57, 70, 0.3);
        }
        
        .error-message {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header class="navbar">
        <a href="#" class="navbar-brand">
            <i class="fas fa-utensils"></i> Workflow-Restaurante
        </a>
    </header>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-utensils"></i>
            </div>
            <h1 class="login-title">Sistema de Pedidos</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div style="text-align: left; margin-bottom: 8px; font-weight: 500; color: var(--dark-color);">
                    <label>Usuario:</label>
                </div>
                <input type="text" name="username" placeholder="Ingrese su usuario" required>
                
                <div style="text-align: left; margin-bottom: 8px; font-weight: 500; color: var(--dark-color);">
                    <label>Contraseña:</label>
                </div>
                <input type="password" name="password" placeholder="Ingrese su contraseña" required>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Ingresar
                </button>
            </form>
        </div>
    </div>
</body>
</html>