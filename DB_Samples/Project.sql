-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 14, 2013 at 06:49 PM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `codebender`
--

-- --------------------------------------------------------

--
-- Table structure for table `Project`
--

CREATE TABLE IF NOT EXISTS `Project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_public` tinyint(1) NOT NULL,
  `type` varchar(255) NOT NULL,
  `projectfiles_id` varchar(255) NOT NULL,
  `parent` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E00EE9727E3C61F9` (`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Project`
--

INSERT INTO `Project` (`id`, `owner_id`, `name`, `description`, `is_public`, `type`, `projectfiles_id`, `parent`) VALUES
(1, 1, 'test_project', 'a project used to test the search function', 1, 'disk', 'tester/1517ed236831cd', NULL),
(2, 1, 'aproject', '', 1, 'disk', 'tester/1517ed2896cf41', NULL),
(3, 2, 'myproj', '', 1, 'disk', 'tzikis/1516c558248054', NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Project`
--
ALTER TABLE `Project`
  ADD CONSTRAINT `FK_E00EE9727E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`);
