LOCK TABLES `admin` WRITE;

/*!40000 ALTER TABLE `admin` DISABLE KEYS */ ;

INSERT INTO `admin` (`username`, `password`, `user_firstname`, `user_lastname`, `photo`, `created_on`, `roles_ids`, `admin_gender`)
VALUES ('admin@admin.com', '$2y$10$RT8BkA4YVF3e1PKyY4ZBlOk1B7wHD8gBiQAleFSPEjTEE98yJiXzm', 'Usuario', 'Administrador', '', NOW(), '1', '0');

/*!40000 ALTER TABLE `admin` ENABLE KEYS */ ;

UNLOCK TABLES;