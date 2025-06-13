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
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            form { max-width: 300px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            input { width: 100%; padding: 8px; margin: 5px 0; }
            button { background-color: #4CAF50; color: white; padding: 10px; border: none; cursor: pointer; width: 100%; }
            .error { color: red; }
        </style>
    </head>
    <body>
        <h1 style="text-align: center;">Sistema de Pedidos</h1>
        <form method="POST">
            <h2>Iniciar Sesión</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <label>Usuario:</label>
            <input type="text" name="username" required>
            
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            
            <button type="submit">Ingresar</button>
        </form>
    </body>
</html>