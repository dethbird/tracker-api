DROP TABLE IF EXISTS `user_flickr`;
CREATE TABLE IF NOT EXISTS `user_flickr` (
  `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `nsid` varchar(64) NOT NULL,
  `username` varchar(255) NOT NULL,
  `oauth_token` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `iconserver` varchar(128) NOT NULL,
  `iconfarm` varchar(128) NOT NULL,
  `photosurl` varchar(255) NOT NULL,
  `user_id` int(32) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instagram_user_id` (`nsid`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

ALTER TABLE `user_flickr`
  ADD CONSTRAINT `user_flickr_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);