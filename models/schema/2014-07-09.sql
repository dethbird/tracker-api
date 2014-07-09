--
-- Table structure for table `user_instagram`
--

CREATE TABLE IF NOT EXISTS `user_instagram` (
  `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `instagram_user_id` varchar(64) NOT NULL,
  `username` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `user_id` int(32) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instagram_user_id` (`instagram_user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_instagram`
--
ALTER TABLE `user_instagram`
  ADD CONSTRAINT `user_instagram_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

