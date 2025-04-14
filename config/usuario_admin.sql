LOCK TABLES `admin` WRITE;


/*!40000 ALTER TABLE `admin` DISABLE KEYS */
;


INSERT INTO
  `admin` (
    `id`,
    `username`,
    `password`,
    `user_firstname`,
    `user_lastname`,
    `photo`,
    `created_on`,
    `color_mode`,
    `last_login`,
    `roles_ids`,
    `admin_gender`,
    `admin_estado`
  )
VALUES
  (
    1,
    'admin@admin.com',
    '$2y$10$RT8BkA4YVF3e1PKyY4ZBlOk1B7wHD8gBiQAleFSPEjTEE98yJiXzm',
    'Usuario',
    'Administrador',
    '',
    '2023-09-07',
    'light',
    '2025-04-12 11:08:43',
    '1',
    '0',
    0
  );


/*!40000 ALTER TABLE `admin` ENABLE KEYS */
;


UNLOCK TABLES;