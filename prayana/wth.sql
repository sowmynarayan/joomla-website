-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 02, 2010 at 01:28 AM
-- Server version: 5.0.51
-- PHP Version: 5.2.6-1+lenny2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `wth`
--

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE IF NOT EXISTS `answer` (
  `qid` int(3) default NULL,
  `answer` varchar(25) default NULL,
  `near` varchar(30) NOT NULL COMMENT 'nearest answer',
  `desc` varchar(200) NOT NULL COMMENT 'description'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `answer`
--

INSERT INTO `answer` (`qid`, `answer`, `near`, `desc`) VALUES
(1, 'notprayana', 'prayana', 'We told you it is not Prayana!'),
(2, 'antarctica', 'placeholder', 'placeholder'),
(3, 'elevation', 'placeholder', 'placeholder'),
(4, 'partsofspeech', 'martinlutherking', 'Not the whole thing!'),
(5, 'nagini', 'snake', 'Give us the proper noun.'),
(6, 'edisonbulb', 'edison', 'And?'),
(8, 'wordpress', 'volkwagen', 'Ha ha ha!Never that easy!'),
(9, 'onthemove', 'oscarmike', 'What do you mean?'),
(11, 'stephenking', 'theshining', 'We need a more novel answer!'),
(12, 'sandsoftime', 'thetwothrones', 'Start from the beginning'),
(14, 'exor', 'logicgate', 'Think deeper ^_^'),
(15, 'norelation', 'placeholder', 'placeholder'),
(19, 'mariecurie', 'placeholder', 'placeholder'),
(16, 'manchesterunited', 'newton', 'Not the scientist!'),
(17, 'vjd', 'duckworthlewis', 'Try again!'),
(18, 'slice', 'placeholder', 'placeholder');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(30) NOT NULL,
  `password` varchar(30) NOT NULL,
  `count` int(5) NOT NULL,
  `email_id` varchar(30) NOT NULL,
  `start_date` varchar(30) NOT NULL default '0',
  `finish_date` varchar(30) NOT NULL default '0',
  PRIMARY KEY  (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `count`, `email_id`, `start_date`, `finish_date`) VALUES
('admin', 'admin', 10, 'admin@gmail.com', 'February 2, 2010, 1:00 am', 'February 2, 2010, 1:06 am'),
('mod', 'mod', 6, 'mod@tce.edu', 'February 1, 2010, 11:23 pm', 'February 2, 2010, 12:05 am'),
('test', 'test', 2, '', 'February 1, 2010, 10:24 pm', 'February 1, 2010, 10:24 pm');

