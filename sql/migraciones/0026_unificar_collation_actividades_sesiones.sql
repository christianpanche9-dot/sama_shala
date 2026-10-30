-- Migración 0026: unificar el collation de actividades, espacios y sesiones
-- con el resto de la base de datos (utf8mb4_unicode_ci). Estas 3 tablas se
-- quedaron en utf8mb4_general_ci (probablemente de una creación anterior a
-- que se estandarizara el resto en utf8mb4_unicode_ci). Eso hace que
-- cualquier consulta que compare o combine (UNION, JOIN, ORDER BY) una
-- columna de texto de estas tablas con una de otra tabla falle con
-- "Illegal mix of collations" — es lo que rompía admin/pagos.php al unir
-- paquetes_clientes (unicode_ci) con reservas/actividades (general_ci).

ALTER TABLE actividades CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE espacios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sesiones CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
