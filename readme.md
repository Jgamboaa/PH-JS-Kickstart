# PH&JS Kickstart

## Dependencias del sistema

El sistema utiliza varias bibliotecas y dependencias que se instalan automáticamente a través de Composer:

- **PHPMailer**: Para el envío de correos electrónicos
- **DOMPDF**: Para la generación de archivos PDF
- **PHPSpreadsheet**: Para la manipulación de hojas de cálculo Excel

### Requisitos del servidor

- PHP 8.1 o superior
- MySQL 5.7 o superior
- Extensión PDO de PHP
- Extensión mbstring de PHP
- Extensión zip de PHP
- GD Library (para manipulación de imágenes)

## Instrucciones de instalación

### Instalación automática (recomendada)

1. Clonar el repositorio

```bash
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git
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

1. Clonar el repositorio

```bash
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git
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
git clone https://github.com/Jgamboaa/PH-JS-Kickstart.git
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
