-- Migración 0017: agrega la tienda de productos (Bija, Ayurveda,
-- Fotografía y Angyoga), sus fotos de detalle y el registro de compras.

CREATE TABLE productos (
  id_producto INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  categoria ENUM('bija', 'ayurveda', 'fotografia', 'angyoga') NOT NULL,
  precio DECIMAL(10, 2) NOT NULL,
  tallas VARCHAR(100) DEFAULT NULL,
  imagen VARCHAR(255) DEFAULT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE producto_imagenes (
  id_imagen INT(11) NOT NULL AUTO_INCREMENT,
  id_producto INT(11) NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  orden INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id_imagen),
  KEY fk_pi_producto (id_producto),
  CONSTRAINT fk_pi_producto FOREIGN KEY (id_producto)
    REFERENCES productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE compras_productos (
  id_compra INT(11) NOT NULL AUTO_INCREMENT,
  id_usuario INT(11) NOT NULL,
  id_producto INT(11) NOT NULL,
  talla_elegida VARCHAR(50) DEFAULT NULL,
  precio_pagado DECIMAL(10, 2) NOT NULL,
  metodo_pago VARCHAR(30) NOT NULL DEFAULT 'simulado',
  referencia_pago VARCHAR(60) NOT NULL,
  estado VARCHAR(30) NOT NULL DEFAULT 'pagado',
  fecha_compra DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_compra),
  KEY fk_cp_usuario (id_usuario),
  KEY fk_cp_producto (id_producto),
  CONSTRAINT fk_cp_usuario FOREIGN KEY (id_usuario)
    REFERENCES usuarios (id_usuario),
  CONSTRAINT fk_cp_producto FOREIGN KEY (id_producto)
    REFERENCES productos (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
