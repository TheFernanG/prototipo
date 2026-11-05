CREATE TABLE IF NOT EXISTS mensajes_eliminados (
 mensaje_id INT UNSIGNED NOT NULL,
 usuario_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(mensaje_id,usuario_id),
 FOREIGN KEY(mensaje_id) REFERENCES mensajes(id) ON DELETE CASCADE,
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS citatorios (
 mensaje_id INT UNSIGNED PRIMARY KEY,
 alumno_nombre VARCHAR(120) NOT NULL,
 docente_nombre VARCHAR(120) NOT NULL,
 fecha_cita DATE NOT NULL,
 hora_cita TIME NOT NULL,
 FOREIGN KEY(mensaje_id) REFERENCES mensajes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
