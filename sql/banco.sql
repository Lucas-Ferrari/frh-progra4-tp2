-- =====================================================================
-- Banco — Base de datos, tablas y datos de prueba
-- Importar desde phpMyAdmin (pestaña "Importar") o con:
--   mysql -u root -p < sql/banco.sql
-- =====================================================================

DROP DATABASE IF EXISTS banco;
CREATE DATABASE banco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE banco;

-- ---------------------------------------------------------------------
-- Roles: niveles de usuario del sistema
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Usuarios: administradores, operadores y clientes
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    dni VARCHAR(8) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,            -- siempre hasheada con password_hash()
    debe_cambiar_password TINYINT(1) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL,
    CONSTRAINT fk_usuarios_roles FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Cuentas: contenido principal del sitio
-- ---------------------------------------------------------------------
CREATE TABLE cuentas (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    numero_cuenta VARCHAR(20) NOT NULL UNIQUE,
    tipo ENUM('Caja de ahorro', 'Cuenta corriente') NOT NULL,
    moneda ENUM('ARS', 'USD') NOT NULL,
    saldo DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_apertura DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activa TINYINT(1) NOT NULL DEFAULT 1,        -- baja lógica
    id_usuario INT NOT NULL,
    CONSTRAINT fk_cuentas_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Movimientos de cada cuenta.
-- El monto se guarda CON SIGNO: positivo = ingreso, negativo = egreso.
-- Así el saldo de una cuenta siempre es la suma de sus movimientos.
-- ---------------------------------------------------------------------
CREATE TABLE movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('Depósito', 'Extracción', 'Transferencia') NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    descripcion VARCHAR(150) NOT NULL DEFAULT '',
    CONSTRAINT fk_movimientos_cuentas FOREIGN KEY (id_cuenta) REFERENCES cuentas(id_cuenta)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Consultas del formulario de contacto
-- ---------------------------------------------------------------------
CREATE TABLE contactos (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leido TINYINT(1) NOT NULL DEFAULT 0,
    id_usuario INT NULL,                        -- solo si la consulta la hizo alguien logueado
    CONSTRAINT fk_contactos_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- DATOS DE PRUEBA
-- =====================================================================

INSERT INTO roles (id_rol, nombre) VALUES
(1, 'Administrador'),
(2, 'Operador'),
(3, 'Cliente');

-- Todas las contraseñas de prueba son "123456" (ver README.md).
-- El hash se generó con sql/generar_hash.php (password_hash + PASSWORD_DEFAULT).
SET @hash = '$2y$10$D.yup/x5PWBtj.cXij750u3Bu7lmtIUbywlv0hxOhO0ZiZ1I6HkUa';

INSERT INTO usuarios (id_usuario, nombre, apellido, dni, fecha_nacimiento, email, password, debe_cambiar_password, activo, fecha_registro, id_rol) VALUES
(1, 'Ana',      'Gómez',     '20111222', '1980-03-15', 'admin@banco.local',     @hash, 0, 1, '2025-01-10 09:00:00', 1),
(2, 'Bruno',    'Pérez',     '22333444', '1985-07-22', 'admin2@banco.local',    @hash, 0, 1, '2025-01-10 09:05:00', 1),
(3, 'Carla',    'Martínez',  '30444555', '1990-11-02', 'operador@banco.local',  @hash, 0, 1, '2025-01-12 10:00:00', 2),
(4, 'Diego',    'Fernández', '33555666', '1992-05-18', 'diego@mail.com',        @hash, 0, 1, '2025-02-01 11:30:00', 3),
(5, 'Elena',    'Rodríguez', '35666777', '1995-09-30', 'elena@mail.com',        @hash, 0, 1, '2025-02-03 15:10:00', 3),
(6, 'Federico', 'López',     '28777888', '1978-01-25', 'federico@mail.com',     @hash, 0, 1, '2025-02-15 08:45:00', 3),
(7, 'Gabriela', 'Sánchez',   '40888999', '2000-12-12', 'gabriela@mail.com',     @hash, 1, 1, '2025-03-01 17:20:00', 3),
(8, 'Hernán',   'Díaz',      '31222333', '1988-04-04', 'hernan@mail.com',       @hash, 0, 0, '2025-03-05 12:00:00', 3);

INSERT INTO cuentas (id_cuenta, numero_cuenta, tipo, moneda, saldo, fecha_apertura, activa, id_usuario) VALUES
(1, '1001000001', 'Caja de ahorro',   'ARS', 0, '2025-02-01 11:35:00', 1, 4),
(2, '1001000002', 'Caja de ahorro',   'USD', 0, '2025-02-01 11:40:00', 1, 4),
(3, '1001000003', 'Caja de ahorro',   'ARS', 0, '2025-02-03 15:15:00', 1, 5),
(4, '1001000004', 'Cuenta corriente', 'ARS', 0, '2025-02-15 08:50:00', 1, 6),
(5, '1001000005', 'Caja de ahorro',   'USD', 0, '2025-02-15 08:55:00', 1, 6),
(6, '1001000006', 'Caja de ahorro',   'ARS', 0, '2025-03-01 17:25:00', 1, 7),
(7, '1001000007', 'Caja de ahorro',   'ARS', 0, '2025-03-05 12:05:00', 1, 8);

INSERT INTO movimientos (id_cuenta, fecha, tipo, monto, descripcion) VALUES
(1, '2025-02-01 11:36:00', 'Depósito',      150000.00, 'Saldo inicial'),
(1, '2025-02-05 10:00:00', 'Depósito',      420000.00, 'Acreditación de sueldo'),
(1, '2025-02-07 18:22:00', 'Extracción',    -35000.00, 'Extracción cajero automático'),
(1, '2025-02-10 09:15:00', 'Transferencia', -20000.00, 'Transferencia a cuenta 1001000003'),
(1, '2025-03-05 10:00:00', 'Depósito',      420000.00, 'Acreditación de sueldo'),
(1, '2025-03-08 13:40:00', 'Extracción',    -60000.00, 'Pago de alquiler'),
(2, '2025-02-01 11:41:00', 'Depósito',         500.00, 'Saldo inicial'),
(2, '2025-02-20 16:00:00', 'Depósito',         300.00, 'Compra de dólares'),
(2, '2025-03-12 11:00:00', 'Extracción',      -100.00, 'Extracción por ventanilla'),
(3, '2025-02-03 15:16:00', 'Depósito',       80000.00, 'Saldo inicial'),
(3, '2025-02-10 09:15:00', 'Transferencia',  20000.00, 'Transferencia de cuenta 1001000001'),
(3, '2025-02-28 19:30:00', 'Extracción',    -15000.00, 'Compra supermercado'),
(4, '2025-02-15 08:51:00', 'Depósito',      900000.00, 'Saldo inicial'),
(4, '2025-02-25 10:10:00', 'Extracción',   -250000.00, 'Pago a proveedores'),
(4, '2025-03-10 14:00:00', 'Depósito',      310000.00, 'Cobro de facturas'),
(5, '2025-02-15 08:56:00', 'Depósito',        2000.00, 'Saldo inicial'),
(5, '2025-03-01 12:00:00', 'Extracción',      -450.00, 'Pago de viaje'),
(6, '2025-03-01 17:26:00', 'Depósito',       10000.00, 'Saldo inicial'),
(7, '2025-03-05 12:06:00', 'Depósito',        5000.00, 'Saldo inicial');

-- El saldo de cada cuenta se calcula a partir de sus movimientos,
-- así los datos de prueba quedan siempre consistentes.
UPDATE cuentas c
SET c.saldo = (SELECT COALESCE(SUM(m.monto), 0) FROM movimientos m WHERE m.id_cuenta = c.id_cuenta);

INSERT INTO contactos (nombre, email, asunto, mensaje, fecha, leido, id_usuario) VALUES
('Laura Benítez', 'laura@mail.com',  'Apertura de cuenta',   'Hola, quisiera saber qué requisitos necesito para abrir una caja de ahorro en dólares.', '2025-03-02 10:20:00', 0, NULL),
('Diego Fernández', 'diego@mail.com', 'Límite de extracción', '¿Cuál es el límite diario de extracción por cajero automático?',                          '2025-03-06 18:05:00', 1, 4),
('Martín Ruiz',   'martin@mail.com', 'Tarjeta de crédito',   'Me gustaría solicitar una tarjeta de crédito. ¿Cómo es el trámite?',                       '2025-03-11 09:45:00', 0, NULL);
