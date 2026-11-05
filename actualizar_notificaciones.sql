CREATE TABLE IF NOT EXISTS notificaciones_vistas (
 usuario_id INT UNSIGNED NOT NULL,
 seccion ENUM('avisos','academico','reportes','calendario') NOT NULL,
 registro_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(usuario_id,seccion,registro_id),
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
