-- Migración 0031: marca las cuentas creadas automáticamente para el
-- checkout de invitado (sin registro previo, pago con tarjeta). Permite
-- reutilizar la misma cuenta si el invitado vuelve a comprar con el mismo
-- correo, sin arriesgar que alguien "inicie sesión" como invitado usando
-- el correo de una cuenta real ya registrada.

ALTER TABLE usuarios
  ADD COLUMN es_invitado TINYINT(1) NOT NULL DEFAULT 0 AFTER activo;
