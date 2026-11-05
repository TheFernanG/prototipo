# Publicar Conecta Escuela en Render

1. Crea una base MySQL o MariaDB en un proveedor accesible desde Render.
   Selecciona la base vacía en el panel del proveedor o en tu cliente MySQL
   e importa `base_render.sql` una sola vez. El archivo crea las tablas,
   sin insertar cuentas de demostración.
   Si ya tienes datos en XAMPP y quieres migrarlos, usa una exportación de
   esa instalación en lugar de inicializar la base vacía. Cambia las
   contraseñas de las cuentas de demostración antes de publicar.

2. Crea el primer administrador en la base nueva. En PowerShell de Windows
   con XAMPP instalado, ejecuta:

   ```powershell
   & C:\xampp\php\php.exe -r 'echo password_hash(trim(fgets(STDIN)), PASSWORD_DEFAULT);'
   ```

   Escribe una contraseña exclusiva y presiona Enter. Copia el hash completo
   generado. En el editor SQL de tu proveedor, selecciona la base y ejecuta
   la siguiente consulta con tu correo y el hash generado:

   ```sql
   INSERT INTO usuarios (nombre, email, password, rol)
   VALUES ('Administrador escolar', 'TU_CORREO', 'PEGA_AQUI_EL_HASH', 'administrador');
   ```

3. Sube el contenido de la carpeta descomprimida a tu repositorio de GitHub.
   `Dockerfile` e `index.php` deben quedar juntos. Si usas tu repositorio
   existente, copia estos archivos en tu copia local y sube los cambios a
   la rama que conectarás en Render.

4. En Render abre **New → Web Service**, elige tu repositorio y selecciona
   **Docker**. Deja **Root Directory** vacío si el Dockerfile está en la raíz.
   Si lo guardaste en una subcarpeta, escribe su ruta en ese campo. Deja
   **Docker Command** vacío. Apache escucha en el puerto 10000. Si reutilizas
   un Web Service y antes configuraste `PORT=80`, cámbialo a `10000`.

5. En **Environment** agrega las variables:

   | Variable | Valor |
   | --- | --- |
   | DB_HOST | Host de tu MySQL remoto, sin protocolo |
   | DB_PORT | Puerto que indica tu proveedor, normalmente 3306 |
   | DB_NAME | Nombre de la base que importaste |
   | DB_USER | Usuario de esa base |
   | DB_PASSWORD | Contraseña de ese usuario |
   | DB_SSL_CA | Si el proveedor requiere TLS con CA, ruta al certificado dentro de Render. Puedes cargarlo en Secret Files y usar `/etc/secrets/mysql-ca.pem`. |

6. Presiona **Create Web Service**. Cuando el servicio aparezca como
   **Live**, abre la URL `onrender.com` del panel. Prueba el inicio de sesión
   del administrador y el envío de mensajes entre dos cuentas.

Si la portada abre pero el inicio de sesión falla, revisa **Logs**, confirma
que importaste el esquema en la base de `DB_NAME` y verifica el acceso remoto
y los requisitos TLS del proveedor. `base_de_datos.sql` es la instalación
original para XAMPP y contiene datos de prueba.

## Actualizar una base de datos existente

Antes de desplegar esta versión, ejecuta `actualizar_notificaciones.sql` en la
misma base de datos que ya usa la aplicación. El script agrega la tabla de
lecturas por usuario y conserva los registros existentes. Para una instalación
nueva, la tabla ya está incluida en `base_render.sql`.

Los puntos se actualizan mientras el panel está abierto. Al entrar a una sección
escolar se marcan sus novedades como vistas para esa cuenta. Los registros
previos de otros usuarios pueden aparecer como novedades hasta la primera visita.
En Mensajes, **Abrir** mueve el mensaje de **Entrada** a **Leídos** y muestra
su contenido. Los mensajes enviados se consultan en **Enviados**.

Reemplaza los archivos y elimina los archivos de recuperación anteriores según
`LEEME.md`. Despliega la nueva versión para activar las actualizaciones
automáticas en computadora y celular.

Para esta versión, ejecuta también `actualizar_mensajes_citatorios.sql` en la
base existente antes del despliegue. Agrega las tablas de citatorios y de
mensajes eliminados por cuenta. En una instalación nueva basta con importar
`base_render.sql`.
