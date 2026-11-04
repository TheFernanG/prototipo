# Verificación de la entrega

## Comprobaciones ejecutadas

Entorno de comprobación: PHP 8.0.30 y MariaDB 10.11.14 en Linux. La instalación en tu Windows/XAMPP debe realizarse con los pasos de `LEEME.md`.

- Sintaxis de `app.php`, `index.php` y `config.php` comprobada con el intérprete PHP.
- Importación completa del SQL en una base nueva: tablas, claves foráneas y datos de prueba creados correctamente.
- Contraseña de las cuatro cuentas verificada con `password_verify()` de PHP.
- Inicio de sesión, navegación por todos los módulos permitidos y cierre de sesión con los cuatro roles.
- Acceso de administración a usuarios y grupos.
- Registro de una calificación por un docente en su clase.
- Envío de mensaje del docente al padre y recepción en su bandeja.
- Rechazo de registro de calificación para un alumno ajeno al docente.
- Rechazo de publicación de aviso en un grupo ajeno al docente.
- Rechazo de registro de calificaciones por un padre.
- Rechazo de mensajes desde un padre hacia un docente ajeno a sus grupos.
- Datos de otro grupo, otro alumno y su calificación excluidos de las pantallas de alumno, padre y docente del primer grupo.
- Rechazo de operaciones con token CSRF inválido.
- Texto con etiquetas de script mostrado como texto escapado en las calificaciones.

Las cuentas y registros adicionales usados en las pruebas no forman parte del SQL entregado.

## Recorrido recomendado en XAMPP

1. Importa el SQL e ingresa con `admin@escuela.mx`.
2. En **Usuarios**, crea un alumno y un padre de prueba con correos distintos.
3. En **Grupos y familias**, inscribe al alumno en `1° A` y vincula a su padre. Ya hay una docente asignada a Matemáticas y Español en ese grupo.
4. Cierra sesión e ingresa con `docente@escuela.mx`.
5. En **Seguimiento académico**, abre el formulario con **＋** y captura una calificación del alumno que acabas de inscribir.
6. Registra una observación de comportamiento, publica un aviso para `1° A` y crea una actividad con fecha futura.
7. Envía un mensaje al padre que acabas de crear.
8. Ingresa con la cuenta del padre y verifica mensaje, calificación, reporte, aviso y actividad. Marca el mensaje como leído.
9. Ingresa con la cuenta del alumno y verifica que vea su propia información.
10. En un celular o en el modo adaptable del navegador, revisa la navegación horizontal y los formularios.

Los registros de pruebas pueden eliminarse desde los módulos según los permisos del rol. Las cuentas se desactivan desde administración.
