-- =====================================================================
-- Migración: plazos fijos y reservas
-- Para una base que YA existe (por ejemplo la de Railway), sin borrar
-- los datos. En una instalación nueva no hace falta: banco.sql ya
-- incluye todo esto.
--   mysql -u root -p banco < sql/migracion_inversiones.sql
-- Se puede correr más de una vez sin romper nada (IF NOT EXISTS).
-- =====================================================================

SET NAMES utf8mb4;

-- Tipos de movimiento nuevos: la plata que va a una inversión (o vuelve)
-- queda en el historial de la cuenta, así el saldo sigue siendo la suma
-- de sus movimientos.
ALTER TABLE movimientos
    MODIFY tipo ENUM('Depósito', 'Extracción', 'Transferencia', 'Plazo fijo', 'Reserva') NOT NULL;

CREATE TABLE IF NOT EXISTS plazos_fijos (
    id_plazo_fijo INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta INT NOT NULL,
    capital DECIMAL(12,2) NOT NULL,
    tna DECIMAL(5,2) NOT NULL,
    dias SMALLINT NOT NULL,
    interes DECIMAL(12,2) NOT NULL,
    fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATETIME NOT NULL,
    estado ENUM('Activo', 'Acreditado') NOT NULL DEFAULT 'Activo',
    fecha_acreditacion DATETIME NULL,
    INDEX idx_plazos_vencimiento (estado, fecha_vencimiento),
    CONSTRAINT fk_plazos_cuentas FOREIGN KEY (id_cuenta) REFERENCES cuentas(id_cuenta)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cuenta INT NOT NULL,
    nombre VARCHAR(40) NOT NULL,
    meta DECIMAL(12,2) NULL,
    saldo DECIMAL(12,2) NOT NULL DEFAULT 0,
    rendimientos DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_calculo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activa TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_reservas_cuentas FOREIGN KEY (id_cuenta) REFERENCES cuentas(id_cuenta)
) ENGINE=InnoDB;
