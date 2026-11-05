# Conecta Escuela — Proyecto PHP + MySQL

Plataforma escolar para docentes, alumnos y padres de familia. Aplicación local para una práctica o demostración, con diseño adaptable y sin dependencias de Composer, servicios externos ni claves de API.

## Requisitos

- XAMPP con PHP 8.0 o superior y MySQL/MariaDB.
- Extensión PDO MySQL habilitada (incluida habitualmente en XAMPP).
- Navegador actualizado.

## Instalación en Windows

1. Extrae el ZIP en una carpeta llamada `comunidad_escolar` dentro de `C:\xampp\htdocs\`.
2. En el panel de XAMPP, inicia **Apache** y **MySQL**.
3. Abre `http://localhost/phpmyadmin`.
4. Crea o selecciona la base `comunidad_escolar`. En **Importar**, selecciona `base_unificada.sql` y ejecuta la importación.
5. Revisa `config.php`: host `127.0.0.1`, puerto `3306`, base `comunidad_escolar`, usuario `root` y contraseña vacía. Si cambiaste los datos de MySQL en tu equipo, escribe los valores reales.
6. Si la base es nueva, crea el primer administrador siguiendo la sección siguiente. Abre **http://localhost/comunidad_escolar/**.

El proyecto incluye un único archivo SQL, `base_unificada.sql`, con las 15
tablas de la aplicación. Al importarlo en la base que ya utiliza el proyecto,
se crean las tablas que falten. Las tablas existentes conservan su estructura
y sus datos.

## Primer administrador en una instalación nueva

La base nueva empieza vacía. Genera un hash con PHP en la terminal de XAMPP.

```powershell
& C:\xampp\php\php.exe -r 'echo password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT);'
```

Escribe la contraseña que usarás y copia el hash. En phpMyAdmin, selecciona
`comunidad_escolar` y ejecuta la consulta con tu correo y el hash generado.

```sql
INSERT INTO usuarios (nombre, email, password, rol)
VALUES ('Administrador escolar', 'TU_CORREO', 'PEGA_AQUI_EL_HASH', 'administrador');
```

Ingresa con ese correo y contraseña. Desde **Usuarios** puedes crear las
cuentas docentes, de alumnos y de padres. Si la base ya contiene usuarios,
utiliza las cuentas existentes.

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
- **Mensajes:** selecciona destinatario, asunto y texto. Las novedades se actualizan automáticamente. Abrir un mensaje recibido lo mueve a Leídos.
- **Avisos:** publicaciones generales o por grupo.
- **Seguimiento académico:** historial con escala 0–10, periodo y observaciones. El sistema verifica que el alumno esté inscrito en la clase seleccionada.
- **Comportamiento:** reportes positivos, observaciones o incidencias con fecha.
- **Calendario:** agenda en tarjetas con fecha y lugar. El panel resume las próximas actividades. No es una cuadrícula mensual.
- **Mi cuenta:** información de la cuenta y cambio de contraseña.

Los formularios de alta se abren pulsando el título con **＋**. Para corregir avisos, eventos, notas o reportes, su autor o administración los elimina y vuelve a capturarlos. La versión incluye altas, consultas y eliminación de esos registros, sin formularios de edición. Las eliminaciones son inmediatas y no tienen papelera.

Se muestran los últimos 100 mensajes, avisos y actividades, y los últimos 200 registros académicos y de comportamiento. Los citatorios se descargan en PDF desde el mensaje.

## Archivos

- `index.php`: interfaz, navegación y formularios.
- `app.php`: conexión, sesiones, validaciones, permisos y operaciones.
- `config.php`: conexión a MySQL y zona horaria de México.
- `base_unificada.sql`: esquema completo con las 15 tablas y sus relaciones.
- `assets/style.css`: diseño para computadora y celular.
- `PRUEBAS.md`: comprobaciones y prueba guiada.

## Protecciones incluidas

Contraseñas con hash, consultas preparadas PDO, escape HTML, verificación de permisos en el servidor, token CSRF, sesiones con cookie HttpOnly y SameSite, renovación de identificador al ingresar, expiración por 30 minutos de inactividad y bloqueo por 15 minutos después de 5 intentos fallidos para una combinación de cuenta e IP.

Para restablecer una contraseña olvidada en esta práctica, un responsable con acceso local puede generar un hash con `php -r "echo password_hash('NuevaClaveLarga!', PASSWORD_DEFAULT);"` y actualizar únicamente el campo `password` de la cuenta correcta desde phpMyAdmin. No se incluye recuperación por correo.

## Solución de problemas

- **Not Found:** verifica la ruta `C:\xampp\htdocs\comunidad_escolar\index.php` y entra por `http://localhost/comunidad_escolar/`, no abriendo el archivo con doble clic.
- **No conecta:** inicia MySQL y revisa usuario, contraseña, puerto y base en `config.php`.
- **Apache usa otro puerto:** por ejemplo, entra por `http://localhost:8080/comunidad_escolar/`.
- **Base existente:** selecciona la base usada por el proyecto e importa `base_unificada.sql`. El nombre debe coincidir con `config.php` o `DB_NAME`.
- **Listas vacías:** revisa las asignaciones, inscripciones, vínculos y cuentas activas.
- **No deja eliminar una clase:** conserva su historial o elimina primero las calificaciones relacionadas si realmente deseas descartarlas.
- **Importación demasiado grande por límite de índices:** usa una instalación reciente de MariaDB/MySQL compatible con utf8mb4.

## Alcance

Entrega académica local. Antes de usar datos reales o publicar en Internet se requiere configurar HTTPS, una cuenta MySQL con permisos limitados, respaldos y políticas de protección de datos de la escuela. El panel consulta novedades automáticamente mientras permanece visible en el navegador.

## Logo, mensajes y cierre de sesión

- Puedes personalizar el logo sustituyendo `assets/logo.svg`.
- Con el sitio abierto, el navegador consulta los mensajes aproximadamente cada dos segundos. El punto en **Mensajes** aparece si hay mensajes sin leer. Al enviar, aparece un punto junto a **Mensaje enviado**.
- Cerrar sesión elimina la sesión y su cookie. Si usas Atrás, las páginas privadas vuelven a comprobar el acceso.
- Para actualizar una instalación existente, copia los archivos del proyecto. Si falta la tabla de notificaciones, ejecuta `base_unificada.sql`. Conserva tu configuración local de `config.php`.

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

## Eliminar usuarios y descargar citatorios

En Usuarios, administración puede pulsar **Eliminar usuario**. La pantalla
siguiente muestra el nombre y el correo de la cuenta y pide confirmar la
eliminación permanente de sus datos relacionados. La cuenta de la sesión actual
no se puede eliminar. La operación se realiza en una transacción.

Los mensajes enviados a alumnos y padres o tutores tienen el botón
**Descargar citatorio PDF** al abrirlos en Leídos y en Enviados. Al enviar un mensaje se abre la carpeta
Enviados para poder descargarlo de inmediato. El documento
incluye folio, fecha de emisión, remitente, destinatario, asunto y mensaje
completo. Para convocar a una reunión, selecciona la fecha y la hora de la cita.
Puedes incluir el lugar en el cuerpo del mensaje.

El PDF se descarga con una línea para firmar a mano al imprimirlo, un campo
para el nombre y otro para la fecha de recepción. Solo el remitente y el
destinatario pueden descargarlo con su sesión iniciada. También funciona con
los mensajes anteriores dirigidos a alumnos y padres.

Copia todos los archivos, incluidos `citatorio.php` y `pdf_citatorio.php`.
El Dockerfile ya los incluye. Ejecuta `base_unificada.sql` para agregar las tablas necesarias.

## Formato del citatorio y eliminación de mensajes

Antes de usar esta versión con una base existente, ejecuta
`base_unificada.sql` una vez en esa base. El mismo archivo sirve para una instalación nueva.

Al elegir un alumno o padre como destinatario se muestran los datos del
citatorio. Elige la fecha y hora de la cita. El alumno se selecciona
automáticamente cuando hay una sola opción. Para un padre con varios hijos,
elige al que corresponde. Si envía un docente, se utiliza su cuenta como
docente del citatorio. Administración puede elegir un docente asignado al grupo
del alumno. Las relaciones se verifican también en el servidor.

El PDF usa un marco, el título CITATORIO, el texto dirigido al padre de familia,
el día, mes y año completos, la hora, el nombre del alumno, el motivo,
ATENTAMENTE, el nombre del docente y la línea para la firma del padre.
Los nombres quedan guardados en el citatorio al enviar el mensaje.
La firma se coloca a mano al imprimir el PDF.

Los mensajes anteriores conservan su descarga con el formato nuevo. Cuando
falta una fecha de cita o la cuenta tiene varios alumnos o docentes posibles,
el documento indica que ese dato está por confirmar.

**Eliminar mensaje** está disponible en Entrada, Leídos y Enviados. La
eliminación afecta solo las carpetas de la cuenta que pulsa el botón. La otra
persona conserva su copia. Los eliminados dejan de contarse como mensajes sin
leer y su citatorio deja de estar disponible para esa cuenta.

## Presentación en computadora y celular

La interfaz utiliza texto principal de 17 px, campos de 16 px, títulos más
grandes y la paleta azul petróleo, turquesa, verde y crema del logo.
Las tarjetas y botones tienen bordes y sombras para dar relieve.

En celular, el botón **Menú** abre las secciones. Un punto junto al botón
indica novedades aunque el menú esté cerrado. En las pantallas más pequeñas,
las secciones se muestran en una columna. Los formularios se apilan y las
tablas anchas se desplazan dentro de su contenedor.

Para aplicar este diseño, reemplaza los archivos y despliega la versión nueva.
Incluye `assets/layout.js` junto con los demás recursos.
