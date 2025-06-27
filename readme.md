# PH&JS Kickstart

## Características principales

- **Autenticación segura**: Sistema de login con protección CSRF y seguridad avanzada
- **Autenticación de dos factores (2FA)**: Implementada con códigos TOTP y códigos de respaldo
- **Panel de administración**: Interfaz moderna basada en AdminLTE y Bootstrap 4
- **Gestión de variables de entorno**: Interfaz para modificar configuraciones sin editar archivos
- **Respaldos de base de datos**: Funciones para exportar y enviar respaldos por correo
- **Migraciones de base de datos**: Sistema para gestionar cambios en la estructura de la BD
- **Correos transaccionales**: Configuración SMTP para envío de notificaciones
- **ORM integrado**: RedBeanPHP para mapeo objeto-relacional y manipulación de datos

## Dependencias del sistema

El sistema utiliza varias bibliotecas y dependencias que se instalan automáticamente a través de Composer:

- **PHPMailer (v6.10.0)**: Para el envío de correos electrónicos con soporte SMTP, adjuntos y HTML
- **DOMPDF (v3.1.0)**: Para la generación de archivos PDF desde HTML
- **PHPSpreadsheet (v4.2.0)**: Para la manipulación y generación de hojas de cálculo Excel, CSV y otros formatos
- **TCPDF (v6.9.4)**: Alternativa para generación de PDF con soporte para headers/footers personalizados
- **OTPHP (v11.3.0)**: Implementación de códigos OTP para autenticación de dos factores (2FA)
- **DB-Dumper (v3.8.0)**: Herramienta para la exportación e importación de bases de datos
- **RedBeanPHP (v5.7)**: ORM (Object-Relational Mapping) para el mapeo y manipulación de datos de manera fluida
- **Phinx (v0.16.9)**: Sistema de migraciones de base de datos para gestionar cambios en la estructura

### Requisitos del servidor

- PHP 8.3 o superior
- MariaDB 10.11.11 o superior
- Extensión PDO de PHP
- Extensión mbstring de PHP
- Extensión zip de PHP
- GD Library (para manipulación de imágenes)
- Extensión curl para TCPDF
- Extensión dom para DOMPDF y PHPSpreadsheet
- Extensión fileinfo
- Extensión xml, xmlreader, y xmlwriter

### Tecnologías utilizadas:

- **Frontend:** HTML, CSS, JS (AdminLTE y Bootstrap 4)
- **Backend:** PHP Puro
- **Base de datos:** Mariadb

## Instrucciones de instalación

### Instalación automática (recomendada)

1. Clonar el repositorio

```bash
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git core
```

2. Acceder al sistema a través del navegador

```
http://tu-servidor/core/
```

3. El sistema detectará que no está configurado y te redirigirá al asistente de instalación donde podrás:
   - Configurar la conexión a la base de datos
   - Configurar el servidor de correo
   - La estructura de la base de datos se importará automáticamente
   - Las dependencias de Composer se instalarán automáticamente (si el servidor lo permite)

### Instalación por línea de comandos (CLI)

Esta opción es ideal para servidores sin interfaz gráfica o cuando necesites automatizar la instalación.

1. Clonar el repositorio (renombralo a tu gusto)

```bash
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git core
```

```bash
cd core
```

2. Ejecutar el instalador CLI desde el directorio del proyecto

```bash
php installer/cli-setup.php
```

3. Seguir las instrucciones en la consola para configurar:
   - La base de datos
   - El servidor de correo
   - El usuario administrador

El instalador creará automáticamente el archivo `.env`, importará la estructura de la base de datos, instalará las dependencias de Composer y configurará el usuario administrador según tus preferencias.

### Instalación manual

1. Clonar el repositorio

```bash
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git core
```

2. Copiar el archivo de configuración

```bash
cp .env_example .env
```

3. Configurar las variables de entorno en el archivo `.env` según sea necesario

4. Instalar las dependencias del proyecto con Composer

```bash
composer install --no-dev --optimize-autoloader
```

5. Importar manualmente la base de datos

```
La estructura se encuentra en /config/core.sql
```

6. Ejecuta la consulta del usuario administrador que se encuentra en: /config/usuario_admin.sql

```
Usuario: admin@admin.com
Contraseña: Admin123
```

> Nota: Se recomienda cambiar estas credenciales.

### Solución de problemas con las dependencias

Si el instalador no puede instalar automáticamente las dependencias, puedes instalarlas manualmente ejecutando el siguiente comando en la raíz del proyecto:

```bash
composer install
```

## Configuración de variables de entorno

El sistema utiliza un archivo .env para gestionar la configuración. Las principales variables son:

- DB_HOST, DB_USER, DB_PASS, DB_NAME: Configuración de la base de datos
- MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD: Configuración del servidor de correo
- MAIL_ENCRYPTION: Tipo de cifrado para el correo (tls, ssl)
- MAIL_SUPPORT: Dirección de correo para soporte técnico
- APP_NAME, APP_TIMEZONE: Configuración general de la aplicación

Estas variables pueden ser modificadas desde el panel de administración en Sistema > Variables de Entorno.

## ORM - RedBeanPHP

El sistema utiliza [RedBeanPHP](https://redbeanphp.com/) como ORM (Object-Relational Mapping) para la interacción con la base de datos. RedBeanPHP es un ORM de configuración cero que facilita el trabajo con bases de datos relacionales.

### Características principales del ORM

- **Configuración cero**: No requiere archivos de configuración complejos
- **Mapeo automático**: Crea automáticamente tablas y columnas según sea necesario
- **Sintaxis fluida**: API intuitiva y fácil de usar
- **Soporte para relaciones**: Manejo sencillo de relaciones entre tablas
- **Validación integrada**: Sistema de validación de datos incorporado

### Ejemplos de uso básico

#### Crear y guardar un registro

```php
<?php
// Crear un nuevo bean (registro)
$usuario = R::dispense('usuario');
$usuario->nombre = 'Juan Pérez';
$usuario->email = 'juan@ejemplo.com';
$usuario->created_at = date('Y-m-d H:i:s');

// Guardar en la base de datos
$id = R::store($usuario);
```

#### Buscar registros

```php
<?php
// Buscar por ID
$usuario = R::load('usuario', $id);

// Buscar con condiciones
$usuarios = R::find('usuario', 'email = ?', ['juan@ejemplo.com']);

// Buscar todos los registros
$todosLosUsuarios = R::findAll('usuario');

// Buscar con ORDER BY y LIMIT
$usuarios = R::find('usuario', 'ORDER BY created_at DESC LIMIT 10');
```

#### Actualizar registros

```php
<?php
// Cargar el registro
$usuario = R::load('usuario', $id);

// Modificar propiedades
$usuario->nombre = 'Juan Carlos Pérez';
$usuario->updated_at = date('Y-m-d H:i:s');

// Guardar cambios
R::store($usuario);
```

#### Eliminar registros

```php
<?php
// Eliminar un registro específico
$usuario = R::load('usuario', $id);
R::trash($usuario);

// O eliminar directamente por ID
R::trash('usuario', $id);

// Eliminar múltiples registros
R::trashAll(R::find('usuario', 'status = ?', ['inactivo']));
```

#### Trabajar con relaciones

```php
<?php
// Crear relación uno a muchos
$usuario = R::dispense('usuario');
$usuario->nombre = 'Juan';

$post = R::dispense('post');
$post->titulo = 'Mi primer post';
$post->contenido = 'Contenido del post...';

// Asociar el post al usuario
$usuario->ownPostList[] = $post;
R::store($usuario);

// Acceder a posts de un usuario
$usuario = R::load('usuario', $id);
$posts = $usuario->ownPostList;

// Relación muchos a muchos
$tag = R::dispense('tag');
$tag->nombre = 'PHP';

$post->sharedTagList[] = $tag;
R::store($post);
```

### Configuración en el sistema

La configuración de RedBeanPHP se realiza automáticamente durante la inicialización del sistema, utilizando las credenciales de base de datos definidas en el archivo `.env`.

```php
<?php
// Configuración automática (ya incluida en el sistema)
R::setup($dsn, $username, $password);
R::freeze(true); // Congela el esquema en producción
```

### Buenas prácticas

- **Congelar en producción**: Usar `R::freeze(true)` para evitar cambios automáticos en el esquema
- **Validación**: Implementar validaciones antes de guardar datos
- **Transacciones**: Usar transacciones para operaciones complejas
- **Nomenclatura**: Seguir convenciones de nomenclatura consistentes para tablas y campos

Para más información sobre RedBeanPHP, consulta la [documentación oficial](https://redbeanphp.com/index.php?p=/crud).

## Migraciones de Base de Datos

El sistema utiliza [Phinx](https://phinx.org/) como herramienta de migración para gestionar la estructura de base de datos y datos iniciales. Esto permite una instalación más robusta y la posibilidad de actualizar la estructura de la base de datos de manera controlada.

### Estructura de directorios

- `/db/migrations/` - Contiene los archivos de migración para crear y modificar tablas
- `/db/seeds/` - Contiene los archivos de semillas para poblar las tablas con datos iniciales

### Uso de comandos de migración

Los siguientes comandos están disponibles para gestionar las migraciones (requieren que Composer esté instalado):

```bash
# Ejecutar todas las migraciones pendientes
vendor/bin/phinx migrate

# Ejecutar semillas (datos iniciales)
vendor/bin/phinx seed:run

# Crear una nueva migración
vendor/bin/phinx create NombreDeMigracion

# Crear una nueva semilla
vendor/bin/phinx seed:create NombreDeSemilla

# Revertir la última migración
vendor/bin/phinx rollback
```

### Migración automática durante la instalación

Durante el proceso de instalación del sistema, las migraciones y semillas se ejecutan automáticamente para configurar la base de datos y crear el usuario administrador con los datos proporcionados en el formulario de instalación.

### Ejemplo de creación de una migración personalizada

Si necesitas crear una nueva tabla o modificar una existente, puedes crear una migración:

```bash
vendor/bin/phinx create AddNuevaCampoATabla
```

Esto generará un archivo en la carpeta `/db/migrations/` que puedes editar:

```php
<?php
// En el método up() defines los cambios a realizar
public function up()
{
    $table = $this->table('nombre_tabla');
    $table->addColumn('nuevo_campo', 'string', ['limit' => 100])
          ->update();
}

// En el método down() defines cómo revertir los cambios
public function down()
{
    $table = $this->table('nombre_tabla');
    $table->removeColumn('nuevo_campo')
          ->update();
}
```

Para más información sobre cómo usar Phinx, consulta la [documentación oficial](https://book.cakephp.org/phinx/0/en/index.html).

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Jgamboaa/PH-JS-Kickstart)
