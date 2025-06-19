# PH&JS Kickstart

## Dependencias del sistema

El sistema utiliza varias bibliotecas y dependencias que se instalan automáticamente a través de Composer:

- **PHPMailer (v6.10.0)**: Para el envío de correos electrónicos con soporte SMTP, adjuntos y HTML
- **DOMPDF (v3.1.0)**: Para la generación de archivos PDF desde HTML
- **PHPSpreadsheet (v4.2.0)**: Para la manipulación y generación de hojas de cálculo Excel, CSV y otros formatos
- **TCPDF (v6.9.4)**: Alternativa para generación de PDF con soporte para headers/footers personalizados
- **OTPHP (v11.3.0)**: Implementación de códigos OTP para autenticación de dos factores (2FA)

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

## Migraciones de Base de Datos

El sistema cuenta con un módulo de migraciones para gestionar la estructura y objetos de la base de datos:

### Estructura de directorios

- `/migrations/functions/` - Funciones SQL
- `/migrations/triggers/` - Triggers SQL
- `/migrations/procedures/` - Procedimientos almacenados

### Uso del comando de migración

Para ejecutar migraciones específicas, utilice el siguiente comando:

```bash
php migrations/migrate.php [tipo]
```

Donde `[tipo]` puede ser:

- `functions` - Para migrar funciones SQL
- `triggers` - Para migrar triggers de la base de datos
- `procedures` - Para migrar procedimientos almacenados

### Ejemplo:

```bash
# Migrar todas las funciones
php migrations/migrate.php functions

# Migrar todos los triggers
php migrations/migrate.php triggers
```

Los archivos SQL deben incluir sentencias `DELIMITER` y `DROP IF EXISTS` para manejar adecuadamente la creación y actualización de objetos existentes.

[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Jgamboaa/PH-JS-Kickstart)
