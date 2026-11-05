-- Conecta Escuela / EduStark
-- Base unificada para MySQL y MariaDB.
-- Incluye las tablas generales, notificaciones, mensajes eliminados y citatorios.
--
-- Selecciona primero la base de datos de la aplicacion en phpMyAdmin o
-- en el editor SQL de tu proveedor. En XAMPP usa comunidad_escolar,
-- o el nombre configurado en config.php / DB_NAME.
-- Para una instalacion nueva, crea esa base vacia antes de importar.
-- CREATE TABLE IF NOT EXISTS agrega las tablas que falten a una instalacion
-- existente. Las tablas que ya existan conservan su estructura y contenido.
-- Este archivo contiene el esquema. Crea el primer administrador siguiendo
-- LEEME.md o RENDER.md si estas instalando una base nueva.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 rol ENUM('administrador','docente','alumno','padre') NOT NULL,
 activo TINYINT(1) NOT NULL DEFAULT 1,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grupos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(80) NOT NULL,
 ciclo VARCHAR(30) NOT NULL,
 UNIQUE KEY grupo_ciclo(nombre,ciclo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS materias (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS asignaciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 grupo_id INT UNSIGNED NOT NULL,
 materia_id INT UNSIGNED NOT NULL,
 docente_id INT UNSIGNED NOT NULL,
 UNIQUE KEY clase(grupo_id,materia_id,docente_id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id),
 FOREIGN KEY(materia_id) REFERENCES materias(id),
 FOREIGN KEY(docente_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscripciones (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 grupo_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 UNIQUE KEY inscrito(grupo_id,alumno_id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS vinculos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 padre_id INT UNSIGNED NOT NULL,
 alumno_id INT UNSIGNED NOT NULL,
 UNIQUE KEY familia(padre_id,alumno_id),
 FOREIGN KEY(padre_id) REFERENCES usuarios(id),
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS avisos (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 autor_id INT UNSIGNED NOT NULL,
 grupo_id INT UNSIGNED NULL,
 titulo VARCHAR(150) NOT NULL,
 cuerpo TEXT NOT NULL,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(autor_id) REFERENCES usuarios(id),
 FOREIGN KEY(grupo_id) REFERENCES grupos(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS eventos (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calificaciones (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reportes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 alumno_id INT UNSIGNED NOT NULL,
 autor_id INT UNSIGNED NOT NULL,
 tipo ENUM('Positivo','Observación','Incidencia') NOT NULL,
 detalle TEXT NOT NULL,
 fecha DATE NOT NULL,
 creado TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(alumno_id) REFERENCES usuarios(id),
 FOREIGN KEY(autor_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS intentos_login (
 clave CHAR(64) PRIMARY KEY,
 intentos INT NOT NULL DEFAULT 0,
 ultimo_intento DATETIME NOT NULL,
 bloqueado_hasta DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notificaciones_vistas (
 usuario_id INT UNSIGNED NOT NULL,
 seccion ENUM('avisos','academico','reportes','calendario') NOT NULL,
 registro_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(usuario_id,seccion,registro_id),
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS mensajes_eliminados (
 mensaje_id INT UNSIGNED NOT NULL,
 usuario_id INT UNSIGNED NOT NULL,
 PRIMARY KEY(mensaje_id,usuario_id),
 FOREIGN KEY(mensaje_id) REFERENCES mensajes(id) ON DELETE CASCADE,
 FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS citatorios (
 mensaje_id INT UNSIGNED PRIMARY KEY,
 alumno_nombre VARCHAR(120) NOT NULL,
 docente_nombre VARCHAR(120) NOT NULL,
 fecha_cita DATE NOT NULL,
 hora_cita TIME NOT NULL,
 FOREIGN KEY(mensaje_id) REFERENCES mensajes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
