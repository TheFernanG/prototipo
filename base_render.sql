CREATE TABLE usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 rol ENUM('administrador','docente','alumno','padre') NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE grupos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL,
 ciclo VARCHAR(30) NOT NULL,
 UNIQUE KEY grupo_ciclo(nombre,ciclo)
) ENGINE=InnoDB;
CREATE TABLE materias (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;
CREATE TABLE asignaciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 grupo_id INT UNSIGNED NOT NULL,
 materia_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 UNIQUE KEY clase(grupo_id,materia_id,docente_id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id),
 FOREIGN KEY(materia_id) REFERENCES materias(id),
 FOREIGN KEY(docente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE inscripciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 grupo_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 UNIQUE KEY inscrito(grupo_id,alumno_id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE vinculos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 padre_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 UNIQUE KEY familia(padre_id,alumno_id),
 FOREIGN KEY(padre_id) REFERENCES usuarios(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE mensajes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 remitente_id INT UNSIGNED NOT NULL,
 destinatario_id INT UNSIGNED NOT NULL,
 asunto VARCHAR(150) NOT NULL,
 cuerpo TEXT NOT NULL,
 leido TINYINT(1) NOT NULL DEFAULT 0,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY bandeja(destinatario_id,leido,creado),
 KEY enviados(remitente_id,creado),
 FOREIGN KEY(remitente_id) REFERENCES usuarios(id),
 FOREIGN KEY(destinatario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE avisos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 autor_id INT UNSIGNED NOT NULL,
 grupo_id INT UNSIGNED NULL,
 titulo VARCHAR(150) NOT NULL,
 cuerpo TEXT NOT NULL,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(autor_id) REFERENCES usuarios(id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id)
) ENGINE=InnoDB;
CREATE TABLE eventos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 autor_id INT UNSIGNED NOT NULL,
 grupo_id INT UNSIGNED NULL,
 titulo VARCHAR(150) NOT NULL,
 descripcion TEXT NOT NULL,
 fecha DATE NOT NULL,
 lugar VARCHAR(150) NOT NULL DEFAULT '',
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY agenda(fecha),
 FOREIGN KEY(autor_id) REFERENCES usuarios(id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id)
) ENGINE=InnoDB;
CREATE TABLE calificaciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 alumno_id INT UNSIGNED NOT NULL,
 asignacion_id INT UNSIGNED NOT NULL,
 autor_id INT UNSIGNED NOT NULL,
 periodo VARCHAR(60) NOT NULL,
 calificacion DECIMAL(4,2) NOT NULL,
 observaciones TEXT NOT NULL,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CHECK(calificacion >= 0 AND calificacion <= 10),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id),
 FOREIGN KEY(asignacion_id) REFERENCES asignaciones(id),
 FOREIGN KEY(autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE reportes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 alumno_id INT UNSIGNED NOT NULL,
 autor_id INT UNSIGNED NOT NULL,
 tipo ENUM('Positivo','Observación','Incidencia') NOT NULL,
 detalle TEXT NOT NULL,
 fecha DATE NOT NULL,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id),
 FOREIGN KEY(autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;
CREATE TABLE intentos_login (
 clave CHAR(64) PRIMARY KEY,
 intentos INT NOT NULL DEFAULT 0,
 ultimo_intento DATETIME NOT NULL,
 bloqueado_hasta DATETIME NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS recuperacion_cuenta (
    usuario_id INT UNSIGNED PRIMARY KEY,
    pregunta VARCHAR(20) NOT NULL,
    respuesta VARCHAR(255) NOT NULL,
    token_hash CHAR(64) NULL UNIQUE,
    vence DATETIME NULL,
    password_version VARCHAR(255) NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS notificaciones_vistas (
 usuario_id INT UNSIGNED NOT NULL,
 seccion ENUM('avisos','academico','reportes','calendario') NOT NULL,
 registro_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(usuario_id,seccion,registro_id),
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;
