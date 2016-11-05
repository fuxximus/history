-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2014 at 10:15 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `svnhistory`
--

-- --------------------------------------------------------

--
-- Table structure for table `deployments`
--

DROP TABLE IF EXISTS `deployments`;
CREATE TABLE IF NOT EXISTS `deployments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rvn` int(11) NOT NULL DEFAULT '0',
  `action_date` datetime NOT NULL,
  `project_path` varchar(512) NOT NULL,
  `status` enum('deploy','redeploy','undeploy') NOT NULL DEFAULT 'deploy',
  `filename` varchar(255) DEFAULT NULL,
  `prefix` varchar(255) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(512) NOT NULL,
  `rvn` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `commit_date` datetime NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repo` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `initial_commit` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `latest_rvn` int(11) NOT NULL,
  `latest_commit_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


--
-- Table structure for table `repos`
--

DROP TABLE IF EXISTS `repos`;
CREATE TABLE IF NOT EXISTS `repos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(511) NOT NULL,
  `svn_user` varchar(255) NOT NULL,
  `svn_pass` varchar(511) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
