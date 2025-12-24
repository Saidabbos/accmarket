CREATE DATABASE IF NOT EXISTS marketplace_dev
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS marketplace_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'marketplace'@'%' IDENTIFIED BY 'marketplace_password';

GRANT ALL PRIVILEGES ON marketplace_dev.* TO 'marketplace'@'%';
GRANT ALL PRIVILEGES ON marketplace_test.* TO 'marketplace'@'%';

FLUSH PRIVILEGES;

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
