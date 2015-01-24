

DROP TABLE IF EXISTS banned_ips;
CREATE TABLE banned_ips (
  ip varchar(10) NOT NULL default '',
  UNIQUE KEY ip (ip)
) TYPE=MyISAM;


#
# Structure de la table `users`
#

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id int(11) NOT NULL auto_increment,
  pseudo varchar(30) NOT NULL default '',
  password varchar(30) NOT NULL default '',
  sex char(2) NOT NULL default 'm',
  role varchar(10) NOT NULL default 'n',
  age int(11) NOT NULL default '18',
  address varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  country varchar(100) NOT NULL default 'France',
  banned tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY pseudo (pseudo)
) TYPE=MyISAM COMMENT='n=normal, a=admin';

#
# Some TEST DATA !
#

INSERT INTO users VALUES (1, 'yarek', 'yarekc', 'm', 'n', 18, '', '', '', 'France', 0);
INSERT INTO users VALUES (2, 'test', 'test', 'm', 'n', 18, '', '', '', 'France', 0);
INSERT INTO users VALUES (3, 'admin', 'admin', 'm', 'a', 18, '', 'yarekc@yahoo.fr', 'description', 'France', 0);
INSERT INTO users VALUES (4, 'esther', 'esther', 'f', 'n', 18, '', '', '', 'France', 0);
INSERT INTO users VALUES (5, 'zaza', 'zaza', 'm', 'n', 18, '', '', '', 'France', 0);
INSERT INTO users VALUES (6, 'a', 'a', 'm', 'n', 18, '', '', '', 'France', 0);

    