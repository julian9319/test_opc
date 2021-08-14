
CREATE DATABASE IF NOT EXISTS test_optime_consultin CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE IF NOT EXISTS category (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(30) NOT NULL,
  active tinyint(4) NOT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT NULL
) ENGINE = innodb DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;


CREATE TABLE IF NOT EXISTS product (
  id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code varchar(10) NOT NULL,
  name varchar(30) NOT NULL,
  description varchar(350) NOT NULL,
  brand varchar(30) NOT NULL,
  active tinyint(4) NOT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT NULL,
  price double(19, 4) NOT NULL DEFAULT '0',
  fk_category int(11) NOT NULL,
  FOREIGN KEY (fk_category) REFERENCES category (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = innodb DEFAULT CHARSET = utf8 COLLATE = utf8_general_ci;
