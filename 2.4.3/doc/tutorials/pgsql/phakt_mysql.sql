CREATE TABLE users_usr (
  id_usr int(4) NOT NULL auto_increment,
  username_usr varchar(8) NOT NULL default '',
  password_usr varchar(8) NOT NULL default '',
  level_usr int(2) NOT NULL default '0',
  PRIMARY KEY  (id_usr)
) TYPE=MyISAM;

CREATE TABLE contacts_con (
  id_con int(4) NOT NULL auto_increment,
  idusr_con int(4) NOT NULL default '0',
  firstname_con varchar(100) NOT NULL default '',
  lastname_con varchar(100) NOT NULL default '',
  email_con varchar(100) NOT NULL default '',
  birthdate_con date NOT NULL default '0000-00-00',
  PRIMARY KEY  (id_con),
  KEY idusr_con (idusr_con)
) TYPE=MyISAM;