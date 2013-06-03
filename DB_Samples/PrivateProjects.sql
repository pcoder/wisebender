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
-- Table structure for table `PrivateProjects`
--

CREATE TABLE IF NOT EXISTS `PrivateProjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `starts` date NOT NULL,
  `expires` date DEFAULT NULL,
  `number` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A891C2F57E3C61F9` (`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `PrivateProjects`
--

INSERT INTO `PrivateProjects` (`id`, `owner_id`, `description`, `starts`, `expires`, `number`) VALUES
(1, 1, 'test', '2013-05-13', NULL, 1);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `PrivateProjects`
--
ALTER TABLE `PrivateProjects`
  ADD CONSTRAINT `FK_A891C2F57E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`);
