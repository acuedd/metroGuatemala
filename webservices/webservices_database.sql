/**
 * Author:  EACU
 * Created: 7/07/2016
 */
CREATE TABLE webservices_operations (
  op_uuid varchar(36) NOT NULL COMMENT 'UUID de la operacion',
  modulo varchar(20) NOT NULL DEFAULT '' COMMENT 'Modulo al que pertenece',
  descripcion varchar(200) NOT NULL DEFAULT '' COMMENT 'Descripcion de operacion',
  include_path varchar(250) NOT NULL DEFAULT '' COMMENT 'Path a la libreria desde root',
  className varchar(250) NOT NULL DEFAULT '' COMMENT 'Nombre de la clase',
  publica enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Para definir si la operacion es publica y no le importa el token de seguridad',
  acceso varchar(100) NOT NULL DEFAULT 'admin' COMMENT 'Acceso que debe tener el usuario para utilizar la operacion',
  activo enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Para desactivar el servicio',
  PRIMARY KEY (op_uuid),
  KEY indice001 (modulo),
  KEY indice002 (activo, modulo),
  KEY indice003 (activo, op_uuid)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Para registrar las operaciones validas en los werbservices';
ALTER TABLE webservices_operations ADD INDEX indice004 (activo, acceso);

CREATE TABLE webservices_devices (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  userid INT(10) UNSIGNED NOT NULL,
  device_udid VARCHAR(60) NOT NULL COMMENT "UDID del device",
  activo ENUM('Y','N') NOT NULL DEFAULT 'Y',
  fecha_alta DATETIME COMMENT "Fecha en que se da de alta al dispositivo",
  fecha_baja DATETIME COMMENT "Fecha en que se dio de baja al dispositivo",
  confirmado ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT "Para indicar que el dispositivo ya se confirmo en el sitio principal",
  fecha_confirmacion DATETIME COMMENT "Fecha en que se confirmo el dispositivo",
  userid_confirma INT(10) UNSIGNED NOT NULL COMMENT "Usuario que lo confirmo",
  tipo VARCHAR(100) COMMENT "Tipo de dispositivo",
  marca VARCHAR(100) COMMENT "Marca de dispositivo",
  modelo VARCHAR(100) COMMENT "Modelo de dispositivo",
  PRIMARY KEY (id)
) ENGINE=InnoDB COMMENT="Para registrar los UDID de los dispositivos y asociarlos a los usuarios";
ALTER TABLE webservices_devices ADD INDEX indice001 (activo, userid, device_udid);
ALTER TABLE webservices_devices ADD INDEX indice002 (activo, device_udid, userid);
ALTER TABLE webservices_devices ADD INDEX indice003 (confirmado, fecha_alta);
ALTER TABLE webservices_devices ADD INDEX indice004 (tipo, marca);
ALTER TABLE webservices_devices ADD INDEX indice005 (marca, modelo);
ALTER TABLE webservices_devices ADD INDEX indice006 (device_udid, userid);
ALTER TABLE webservices_devices ADD INDEX indice007 (userid, device_udid);

CREATE TABLE webservices_last_deactivate (
	lastRun DATETIME COMMENT "Ultima fecha en que corrio la funcion para desactivar dispositivos no activos"
) ENGINE=InnoDB COMMENT="Tabla de un solo registro y un solo campo para llevar un control de la ultima vez en que ejecuto la funcion para desactivar dispositivos no confirmados";


ALTER TABLE webservices_devices
ADD nombre_p VARCHAR(50) NOT NULL COMMENT 'Nombre personalizado por el usuario' AFTER userid_confirma;

ALTER TABLE webservices_devices
	ADD last_use DATETIME NOT NULL COMMENT 'Ultima vez que este dispositivo se conectó al sitio' AFTER nombre_p ,
	ADD uses INT NOT NULL COMMENT 'Conteo de consultas por medio de este dispositivo' AFTER last_use;

CREATE TABLE catalogos_last_update(
    table_name VARCHAR(100) NOT NULL DEFAULT '',
    fecha DATE NOT NULL DEFAULT '0000-00-00',
    hora TIME NOT NULL DEFAULT '00:00:00',
    PRIMARY KEY (table_name)
);
ALTER TABLE catalogos_last_update ENGINE = MyISAM;

DROP TABLE IF EXISTS webservices_mobile_responses;
CREATE TABLE webservices_mobile_responses (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  userid INT(10) UNSIGNED NOT NULL,
  device_id INT(10) UNSIGNED NOT NULL,
  device_aim VARCHAR(10) NOT NULL DEFAULT '',
  process_log_id INT(10) UNSIGNED NOT NULL,
  status ENUM('en_proceso', 'terminada'),
  fecha DATE,
  hora TIME,
  formato VARCHAR(10),
  respuesta MEDIUMTEXT,
  PRIMARY KEY (id),
  INDEX indice01 (device_id, device_aim),
  INDEX indice02 (fecha)
) ENGINE=InnoDB COMMENT="Para registrar las respuestas a las llamadas desde los telefonos en caso de mala conexion";

ALTER TABLE webservices_devices ADD eliminado ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'N' AFTER activo ;

ALTER TABLE webservices_devices ADD telefono VARCHAR( 50 ) NULL DEFAULT NULL;


CREATE  TABLE IF NOT EXISTS webservices_devices_auth (
  id_deviceauth INT(11) NOT NULL AUTO_INCREMENT ,
  id_credencial INT(11) NULL COMMENT 'id de la credencial para tarjeta de credito, table tarjeta_credito_visanet_credencial' ,
  no_telefono VARCHAR(50) NOT NULL ,
  userid INT(11) NOT NULL ,
  fecha_alta DATETIME NULL ,
  fecha_baja DATETIME NULL ,
  activo ENUM('Y','N') NOT NULL DEFAULT 'Y' ,
  alias VARCHAR(100) NOT NULL ,
  modelo VARCHAR(100) NOT NULL ,
  marca VARCHAR(100) NOT NULL ,
  tipo VARCHAR(100) NOT NULL ,
  PRIMARY KEY (id_deviceauth) ,
  INDEX credencial (id_credencial ASC))
ENGINE = InnoDB COMMENT = 'registra quienes tienen acceso a relacionar devices y relacionar afiliaciones de tarjeta de credito';

ALTER TABLE webservices_devices ADD id_deviceauth INT(11) NOT NULL AFTER id;
ALTER TABLE webservices_devices ADD INDEX deviceauth ( id_deviceauth );

ALTER TABLE webservices_devices ADD osversion VARCHAR( 20 ) NOT NULL , ADD appversion VARCHAR( 20 ) NOT NULL , ADD code_device VARCHAR( 50 ) NOT NULL , ADD apiversion VARCHAR( 20 ) NOT NULL;

ALTER TABLE webservices_devices ADD OS VARCHAR(30) NULL;

ALTER TABLE webservices_devices ADD appname VARCHAR(100) NULL;

ALTER TABLE webservices_devices ADD modified_config ENUM('Y','N') NOT NULL DEFAULT 'N';

ALTER TABLE webservices_operations 
ADD path_mainClass VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'path de la clase a la que hara referencia',
ADD class_mainClass VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'nombre de la clase que hara referencia',
ADD allowed_format SET('w','wm','am') NOT NULL DEFAULT '' COMMENT 'formatos permitidos para el webservice',
ADD format_response SET('json','html','xmlno') NOT NULL DEFAULT '' COMMENT 'formatos permitidos de repuestas para el webservice',
ADD method_response VARCHAR(250) NOT NULL DEFAULT '' COMMENT 'metodo que llamara para dar la respuesta';

ALTER TABLE webservices_operations ADD isNewMod ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER activo;

CREATE TABLE IF NOT EXISTS webservices_operations_extra_data(
id INT(11) AUTO_INCREMENT,
op VARCHAR(36) NOT NULL,
required ENUM('Y','N') NOT NULL DEFAULT 'Y',
parameter_description VARCHAR(250) NOT NULL DEFAULT '',
method_validation VARCHAR(250) NOT NULL DEFAULT '',
key_parameter VARCHAR(30) NOT NULL DEFAULT '',
error_response VARCHAR(250) NOT NULL DEFAULT '',
transform_key VARCHAR(250) NOT NULL DEFAULT '',
PRIMARY KEY (id)
)ENGINE = INNODB;

#20150616
ALTER TABLE webservices_operations ADD check_config_device ENUM('Y','N') NOT NULL DEFAULT 'N';

CREATE TABLE IF NOT EXISTS webservices_operations_extra_function(
id INT(11) NOT NULL AUTO_INCREMENT,
op VARCHAR(36) NOT NULL DEFAULT '',
str_function VARCHAR(60) NOT NULL DEFAULT '',
webservices_baseClass ENUM('Y','N') DEFAULT 'N',
PRIMARY KEY(id)
)
ENGINE = INNODB;



INSERT INTO webservices_operations (op_uuid, modulo, descripcion, include_path, className, publica, acceso, activo)
VALUES ('bdef04ac-cadb-11e1-8235-df5303155f9f', 'core', 'Registra un UDID en la tabla webservices_devices para un usuario', 'webservices/webservices_core/record_udid.php', 'webservice_record_udid', 'Y', 'freeAccess', 'Y');
