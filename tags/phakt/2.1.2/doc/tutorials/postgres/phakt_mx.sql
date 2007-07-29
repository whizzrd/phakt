
/* -------------------------------------------------------- 
  phpPgAdmin 2.4-1 DB Dump
  http://sourceforge.net/projects/phppgadmin/
  Host: 127.0.0.1:5432
  Database  : "phakt_mx"
  2002-07-04 10:07:52
-------------------------------------------------------- */ 

/* -------------------------------------------------------- 
  Sequences 
-------------------------------------------------------- */ 
CREATE SEQUENCE "contacts_con_id_con_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1; 
CREATE SEQUENCE "users_usr_id_usr_seq" start 1 increment 1 maxvalue 9223372036854775807 minvalue 1 cache 1; 

/* -------------------------------------------------------- 
  Table structure for table "users_usr" 
-------------------------------------------------------- */
CREATE TABLE "users_usr" (
   "id_usr" int4 DEFAULT nextval('"users_usr_id_usr_seq"'::text) NOT NULL,
   "username_usr" varchar(8) NOT NULL,
   "password_usr" varchar(8) NOT NULL,
	 "level_usr" int2 NOT NULL default 0,
   CONSTRAINT "users_usr_pkey" PRIMARY KEY ("id_usr")
);

/* -------------------------------------------------------- 
  Table structure for table "contacts_con" 
-------------------------------------------------------- */
CREATE TABLE "contacts_con" (
   "id_con" int4 DEFAULT nextval('"contacts_con_id_con_seq"'::text) NOT NULL,
   "idusr_con" int4 NOT NULL references users_usr(id_usr) on delete cascade,
   "firstname_con" varchar(100) NOT NULL,
   "lastname_con" varchar(100) NOT NULL,
   "email_con" varchar(100) NOT NULL,
   "birthdate_con" date,
   CONSTRAINT "contacts_con_pkey" PRIMARY KEY ("id_con")
);
CREATE  INDEX "contacts_con_idusr_con_key" ON "contacts_con" ("idusr_con");


/* -------------------------------------------------------- 
  Dumping data for table "contacts_con" 
-------------------------------------------------------- */ 




/* -------------------------------------------------------- 
  Dumping data for table "users_usr" 
-------------------------------------------------------- */ 

/* No Views found */

/* No Functions found */

/* No Triggers found */
