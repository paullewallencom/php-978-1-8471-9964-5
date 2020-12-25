-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 13, 2009 at 03:09 AM
-- Server version: 5.1.33
-- PHP Version: 5.2.9-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `book4database`
--

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE IF NOT EXISTS `content` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `current_revision` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `secure` tinyint(1) NOT NULL,
  `parent` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `current_revision` (`current_revision`,`active`,`type`),
  KEY `type` (`type`),
  KEY `author` (`author`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Content Elements Table' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`ID`, `current_revision`, `active`, `secure`, `parent`, `order`, `author`, `type`, `path`) VALUES
(1, 1, 1, 0, 0, 0, 2, 2, 'super-product'),
(2, 2, 1, 0, 0, 0, 2, 1, 'test-page');

-- --------------------------------------------------------

--
-- Table structure for table `content_types`
--

CREATE TABLE IF NOT EXISTS `content_types` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(15) NOT NULL,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `content_types`
--

INSERT INTO `content_types` (`ID`, `reference`, `name`) VALUES
(1, 'page', 'CMS Pages'),
(2, 'product', 'E-Commerce Product');

-- --------------------------------------------------------

--
-- Table structure for table `content_types_products`
--

CREATE TABLE IF NOT EXISTS `content_types_products` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `content_version` int(11) NOT NULL,
  `price` float NOT NULL,
  `weight` int(11) NOT NULL,
  `SKU` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `featured` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `content_version` (`content_version`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `content_types_products`
--

INSERT INTO `content_types_products` (`ID`, `content_version`, `price`, `weight`, `SKU`, `stock`, `image`, `featured`) VALUES
(1, 1, 50, 20, '', 0, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `content_types_products_in_categories`
--

CREATE TABLE IF NOT EXISTS `content_types_products_in_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `content_types_products_in_categories`
--


-- --------------------------------------------------------

--
-- Table structure for table `content_versions`
--

CREATE TABLE IF NOT EXISTS `content_versions` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `metakeywords` varchar(255) NOT NULL,
  `metadescription` varchar(255) NOT NULL,
  `metarobots` varchar(255) NOT NULL,
  `author` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `author` (`author`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `content_versions`
--

INSERT INTO `content_versions` (`ID`, `name`, `title`, `heading`, `content`, `metakeywords`, `metadescription`, `metarobots`, `author`, `created`) VALUES
(1, 'test product', '', 'test product', 'some content\r\n', '', '', '', 2, '0000-00-00 00:00:00'),
(2, 'A test page', 'A test page', 'A test page', '<p>This is a test page</p>', 'test, page', 'a test page', '', 2, '2009-07-13 02:23:59');

-- --------------------------------------------------------

--
-- Table structure for table `content_versions_history`
--

CREATE TABLE IF NOT EXISTS `content_versions_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `version_id` int(11) NOT NULL,
  `date_changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `content_id` (`content_id`,`version_id`,`changed_by`),
  KEY `changed_by` (`changed_by`),
  KEY `version_id` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `content_versions_history`
--


-- --------------------------------------------------------

--
-- Table structure for table `controllers`
--

CREATE TABLE IF NOT EXISTS `controllers` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `controller` varchar(25) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `controllers`
--

INSERT INTO `controllers` (`ID`, `controller`, `active`) VALUES
(1, 'products', 1),
(2, 'categories', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(40) NOT NULL,
  `password_salt` varchar(5) NOT NULL,
  `email` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_reset_key` varchar(15) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `username`, `password_hash`, `password_salt`, `email`, `active`, `admin`, `banned`, `pwd_reset_key`) VALUES
(2, 'Michael', '', '', '', 0, 0, 0, '');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_10` FOREIGN KEY (`type`) REFERENCES `content_types` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `content_ibfk_8` FOREIGN KEY (`current_revision`) REFERENCES `content_versions` (`ID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `content_ibfk_9` FOREIGN KEY (`author`) REFERENCES `users` (`ID`) ON UPDATE CASCADE;

--
-- Constraints for table `content_types_products`
--
ALTER TABLE `content_types_products`
  ADD CONSTRAINT `content_types_products_ibfk_1` FOREIGN KEY (`content_version`) REFERENCES `content_versions` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `content_versions`
--
ALTER TABLE `content_versions`
  ADD CONSTRAINT `content_versions_ibfk_1` FOREIGN KEY (`author`) REFERENCES `users` (`ID`) ON UPDATE CASCADE;

--
-- Constraints for table `content_versions_history`
--
ALTER TABLE `content_versions_history`
  ADD CONSTRAINT `content_versions_history_ibfk_3` FOREIGN KEY (`changed_by`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `content_versions_history_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `content_versions_history_ibfk_2` FOREIGN KEY (`version_id`) REFERENCES `content_versions` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
