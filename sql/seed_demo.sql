-- Datos de ejemplo para Sama Shala (escuela de yoga).

-- Contraseña: SamaShala2026
INSERT INTO usuarios (nombre, apellidos, email, password, telefono, rol) VALUES
('Admin', 'Sama Shala', 'admin@samashala.test', '$2y$10$Vl25F0t5NKS3PTfRAte4peJMj0T1zKKBRbCOlJgNOFwHnSg8Xmbk.', '600000000', 'admin');

INSERT INTO espacios (nombre, ubicacion, descripcion, aforo_maximo, activo) VALUES
('Sala Ganesha', 'Planta baja', 'Sala principal con suelo de madera y luz natural.', 18, 1),
('Sala Shakti', 'Primera planta', 'Sala pequeña, ideal para clases de meditación y yin yoga.', 10, 1),
('Terraza', 'Azotea', 'Espacio exterior para clases al aire libre en temporada cálida.', 14, 1);

INSERT INTO monitores (nombre, apellidos, especialidad, email, telefono, activo) VALUES
('Laia', 'Ferrer Puig', 'Hatha Yoga y respiración consciente', 'laia@samashala.test', '600111222', 1),
('Marc', 'Soler Vidal', 'Vinyasa y Ashtanga', 'marc@samashala.test', '600222333', 1),
('Nuria', 'Camps Roig', 'Yin Yoga y meditación', 'nuria@samashala.test', '600333444', 1);

INSERT INTO actividades (nombre, descripcion, categoria, nivel, duracion_minutos, imagen, activa) VALUES
('Hatha Yoga', 'Clase suave centrada en posturas estáticas y respiración consciente.', 'Yoga', 'inicial', 60, NULL, 1),
('Vinyasa Flow', 'Secuencias dinámicas que enlazan movimiento y respiración.', 'Yoga', 'intermedio', 75, NULL, 1),
('Yin Yoga', 'Posturas mantenidas varios minutos para trabajar tejido profundo.', 'Yoga', 'todos', 60, NULL, 1),
('Ashtanga Yoga', 'Serie tradicional dinámica y exigente físicamente.', 'Yoga', 'avanzado', 90, NULL, 1),
('Meditación guiada', 'Sesión de mindfulness y respiración para reducir el estrés.', 'Bienestar', 'todos', 45, NULL, 1);

INSERT INTO sesiones (id_actividad, id_espacio, id_monitor, fecha, hora_inicio, hora_fin, aforo, estado) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', 18, 'programada'),
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '18:30:00', '19:45:00', 18, 'programada'),
(3, 2, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '19:00:00', '20:00:00', 10, 'programada'),
(4, 1, 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '08:00:00', '09:30:00', 18, 'programada'),
(5, 2, 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '20:00:00', '20:45:00', 10, 'programada'),
(1, 3, 1, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '09:00:00', '10:00:00', 14, 'programada');
