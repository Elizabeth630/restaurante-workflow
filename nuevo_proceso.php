<?php
include "conexion.inc.php";
?>
<html>
    <head>
        <title>Nuevo Proceso</title>
        <style>
            .flujo-option {
                border: 1px solid #ddd;
                padding: 15px;
                margin: 10px;
                border-radius: 5px;
                cursor: pointer;
            }
            .flujo-option:hover {
                background-color: #f5f5f5;
            }
        </style>
    </head>
    <body>
        <h1>Seleccione el Tipo de Proceso</h1>
        
        <form action="iniciar_proceso.php" method="post">
            <div class="flujo-option">
                <h2>Proceso Normal (F1)</h2>
                <p>Pedido → Preparación → Cocina → Revisión → Factura</p>
                <input type="radio" name="flujo" value="F1" checked>
                <label>Seleccionar</label>
            </div>
            
            <div class="flujo-option">
                <h2>Proceso Rápido (F2)</h2>
                <p>Pedido → Cocina → Factura</p>
                <input type="radio" name="flujo" value="F2">
                <label>Seleccionar</label>
            </div>
            
            <input type="submit" value="Iniciar Proceso">
        </form>
    </body>
</html>