--
-- Permissions
--
INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('newsletter', 'can_admin', 'Amministrazione modulo', 'Amministrazione completa del modulo newsletter: categorie, utenti, articoli.', 1);

--
-- Table structure for table `newsletter_ctg`
--

CREATE TABLE IF NOT EXISTS `newsletter_ctg` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_item`
--

CREATE TABLE IF NOT EXISTS `newsletter_item` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `date_creation` datetime NOT NULL,
  `date_last_edit` datetime NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text` text NOT NULL,
  `date_last_send` datetime DEFAULT NULL,
  `public` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_log`
--

CREATE TABLE IF NOT EXISTS `newsletter_log` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `newsletter` int(16) NOT NULL,
  `category` int(5) NOT NULL,
  `logdate` datetime NOT NULL,
  `success` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_log_error`
--

CREATE TABLE IF NOT EXISTS `newsletter_log_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment` int(16) NOT NULL,
  `emails` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_opt`
--

CREATE TABLE IF NOT EXISTS `newsletter_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `from_name` varchar(256) NOT NULL,
  `from_email` varchar(128) NOT NULL,
  `to_name` varchar(256) NOT NULL,
  `to_email` varchar(256) NOT NULL,
  `return_path` varchar(200) NOT NULL,
  `test_email` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_user`
--

CREATE TABLE IF NOT EXISTS `newsletter_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `firstname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `cap` varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_user_ctg`
--

CREATE TABLE IF NOT EXISTS `newsletter_user_ctg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `newsletteruser_id` int(11) NOT NULL,
  `newsletterctg_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
