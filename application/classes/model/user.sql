CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(32) NOT NULL,
  `name` varchar(127) NOT NULL,
  `gender` varchar(32) NOT NULL,
  `first_name` varchar(127) NOT NULL,
  `last_name` varchar(127) NOT NULL,
  `email` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL default '0000-00-00 00:00:00' on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;