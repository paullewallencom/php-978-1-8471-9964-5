-- phpMyAdmin SQL Dump
-- version 2.10.1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Sep 27, 2009 at 09:26 PM
-- Server version: 5.0.41
-- PHP Version: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `book4chapter8`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `basket_attribute_value_association`
-- 

CREATE TABLE `basket_attribute_value_association` (
  `basket_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  KEY `basket_id` (`basket_id`,`attribute_id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Association of basket contents and attribute values';

-- 
-- Dumping data for table `basket_attribute_value_association`
-- 

INSERT INTO `basket_attribute_value_association` (`basket_id`, `attribute_id`) VALUES 
(1, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `basket_contents`
-- 

CREATE TABLE `basket_contents` (
  `ID` int(11) NOT NULL auto_increment,
  `session_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `ip_address` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `uploaded_file` varchar(255) NOT NULL,
  `custom_text_values` longtext NOT NULL,
  `standard` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `basket_contents`
-- 

INSERT INTO `basket_contents` (`ID`, `session_id`, `user_id`, `product_id`, `quantity`, `ip_address`, `timestamp`, `uploaded_file`, `custom_text_values`, `standard`) VALUES 
(1, 'acab6b5804cd618c8e029606eaa079ca', 0, 1, 1, '127.0.0.1', '2009-09-27 21:02:36', '', '', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `content`
-- 

CREATE TABLE `content` (
  `ID` int(11) NOT NULL auto_increment,
  `current_revision` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `secure` tinyint(1) NOT NULL,
  `parent` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `current_revision` (`current_revision`,`active`,`type`),
  KEY `type` (`type`),
  KEY `author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Content Elements Table' AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `content`
-- 

INSERT INTO `content` (`ID`, `current_revision`, `active`, `secure`, `parent`, `order`, `author`, `type`, `path`) VALUES 
(1, 1, 1, 0, 0, 0, 2, 2, 'super-product'),
(2, 2, 1, 0, 0, 0, 2, 1, 'test-page'),
(3, 3, 1, 0, 0, 1, 2, 1, 'test-path');

-- --------------------------------------------------------

-- 
-- Table structure for table `content_comments`
-- 

CREATE TABLE `content_comments` (
  `ID` int(11) NOT NULL auto_increment,
  `content` int(11) NOT NULL,
  `authorName` varchar(50) NOT NULL,
  `authorEmail` varchar(50) NOT NULL,
  `comment` longtext NOT NULL,
  `IPAddress` varchar(40) NOT NULL,
  `dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `approved` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `content` (`content`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Content comments - also for product reviews' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `content_comments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `content_ratings`
-- 

CREATE TABLE `content_ratings` (
  `ID` int(11) NOT NULL auto_increment,
  `rating` int(11) NOT NULL,
  `contentID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `sessionID` varchar(255) NOT NULL,
  `IPAddress` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `contentID` (`contentID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `content_ratings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `content_types`
-- 

CREATE TABLE `content_types` (
  `ID` int(11) NOT NULL auto_increment,
  `reference` varchar(15) NOT NULL,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

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

CREATE TABLE `content_types_products` (
  `ID` int(11) NOT NULL auto_increment,
  `content_version` int(11) NOT NULL,
  `price` float NOT NULL,
  `weight` int(11) NOT NULL,
  `SKU` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `featured` tinyint(1) NOT NULL,
  `allow_upload` tinyint(1) NOT NULL,
  `custom_text_inputs` longtext NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `content_version` (`content_version`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `content_types_products`
-- 

INSERT INTO `content_types_products` (`ID`, `content_version`, `price`, `weight`, `SKU`, `stock`, `image`, `featured`, `allow_upload`, `custom_text_inputs`) VALUES 
(1, 1, 50, 20, '', -1, '', 0, 0, '');

-- --------------------------------------------------------

-- 
-- Table structure for table `content_types_products_in_categories`
-- 

CREATE TABLE `content_types_products_in_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY  (`product_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `content_types_products_in_categories`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `content_versions`
-- 

CREATE TABLE `content_versions` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `metakeywords` varchar(255) NOT NULL,
  `metadescription` varchar(255) NOT NULL,
  `metarobots` varchar(255) NOT NULL,
  `author` int(11) NOT NULL,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`),
  KEY `author` (`author`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `content_versions`
-- 

INSERT INTO `content_versions` (`ID`, `name`, `title`, `heading`, `content`, `metakeywords`, `metadescription`, `metarobots`, `author`, `created`) VALUES 
(1, 'test product', '', 'test product', 'some content\r\n', '', '', '', 2, '0000-00-00 00:00:00'),
(2, 'A test page', 'A test page', 'A test page', '<p>This is a test page</p>', 'test, page', 'a test page', '', 2, '2009-07-13 02:23:59'),
(3, 'test', 'test', 'test', 'test', 'test', 'test', 'test', 2, '2009-07-24 20:28:39');

-- --------------------------------------------------------

-- 
-- Table structure for table `content_versions_history`
-- 

CREATE TABLE `content_versions_history` (
  `ID` int(11) NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `version_id` int(11) NOT NULL,
  `date_changed` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `changed_by` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
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

CREATE TABLE `controllers` (
  `ID` int(11) NOT NULL auto_increment,
  `controller` varchar(25) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `controllers`
-- 

INSERT INTO `controllers` (`ID`, `controller`, `active`) VALUES 
(1, 'products', 1),
(2, 'categories', 1),
(3, 'wishlist', 1),
(4, 'contentcomments', 0),
(5, 'contentratings', 1),
(6, 'basket', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `discount_codes`
-- 

CREATE TABLE `discount_codes` (
  `ID` int(11) NOT NULL auto_increment,
  `vouchercode` varchar(25) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `min_basket_cost` float NOT NULL,
  `discount_operation` enum('-','%','s') NOT NULL,
  `discount_amount` float NOT NULL,
  `num_vouchers` int(11) NOT NULL default '-1',
  `expiry` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `discount_codes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `groups`
-- 

CREATE TABLE `groups` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `groups`
-- 

INSERT INTO `groups` (`ID`, `name`) VALUES 
(1, 'General');

-- --------------------------------------------------------

-- 
-- Table structure for table `group_memberships`
-- 

CREATE TABLE `group_memberships` (
  `user` int(11) NOT NULL,
  `group` int(11) NOT NULL,
  PRIMARY KEY  (`user`,`group`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `group_memberships`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `product_attributes`
-- 

CREATE TABLE `product_attributes` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Product Attributes e.g. Color, Size, etc.' AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `product_attributes`
-- 

INSERT INTO `product_attributes` (`ID`, `name`) VALUES 
(1, 'Colors'),
(2, 'Sizes');

-- --------------------------------------------------------

-- 
-- Table structure for table `product_attribute_values`
-- 

CREATE TABLE `product_attribute_values` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Product Attribute Values e.g. Blue, Large, etc.' AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `product_attribute_values`
-- 

INSERT INTO `product_attribute_values` (`ID`, `name`, `attribute_id`) VALUES 
(1, 'Blue', 1),
(2, 'Red', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_attribute_value_association`
-- 

CREATE TABLE `product_attribute_value_association` (
  `product_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `cost_difference` double NOT NULL,
  KEY `product_id` (`product_id`,`attribute_id`),
  KEY `attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Association of products and attribute values';

-- 
-- Dumping data for table `product_attribute_value_association`
-- 

INSERT INTO `product_attribute_value_association` (`product_id`, `attribute_id`, `order`, `cost_difference`) VALUES 
(1, 1, 1, 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_filter_attribute_associations`
-- 

CREATE TABLE `product_filter_attribute_associations` (
  `attribute` int(11) NOT NULL,
  `product` int(11) NOT NULL,
  PRIMARY KEY  (`attribute`,`product`),
  KEY `product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Product attribute associations for filtering product lists';

-- 
-- Dumping data for table `product_filter_attribute_associations`
-- 

INSERT INTO `product_filter_attribute_associations` (`attribute`, `product`) VALUES 
(1, 1),
(2, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_filter_attribute_types`
-- 

CREATE TABLE `product_filter_attribute_types` (
  `ID` int(11) NOT NULL auto_increment,
  `reference` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL,
  `ProductContainedAttribute` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Product Attributes for Filtering Product Lists' AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `product_filter_attribute_types`
-- 

INSERT INTO `product_filter_attribute_types` (`ID`, `reference`, `name`, `ProductContainedAttribute`) VALUES 
(1, 'price', 'Price', 1),
(3, 'weight', 'Weight', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_filter_attribute_values`
-- 

CREATE TABLE `product_filter_attribute_values` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `attributeType` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `lowerValue` int(11) NOT NULL,
  `upperValue` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `attributeType` (`attributeType`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Attribute values for filtering products' AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `product_filter_attribute_values`
-- 

INSERT INTO `product_filter_attribute_values` (`ID`, `name`, `attributeType`, `order`, `lowerValue`, `upperValue`) VALUES 
(1, '< $5', 1, 1, 0, 5),
(2, '$5 - $20', 1, 1, 5, 20),
(3, '< 5kg', 3, 1, 0, 5);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_relevant_products`
-- 

CREATE TABLE `product_relevant_products` (
  `ID` int(11) NOT NULL auto_increment,
  `productA` int(11) NOT NULL,
  `ProductB` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `ProductB` (`ProductB`),
  KEY `productA` (`productA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `product_relevant_products`
-- 

INSERT INTO `product_relevant_products` (`ID`, `productA`, `ProductB`) VALUES 
(1, 1, 2),
(2, 3, 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `product_stock_notification_requests`
-- 

CREATE TABLE `product_stock_notification_requests` (
  `ID` int(11) NOT NULL auto_increment,
  `customer` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `product` int(11) NOT NULL,
  `processed` tinyint(1) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `product` (`product`,`processed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Customer notification requests for new stock levels' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `product_stock_notification_requests`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `shipping_costs_product`
-- 

CREATE TABLE `shipping_costs_product` (
  `shipping_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `cost` float NOT NULL,
  PRIMARY KEY  (`shipping_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `shipping_costs_product`
-- 

INSERT INTO `shipping_costs_product` (`shipping_id`, `product_id`, `cost`) VALUES 
(1, 1, 5);

-- --------------------------------------------------------

-- 
-- Table structure for table `shipping_costs_weight`
-- 

CREATE TABLE `shipping_costs_weight` (
  `ID` int(11) NOT NULL auto_increment,
  `shipping_id` int(11) NOT NULL,
  `lower_weight` float NOT NULL,
  `cost` float NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `shipping_costs_weight`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `shipping_methods`
-- 

CREATE TABLE `shipping_methods` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `default_cost` double NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `active` (`active`,`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Shipping methods' AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `shipping_methods`
-- 

INSERT INTO `shipping_methods` (`ID`, `name`, `active`, `is_default`, `default_cost`) VALUES 
(1, 'Standard Shipping', 1, 1, 5);

-- --------------------------------------------------------

-- 
-- Table structure for table `shipping_rules`
-- 

CREATE TABLE `shipping_rules` (
  `ID` int(11) NOT NULL auto_increment,
  `shipping_id` int(11) NOT NULL,
  `match_amount` float NOT NULL,
  `match_type` enum('shipping','products') NOT NULL,
  `match_operator` enum('<','>','<=','>=','<>','==') NOT NULL,
  `rule` varchar(255) NOT NULL,
  `rule_amount` float NOT NULL,
  `rule_operator` enum('+','-','=','*','/') NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `shipping_id` (`shipping_id`,`order`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `shipping_rules`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tax_codes`
-- 

CREATE TABLE `tax_codes` (
  `ID` int(11) NOT NULL auto_increment,
  `tax_code` varchar(255) NOT NULL,
  `calculation_value` double NOT NULL,
  `calculation_operation` enum('+','-','*','/','=') NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `tax_code` (`tax_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tax_codes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `users`
-- 

CREATE TABLE `users` (
  `ID` int(11) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(40) NOT NULL,
  `password_salt` varchar(5) NOT NULL,
  `email` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `admin` tinyint(1) NOT NULL default '0',
  `banned` tinyint(1) NOT NULL default '0',
  `pwd_reset_key` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `users`
-- 

INSERT INTO `users` (`ID`, `username`, `password_hash`, `password_salt`, `email`, `active`, `admin`, `banned`, `pwd_reset_key`, `name`) VALUES 
(1, 'Michael', '5f4dcc3b5aa765d61d8327deb882cf99', '', 'mkpeacock@gmail.com', 1, 1, 0, '', 'Michael Peacock'),
(2, 'user', '5f4dcc3b5aa765d61d8327deb882cf99', '', 'user@domain.com', 1, 0, 0, '', 'A User');

-- --------------------------------------------------------

-- 
-- Table structure for table `wish_list_products`
-- 

CREATE TABLE `wish_list_products` (
  `ID` int(11) NOT NULL auto_increment,
  `product` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `dateadded` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `priority` int(11) NOT NULL,
  `sessionID` varchar(50) NOT NULL,
  `IPAddress` varchar(50) NOT NULL,
  `user` int(11) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Wish list products' AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `wish_list_products`
-- 


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
-- Constraints for table `content_comments`
-- 
ALTER TABLE `content_comments`
  ADD CONSTRAINT `content_comments_ibfk_1` FOREIGN KEY (`content`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `content_versions_history_ibfk_1` FOREIGN KEY (`content_id`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `content_versions_history_ibfk_2` FOREIGN KEY (`version_id`) REFERENCES `content_versions` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `content_versions_history_ibfk_3` FOREIGN KEY (`changed_by`) REFERENCES `users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_attribute_values`
-- 
ALTER TABLE `product_attribute_values`
  ADD CONSTRAINT `product_attribute_values_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `product_attribute_values` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_attribute_value_association`
-- 
ALTER TABLE `product_attribute_value_association`
  ADD CONSTRAINT `product_attribute_value_association_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_attribute_value_association_ibfk_2` FOREIGN KEY (`attribute_id`) REFERENCES `product_attribute_values` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_filter_attribute_associations`
-- 
ALTER TABLE `product_filter_attribute_associations`
  ADD CONSTRAINT `product_filter_attribute_associations_ibfk_1` FOREIGN KEY (`attribute`) REFERENCES `product_attribute_values` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_filter_attribute_associations_ibfk_2` FOREIGN KEY (`product`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_filter_attribute_values`
-- 
ALTER TABLE `product_filter_attribute_values`
  ADD CONSTRAINT `product_filter_attribute_values_ibfk_1` FOREIGN KEY (`attributeType`) REFERENCES `product_filter_attribute_types` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_relevant_products`
-- 
ALTER TABLE `product_relevant_products`
  ADD CONSTRAINT `product_relevant_products_ibfk_1` FOREIGN KEY (`productA`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `product_relevant_products_ibfk_2` FOREIGN KEY (`ProductB`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `product_stock_notification_requests`
-- 
ALTER TABLE `product_stock_notification_requests`
  ADD CONSTRAINT `product_stock_notification_requests_ibfk_1` FOREIGN KEY (`product`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 
-- Constraints for table `wish_list_products`
-- 
ALTER TABLE `wish_list_products`
  ADD CONSTRAINT `wish_list_products_ibfk_1` FOREIGN KEY (`product`) REFERENCES `content` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
