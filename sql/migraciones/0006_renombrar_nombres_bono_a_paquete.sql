-- Migración 0006: la migración 0002 renombró tablas/columnas de "bono" a
-- "paquete", pero no actualizó los datos ya sembrados por sql/seed_bonos.sql,
-- que guardó nombres literales como "Bono 4 clases". Corrige esos nombres.
UPDATE tipos_paquete
SET nombre = REPLACE(nombre, 'Bono', 'Paquete')
WHERE nombre LIKE 'Bono%';
