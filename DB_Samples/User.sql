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
-- Table structure for table `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referrer_id` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `username_canonical` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_canonical` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `locked` tinyint(1) NOT NULL,
  `expired` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `confirmation_token` varchar(255) DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:array)',
  `credentials_expired` tinyint(1) NOT NULL,
  `credentials_expire_at` datetime DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `karma` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `referrer_username` varchar(255) DEFAULT NULL,
  `referral_code` varchar(255) DEFAULT NULL,
  `referrals` int(11) NOT NULL,
  `walkthrough_status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_2DA1797792FC23A8` (`username_canonical`),
  UNIQUE KEY `UNIQ_2DA17977A0D96FBF` (`email_canonical`),
  KEY `IDX_2DA17977798C22DB` (`referrer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`id`, `referrer_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, `password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `firstname`, `lastname`, `twitter`, `karma`, `points`, `referrer_username`, `referral_code`, `referrals`, `walkthrough_status`) VALUES
(1, NULL, 'tester', 'tester', 'tester@codebender.cc', 'tester@codebender.cc', 1, '6y45wch9yycc8ok4sc0wowsok48kk08', '8DkTW7gaKgGngS1Yv2Jcblvurxkp/SY/XIHl+hfqBaVNSwb7CqIBK0xGDFVgz77Ta6UN2/sVC7+lr6nh1IXVBw==', '2013-05-14 14:40:14', 0, 0, NULL, NULL, NULL, 'a:1:{i:0;s:10:"ROLE_ADMIN";}', 0, NULL, 'myfirstname', 'mylastname', 'codebender_cc', 0, 0, NULL, NULL, 0, 0),
(2, NULL, 'testacc', 'testacc', 'testacc@codebender.cc', 'testacc@codebender.cc', 1, '5b6bje7jdb8kkkwwwwoo0g0ok0g0k80', 'Vvz62gTdYfNVU9LQIOsuiEjP/XqLjsglcPz5tOCvsqYjk/e5KkLzlGKHjGvg7Pk1CU3gTQAQRLeRxdoeXABWuA==', '2013-05-14 18:39:21', 0, 0, NULL, NULL, NULL, 'a:0:{}', 0, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, 0, 0);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `User`
--
ALTER TABLE `User`
  ADD CONSTRAINT `FK_2DA17977798C22DB` FOREIGN KEY (`referrer_id`) REFERENCES `user` (`id`);
