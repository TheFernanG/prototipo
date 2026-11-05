# Publicar Conecta Escuela en Render

1. Crea una base MySQL o MariaDB en un proveedor accesible desde Render.
   Selecciona la base vacía en el panel del proveedor o en tu cliente MySQL
   e importa `base_unificada.sql` una sola vez. El archivo crea las tablas,
   con todas las tablas de la plataforma.
   Si ya tienes datos en XAMPP y quieres migrarlos, usa una exportación de
   esa instalación en lugar de inicializar la base vacía.

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
y los requisitos TLS del proveedor.

## Actualizar una base de datos existente

Selecciona la base que usa `DB_NAME` e importa `base_unificada.sql`. Es el
único archivo SQL del proyecto e incluye las 15 tablas de la plataforma,
las notificaciones, los mensajes eliminados por cuenta y los citatorios.

La importación crea las tablas que falten. Las tablas existentes conservan
su estructura y sus datos. Después, sube los archivos y despliega la nueva
versión de la aplicación.
