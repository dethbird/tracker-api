
CREATE TABLE IF NOT EXISTS `user_twitter` (
  `id` int(32) unsigned NOT NULL AUTO_INCREMENT,
  `twitter_user_id` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `access_token_secret` varchar(255) NOT NULL,
  `user_id` int(32) unsigned NOT NULL,
  `date_added` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instagram_user_id` (`twitter_user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO  `tracker`.`activity_type` (
`id` ,
`name` ,
`polarity` ,
`user_id`
)
VALUES (
35 ,  'Twitter',  '1',  '4'
);
