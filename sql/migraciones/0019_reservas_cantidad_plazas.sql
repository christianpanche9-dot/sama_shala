-- Migración 0019: permite comprar varias plazas en una sola reserva de
-- evento, terapia o taller (sesiones de precio fijo). El descuento por
-- paquete, si el usuario tiene uno, solo se aplica a la primera plaza;
-- el resto de plazas se cobran al precio completo.

ALTER TABLE reservas
  ADD COLUMN cantidad INT(11) NOT NULL DEFAULT 1 AFTER tipo_pago;
