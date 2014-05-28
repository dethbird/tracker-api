# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.14)
# Database: 15min
# Generation Time: 2013-12-29 14:41:06 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table programs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `programs`;

CREATE TABLE `programs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `provider` enum('youtube') NOT NULL DEFAULT 'youtube',
  `title` varchar(255) DEFAULT NULL,
  `external_id` varchar(255) NOT NULL DEFAULT '',
  `thumbnail_url` text,
  `clickthrough_url` text NOT NULL,
  `length` int(11) DEFAULT NULL COMMENT 'in seconds',
  `timeslot` int(11) DEFAULT NULL COMMENT 'unix timestamp',
  `timeslot_length` int(11) NOT NULL DEFAULT '900' COMMENT 'in seconds, default 15 minutes',
  `date_added` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='pages of an issue';

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;

INSERT INTO `programs` (`id`, `user_id`, `provider`, `title`, `external_id`, `thumbnail_url`, `clickthrough_url`, `length`, `timeslot`, `timeslot_length`, `date_added`, `date_updated`)
VALUES
	(1,1,'youtube','The Marriage Ref - Episode 3 - Madonna, Ricky Gervais, Larry David [Part 1] March 11, 2010','e892KoJ7rAE','http://i1.ytimg.com/vi/e892KoJ7rAE/hqdefault.jpg','http://google.com',340,1378275300,900,'2013-09-03 18:10:00','2013-09-03 23:15:00'),
	(5,6,'youtube','Michael Richards (Kramer) Doesn&#x27;t Like When his Co-Stars Mess Up','Fge0sIjrNps','http://i1.ytimg.com/vi/Fge0sIjrNps/hqdefault.jpg','http://google.com',600,1378273500,900,'2013-09-03 18:07:00','2013-09-04 15:10:00'),
	(21,1,'youtube','Jim Gaffigan - Jesus - Beyond the Pale','2k_9mXpNdgU','http://i1.ytimg.com/vi/2k_9mXpNdgU/hqdefault.jpg','http://hotsaucevehicle.com',483,1378312800,900,'2013-09-03 18:52:00','2013-09-04 12:01:00'),
	(22,1,'youtube','Rolling Stones 1991- Live at the Max - Show Completo','dZesbTTpsVk','http://i1.ytimg.com/vi/dZesbTTpsVk/hqdefault.jpg','http://hotsaucevehicle.com',5084,1378314000,900,'2013-09-03 22:40:00','2013-09-04 09:41:00');

/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT 'encoded password',
  `name` varchar(255) NOT NULL,
  `bio_image_url` varchar(255) DEFAULT NULL,
  `bio` text,
  `url` varchar(255) NOT NULL COMMENT 'artist''s external portfolio url',
  `api_key` varchar(255) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `email`, `password`, `name`, `bio_image_url`, `bio`, `url`, `api_key`, `date_added`, `date_updated`)
VALUES
	(1,'rishi.satsangi@gmail.com','be83d927bde40530e89e8790b7828776','Rishi Satsangi','http://imgsrc-dev.artistcontrolbox.com/images/artists/1.jpg','<p>As I move and I slither I&#39;m a ball of mass of this that and whatever chooses to attack</p>\r\n\r\n<p>These things I see they become me and in transformation I can&#39;t look back</p>\r\n\r\n<p>I&#39;m a moving node through which detritus flows and saturated I barf art</p>\r\n\r\n<p>The city spirit you can always hear it resounding, reconnecting as it falls apart</p>\r\n\r\n<p>Feed me hunger enrage my senses I&#39;m a happy buddha machine that dispenses</p>\r\n','http://hotsaucevehicle.com','c4ca4238a0b923820dcc509a6f75849b','2013-01-18 13:11:00','2013-04-01 05:52:00'),
	(6,'dheerajs@gmail.com','818cf917ecf623cfddce33a7e32f21a9','Dheeraj Sultanian',NULL,NULL,'',NULL,NULL,NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
