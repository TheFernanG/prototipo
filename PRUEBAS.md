# Verificación de la base unificada

Se compararon los tres SQL originales. La versión unificada contiene las
15 tablas con una sola definición por tabla. Se comprobaron las columnas,
claves foráneas y el orden de creación de sus dependencias.

## Comprobar en tu instalación

1. Selecciona en phpMyAdmin la base configurada en `config.php` o `DB_NAME`.
2. Importa `base_unificada.sql`.
3. Comprueba que se muestren 15 tablas de la aplicación.
4. Si es una instalación nueva, crea el administrador siguiendo `LEEME.md`.
5. Ingresa y configura usuarios, grupos, docentes, inscripciones y familias.
6. Envía un mensaje con citatorio y comprueba la descarga en PDF.
7. Revisa los indicadores de novedades y la eliminación de mensajes.
8. Si actualizaste una instalación, comprueba también sus usuarios y registros anteriores.

La importación sobre tu servidor MySQL o MariaDB queda por comprobar.
