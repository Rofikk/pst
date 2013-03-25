SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


CREATE TABLE IF NOT EXISTS `checklist` (
  `checklist_id` int(6) NOT NULL auto_increment,
  `location_id` varchar(20) NOT NULL,
  `client_id` int(3) NOT NULL,
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `project_type` varchar(500) default NULL,
  `tank_type` varchar(500) default NULL,
  `prepared_by_name` varchar(75) NOT NULL default 'Tim Way',
  `prepared_by_phone` varchar(20) NOT NULL default '817.229.1330',
  `completed_date` date default NULL,
  `completed` tinyint(1) default NULL,
  `ccemail` varchar(1000) default NULL,
  PRIMARY KEY  (`checklist_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_auth`
--

CREATE TABLE IF NOT EXISTS `checklist_auth` (
  `cl_auth_id` int(6) NOT NULL auto_increment,
  `user_id` int(4) NOT NULL,
  `checklist_id` int(6) NOT NULL,
  `client_id` int(4) NOT NULL,
  PRIMARY KEY  (`cl_auth_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_doc`
--

CREATE TABLE IF NOT EXISTS `checklist_doc` (
  `checklist_doc_id` int(9) NOT NULL auto_increment,
  `checklist_id` int(6) NOT NULL,
  `checklist_question_id` int(8) NOT NULL,
  `path` varchar(500) NOT NULL,
  `name` varchar(120) NOT NULL,
  `filetype` varchar(4) NOT NULL,
  PRIMARY KEY  (`checklist_doc_id`),
  KEY `checklist_question_id` (`checklist_question_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=684 ;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_notes`
--

CREATE TABLE IF NOT EXISTS `checklist_notes` (
  `checklist_note_id` int(8) NOT NULL auto_increment,
  `checklist_id` int(6) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(500) NOT NULL,
  `alert` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`checklist_note_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=76 ;

-- --------------------------------------------------------

--
-- Table structure for table `checklist_questions`
--

CREATE TABLE IF NOT EXISTS `checklist_questions` (
  `checklist_question_id` int(8) NOT NULL auto_increment,
  `checklist_id` int(6) NOT NULL,
  `q_questions_id` int(4) NOT NULL,
  `value` int(1) NOT NULL default '0',
  `extra` varchar(30) default NULL,
  PRIMARY KEY  (`checklist_question_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1365 ;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE IF NOT EXISTS `client` (
  `client_id` int(3) NOT NULL auto_increment,
  `business_name` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `city` varchar(30) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `phone` varchar(20) default NULL,
  `contact_name` varchar(50) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `doc_to_questions`
--
CREATE TABLE IF NOT EXISTS `doc_to_questions` (
`checklist_doc_id` int(9)
,`checklist_id` int(6)
,`q_cat_id` int(2)
,`checklist_question_id` int(8)
,`q_name` varchar(50)
,`c_name` varchar(50)
,`path` varchar(500)
,`filename` varchar(120)
);
-- --------------------------------------------------------

--
-- Table structure for table `filetype`
--

CREATE TABLE IF NOT EXISTS `filetype` (
  `filetype_id` int(11) NOT NULL auto_increment,
  `filetype_name` varchar(5) NOT NULL,
  `filetype_mime` varchar(40) NOT NULL,
  PRIMARY KEY  (`filetype_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE IF NOT EXISTS `location` (
  `location_id` int(7) NOT NULL auto_increment,
  `location_ref_id` varchar(20) default NULL,
  `client_id` int(3) NOT NULL,
  `name` varchar(60) default NULL,
  `address` varchar(50) default NULL,
  `city` varchar(30) default NULL,
  `state` varchar(2) default NULL,
  `zip` varchar(10) default NULL,
  `engagement` varchar(20) default NULL,
  `project_drawing_number` varchar(20) default NULL,
  `project_manager` varchar(40) default NULL,
  `architect` varchar(40) default NULL,
  `af_contractor` varchar(75) default NULL,
  PRIMARY KEY  (`location_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Table structure for table `pw_reset`
--

CREATE TABLE IF NOT EXISTS `pw_reset` (
  `id` int(6) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `token` varchar(250) NOT NULL,
  `time` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- Table structure for table `q_cat`
--

CREATE TABLE IF NOT EXISTS `q_cat` (
  `q_cat_id` int(2) NOT NULL auto_increment,
  `name` varchar(80) NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  `shortname` varchar(50) default NULL,
  PRIMARY KEY  (`q_cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `q_questions`
--

CREATE TABLE IF NOT EXISTS `q_questions` (
  `q_question_id` int(4) NOT NULL auto_increment,
  `q_cat_id` int(2) NOT NULL,
  `value` varchar(150) NOT NULL,
  `shortname` varchar(50) default NULL,
  PRIMARY KEY  (`q_question_id`),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `search_table`
--
CREATE TABLE IF NOT EXISTS `search_table` (
`location_id` int(7)
,`location_ref_id` varchar(20)
,`name` varchar(60)
,`city` varchar(30)
,`state` varchar(2)
,`checklist_id` int(6)
,`client_id` int(3)
,`date` varchar(10)
,`business_name` varchar(50)
,`completed` tinyint(1)
);
-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(4) NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `password` varchar(250) NOT NULL,
  `email` varchar(175) NOT NULL,
  `first_name` varchar(30) default NULL,
  `last_name` varchar(30) default NULL,
  `client_id` int(4) NOT NULL,
  `type` int(1) NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- Structure for view `doc_to_questions`
--
DROP TABLE IF EXISTS `doc_to_questions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pst`@`%` SQL SECURITY DEFINER VIEW `pst`.`doc_to_questions` AS select `cd`.`checklist_doc_id` AS `checklist_doc_id`,`cd`.`checklist_id` AS `checklist_id`,`qq`.`q_cat_id` AS `q_cat_id`,`cq`.`checklist_question_id` AS `checklist_question_id`,`qq`.`shortname` AS `q_name`,`qc`.`shortname` AS `c_name`,`cd`.`path` AS `path`,`cd`.`name` AS `filename` from (((`pst`.`checklist_doc` `cd` join `pst`.`checklist_questions` `cq`) join `pst`.`q_questions` `qq`) join `pst`.`q_cat` `qc`) where ((`qq`.`q_cat_id` = `qc`.`q_cat_id`) and (`cq`.`q_questions_id` = `qq`.`q_question_id`) and (`cd`.`checklist_question_id` = `cq`.`checklist_question_id`));

-- --------------------------------------------------------

--
-- Structure for view `search_table`
--
DROP TABLE IF EXISTS `search_table`;

CREATE ALGORITHM=UNDEFINED DEFINER=`pst`@`%` SQL SECURITY DEFINER VIEW `pst`.`search_table` AS select `pst`.`location`.`location_id` AS `location_id`,`pst`.`location`.`location_ref_id` AS `location_ref_id`,`pst`.`location`.`name` AS `name`,`pst`.`location`.`city` AS `city`,`pst`.`location`.`state` AS `state`,`pst`.`checklist`.`checklist_id` AS `checklist_id`,`pst`.`location`.`client_id` AS `client_id`,date_format(`pst`.`checklist`.`date`,_utf8'%m-%d-%Y') AS `date`,`pst`.`client`.`business_name` AS `business_name`,`pst`.`checklist`.`completed` AS `completed` from (`pst`.`location` left join (`pst`.`checklist` join `pst`.`client` on((`pst`.`checklist`.`client_id` = `pst`.`client`.`client_id`))) on((`pst`.`location`.`location_id` = `pst`.`checklist`.`location_id`))) order by `pst`.`checklist`.`date`;
