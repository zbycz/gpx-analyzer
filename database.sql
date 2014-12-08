-- Adminer 4.1.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `gpx`;
CREATE TABLE `gpx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `time` datetime NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'seconds',
  `length` float NOT NULL COMMENT 'meters',
  `ascent` float NOT NULL COMMENT 'meters',
  `descent` float NOT NULL COMMENT 'meters',
  `classification` text COLLATE utf8_czech_ci NOT NULL,
  `gpx` longtext COLLATE utf8_czech_ci NOT NULL COMMENT 'JSON',
  `points` text COLLATE utf8_czech_ci NOT NULL COMMENT 'JSON',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2014-12-08 01:16:16
