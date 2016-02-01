#
# Table structure for table 'lth_solr'
#
CREATE TABLE fe_groups (
	title varchar 255 DEFAULT '' NOT NULL
);

CREATE TABLE fe_users (
    lth_solr_cat text DEFAULT '' NOT NULL,
    lth_solr_sort text DEFAULT '' NOT NULL,
    lth_solr_intro_id int(11) DEFAULT '0' NOT NULL,
    lth_solr_hide text DEFAULT '' NOT NULL
);


CREATE TABLE tx_lthsolr_intro (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  deleted int(11) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,
  parentid tinytext NOT NULL,
  parenttable tinytext NOT NULL,  
  lth_solr_intro_host int(11) DEFAULT '0' NOT NULL,
  lth_solr_intro_text text DEFAULT '' NOT NULL,
  PRIMARY KEY (uid)
);