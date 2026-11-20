-- Migración 0029: agrega "efectivo" como método de pago de una reserva,
-- para que el admin pueda registrar desde el panel una clase o evento que
-- el cliente ya pagó en persona (sin pasar por transferencia ni PayPhone).

ALTER TABLE reservas
  MODIFY COLUMN metodo_pago ENUM('simulado','transferencia','payphone','efectivo') DEFAULT NULL;
