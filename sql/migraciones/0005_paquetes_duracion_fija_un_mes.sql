-- Migración 0005: todos los paquetes duran exactamente 1 mes desde la compra.
-- Deja de ser una duración configurable por paquete, así que se elimina la
-- columna dias_validez de tipos_paquete (la fecha de caducidad de cada compra
-- se sigue calculando y guardando en paquetes_clientes.fecha_caducidad).
ALTER TABLE tipos_paquete DROP COLUMN dias_validez;
