-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 14, 2012 at 05:29 PM
-- Server version: 5.1.62
-- PHP Version: 5.3.2-1ubuntu4.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nte_moodle`
--

-- --------------------------------------------------------

--
-- Table structure for table `mdl_nte_archives`
--

CREATE TABLE `mdl_nte_archives` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `archivedcourseid` int(10) unsigned NOT NULL,
  `archivedcategoryid` int(10) unsigned NOT NULL,
  `timemodified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `mdl_nte_duplicatecourse`
--

CREATE TABLE `mdl_nte_duplicatecourse` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reusecourseid` int(10) unsigned NOT NULL,
  `requesteruserid` int(10) unsigned NOT NULL,
  `reusecomment` text,
  `timemodified` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
