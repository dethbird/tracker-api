CREATE TABLE IF NOT EXISTS `user_foursquare` (
  `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `foursquare_user_id` varchar(64) NOT NULL,
  `username` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `user_id` int(32) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instagram_user_id` (`foursquare_user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

ALTER TABLE `user_foursquare`
  ADD CONSTRAINT `user_foursquare_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

  