-- Migración 0007: top 3 de actividades destacadas del mes.
-- es_top marca la actividad como parte del top 3. posicion_top (1, 2 o 3)
-- define el orden, con 1 = la más importante ("número uno"). Solo la
-- actividad en la posición 1 usa imagen_banner_top, el banner ancho que se
-- muestra debajo del botón de filtrar en actividades.php.
ALTER TABLE actividades
  ADD COLUMN es_top TINYINT(1) NOT NULL DEFAULT 0 AFTER activa,
  ADD COLUMN posicion_top TINYINT UNSIGNED DEFAULT NULL AFTER es_top,
  ADD COLUMN imagen_banner_top VARCHAR(255) DEFAULT NULL AFTER posicion_top,
  ADD UNIQUE KEY ux_actividades_posicion_top (posicion_top);
