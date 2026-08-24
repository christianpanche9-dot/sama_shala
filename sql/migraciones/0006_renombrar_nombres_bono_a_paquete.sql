-- Migración 0006: la migración 0002 renombró tablas/columnas de "bono" a
-- "paquete", pero no actualizó los datos ya sembrados por sql/seed_bonos.sql,
-- que guardó nombres literales como "Bono 4 clases". Corrige esos nombres.
UPDATE tipos_paquete SET nombre = 'Paquete de 4 clases' WHERE numero_usos = 4;
UPDATE tipos_paquete SET nombre = 'Paquete de 8 clases' WHERE numero_usos = 8;
UPDATE tipos_paquete SET nombre = 'Paquete de 12 clases' WHERE numero_usos = 12;
