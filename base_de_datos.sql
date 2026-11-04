-- Importar en phpMyAdmin. No elimina tablas existentes.
CREATE DATABASE IF NOT EXISTS comunidad_escolar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE comunidad_escolar;
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
-- Contraseña de demostración para todas las cuentas: Escuela2026!
-- Hash bcrypt compatible con password_verify() de PHP. Cambiar las contraseñas al instalar.

INSERT INTO usuarios(id,nombre,email,password,rol) VALUES
(1,'Administrador Escolar','admin@escuela.mx','$2b$12$SwKMSmuXBoJrMmQJRc.wiO9QT4jhAyC.F8chOYm9eUMTziE.JpBzW','administrador'),
(2,'Laura Martínez','docente@escuela.mx','$2b$12$SwKMSmuXBoJrMmQJRc.wiO9QT4jhAyC.F8chOYm9eUMTziE.JpBzW','docente'),
(3,'Diego Hernández','alumno@escuela.mx','$2b$12$SwKMSmuXBoJrMmQJRc.wiO9QT4jhAyC.F8chOYm9eUMTziE.JpBzW','alumno'),
(4,'Ana Hernández','padre@escuela.mx','$2b$12$SwKMSmuXBoJrMmQJRc.wiO9QT4jhAyC.F8chOYm9eUMTziE.JpBzW','padre');
INSERT INTO grupos(id,nombre,ciclo) VALUES(1,'1° A','2026–2027');
INSERT INTO materias(id,nombre) VALUES(1,'Matemáticas'),(2,'Español');
INSERT INTO asignaciones(id,grupo_id,materia_id,docente_id) VALUES(1,1,1,2),(2,1,2,2);
INSERT INTO inscripciones(grupo_id,alumno_id) VALUES(1,3);
INSERT INTO vinculos(padre_id,alumno_id) VALUES(4,3);
INSERT INTO avisos(autor_id,grupo_id,titulo,cuerpo) VALUES
(1,NULL,'¡Bienvenidos a Conecta Escuela!','Este es nuestro espacio de comunicación escolar. Consulta los avisos y mantén tus datos de acceso privados.'),
(2,1,'Material para la siguiente clase','Recuerden traer su cuaderno de Matemáticas y una regla. ¡Nos vemos en clase!');
INSERT INTO mensajes(remitente_id,destinatario_id,asunto,cuerpo) VALUES(2,4,'Bienvenida al grupo','Hola, Ana. Desde este espacio podemos conversar sobre el progreso de Diego. ¡Gracias por acompañar su aprendizaje!');
INSERT INTO calificaciones(alumno_id,asignacion_id,autor_id,periodo,calificacion,observaciones) VALUES(3,1,2,'Primer trimestre',9.20,'Participa con entusiasmo y resuelve los ejercicios de clase.');
INSERT INTO reportes(alumno_id,autor_id,tipo,detalle,fecha) VALUES(3,2,'Positivo','Apoyó a sus compañeros durante la actividad en equipo.',CURDATE());
INSERT INTO eventos(autor_id,grupo_id,titulo,descripcion,fecha,lugar) VALUES(1,NULL,'Reunión de bienvenida','Presentaremos las actividades y los objetivos del ciclo escolar.',DATE_ADD(CURDATE(),INTERVAL 7 DAY),'Salón de usos múltiples');
