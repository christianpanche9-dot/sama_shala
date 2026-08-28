-- Migración 0018: agrega la cantidad comprada por línea, necesaria
-- para el carrito de la tienda (varios productos y cantidades a la vez).

ALTER TABLE compras_productos
  ADD COLUMN cantidad INT(11) NOT NULL DEFAULT 1 AFTER talla_elegida;
