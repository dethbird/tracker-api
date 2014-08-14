CREATE TABLE `user_github` (
  `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `github_user_id` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `user_id` int(32) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instagram_user_id` (`github_user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_github_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


