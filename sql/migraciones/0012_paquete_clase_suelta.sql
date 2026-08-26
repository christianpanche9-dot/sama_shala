-- Migración 0012: agregar el paquete de 1 clase suelta a 10 USD.

INSERT INTO tipos_paquete (id_tenant, nombre, numero_usos, precio, activo)
VALUES (1, 'Clase suelta', 1, 10.00, 1);
