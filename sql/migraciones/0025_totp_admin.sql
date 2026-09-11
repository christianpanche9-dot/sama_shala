-- Migración 0025: autenticación en dos pasos (TOTP) para cuentas admin.
-- totp_secret guarda la clave base32 (compatible con Google Authenticator,
-- Authy, etc). totp_habilitado indica si el segundo factor está activo.
-- Solo aplica a cuentas con rol admin; el resto de la app la ignora.

ALTER TABLE usuarios
  ADD COLUMN totp_secret VARCHAR(32) NULL AFTER password,
  ADD COLUMN totp_habilitado TINYINT(1) NOT NULL DEFAULT 0 AFTER totp_secret;
