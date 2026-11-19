-- =========================================================================
-- MÓDULO 4: BASES DE DATOS Y SQL (MySQL - Entorno Laragon)
-- Proyecto: Esquema Normalizado de Tienda en Línea
-- =========================================================================

DROP DATABASE IF EXISTS pasantias_tienda;
CREATE DATABASE pasantias_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pasantias_tienda;

-- =========================================================================
-- 1. DDL: DEFINICIÓN DE TABLAS, RESTRICCIONES E ÍNDICES
-- =========================================================================

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'admin') NOT NULL DEFAULT 'cliente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE categorias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NULL,
    CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB;

CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT UNSIGNED NOT NULL,
    sku VARCHAR(50) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_productos_sku UNIQUE (sku),
    CONSTRAINT chk_productos_precio CHECK (precio >= 0),
    CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id)
        REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_productos_categoria (categoria_id),
    INDEX idx_productos_nombre (nombre)
) ENGINE=InnoDB;

CREATE TABLE pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    estado ENUM('pendiente', 'pagado', 'enviado', 'cancelado') NOT NULL DEFAULT 'pendiente',
    total DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_pedidos_usuario_estado (usuario_id, estado)
) ENGINE=InnoDB;

CREATE TABLE detalle_pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    CONSTRAINT chk_detalle_cantidad CHECK (cantidad > 0),
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id)
        REFERENCES pedidos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id)
        REFERENCES productos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY uq_pedido_producto (pedido_id, producto_id)
) ENGINE=InnoDB;

-- =========================================================================
-- 2. DML: INSERCIÓN DE DATOS INICIALES (SEED DATA)
-- =========================================================================

INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Joel Alanes', 'joel@ejemplo.com', '$2y$10$hashDeEjemplo1234567890', 'admin'),
('María Gómez', 'maria@ejemplo.com', '$2y$10$hashDeEjemplo0987654321', 'cliente'),
('Carlos Pérez', 'carlos@ejemplo.com', '$2y$10$hashDeEjemplo1122334455', 'cliente');

INSERT INTO categorias (nombre, descripcion) VALUES
('Periféricos', 'Teclados, ratones y accesorios de escritorio'),
('Monitores', 'Pantallas LED, IPS y gaming'),
('Almacenamiento', 'Discos SSD NVMe y externos');

INSERT INTO productos (categoria_id, sku, nombre, precio, stock) VALUES
(1, 'TEC-MEC-01', 'Teclado Mecánico RGB', 450.00, 15),
(1, 'MOU-GAM-02', 'Mouse Inalámbrico Pro', 280.00, 25),
(2, 'MON-24-03', 'Monitor IPS 24 Pulgadas 144Hz', 1450.00, 8),
(3, 'SSD-1TB-04', 'SSD NVMe 1TB Gen4', 620.00, 20);

-- =========================================================================
-- 3. TRANSACCIÓN ACID: CREACIÓN DE UN PEDIDO Y DESCUENTO DE STOCK
-- =========================================================================

START TRANSACTION;

INSERT INTO pedidos (usuario_id, estado, total) VALUES (2, 'pagado', 1180.00);
SET @ultimo_pedido_id = LAST_INSERT_ID();

INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio_unitario) VALUES
(@ultimo_pedido_id, 1, 2, 450.00), -- 2 Teclados = 900.00
(@ultimo_pedido_id, 2, 1, 280.00); -- 1 Mouse = 280.00

UPDATE productos SET stock = stock - 2 WHERE id = 1;
UPDATE productos SET stock = stock - 1 WHERE id = 2;

COMMIT;

-- =========================================================================
-- 4. CONSULTAS ANALÍTICAS (JOIN, GROUP BY, HAVING)
-- =========================================================================

-- Consulta A: Resumen de pedidos con nombre de cliente y cantidad de ítems
SELECT
    p.id AS pedido_id,
    u.nombre AS cliente,
    p.estado,
    COUNT(dp.id) AS lineas_producto,
    SUM(dp.cantidad) AS total_unidades,
    p.total AS total_bs
FROM pedidos p
INNER JOIN usuarios u ON p.usuario_id = u.id
INNER JOIN detalle_pedidos dp ON p.id = dp.pedido_id
GROUP BY p.id, u.nombre, p.estado, p.total;

-- Consulta B: Productos más vendidos (filtrando con HAVING)
SELECT
    pr.sku,
    pr.nombre,
    c.nombre AS categoria,
    SUM(dp.cantidad) AS unidades_vendidas,
    SUM(dp.subtotal) AS ingresos_generados
FROM productos pr
INNER JOIN categorias c ON pr.categoria_id = c.id
LEFT JOIN detalle_pedidos dp ON pr.id = dp.producto_id
GROUP BY pr.id, pr.sku, pr.nombre, c.nombre
HAVING unidades_vendidas > 0
ORDER BY ingresos_generados DESC;
