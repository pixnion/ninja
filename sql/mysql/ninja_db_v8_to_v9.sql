CREATE TABLE IF NOT EXISTS `ninja_saved_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(200) DEFAULT NULL,
  `filter_name` varchar(255) NOT NULL,
  `filter_table` varchar(255) NOT NULL,
  `filter` text NOT NULL,
  `filter_description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_2` (`username`,`filter_name`),
  KEY `username` (`username`,`filter_table`),
  KEY `filter_table` (`filter_table`,`username`),
  KEY `filter_name` (`filter_name`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;

DROP TABLE IF EXISTS `ninja_saved_queries`;
