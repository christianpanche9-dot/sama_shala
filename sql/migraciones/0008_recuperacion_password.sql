-- Migración 0008: recuperación de contraseña.
-- token_recuperacion y su expiración permiten validar el enlace que recibe
-- el usuario para restablecer su contraseña sin conocerla. El envío real
-- del correo queda pendiente de un proveedor (ver enviar_correo_recuperacion
-- en funciones.php).
ALTER TABLE usuarios
  ADD COLUMN token_recuperacion VARCHAR(64) DEFAULT NULL AFTER password,
  ADD COLUMN token_recuperacion_expira DATETIME DEFAULT NULL AFTER token_recuperacion;
