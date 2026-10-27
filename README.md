# ClassCheck — versión inicial reconstruida

Sistema web PHP + MySQL preparado para una instalación local con XAMPP en macOS.

## Importante: alcance honesto

Esta entrega incluye autenticación basada en el rol real almacenado en MySQL, creación segura del primer administrador, registro de alumnos y solicitudes de cuentas docentes, paneles por rol, gestión básica de usuarios/carreras/unidades/horarios, control de plazos, solicitudes de reapertura y auditoría.

**No es todavía una implementación académica completa para producción.** Las pantallas de captura detallada de asistencia y calificaciones están definidas en la base de datos, pero los formularios y flujos de registro de esas notas/asistencias aún deben implementarse y probarse con las reglas oficiales del instituto. La edición/eliminación de horarios también necesita un flujo POST completo. No uses datos personales reales hasta completar esas partes y realizar pruebas.

## Requisitos

- XAMPP para macOS con Apache, PHP y MySQL/MariaDB.
- PHP 8.1 o superior recomendado.
- PDO MySQL habilitado.

## Instalación en Mac

1. Descomprime `ClassCheck.zip`.
2. Copia la carpeta `ClassCheck` a `/Applications/XAMPP/htdocs/`.
3. Abre el panel de XAMPP y enciende **Apache** y **MySQL**.
4. Abre `http://localhost/phpmyadmin`.
5. Ve a **Importar**, selecciona `ClassCheck/database/classcheck.sql` y ejecuta la importación. El SQL crea la base `classcheck`, sus tablas y las tres carreras.
6. Revisa `ClassCheck/config/conexion.php`. La configuración inicial usa host `127.0.0.1`, base `classcheck`, usuario `root` y contraseña vacía, que es frecuente en XAMPP local. Si configuraste otra contraseña, actualiza `$DB_PASS`.
7. Abre `http://localhost/ClassCheck/crear_administrador.php`.
8. Crea el primer administrador usando un correo propio y una contraseña de **al menos 14 caracteres**.
9. Cuando aparezca la confirmación, elimina inmediatamente `crear_administrador.php` de `htdocs/ClassCheck/`.
10. Abre `http://localhost/ClassCheck/` e inicia sesión.

## Roles y permisos

- El rol usado para autorizar y redirigir se lee de la fila de `usuarios`; seleccionar un rol visual en el formulario de login no cambia permisos.
- El registro público solo admite alumno y docente. Los docentes quedan `pendiente` hasta aprobación.
- El primer administrador se crea mediante un instalador que deja de funcionar cuando ya existe uno. Elimina ese archivo después de usarlo.
- Los permisos se comprueban en el servidor mediante `require_role()`.

## Datos académicos

El registro de alumno exige carrera, módulo (1–3) y ciclo (1–2). Las cuentas docentes no muestran estos campos. Las contraseñas se guardan con `password_hash()`.

## Pruebas recomendadas después de instalar

1. Crear el primer administrador e iniciar sesión.
2. Crear una cuenta de alumno y comprobar el panel alumno.
3. Crear una cuenta docente y comprobar que queda pendiente.
4. Desde el administrador, aprobar la cuenta docente.
5. Iniciar sesión como docente y alumno en sesiones separadas.
6. Intentar abrir `administrador/index.php` con una sesión de alumno o docente; debe devolver acceso denegado.
7. Crear unidades didácticas y horarios; verificar que aparecen en las vistas correspondientes.
8. Crear un plazo y probar una solicitud de reapertura después del vencimiento.
9. Revisar la auditoría.

## Seguridad y limitaciones

- No subir `config/conexion.php` con contraseñas reales a un repositorio público.
- Para producción, configura HTTPS, contraseñas MySQL, límites de intentos de acceso, política de respaldos, gestión segura de secretos y revisión profesional.
- La recuperación por correo no está habilitada.
- El panel docente permite solicitar reapertura, pero aún no incorpora los formularios completos de captura/edición de asistencia y calificaciones.
- El esquema SQL borra las tablas de `classcheck` al importarse; importa solo en una base nueva o respalda primero tus datos.
