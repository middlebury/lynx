-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 23, 2009 at 03:44 PM
-- Server version: 5.0.77
-- PHP Version: 5.2.9

--
-- Database: `afranco_lynx`
--

-- --------------------------------------------------------

--
-- Table structure for table `mark`
--

CREATE TABLE IF NOT EXISTS `mark` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `fk_url` int(10) unsigned NOT NULL,
  `fk_user` int(10) unsigned NOT NULL,
  `create_time` timestamp NULL default NULL,
  `update_time` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `description` varchar(255) collate utf8_bin NOT NULL,
  `notes` text collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `fk_url_2` (`fk_url`,`fk_user`),
  KEY `fk_url` (`fk_url`),
  KEY `fk_user` (`fk_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=36 ;

--
-- Triggers `mark`
--
DROP TRIGGER IF EXISTS `i_mark`;
DELIMITER //
CREATE TRIGGER `i_mark` AFTER INSERT ON `mark`
 FOR EACH ROW CALL update_mark_fulltext(NEW.id)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `u_mark`;
DELIMITER //
CREATE TRIGGER `u_mark` AFTER UPDATE ON `mark`
 FOR EACH ROW CALL update_mark_fulltext(NEW.id)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `d_mark`;
DELIMITER //
CREATE TRIGGER `d_mark` AFTER DELETE ON `mark`
 FOR EACH ROW DELETE FROM mark_fulltext WHERE fk_mark = OLD.id
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `mark_fulltext`
--

CREATE TABLE IF NOT EXISTS `mark_fulltext` (
  `fk_mark` int(10) unsigned NOT NULL,
  `mark_fulltext` mediumtext NOT NULL,
  PRIMARY KEY  (`fk_mark`),
  FULLTEXT KEY `mark_fulltext` (`mark_fulltext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `fk_mark` int(10) unsigned NOT NULL auto_increment,
  `tag` varchar(50) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`fk_mark`,`tag`),
  KEY `fk_mark` (`fk_mark`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=36 ;

--
-- Triggers `tag`
--
DROP TRIGGER IF EXISTS `i_tag`;
DELIMITER //
CREATE TRIGGER `i_tag` AFTER INSERT ON `tag`
 FOR EACH ROW BEGIN
 	CALL update_mark_fulltext(NEW.fk_mark);
 	UPDATE mark SET update_time = NOW() WHERE id = NEW.fk_mark;
 END;
//
DELIMITER ;
DROP TRIGGER IF EXISTS `u_tag`;
DELIMITER //
CREATE TRIGGER `u_tag` AFTER UPDATE ON `tag`
 FOR EACH ROW BEGIN
 	CALL update_mark_fulltext(NEW.fk_mark);
 	UPDATE mark SET update_time = NOW() WHERE id = NEW.fk_mark;
 END;//
DELIMITER ;
DROP TRIGGER IF EXISTS `d_tag`;
DELIMITER //
CREATE TRIGGER `d_tag` AFTER DELETE ON `tag`
 FOR EACH ROW BEGIN
 	CALL update_mark_fulltext(OLD.fk_mark);
 	UPDATE mark SET update_time = NOW() WHERE id = OLD.fk_mark;
 END;
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `url`
--

CREATE TABLE IF NOT EXISTS `url` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `url` text collate utf8_bin NOT NULL,
  `title` varchar(255) collate utf8_bin default NULL COMMENT 'The page title if known. Should not be user-submitted.',
  PRIMARY KEY  (`id`),
  KEY `url_index` (`url`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(100) collate utf8_bin NOT NULL,
  `display_name` varchar(50) collate utf8_bin NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=4 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mark`
--
ALTER TABLE `mark`
  ADD CONSTRAINT `mark_ibfk_1` FOREIGN KEY (`fk_url`) REFERENCES `url` (`id`),
  ADD CONSTRAINT `mark_ibfk_2` FOREIGN KEY (`fk_user`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tag`
--
ALTER TABLE `tag`
  ADD CONSTRAINT `tag_ibfk_1` FOREIGN KEY (`fk_mark`) REFERENCES `mark` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
