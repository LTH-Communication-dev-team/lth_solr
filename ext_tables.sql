#
# Table structure for table 'lth_solr'
#

CREATE TABLE fe_groups (
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE fe_users (
    lth_solr_cat text DEFAULT '' NOT NULL,
    lth_solr_sort text DEFAULT '' NOT NULL,
    lth_solr_intro text DEFAULT '' NOT NULL,
    lth_solr_image text DEFAULT '' NOT NULL,
    lth_solr_hide text DEFAULT '' NOT NULL,
    lth_solr_show text DEFAULT '' NOT NULL,
    lth_solr_heritage text DEFAULT '' NOT NULL,
    lth_solr_legacy_heritage text DEFAULT '' NOT NULL,
    lth_solr_index tinyint(1) NOT NULL DEFAULT '0',
    image_id tinyint(1) NOT NULL DEFAULT '0',
    hide_on_web tinyint(1) NOT NULL DEFAULT '0',
    lucache_id varchar(15) DEFAULT '' NOT NULL,
    lth_solr_uuid varchar(255) DEFAULT '' NOT NULL,
    lth_solr_autohomepage text DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE sys_file (
    lth_solr_index tinyint(1) NOT NULL DEFAULT '0'
);


CREATE TABLE tx_lthsolr_categories (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  parentId int(10) unsigned DEFAULT NULL,
  type varchar(31) NOT NULL,
  name_sv varchar(63) CHARACTER SET utf8 COLLATE utf8_swedish_ci DEFAULT NULL,
  name_en varchar(63) DEFAULT NULL,
  comment varchar(127) DEFAULT NULL,
  disabled tinyint(1) NOT NULL DEFAULT '0',
  created datetime DEFAULT NULL,
  createdBy varchar(16) NOT NULL,
  modified timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  modifiedBy varchar(16) NOT NULL,
  PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_titles (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    title_sv varchar(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
    title_en varchar(64) NOT NULL,
    category int(10) unsigned NOT NULL,
    comment varchar(127) DEFAULT NULL,
    disabled tinyint(1) NOT NULL DEFAULT '0',
    created datetime DEFAULT NULL,
    createdBy varchar(16) DEFAULT NULL,
    modified timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    modifiedBy varchar(16) DEFAULT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_lucrisdata (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    typo3_id varchar(255) DEFAULT NULL,
    lucris_id varchar(255) DEFAULT NULL,
    lucris_photo varchar(255) DEFAULT NULL,
    lucris_profile_information text DEFAULT '' NOT NULL,
    lucris_type varchar(25) DEFAULT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_uniquetitle (
    uid int(10) unsigned NOT NULL AUTO_INCREMENT,
    title varchar(255) DEFAULT NULL,
    lucris_id varchar(255) DEFAULT NULL,
    PRIMARY KEY (uid)
);