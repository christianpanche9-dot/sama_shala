-- Migración 0033: permite regalar un monto libre (sin anclarlo a un
-- producto/paquete/evento/terapia especifico) como quinta opcion en el
-- selector de comprar_regalo.php.

ALTER TABLE tarjetas_regalo
  MODIFY COLUMN categoria_origen ENUM('saldo','tienda','paquetes','eventos','terapias') NOT NULL;
