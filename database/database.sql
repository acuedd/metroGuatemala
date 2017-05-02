-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema metroguatemala
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS metroguatemala ;

-- -----------------------------------------------------
-- Schema metroguatemala
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS metroguatemala DEFAULT CHARACTER SET latin1 ;
USE metroguatemala ;

-- -----------------------------------------------------
-- Table menu_categoria
-- -----------------------------------------------------
DROP TABLE IF EXISTS menu_categoria ;

CREATE TABLE IF NOT EXISTS menu_categoria (
  id INT(11) NOT NULL AUTO_INCREMENT COMMENT '',
  nombre VARCHAR(45) NOT NULL DEFAULT '' COMMENT '',
  imagen VARCHAR(150) NOT NULL DEFAULT '' COMMENT '',
  PRIMARY KEY (id)  COMMENT '')
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table menu
-- -----------------------------------------------------
DROP TABLE IF EXISTS menu ;

CREATE TABLE IF NOT EXISTS menu (
  menu_id INT(11) NOT NULL AUTO_INCREMENT COMMENT '',
  page VARCHAR(45) NOT NULL DEFAULT '' COMMENT '',
  nombre VARCHAR(200) NOT NULL DEFAULT '' COMMENT '',
  modulo VARCHAR(45) NOT NULL DEFAULT '' COMMENT '',
  image VARCHAR(155) NOT NULL DEFAULT '' COMMENT '',
  categoria_id INT(11) NOT NULL COMMENT '',
  father INT(11) NULL COMMENT '',
  PRIMARY KEY (menu_id)  COMMENT '',
  CONSTRAINT fk_menu_menu_categoria1
    FOREIGN KEY (categoria_id)
    REFERENCES menu_categoria (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_menu_menu1
    FOREIGN KEY (father)
    REFERENCES menu (menu_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = latin1;

CREATE INDEX fk_menu_menu_categoria1_idx ON menu (categoria_id ASC)  COMMENT '';

CREATE INDEX fk_menu_menu1_idx ON menu (father ASC)  COMMENT '';


-- -----------------------------------------------------
-- Table usuario
-- -----------------------------------------------------
DROP TABLE IF EXISTS main_user ;

CREATE TABLE IF NOT EXISTS main_user (
  userid INT(10) NOT NULL AUTO_INCREMENT COMMENT '',
  nickname VARCHAR(45) NOT NULL DEFAULT '' COMMENT '',
  password VARCHAR(100) NOT NULL COMMENT '',
  tipo ENUM('admin','normal','other') NOT NULL DEFAULT 'admin' COMMENT '',
  fullname VARCHAR(150) NOT NULL DEFAULT '' COMMENT '',
  first_name VARCHAR(150) NOT NULL DEFAULT '' COMMENT '',
  last_name VARCHAR(150) NOT NULL DEFAULT '' COMMENT '',
  register_date DATE NOT NULL COMMENT '',
  time TIME NOT NULL COMMENT '',
  active ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '',
  email VARCHAR(100) NULL COMMENT '',
  birth_date DATE NULL COMMENT '',
  modify_date DATETIME NULL COMMENT '',
  avatar BLOB NULL COMMENT '',
  genre VARCHAR (10) NULL,
  PRIMARY KEY (userid)  COMMENT '')
ENGINE = InnoDB
AUTO_INCREMENT = 1
DEFAULT CHARACTER SET = latin1;

-- -----------------------------------------------------
-- Table phones
-- -----------------------------------------------------
DROP TABLE IF EXISTS phones ;

CREATE TABLE IF NOT EXISTS phones (
  idphone INT NOT NULL AUTO_INCREMENT COMMENT '',
  table_from VARCHAR(45) NOT NULL COMMENT 'table_from',
  idtable INT(11) NOT NULL COMMENT '',
  phone_number VARCHAR(45) NOT NULL COMMENT '',
  tag VARCHAR(45) NULL COMMENT '',
  PRIMARY KEY (idphone)  COMMENT '')
ENGINE = InnoDB;

CREATE INDEX idtable ON phones (idtable ASC)  COMMENT '';


-- -----------------------------------------------------
-- Table direccion
-- -----------------------------------------------------
DROP TABLE IF EXISTS addresses ;

CREATE TABLE IF NOT EXISTS addresses (
  idaddress INT NOT NULL AUTO_INCREMENT COMMENT '',
  table_from VARCHAR(45) NOT NULL COMMENT '',
  idtable INT(11) NULL COMMENT '',
  address VARCHAR(250) NULL COMMENT '',
  condominium VARCHAR(250) NULL COMMENT '',
  house_number VARCHAR(10) NULL COMMENT '',
  settlement VARCHAR(250) NULL COMMENT '',
  district VARCHAR(10) NULL COMMENT '',
  town VARCHAR(250) NULL COMMENT 'profesion_i',
  state VARCHAR(250) NULL COMMENT '',
  zone INT NULL,
  PRIMARY KEY (idaddress)  COMMENT '')
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table usuario_acceso
-- -----------------------------------------------------
DROP TABLE IF EXISTS user_access ;

CREATE TABLE IF NOT EXISTS user_access (
  id INT(10) NOT NULL COMMENT '',
  user INT(11) NOT NULL COMMENT '',
  access VARCHAR(150) NOT NULL DEFAULT '' COMMENT '',
  PRIMARY KEY (id)  COMMENT '',
  CONSTRAINT fk_useraccess
    FOREIGN KEY (user)
    REFERENCES main_user (userid)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;

CREATE INDEX fk_user_access1_idx ON user_access (user ASC)  COMMENT '';
-- -----------------------------------------------------
-- Table mensaje
-- -----------------------------------------------------
DROP TABLE IF EXISTS message ;

CREATE TABLE IF NOT EXISTS message (
  id INT(11) NOT NULL AUTO_INCREMENT COMMENT '',
  userid_from INT(11) NOT NULL COMMENT '',
  header VARCHAR(100) NOT NULL COMMENT '',
  content LONGTEXT NULL COMMENT '',
  userid_to INT(11) NULL COMMENT '',
  readed ENUM('Y', 'N') NOT NULL DEFAULT 'N' COMMENT '',
  shipping_date DATETIME NULL COMMENT '',
  PRIMARY KEY (id)  COMMENT '',
  CONSTRAINT fk_mensaje_usuario1
    FOREIGN KEY (userid_from)
    REFERENCES main_user (userid)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX fk_mensaje_usuario1_idx ON message (userid_from ASC)  COMMENT '';

CREATE INDEX userid_to ON message (userid_to ASC)  COMMENT '';

-- -----------------------------------------------------
-- Table mensaje_detail
-- -----------------------------------------------------
DROP TABLE IF EXISTS message_detail ;

CREATE TABLE IF NOT EXISTS message_detail (
  id INT NOT NULL AUTO_INCREMENT COMMENT '',
  message_id INT NOT NULL COMMENT '',
  attach BLOB NULL COMMENT '',
  PRIMARY KEY (id, message_id)  COMMENT '',
  CONSTRAINT fk_message_detail_message1
    FOREIGN KEY (message_id)
    REFERENCES message_detail (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX fk_message_detail_message1_idx ON message_detail (message_id ASC)  COMMENT '';

-- -----------------------------------------------------
-- Table user_profile_detail
-- -----------------------------------------------------
DROP TABLE IF EXISTS user_profile;
CREATE TABLE user_profile(
  id INT(12) NOT NULL AUTO_INCREMENT COMMENT '',
  name VARCHAR(50) NOT NULL,
  description VARCHAR(255) NOT NULL,
  for_business ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '',
  active ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT '',
  PRIMARY KEY (id)
) ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table user_profile_detail
-- -----------------------------------------------------
DROP TABLE IF EXISTS user_profile_detail ;
CREATE TABLE user_profile_detail(
  id INT NOT NULL AUTO_INCREMENT COMMENT '',
  id_profile INT NOT NULL,
  access VARCHAR(150) NOT NULL,
  PRIMARY KEY(id),
  CONSTRAINT fk_user_profile1
    FOREIGN KEY (id_profile)
    REFERENCES user_profile(id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION )
ENGINE = InnoDB;
-- -----------------------------------------------------
-- Table profession
-- -----------------------------------------------------
DROP TABLE IF EXISTS profession ;

CREATE TABLE IF NOT EXISTS profession (
  idprofession INT NOT NULL AUTO_INCREMENT COMMENT '',
  label VARCHAR(100) NULL COMMENT '',
  PRIMARY KEY (idprofession)  COMMENT '')
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table user_profession
-- -----------------------------------------------------
DROP TABLE IF EXISTS user_profession ;

CREATE TABLE IF NOT EXISTS user_profession (
  id INT NOT NULL AUTO_INCREMENT COMMENT '',
  idprofession INT NOT NULL COMMENT '',
  userid INT(10) NOT NULL COMMENT '',
  PRIMARY KEY (id, idprofession)  COMMENT '',
  CONSTRAINT fk_colaborador_profesion_profesiones1
    FOREIGN KEY (idprofession)
    REFERENCES profession (idprofession)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_colaborador_profesion_usuario1
    FOREIGN KEY (userid)
    REFERENCES main_user (userid)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX fk_colaborador_profesion_profesiones1_idx ON user_profession (idprofession ASC)  COMMENT '';

CREATE INDEX fk_colaborador_profesion_usuario1_idx ON user_profession (userid ASC)  COMMENT '';

-- -----------------------------------------------------
-- View notificacion
-- -----------------------------------------------------
DROP VIEW IF EXISTS notification ;
DROP TABLE IF EXISTS notification;
USE metroguatemala;
CREATE  OR REPLACE VIEW notificacion AS
SELECT userid_to, COUNT(*) as notification
FROM message m
WHERE readed = 'N'
GROUP BY userid_to;
-- -----------------------------------------------------
-- trigger users
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS user_BEFORE_INSERT;
CREATE TRIGGER user_BEFORE_INSERT
BEFORE INSERT ON main_user FOR EACH ROW
BEGIN
    SET NEW.register_date = now();
    SET NEW.time = CURTIME();
END;

-- -----------------------------------------------------
-- trigger message
-- -----------------------------------------------------
DROP TRIGGER IF EXISTS message_BEFORE_INSERT;
CREATE TRIGGER message_BEFORE_INSERT
BEFORE INSERT ON message FOR EACH ROW
BEGIN
    SET NEW.shipping_date = now();
END;

-- -----------------------------------------------------
-- View notificacion
-- -----------------------------------------------------
CREATE VIEW user_merge_profession AS
SELECT CP.id, CP.userid, P.idprofession, P.label
FROM profession P INNER JOIN user_profession CP ON CP.idprofession = P.idprofession;

ALTER TABLE main_user ADD class ENUM('webmaster','provider','merchant') NOT NULL DEFAULT 'provider' COMMENT '';
ALTER TABLE main_user CHANGE tipo type ENUM('admin','normal','other') NOT NULL DEFAULT 'admin' COMMENT '';
ALTER TABLE main_user ADD id_profile INT(12) NOT NULL;
ALTER TABLE main_user ADD CONSTRAINT  fk_main_user_user_profile_1 FOREIGN KEY (id_profile) REFERENCES user_profile (id);
ALTER TABLE main_user ADD degree VARCHAR(45) NULL COMMENT '';