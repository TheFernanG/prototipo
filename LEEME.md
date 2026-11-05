# Conecta Escuela — Proyecto PHP + MySQL

Plataforma escolar para docentes, alumnos y padres de familia. Aplicación local para una práctica o demostración, con diseño adaptable y sin dependencias de Composer, servicios externos ni claves de API.

## Requisitos

- XAMPP con PHP 8.0 o superior y MySQL/MariaDB.
- Extensión PDO MySQL habilitada (incluida habitualmente en XAMPP).
- Navegador actualizado.

## Instalación en Windows

1. Extrae el ZIP. Copia la carpeta `comunidad_escolar` completa a `C:\xampp\htdocs\`.
2. En el panel de XAMPP, inicia **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin`.
4. En **Importar**, selecciona `comunidad_escolar/base_de_datos.sql` y ejecuta la importación. El archivo crea la base y todas las tablas con datos de ejemplo. No necesitas crear tablas manualmente.
5. Revisa `config.php`: host `127.0.0.1`, puerto `3306`, base `comunidad_escolar`, usuario `root` y contraseña vacía. Si cambiaste los datos de MySQL en tu equipo, escribe los valores reales.
6. Abre **http://localhost/comunidad_escolar/**.

Importa el SQL una sola vez en una base nueva. No lo importes encima de otra instalación: las tablas usan nombres explícitos para evitar modificar registros existentes de manera silenciosa.

## Cuentas de prueba

Todas usan la contraseña **Escuela2026!** (respeta mayúscula y signo).

| Rol | Correo |
| --- | --- |
| Administrador | admin@escuela.mx |
| Docente | docente@escuela.mx |
| Alumno | alumno@escuela.mx |
| Padre de familia | padre@escuela.mx |

Entra a **Mi cuenta** para cambiar cada contraseña. Los nombres y datos incluidos son ficticios.

## Qué puede hacer cada rol

| Función | Administrador | Docente | Alumno | Padre / tutor |
| --- | --- | --- | --- | --- |
| Crear y activar/desactivar usuarios | Sí | No | No | No |
| Crear grupos, materias y vínculos familiares | Sí | No | No | No |
| Mensajes | Todos los usuarios activos | Alumnos y familias de sus grupos, administración | Docentes de sus grupos, administración | Docentes de los grupos de sus hijos, administración |
| Avisos y actividades | Publicar para la escuela o un grupo | Publicar para sus grupos | Consultar los de sus grupos y generales | Consultar los de sus hijos y generales |
| Calificaciones | Registrar y consultar | Registrar y consultar en sus clases | Consultar las propias | Consultar las de sus hijos |
| Comportamiento | Registrar y consultar | Registrar y consultar de sus alumnos | Consultar el propio | Consultar el de sus hijos |
| Eliminar avisos, actividades, notas y reportes | Cualquiera | Los que él registró | No | No |

Los mensajes son privados entre remitente y destinatario; el administrador no ve conversaciones ajenas desde su bandeja.

## Orden recomendado para configurar una escuela

1. **Usuarios:** crea docentes, alumnos y padres o tutores. No hay registro público; administración controla las altas y los roles.
2. **Grupos y familias:** crea los grupos y las materias.
3. Asigna el docente a un grupo y materia.
4. Inscribe a cada alumno en su grupo.
5. Vincula a cada padre con su hijo. Un tutor puede tener varios hijos y un alumno puede tener varios tutores.
6. El docente ya puede capturar calificaciones y reportes y conversar con las familias.

Cada grupo incluye su ciclo escolar. Un alumno puede estar inscrito en varios grupos; elimina inscripciones antiguas cuando deba dejar de recibir sus avisos. Para conservar el historial académico, no elimines clases con calificaciones: la base bloquea esa eliminación.

## Uso de módulos

- **Mi panel:** mensajes sin leer, grupos, alumnos, avisos y próximas actividades.
- **Mensajes:** selecciona destinatario, asunto y texto. Los mensajes se ven al recargar la página; no se envían por WhatsApp ni correo. Abre un mensaje recibido y pulsa **Marcar como leído**.
- **Avisos:** publicaciones generales o por grupo.
- **Seguimiento académico:** historial con escala 0–10, periodo y observaciones. El sistema verifica que el alumno esté inscrito en la clase seleccionada.
- **Comportamiento:** reportes positivos, observaciones o incidencias con fecha.
- **Calendario:** agenda en tarjetas con fecha y lugar. El panel resume las próximas actividades. No es una cuadrícula mensual.
- **Mi cuenta:** información de la cuenta y cambio de contraseña.

Los formularios de alta se abren pulsando el título con **＋**. Para corregir avisos, eventos, notas o reportes, su autor o administración los elimina y vuelve a capturarlos. La versión incluye altas, consultas y eliminación de esos registros, sin formularios de edición. Las eliminaciones son inmediatas y no tienen papelera.

Se muestran los últimos 100 mensajes, avisos y actividades, y los últimos 200 registros académicos y de comportamiento. No hay exportación PDF ni paginación en esta versión.

## Archivos

- `index.php`: interfaz, navegación y formularios.
- `app.php`: conexión, sesiones, validaciones, permisos y operaciones.
- `config.php`: conexión a MySQL y zona horaria de México.
- `base_de_datos.sql`: base de datos con relaciones y ejemplos.
- `assets/style.css`: diseño para computadora y celular.
- `PRUEBAS.md`: comprobaciones y prueba guiada.

## Protecciones incluidas

Contraseñas con hash, consultas preparadas PDO, escape HTML, verificación de permisos en el servidor, token CSRF, sesiones con cookie HttpOnly y SameSite, renovación de identificador al ingresar, expiración por 30 minutos de inactividad y bloqueo por 15 minutos después de 5 intentos fallidos para una combinación de cuenta e IP.

Para restablecer una contraseña olvidada en esta práctica, un responsable con acceso local puede generar un hash con `php -r "echo password_hash('NuevaClaveLarga!', PASSWORD_DEFAULT);"` y actualizar únicamente el campo `password` de la cuenta correcta desde phpMyAdmin. No se incluye recuperación por correo.

## Solución de problemas

- **Not Found:** verifica la ruta `C:\xampp\htdocs\comunidad_escolar\index.php` y entra por `http://localhost/comunidad_escolar/`, no abriendo el archivo con doble clic.
- **No conecta:** inicia MySQL y revisa usuario, contraseña, puerto y base en `config.php`.
- **Apache usa otro puerto:** por ejemplo, entra por `http://localhost:8080/comunidad_escolar/`.
- **Base ya existe:** no borres tu información; usa otra base vacía y cambia el nombre tanto en las primeras líneas del SQL como en `config.php`.
- **Listas vacías:** revisa las asignaciones, inscripciones, vínculos y cuentas activas.
- **No deja eliminar una clase:** conserva su historial o elimina primero las calificaciones relacionadas si realmente deseas descartarlas.
- **Importación demasiado grande por límite de índices:** usa una instalación reciente de MariaDB/MySQL compatible con utf8mb4.

## Alcance

Entrega académica local. Antes de usar datos reales o publicar en Internet se requiere configurar HTTPS, una cuenta MySQL con permisos limitados, respaldos y políticas de protección de datos de la escuela. El panel consulta novedades automáticamente mientras permanece visible en el navegador.

## Logo, mensajes y cierre de sesión

- Puedes personalizar el logo sustituyendo `assets/logo.svg`.
- Con el sitio abierto, el navegador consulta los mensajes aproximadamente cada dos segundos. El punto en **Mensajes** aparece si hay mensajes sin leer. Al enviar, aparece un punto junto a **Mensaje enviado**.
- Cerrar sesión elimina la sesión y su cookie. Si usas Atrás, las páginas privadas vuelven a comprobar el acceso.
- Para actualizar una instalación existente, copia los archivos del proyecto. Si falta la tabla de notificaciones, ejecuta `actualizar_notificaciones.sql`. Conserva tu configuración local de `config.php`.

## Actualización automática en computadora y celular

Mensajes, avisos escolares, seguimiento académico, comportamiento, calendario y
el panel de inicio consultan los cambios aproximadamente cada dos segundos.
Los puntos del menú también se muestran en pantallas pequeñas.
Los formularios de captura conservan el texto durante estas actualizaciones.
Los registros de usuarios y los listados de grupos también se actualizan.

Mantén la página abierta, visible y con conexión. Si el teléfono bloquea la
pantalla o el navegador suspende la pestaña, se sincroniza al volver.
Estas son notificaciones dentro del sitio.

La recuperación de contraseña y las preguntas de seguridad fueron retiradas.
Para aplicar la actualización, reemplaza los archivos del proyecto y elimina
`recuperacion_logica.php`, `assets/qr-recuperacion.js` y
`assets/QR-LICENSE.txt` de la instalación anterior.
Los enlaces antiguos de recuperación llevan al inicio de sesión.
La tabla antigua de recuperación puede permanecer en una base existente.

En Mensajes, abrir un recibido lo mueve a Leídos. En las otras secciones,
las novedades se marcan como vistas al entrar. Si llegan más mientras estás
dentro, aparece el punto y el contenido se actualiza. Al volver a entrar,
se quita el punto de esas novedades.
