CREATE TABLE menu_categoria (
  id int IDENTITY(1,1) PRIMARY KEY,
  nombre VARCHAR(45) NOT NULL DEFAULT '',
  imagen VARCHAR(150) NOT NULL DEFAULT '');

CREATE TABLE menu (
  menu_id int IDENTITY(1,1) PRIMARY KEY,
  page VARCHAR(45) NOT NULL DEFAULT '',
  nombre VARCHAR(200) NOT NULL DEFAULT '',
  modulo VARCHAR(45) NOT NULL DEFAULT '',
  image VARCHAR(155) NOT NULL DEFAULT '',
  categoria_id INT NOT NULL,
  father INT NULL,
  CONSTRAINT fk_menu_menu_categoria1
    FOREIGN KEY (categoria_id)
    REFERENCES menu_categoria (id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT fk_menu_menu1
    FOREIGN KEY (father)
    REFERENCES menu (menu_id)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

CREATE TABLE [main_user] (
    [userid] int NOT NULL IDENTITY(1,1),
    [nickname] varchar(45) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT '',
    [password] varchar(100) COLLATE Modern_Spanish_CI_AS NOT NULL,
    [type] varchar(30) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT ('admin'),
    [fullname] varchar(150) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT '',
    [first_name] varchar(150) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT '',
    [last_name] varchar(150) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT '',
    [register_date] date NOT NULL,
    [time] time(7) NOT NULL,
    [active] varchar(1) COLLATE Modern_Spanish_CI_AS NOT NULL DEFAULT ('Y'),
    [email] varchar(100) COLLATE Modern_Spanish_CI_AS NULL,
    [birth_date] date NULL,
    [modify_date] datetime NULL,
    [avatar] varbinary(MAX) NULL,
    [genre] varchar(10) NULL,
    CONSTRAINT [PK__usuario] PRIMARY KEY ([userid]) ,
    CONSTRAINT [CK__usuario__active] CHECK (([active]='N' OR [active]='Y')),
    CONSTRAINT [CK__usuario__tipo] CHECK (([type]='other' OR [type]='normal' OR [type]='admin'))
)
GO

CREATE TABLE [addresses] (
    [idaddress] int NOT NULL IDENTITY(1,1),
    [table_from] varchar(45)NULL,
    [idtable] int NULL,
    [address] varchar(250) NULL,
    [condominium] varchar(250) NULL,
    [house_number] varchar(10) NULL,
    [settlement] varchar(255) NULL,
    [district] VARCHAR(10) NULL ,
    [town] varchar(255) NULL,
    [state] varchar(250) NULL,
    [zone] int NULL,
    PRIMARY KEY ([idaddress])
)
GO

CREATE TABLE [phones] (
    [idphone] int NOT NULL IDENTITY(1,1),
    [table_from] varchar(50) NULL,
    [idtable] int NULL,
    [phone_number] varchar(45) NULL,
    [tag] varchar(45) NULL,
    PRIMARY KEY ([idphone])
)
GO

CREATE TABLE [user_access] (
  [id] int NOT NULL IDENTITY(1,1),
  [user] int NOT NULL,
  [access] varchar(150) NOT NULL,
  PRIMARY KEY ([id])
)
GO

CREATE TABLE [user_profile] (
  [id] int NOT NULL IDENTITY(1,1),
  [name] varchar(50) NOT NULL,
  [description] varchar(255) NOT NULL,
  [for_business] varchar(1) NOT NULL DEFAULT ('N'),
  [active] varchar(1) NOT NULL DEFAULT ('Y'),
  PRIMARY KEY ([id])
)
GO

CREATE TABLE [user_profile_detail] (
  [id] int NOT NULL IDENTITY(1,1),
  [id_profile] int NOT NULL,
  [access] varchar(150) NOT NULL,
  PRIMARY KEY ([id])
)
GO

CREATE TABLE [profession](
  [idprofession] INT NOT NULL IDENTITY (1,1),
  [label] VARCHAR(100) NULL,
  PRIMARY KEY ([idprofession])
)
GO

CREATE TABLE [user_profession](
  [id] INT NOT NULL IDENTITY(1,1),
  [idprofession] INT NOT NULL,
  [userid] INT NOT NULL,
  PRIMARY KEY([id])
)
GO

ALTER TABLE [user_profession] ADD CONSTRAINT [fk_user_profession_prof]
  FOREIGN KEY ([idprofession]) REFERENCES [profession] ([idprofession])
GO

ALTER TABLE [user_profession] ADD CONSTRAINT [fk_user_profession_user]
  FOREIGN KEY ([userid]) REFERENCES [main_user] ([userid])
GO

ALTER TABLE [user_profile_detail] ADD CONSTRAINT [fk_user_profile_access_detail_user_profile_access_1] FOREIGN KEY ([id_profile]) REFERENCES [user_profile] ([id])
GO

CREATE VIEW user_merge_profession AS
SELECT CP.id, CP.userid, P.idprofession, P.label
FROM profession P INNER JOIN user_profession CP ON CP.idprofession = P.idprofession;

ALTER TABLE [main_user] ADD [class] varchar(9) NOT NULL DEFAULT ('provider');
ALTER TABLE [main_user] ADD [id_profile] int NULL;
ALTER TABLE [main_user] ADD CONSTRAINT [fk_main_user_user_profile_1] FOREIGN KEY ([id_profile]) REFERENCES [user_profile] ([id]);
ALTER TABLE [main_user] ALTER COLUMN [nickname] varchar(150);
ALTER TABLE [main_user] ADD degree VARCHAR(45) NULL;