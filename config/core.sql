/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;


/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;


/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;


SET NAMES
  utf8mb4;


/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */
;


/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */
;


/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */
;


# Dump of table admin
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `admin`;


CREATE TABLE
  `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(150) NOT NULL,
    `password` varchar(250) DEFAULT NULL,
    `user_firstname` varchar(50) NOT NULL,
    `user_lastname` varchar(50) NOT NULL,
    `photo` varchar(200) NOT NULL,
    `created_on` date NOT NULL,
    `color_mode` varchar(15) NOT NULL DEFAULT 'light',
    `last_login` varchar(100) DEFAULT NULL,
    `roles_ids` varchar(50) DEFAULT NULL,
    `admin_gender` varchar(30) DEFAULT NULL,
    `admin_estado` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


# Dump of table company_data
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `company_data`;


CREATE TABLE
  `company_data` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rep_name` varchar(150) NOT NULL,
    `company_name` varchar(150) NOT NULL,
    `company_name_short` varchar(100) NOT NULL,
    `address` text NOT NULL,
    `rep_age_ltr` varchar(50) NOT NULL,
    `rep_marital_status` varchar(50) NOT NULL,
    `rep_nacionality` varchar(50) NOT NULL,
    `rep_studies` varchar(100) NOT NULL,
    `rep_dpi_number` varchar(50) NOT NULL,
    `rep_position` varchar(150) NOT NULL,
    `company_nit` varchar(50) NOT NULL,
    `company_employers_number` varchar(50) NOT NULL,
    `app_name` varchar(50) NOT NULL,
    `app_version` varchar(20) NOT NULL,
    `developer_name` varchar(100) NOT NULL,
    `rep_contract` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 2 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


LOCK TABLES `company_data` WRITE;


/*!40000 ALTER TABLE `company_data` DISABLE KEYS */
;


INSERT INTO
  `company_data` (
    `id`,
    `rep_name`,
    `company_name`,
    `company_name_short`,
    `address`,
    `rep_age_ltr`,
    `rep_marital_status`,
    `rep_nacionality`,
    `rep_studies`,
    `rep_dpi_number`,
    `rep_position`,
    `company_nit`,
    `company_employers_number`,
    `app_name`,
    `app_version`,
    `developer_name`,
    `rep_contract`
  )
VALUES
  (
    1,
    'Usuario',
    'Core',
    'Core',
    'Ciudad',
    'veinticeis años',
    'Soltero',
    'Guatemalteco',
    'Bachiller',
    '3019 21090 0101',
    'Administrador Único y Representante Legal',
    '0000',
    '0000',
    'Core',
    '1.0',
    'Isaí Gamboa',
    'Texto'
  );


/*!40000 ALTER TABLE `company_data` ENABLE KEYS */
;


UNLOCK TABLES;


# Dump of table login_attempts
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `login_attempts`;


CREATE TABLE
  `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `login_attempts` int(11) DEFAULT 0,
    `last_attempt` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_username` (`username`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


# Dump of table login_logs
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `login_logs`;


CREATE TABLE
  `login_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(255) NOT NULL,
    `status` varchar(50) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


# Dump of table security_logs
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `security_logs`;


CREATE TABLE
  IF NOT EXISTS `security_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(50) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


# Dump of table roles
# ------------------------------------------------------------
DROP TABLE
  IF EXISTS `roles`;


CREATE TABLE
  `roles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
  ) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_spanish_ci;


LOCK TABLES `roles` WRITE;


/*!40000 ALTER TABLE `roles` DISABLE KEYS */
;


INSERT INTO
  `roles` (`id`, `nombre`)
VALUES
  (1, 'Administrador'),
  (2, 'Usuario');


/*!40000 ALTER TABLE `roles` ENABLE KEYS */
;


UNLOCK TABLES;


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */
;


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */
;


/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */
;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;


/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;


/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;