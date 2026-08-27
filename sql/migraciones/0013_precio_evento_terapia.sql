-- Migración 0013: precio fijo para actividades de tipo evento o terapia.
-- Ese precio se paga directamente al reservar (sin paquete ni clase suelta),
-- y el pago simulado queda registrado en la propia reserva, igual que en
-- paquetes_clientes.

ALTER TABLE actividades
  ADD COLUMN precio DECIMAL(10,2) DEFAULT NULL AFTER tipo;

ALTER TABLE reservas
  MODIFY COLUMN tipo_pago ENUM('paquete','suelta','evento') NOT NULL DEFAULT 'suelta',
  ADD COLUMN precio_pagado DECIMAL(10,2) DEFAULT NULL AFTER tipo_pago,
  ADD COLUMN metodo_pago ENUM('simulado') DEFAULT NULL AFTER precio_pagado,
  ADD COLUMN referencia_pago VARCHAR(40) DEFAULT NULL AFTER metodo_pago,
  ADD UNIQUE KEY referencia_pago (referencia_pago);
