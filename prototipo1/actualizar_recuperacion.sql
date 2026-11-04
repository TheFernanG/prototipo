USE comunidad_escolar;
CREATE TABLE IF NOT EXISTS recuperacion_cuenta (
    usuario_id INT UNSIGNED PRIMARY KEY,
    pregunta VARCHAR(20) NOT NULL,
    respuesta VARCHAR(255) NOT NULL,
    token_hash CHAR(64) NULL UNIQUE,
    vence DATETIME NULL,
    password_version VARCHAR(255) NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
