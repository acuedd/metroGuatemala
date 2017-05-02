CREATE TABLE webservices_operations (
  op_uuid varchar(36) NOT NULL,
  modulo varchar(20) NOT NULL DEFAULT '',
  descripcion varchar(200) NOT NULL DEFAULT '',
  include_path varchar(250) NOT NULL DEFAULT '',
  className varchar(250) NOT NULL DEFAULT '',
  publica VARCHAR(1) NOT NULL DEFAULT 'N',
  acceso varchar(100) NOT NULL DEFAULT 'admin',
  activo varchar(1) NOT NULL DEFAULT 'Y',
  isNewMod varchar(1) NOT NULL DEFAULT 'N',
  path_mainClass VARCHAR(250) NOT NULL DEFAULT '',
  class_mainClass VARCHAR(250) NOT NULL DEFAULT '',
  allowed_format varchar(50) NOT NULL DEFAULT '',
  format_response varchar(50) NOT NULL DEFAULT '',
  method_response VARCHAR(250) NOT NULL DEFAULT '',
  check_config_device varchar(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (op_uuid),
  INDEX indice001 (modulo),
  INDEX indice002 (activo, modulo),
  INDEX indice003 (activo, op_uuid),
  CONSTRAINT [webservices_operations_publica] CHECK (([publica]='N' OR [publica]='Y')),
  CONSTRAINT [webservices_operations_activo] CHECK (([activo]='N' OR [activo]='Y')),
  CONSTRAINT [webservices_operations_isNewMod] CHECK (([isNewMod]='N' OR [isNewMod]='Y')),
  CONSTRAINT [webservices_operations_check_config] CHECK (([check_config_device]='N' OR [check_config_device]='Y'))
);

CREATE TABLE webservices_operations_extra_data(
  [id] INT IDENTITY(1,1),
  [op] VARCHAR(36) NOT NULL,
  [required] VARCHAR(1) NOT NULL DEFAULT 'Y',
  [parameter_description] VARCHAR(250) NOT NULL DEFAULT '',
  [method_validation] VARCHAR(250) NOT NULL DEFAULT '',
  [key_parameter] VARCHAR(30) NOT NULL DEFAULT '',
  [error_response] VARCHAR(250) NOT NULL DEFAULT '',
  [transform_key] VARCHAR(250) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  CONSTRAINT service_extradatarequired CHECK(([required]='Y' OR [required]='N'))
);

CREATE TABLE webservices_operations_extra_function(
id INT IDENTITY(1,1),
op VARCHAR(36) NOT NULL DEFAULT '',
str_function VARCHAR(60) NOT NULL DEFAULT '',
webservices_baseClass VARCHAR(1) DEFAULT 'N',
PRIMARY KEY(id),
CONSTRAINT service_extrafunction_base CHECK(([webservices_baseClass]='Y' OR [webservices_baseClass]='N'))
);


CREATE TABLE webservices_devices (
  id INT IDENTITY(1,1),
  id_deviceauth INT NOT NULL,
  userid INT NOT NULL,
  device_udid VARCHAR(60) NOT NULL,
  activo VARCHAR(1) NOT NULL DEFAULT 'Y',
  eliminado VARCHAR(1) NOT NULL DEFAULT 'N',
  fecha_alta DATETIME,
  fecha_baja DATETIME,
  confirmado VARCHAR(1) NOT NULL DEFAULT 'N',
  fecha_confirmacion DATETIME,
  userid_confirma INT NULL,
  nombre_p VARCHAR(50) NULL,
  last_use DATETIME NULL,
  uses INT NULL,
  tipo VARCHAR(100),
  marca VARCHAR(100),
  modelo VARCHAR(100),
  telefono VARCHAR(50) NULL,
  osversion VARCHAR( 20 ) NULL,
  appversion VARCHAR( 20 ) NULL,
  code_device VARCHAR( 50 ) NULL,
  apiversion VARCHAR( 20 ) NULL,
  OS VARCHAR(30) NULL,
  appname VARCHAR(100) NULL,
  modified_config VARCHAR(1) NULL DEFAULT 'N',
  PRIMARY KEY (id),
  INDEX indice001 (activo, userid, device_udid),
  INDEX indice002 (activo, device_udid, userid),
  INDEX indice003 (confirmado, fecha_alta),
  INDEX indice004 (tipo, marca),
  INDEX indice005 (marca, modelo),
  INDEX indice006 (device_udid, userid),
  INDEX indice007 (userid, device_udid),
  INDEX deviceauth (id_deviceauth),
  CONSTRAINT webservices_devices_activo CHECK(([activo]='Y' OR [activo]='N')),
  CONSTRAINT webservices_devices_elimando CHECK(([eliminado]='Y' OR [eliminado]='N')),
  CONSTRAINT webservices_devices_confirmado CHECK(([confirmado]='Y' OR [confirmado]='N')),
  CONSTRAINT webservices_devices_modified_config CHECK(([modified_config]='Y' OR [modified_config]='N'))
);

CREATE TABLE webservices_last_deactivate (
  lastRun DATETIME
);

CREATE  TABLE webservices_devices_auth (
  id_deviceauth INT IDENTITY(1,1) ,
  id_credencial INT NULL,
  no_telefono VARCHAR(50) NOT NULL ,
  userid INT NOT NULL ,
  fecha_alta DATETIME NULL ,
  fecha_baja DATETIME NULL ,
  activo varchar(1) NOT NULL DEFAULT 'Y' ,
  alias VARCHAR(100) NOT NULL ,
  modelo VARCHAR(100) NOT NULL ,
  marca VARCHAR(100) NOT NULL ,
  tipo VARCHAR(100) NOT NULL ,
  PRIMARY KEY (id_deviceauth) ,
  INDEX credencial (id_credencial ASC),
  CONSTRAINT webservices_devices_auth_activo CHECK(([activo] = 'N' OR [activo] = 'Y'))
  );