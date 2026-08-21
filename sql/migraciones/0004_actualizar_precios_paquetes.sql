-- Migración 0004: actualizar los precios de los paquetes existentes.
UPDATE tipos_paquete SET precio = 30.00 WHERE numero_usos = 4;
UPDATE tipos_paquete SET precio = 50.00 WHERE numero_usos = 8;
UPDATE tipos_paquete SET precio = 70.00 WHERE numero_usos = 12;
