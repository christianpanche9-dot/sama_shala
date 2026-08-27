-- Migración 0014: nuevos nombres para los paquetes.
UPDATE tipos_paquete SET nombre = 'Descubre' WHERE numero_usos = 1;
UPDATE tipos_paquete SET nombre = 'Conecta' WHERE numero_usos = 4;
UPDATE tipos_paquete SET nombre = 'Cultiva' WHERE numero_usos = 8;
UPDATE tipos_paquete SET nombre = 'Integra' WHERE numero_usos = 12;
