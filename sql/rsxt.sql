create database rsxt_db;

create user 'rsxt_db_admin'@'localhost' identified by 'rsxt_password_123456';
grant select,insert,update,delete on rsxt_db.* to 'rsxt_db_admin'@'localhost';
