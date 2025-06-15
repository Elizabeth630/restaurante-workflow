-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS restaurante;
USE restaurante;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('mesero', 'cocinero') NOT NULL,
    nombre VARCHAR(100) NOT NULL
);

-- Tabla de mesas
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    capacidad INT NOT NULL,
    estado ENUM('libre', 'ocupada') DEFAULT 'libre'
);

-- Tabla de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mesa_id INT NOT NULL,
    items TEXT NOT NULL,
    observaciones TEXT,
    estado ENUM('pendiente', 'en_preparacion', 'en_cocina', 'para_revision', 'para_facturar', 'completado') NOT NULL,
    mesero VARCHAR(50) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id)
);

-- Tabla de facturas
CREATE TABLE facturas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
);

-- Tabla de flujoproceso
CREATE TABLE flujoproceso (
    flujo VARCHAR(3) NOT NULL,
    proceso VARCHAR(3) NOT NULL,
    siguiente VARCHAR(3),
    pantalla VARCHAR(30) NOT NULL,
    rol VARCHAR(30) NOT NULL,
    PRIMARY KEY (flujo, proceso)
);

-- Tabla de flujousuario
CREATE TABLE flujousuario (
    ticket INT AUTO_INCREMENT,
    usuario VARCHAR(15) NOT NULL,
    flujo VARCHAR(3) NOT NULL,
    proceso VARCHAR(3) NOT NULL,
    fechainicial DATETIME NOT NULL,
    fechafinal DATETIME,
    PRIMARY KEY (ticket, flujo, proceso)
);

-- Insertar flujos
-- Flujo 1: Proceso normal de pedido (mesero)
INSERT INTO flujoproceso VALUES ('F1', 'P1', 'P2', 'pedido', 'mesero');
INSERT INTO flujoproceso VALUES ('F1', 'P2', 'P3', 'preparacion', 'mesero');
INSERT INTO flujoproceso VALUES ('F1', 'P3', 'P4', 'cocina', 'cocinero');
INSERT INTO flujoproceso VALUES ('F1', 'P4', 'P5', 'revision', 'mesero');
INSERT INTO flujoproceso VALUES ('F1', 'P5', NULL, 'factura', 'mesero');

-- Flujo 2: Proceso rápido (mesero puede hacer todo)
INSERT INTO flujoproceso VALUES ('F2', 'P1', 'P2', 'pedido', 'mesero');
INSERT INTO flujoproceso VALUES ('F2', 'P2', 'P3', 'cocina', 'mesero');
INSERT INTO flujoproceso VALUES ('F2', 'P3', NULL, 'factura', 'mesero');

-- Insertar datos de ejemplo
-- Usuarios
INSERT INTO usuarios (username, password, rol, nombre) VALUES 
('mesero1', SHA1('123456'), 'mesero', 'Juan Pérez'),
('cocinero1', SHA1('123456'), 'cocinero', 'María García');

-- Mesas
INSERT INTO mesas (nombre, capacidad) VALUES 
('Mesa 1', 4),
('Mesa 2', 6),
('Mesa 3', 2),
('Mesa 4', 8);

-- Flujo F1 ejecutado por mesero1 y cocinero1
INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) VALUES
(1, 'mesero1',   'F1', 'P1', '2025-06-10 12:00:00', '2025-06-10 12:05:00'),
(1, 'mesero1',   'F1', 'P2', '2025-06-10 12:05:00', '2025-06-10 12:10:00'),
(1, 'cocinero1', 'F1', 'P3', '2025-06-10 12:10:00', '2025-06-10 12:30:00'),
(1, 'mesero1',   'F1', 'P4', '2025-06-10 12:30:00', '2025-06-10 12:35:00'),
(1, 'mesero1',   'F1', 'P5', '2025-06-10 12:35:00', NULL);

-- Flujo F2 completo ejecutado por mesero1 (quien hace todo)
INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) VALUES
(2, 'mesero1', 'F2', 'P1', '2025-06-11 13:00:00', '2025-06-11 13:05:00'),
(2, 'mesero1', 'F2', 'P2', '2025-06-11 13:05:00', '2025-06-11 13:20:00'),
(2, 'mesero1', 'F2', 'P3', '2025-06-11 13:20:00', NULL);

INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) VALUES
(3, 'mesero1',   'F1', 'P1', '2025-06-12 11:00:00', '2025-06-12 11:10:00'),
(3, 'mesero1',   'F1', 'P2', '2025-06-12 11:10:00', '2025-06-12 11:15:00'),
(3, 'cocinero1', 'F1', 'P3', '2025-06-12 11:15:00', NULL);  -- cocinero sigue en cocina

INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) VALUES
(4, 'mesero1',   'F1', 'P1', '2025-06-12 12:00:00', '2025-06-12 12:05:00'),
(4, 'mesero1',   'F1', 'P2', '2025-06-12 12:05:00', '2025-06-12 12:10:00'),
(4, 'cocinero1', 'F1', 'P3', '2025-06-12 12:10:00', '2025-06-12 12:30:00'),
(4, 'mesero1',   'F1', 'P4', '2025-06-12 12:30:00', NULL);  -- mesero aún no revisó

INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) VALUES
(5, 'mesero1',   'F1', 'P1', '2025-06-12 13:00:00', '2025-06-12 13:10:00'),
(5, 'mesero1',   'F1', 'P2', '2025-06-12 13:10:00', '2025-06-12 13:20:00'),
(5, 'cocinero1', 'F1', 'P3', '2025-06-12 13:20:00', NULL);  -- cocinero en proceso actual

-- Pedido 1: Pedido en preparación
INSERT INTO pedidos (mesa_id, items, observaciones, estado, mesero, fecha_creacion) VALUES
(1, 'Hamburguesa, Papas, Coca-Cola', 'Sin cebolla', 'en_preparacion', 'mesero1', '2025-06-12 12:00:00');

-- Pedido 2: En cocina (ya fue enviado al cocinero)
INSERT INTO pedidos (mesa_id, items, observaciones, estado, mesero, fecha_creacion) VALUES
(2, 'Pizza mediana, Jugo de naranja', '', 'en_cocina', 'mesero1', '2025-06-12 12:10:00');

-- Pedido 3: Listo para facturar
INSERT INTO pedidos (mesa_id, items, observaciones, estado, mesero, fecha_creacion) VALUES
(3, 'Milanesa con papas, Agua', 'Sin mayonesa', 'para_facturar', 'mesero1', '2025-06-12 12:20:00');

-- Pedido 4: Pedido ya completado
INSERT INTO pedidos (mesa_id, items, observaciones, estado, mesero, fecha_creacion) VALUES
(4, 'Tacos x3, Refresco', '', 'completado', 'mesero1', '2025-06-12 12:30:00');

-- Factura para Pedido 3 (para_facturar)
INSERT INTO facturas (pedido_id, total, metodo_pago, fecha) VALUES
(3, 48.50, 'tarjeta', '2025-06-12 12:40:00');

-- Factura para Pedido 4 (completado)
INSERT INTO facturas (pedido_id, total, metodo_pago, fecha) VALUES
(4, 35.00, 'efectivo', '2025-06-12 12:45:00');



-- ==================== MODIFICACIONES SQL PARA FLUJO F3 ====================

-- 1. Crear tabla para manejar condicionales en flujos
CREATE TABLE flujoProcesoCondicion (
    flujo VARCHAR(3) NOT NULL,
    proceso VARCHAR(3) NOT NULL,
    verdad VARCHAR(3),
    falso VARCHAR(3),
    PRIMARY KEY (flujo, proceso)
);

-- 2. Insertar configuración del nuevo flujo F3 en flujoproceso
-- Basándome en el patrón típico de un flujo con condicionales para restaurante:
-- P1: Pedido (mesero) -> P2: Evaluación (mesero) -> 
-- Si complejo: P3: Preparación especial (cocinero) -> P4: Supervisión (mesero) -> P5: Entrega (mesero)
-- Si simple: P6: Preparación rápida (mesero) -> P5: Entrega (mesero)

INSERT INTO flujoproceso VALUES ('F3', 'P1', 'P2', 'pedido', 'mesero');
INSERT INTO flujoproceso VALUES ('F3', 'P2', NULL, 'evaluacion', 'mesero'); -- NULL porque depende de condicional
INSERT INTO flujoproceso VALUES ('F3', 'P3', 'P4', 'preparacion_especial', 'cocinero');
INSERT INTO flujoproceso VALUES ('F3', 'P4', 'P5', 'supervision', 'mesero');
INSERT INTO flujoproceso VALUES ('F3', 'P5', NULL, 'entrega', 'mesero');
INSERT INTO flujoproceso VALUES ('F3', 'P6', 'P5', 'preparacion_rapida', 'mesero');

-- 3. Insertar condicionales para F3
-- P2 (evaluación): si verdad (pedido complejo) -> P3, si falso (pedido simple) -> P6
INSERT INTO flujoProcesoCondicion VALUES('F3', 'P2', 'P3', 'P6');

-- 4. Agregar nueva columna a la tabla pedidos para manejar el tipo de preparación
ALTER TABLE pedidos ADD COLUMN tipo_preparacion ENUM('simple', 'complejo') DEFAULT 'simple';

-- 5. Insertar datos de ejemplo para F3
INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) 
VALUES
-- Ejemplo 1: Pedido complejo (va por cocinero)
(6, 'mesero1', 'F3', 'P1', '2025-06-14 14:00:00', '2025-06-14 14:05:00'),
(6, 'mesero1', 'F3', 'P2', '2025-06-14 14:05:00', '2025-06-14 14:10:00'),
(6, 'cocinero1', 'F3', 'P3', '2025-06-14 14:10:00', '2025-06-14 14:25:00'),
(6, 'mesero1', 'F3', 'P4', '2025-06-14 14:25:00', '2025-06-14 14:30:00'),
(6, 'mesero1', 'F3', 'P5', '2025-06-14 14:30:00', NULL);

-- Ejemplo 2: Pedido simple (solo mesero)
INSERT INTO flujousuario (ticket, usuario, flujo, proceso, fechainicial, fechafinal) 
VALUES
(7, 'mesero1', 'F3', 'P1', '2025-06-14 15:00:00', '2025-06-14 15:05:00'),
(7, 'mesero1', 'F3', 'P2', '2025-06-14 15:05:00', '2025-06-14 15:08:00'),
(7, 'mesero1', 'F3', 'P6', '2025-06-14 15:08:00', '2025-06-14 15:15:00'),
(7, 'mesero1', 'F3', 'P5', '2025-06-14 15:15:00', NULL);

-- 6. Insertar pedidos de ejemplo para F3
INSERT INTO pedidos (id, mesa_id, items, observaciones, estado, mesero, fecha_creacion, tipo_preparacion) 
VALUES
(6, 1, 'Paella para 4 personas, Vino tinto', 'Alérgico a mariscos, sin mejillones', 'en_preparacion', 'mesero1', '2025-06-14 14:00:00', 'complejo'),
(7, 2, 'Sandwich, Papas fritas, Coca Cola', '', 'para_revision', 'mesero1', '2025-06-14 15:00:00', 'simple');

ALTER TABLE pedidos 
MODIFY COLUMN estado ENUM(
    'pendiente',
    'en_preparacion', 
    'en_preparacion_especial',
    'en_preparacion_rapida',
    'en_cocina',
    'para_supervision',
    'para_revision',
    'para_entrega',  -- NUEVO ESTADO AÑADIDO
    'para_facturar',
    'completado',
    'problema'
) NOT NULL DEFAULT 'pendiente';