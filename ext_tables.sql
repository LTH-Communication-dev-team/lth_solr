#
# Table structure for table 'lth_solr'
#
#

CREATE TABLE fe_groups (
    title varchar(255) DEFAULT '' NOT NULL
);

#
# Table structure for table 'tt_news'
#
CREATE TABLE tt_news (
    sorting int(11) DEFAULT '0' NOT NULL,
);

CREATE TABLE fe_users (
    lth_solr_cat text NOT NULL,
    lth_solr_sort text NOT NULL,
    lth_solr_intro text NOT NULL,
    lth_solr_image text NOT NULL,
    lth_solr_hide text NOT NULL,
    lth_solr_show text NOT NULL,
    lth_solr_heritage text NOT NULL,
    lth_solr_legacy_heritage text NOT NULL,
    lth_solr_index tinyint(1) NOT NULL DEFAULT '0',
    image_id tinyint(1) NOT NULL DEFAULT '0',
    hide_on_web tinyint(1) NOT NULL DEFAULT '0',
    lucache_id varchar(15) DEFAULT '' NOT NULL,
    lth_solr_uuid varchar(255) DEFAULT '' NOT NULL,
    lth_solr_autohomepage text NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL
);

CREATE TABLE sys_file (
    lth_solr_index tinyint(1) NOT NULL DEFAULT '0'
);


CREATE TABLE tx_lthsolr_categories (
  id int(11) NOT NULL auto_increment,
  parentId int(11) NOT NULL,
  type varchar(31) NOT NULL,
  name_sv varchar(63) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
  name_en varchar(63) NOT NULL,
  comment varchar(127) NOT NULL,
  disabled tinyint(1) NOT NULL DEFAULT '0',
  created datetime NOT NULL,
  createdBy varchar(16) NOT NULL,
  modified timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  modifiedBy varchar(16) NOT NULL,
  PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_titles (
    id int(11) NOT NULL auto_increment,
    title_sv varchar(64) CHARACTER SET utf8 COLLATE utf8_swedish_ci NOT NULL,
    title_en varchar(64) NOT NULL,
    category int(10) NOT NULL,
    comment varchar(127) NOT NULL,
    disabled tinyint(1) NOT NULL DEFAULT '0',
    created datetime NOT NULL,
    createdBy varchar(16) NOT NULL,
    modified timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    modifiedBy varchar(16) NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_lucrisdata (
    id int(11) NOT NULL auto_increment,
    typo3_id varchar(255) NOT NULL,
    lucris_id varchar(255) NOT NULL,
    lucris_photo varchar(255) NOT NULL,
    lucris_photo_width int(10) NOT NULL,
    lucris_photo_height int(10) NOT NULL,
    lucris_profile_information text NOT NULL,
    lucris_type varchar(25) NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE tx_lthsolr_uniquetitle (
    uid int(11) NOT NULL auto_increment,
    title varchar(255) NOT NULL,
    lucris_id varchar(255) NOT NULL,
    PRIMARY KEY (uid)
);


CREATE TABLE tx_lthsolr_cleanup (
    uid int(11) NOT NULL auto_increment,
    lucris_id varchar(255) NOT NULL,
    index_type varchar(25) NOT NULL,
    modified varchar(50) NOT NULL,
    created timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (uid)
);