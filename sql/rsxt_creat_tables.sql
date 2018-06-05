
use rsxt_db;

DROP TABLE IF EXISTS `think_files`;
create table think_files(
	file_id int not null auto_increment,
	user_id int not null,
	file_name VARCHAR (50) not null,
	file_full_name VARCHAR (50) not null,

	updata_at date not null,

	primary key(file_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `think_position_posts`;
create table think_position_posts(
	application_id int not null auto_increment,
	user_id int not null,
	recruit_id int not null,
	position_id int not null,

	application_status varchar(10) not null,
	application_reason VARCHAR (100),

	primary key(application_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `think_recruits`;
create table think_recruits(
	recruit_id int not null auto_increment,
	admin_id int not null,
	title VARCHAR(30) not NULL,
	begin_time datetime NOT null,
	end_time datetime not null,
	recruit_status varchar(10) not null,

	primary key(recruit_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `think_positions`;
create table think_positions(
	position_id int not null auto_increment,
	recruit_id int not null,
	department_id int not null,
	position_name VARCHAR(30) not NULL,
	position_number int not null,
	major_demand VARCHAR(100) ,
	education_demand VARCHAR (30),

	note VARCHAR (100),
	primary key(position_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;

create table think_dapartments(
	dapartment_id int not null auto_increment,
	dapartment_name VARCHAR(30) not null,
	primary key(dapartment_id)
)CHARACTER SET utf8 COLLATE utf8_general_ci;

create table think_profiles(
	profile_id int not null auto_increment,
	user_id int not null,
	username VARCHAR(10) not NULL,
	sex CHAR (1) NOT null,
	birthdate DATE not null,
	identity_card char(18) not null,
	political_status VARCHAR(20) not NULL,
	birthplace VARCHAR(50) not null,
	marry char(2) not NULL,
	education varchar(8) not null,
	degree char(4) not null,
	occupation VARCHAR (20),
	positional_title VARCHAR (20),
	university VARCHAR (30) not null,
	major VARCHAR (30) not null,
	address VARCHAR (50) not null,
	phone_number CHAR (11) not null,
	office CHAR (1) not null,
	working_experiences text not null,
	special_skill text,
	awarks_honers text,

	primary key(profile_id)

)CHARACTER SET utf8 COLLATE utf8_general_ci;


create table think_articles(
	article_id int not null auto_increment,
	title varchar(50) not null,
	tab varchar(10) not null,
	contect text,
	creat_at datetime,
	updata_at datetime,
	primary key(article_id)

)CHARACTER SET utf8 COLLATE utf8_general_ci;

create table think_users(
	user_id int not null auto_increment,
	password varchar(80) not null,
	email varchar(50) not null,
	username varchar(30) not null,
	
	primary key(user_id)

)CHARACTER SET utf8 COLLATE utf8_general_ci;

create table think_admins(
	admin_id int not null auto_increment,
	password varchar(80) not null,
	email varchar(50) not null,
  admin_name varchar(30) not null,
  status varchar(5) not null,
  authority VARCHAR (10) not null;
	primary key(admin_id)

)CHARACTER SET utf8 COLLATE utf8_general_ci;
create table think_score(
	score_id int not null auto_increment,
	recruit_id int not null,
	department_id int not null,
  user_id int not null,
  first_score int,
  second_score int,
  total_iscore int,
  status varchar(5),
	primary key(score_id)

)CHARACTER SET utf8 COLLATE utf8_general_ci;