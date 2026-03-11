ALTER USER 'laravel'@'%' IDENTIFIED WITH mysql_native_password BY 'laravel';
ALTER USER 'root'@'%' IDENTIFIED WITH mysql_native_password BY 'root';
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';

FLUSH PRIVILEGES;
