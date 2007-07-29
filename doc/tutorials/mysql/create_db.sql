# Server version: 3.23.36
# PHP Version: 4.0.6
# Database : `doctest`
# --------------------------------------------------------

#
# Table structure for table `marital_mar`
#

DROP TABLE IF EXISTS marital_mar;
CREATE TABLE marital_mar (
  id_mar int NOT NULL auto_increment,
  status_mar varchar(100) NOT NULL default '',
  radio_mar tinyint NOT NULL default '0',
  checkbox_mar tinyint NOT NULL default '0',
  PRIMARY KEY  (id_mar),
  UNIQUE KEY id_mar (id_mar)
) TYPE=MyISAM;
# --------------------------------------------------------
INSERT INTO marital_mar VALUES (1, 'Single', 0, 0);
INSERT INTO marital_mar VALUES (2, 'Married', 0, 0);

#
# Table structure for table `users_usr`
#

DROP TABLE IF EXISTS users_usr;
CREATE TABLE users_usr (
  id_usr int NOT NULL auto_increment,
  username_usr varchar(16) NOT NULL,
  password_usr varchar(16) NOT NULL,
  PRIMARY KEY  (id_usr),
  UNIQUE KEY id_usr (id_usr)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `emplyees_emp`
#

DROP TABLE IF EXISTS employees_emp;
CREATE TABLE employees_emp (
  id_emp int NOT NULL auto_increment,
  firstname_emp varchar(100) NOT NULL,
  lastname_emp varchar(100) NOT NULL,
  address_emp text NOT NULL,
  code_emp int NOT NULL default '0',
  email_emp varchar(100) NOT NULL,
  phone_emp varchar(40) NOT NULL,
  fax_emp varchar(40) NOT NULL,
  childreno_emp int NOT NULL default '0',
  marital_emp int NOT NULL,
  PRIMARY KEY  (id_emp),
  UNIQUE KEY id_emp (id_emp)
) TYPE=MyISAM;
# End