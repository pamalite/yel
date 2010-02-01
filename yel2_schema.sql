-- MySQL dump 10.13  Distrib 5.1.37, for apple-darwin9.5.0 (i386)
--
-- Host: localhost    Database: yel2_dev
-- ------------------------------------------------------
-- Server version	5.1.37

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `branch` varchar(150) DEFAULT NULL,
  `founded_on` date DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_ibfk_1` (`country`),
  KEY `branches_ibfk_2` (`currency`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `currencies` (`symbol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `business_groups`
--

DROP TABLE IF EXISTS `business_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL,
  `security_clearance` int(11) NOT NULL,
  `branch` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_groups_ibfk_1` (`security_clearance`),
  KEY `business_groups_ibfk_2` (`branch`),
  CONSTRAINT `business_groups_ibfk_1` FOREIGN KEY (`security_clearance`) REFERENCES `security_clearances` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `business_groups_ibfk_2` FOREIGN KEY (`branch`) REFERENCES `branches` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidate_email_manifests`
--

DROP TABLE IF EXISTS `candidate_email_manifests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_email_manifests` (
  `mailing_list` int(10) unsigned DEFAULT NULL,
  `email_addr` varchar(100) DEFAULT NULL,
  KEY `email_addr` (`email_addr`),
  KEY `mailing_list` (`mailing_list`),
  CONSTRAINT `candidate_email_manifests_ibfk_1` FOREIGN KEY (`email_addr`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `candidate_email_manifests_ibfk_2` FOREIGN KEY (`mailing_list`) REFERENCES `candidates_mailing_lists` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidates_mailing_lists`
--

DROP TABLE IF EXISTS `candidates_mailing_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidates_mailing_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `candidates_mailing_lists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidates_with_no_contacts`
--

DROP TABLE IF EXISTS `candidates_with_no_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidates_with_no_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `resume` int(11) NOT NULL,
  `job` int(11) NOT NULL,
  `requested_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  KEY `resume` (`resume`),
  KEY `job` (`job`),
  CONSTRAINT `candidates_with_no_contacts_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON DELETE CASCADE,
  CONSTRAINT `candidates_with_no_contacts_ibfk_2` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `candidates_with_no_contacts_ibfk_3` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `country_code` char(2) NOT NULL,
  `country` varchar(200) DEFAULT NULL,
  `branch_country_code` char(2) DEFAULT NULL,
  `show_in_list` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_notes`
--

DROP TABLE IF EXISTS `credit_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `previous_invoice` int(11) NOT NULL,
  `free_invoice` int(11) NOT NULL,
  `credit_amount` float(9,2) DEFAULT '0.00',
  `issued_on` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `previous_invoice` (`previous_invoice`),
  KEY `free_invoice` (`free_invoice`),
  CONSTRAINT `credit_notes_ibfk_1` FOREIGN KEY (`previous_invoice`) REFERENCES `invoices` (`id`),
  CONSTRAINT `credit_notes_ibfk_2` FOREIGN KEY (`free_invoice`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `symbol` char(3) NOT NULL,
  `country_code` char(2) NOT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `rate` decimal(6,4) NOT NULL DEFAULT '1.0000',
  PRIMARY KEY (`symbol`,`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee_sessions`
--

DROP TABLE IF EXISTS `employee_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_sessions` (
  `employee` int(11) NOT NULL,
  `sha1` char(40) NOT NULL,
  `last_login` date DEFAULT NULL,
  PRIMARY KEY (`employee`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joined_on` date NOT NULL,
  `password` char(32) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(20) NOT NULL,
  `country` char(2) NOT NULL,
  `phone_num` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email_addr` varchar(150) DEFAULT NULL,
  `alternate_email` varchar(150) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `branch` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_addr` (`email_addr`),
  KEY `employees_ibfk_2` (`branch`),
  KEY `employees_ibfk_3` (`created_by`),
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`branch`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees_groups`
--

DROP TABLE IF EXISTS `employees_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees_groups` (
  `employee` int(11) NOT NULL,
  `business_group` int(11) NOT NULL,
  PRIMARY KEY (`employee`,`business_group`),
  KEY `employees_groups_ibfk_2` (`business_group`),
  CONSTRAINT `employees_groups_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `employees_groups_ibfk_2` FOREIGN KEY (`business_group`) REFERENCES `business_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employer_extras`
--

DROP TABLE IF EXISTS `employer_extras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employer_extras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employer` varchar(10) NOT NULL,
  `label` varchar(255) NOT NULL,
  `charges` decimal(9,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emplyer` (`employer`),
  CONSTRAINT `employer_extras_ibfk_1` FOREIGN KEY (`employer`) REFERENCES `employers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employer_fees`
--

DROP TABLE IF EXISTS `employer_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employer_fees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employer` varchar(10) NOT NULL,
  `service_fee` float(4,2) NOT NULL,
  `premier_fee` float(4,2) NOT NULL DEFAULT '0.00',
  `discount` float(5,2) DEFAULT '0.00',
  `reward_percentage` float(4,2) NOT NULL DEFAULT '25.00',
  `salary_start` decimal(9,2) NOT NULL DEFAULT '1.00',
  `salary_end` decimal(9,2) DEFAULT NULL,
  `guarantee_months` int(2) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `employer` (`employer`),
  CONSTRAINT `employer_fees_ibfk_1` FOREIGN KEY (`employer`) REFERENCES `employers` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employer_sessions`
--

DROP TABLE IF EXISTS `employer_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employer_sessions` (
  `employer` varchar(10) NOT NULL,
  `sha1` char(40) NOT NULL,
  `last_login` date DEFAULT NULL,
  `first_login` date DEFAULT NULL,
  PRIMARY KEY (`employer`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employers`
--

DROP TABLE IF EXISTS `employers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employers` (
  `id` varchar(10) NOT NULL,
  `password` char(32) NOT NULL,
  `license_num` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone_num` varchar(20) NOT NULL,
  `fax_num` varchar(20) DEFAULT NULL,
  `email_addr` varchar(100) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(50) NOT NULL,
  `country` char(2) NOT NULL,
  `website_url` varchar(100) DEFAULT NULL,
  `logo_path` varchar(100) DEFAULT NULL,
  `about` mediumtext,
  `joined_on` date DEFAULT NULL,
  `active` enum('Y','N') DEFAULT 'Y',
  `like_instant_notification` tinyint(1) DEFAULT '1',
  `like_newsletter` tinyint(1) DEFAULT '1',
  `working_months` int(2) unsigned DEFAULT '12',
  `bonus_months` int(2) unsigned DEFAULT '1',
  `payment_terms_days` int(2) unsigned DEFAULT '30',
  `registered_through` enum('E','M') DEFAULT 'M',
  `registered_by` int(11) NOT NULL,
  `branch` int(11) NOT NULL,
  `subscription_expire_on` date DEFAULT NULL,
  `subscription_suspended` tinyint(1) DEFAULT '1',
  `free_postings_left` int(10) unsigned DEFAULT '1',
  `paid_postings_left` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contact_person` (`contact_person`),
  KEY `name` (`name`),
  KEY `email_addr` (`email_addr`),
  KEY `country` (`country`),
  KEY `registered_by` (`registered_by`),
  KEY `branch` (`branch`),
  CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `employers_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `employers_ibfk_3` FOREIGN KEY (`branch`) REFERENCES `branches` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `industries`
--

DROP TABLE IF EXISTS `industries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `industries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL,
  `description` tinytext,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `industries_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `industry_locales`
--

DROP TABLE IF EXISTS `industry_locales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `industry_locales` (
  `country` char(2) NOT NULL,
  `industry` int(10) unsigned NOT NULL,
  `localized_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `localized_description` text CHARACTER SET utf8,
  PRIMARY KEY (`country`,`industry`),
  KEY `industry` (`industry`),
  CONSTRAINT `industry_locales_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `industry_locales_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `itemdesc` varchar(100) DEFAULT NULL,
  `amount` decimal(9,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `invoice_items_ibfk_1` (`invoice`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice`) REFERENCES `invoices` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `issued_on` date NOT NULL,
  `type` enum('J','R','M','P') DEFAULT NULL,
  `employer` varchar(10) NOT NULL,
  `payable_by` date DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `paid_through` enum('CSH','IBT','CHQ') DEFAULT NULL,
  `paid_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_ibfk_1` (`employer`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`employer`) REFERENCES `employers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_extensions`
--

DROP TABLE IF EXISTS `job_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` int(11) NOT NULL,
  `previously_created_on` datetime DEFAULT NULL,
  `previously_expired_on` datetime DEFAULT NULL,
  `for_replacement` enum('Y','N') DEFAULT 'N',
  `invoiced` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `job_extensions` (`job`),
  CONSTRAINT `job_extensions` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_index`
--

DROP TABLE IF EXISTS `job_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_index` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` int(11) NOT NULL,
  `country` char(2) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `state` mediumtext,
  `title` varchar(100) DEFAULT NULL,
  `description` mediumtext,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `state` (`state`),
  FULLTEXT KEY `criteria_match` (`description`,`title`,`state`)
) ENGINE=MyISAM AUTO_INCREMENT=133 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employer` varchar(50) NOT NULL,
  `contact_carbon_copy` varchar(255) DEFAULT NULL,
  `industry` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
  `currency` char(3) NOT NULL,
  `salary` int(10) unsigned DEFAULT NULL,
  `salary_end` int(10) unsigned DEFAULT NULL,
  `salary_negotiable` enum('Y','N') DEFAULT 'Y',
  `potential_reward` int(10) unsigned DEFAULT NULL,
  `closed` enum('Y','N','S') DEFAULT 'N',
  `created_on` datetime NOT NULL,
  `expire_on` datetime NOT NULL,
  `premium_only` enum('Y','N') DEFAULT 'N',
  `state` mediumtext,
  `title` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `invoiced` enum('Y','N') DEFAULT 'N',
  `acceptable_resume_type` enum('A','O','F') DEFAULT 'A',
  `views_count` int(10) unsigned DEFAULT '0',
  `for_replacement` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `jobs_ibfk_1` (`employer`),
  KEY `jobs_ibfk_2` (`industry`),
  KEY `jobs_ibfk_3` (`country`),
  KEY `jobs_ibfk_4` (`currency`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer`) REFERENCES `employers` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_3` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_4` FOREIGN KEY (`currency`) REFERENCES `currencies` (`symbol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=159 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_activation_tokens`
--

DROP TABLE IF EXISTS `member_activation_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_activation_tokens` (
  `id` varchar(100) NOT NULL,
  `member` varchar(100) NOT NULL,
  `joined_on` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  CONSTRAINT `member_activation_tokens_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_banks`
--

DROP TABLE IF EXISTS `member_banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `bank` varchar(50) DEFAULT NULL,
  `account` varchar(50) DEFAULT NULL,
  `in_used` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  CONSTRAINT `member_bank_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_banners`
--

DROP TABLE IF EXISTS `member_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pref_key` varchar(50) NOT NULL,
  `pref_value` varchar(255) DEFAULT NULL,
  `member` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  CONSTRAINT `member_banners_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_invites`
--

DROP TABLE IF EXISTS `member_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_invites` (
  `referee_email` varchar(100) NOT NULL,
  `member` varchar(100) NOT NULL,
  `invited_on` datetime DEFAULT NULL,
  `signed_up_on` datetime DEFAULT NULL,
  `referred_job` int(11) NOT NULL,
  `testimony` mediumtext,
  PRIMARY KEY (`referee_email`,`member`,`referred_job`),
  KEY `member` (`member`),
  KEY `member_invites_ibfk_2` (`referred_job`),
  CONSTRAINT `member_invites_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`),
  CONSTRAINT `member_invites_ibfk_2` FOREIGN KEY (`referred_job`) REFERENCES `jobs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_networks`
--

DROP TABLE IF EXISTS `member_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_networks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `industry` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`,`industry`),
  KEY `member_networks_ibfk_2` (`industry`),
  CONSTRAINT `member_networks_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `member_networks_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_networks_referees`
--

DROP TABLE IF EXISTS `member_networks_referees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_networks_referees` (
  `network` int(11) NOT NULL,
  `referee` int(11) NOT NULL,
  PRIMARY KEY (`network`,`referee`),
  KEY `member_networks_referees_ibfk_2` (`referee`),
  CONSTRAINT `member_networks_referees_ibfk_1` FOREIGN KEY (`network`) REFERENCES `member_networks` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `member_networks_referees_ibfk_2` FOREIGN KEY (`referee`) REFERENCES `member_referees` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_photos`
--

DROP TABLE IF EXISTS `member_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `photo_hash` varchar(100) NOT NULL,
  `photo_type` varchar(50) NOT NULL,
  `approved` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `member_photos_ibfk_1` (`member`),
  CONSTRAINT `member_photos_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_referees`
--

DROP TABLE IF EXISTS `member_referees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_referees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `referee` varchar(100) NOT NULL,
  `referred_on` date DEFAULT NULL,
  `hidden` enum('Y','N') DEFAULT 'N',
  `approved` enum('Y','N') DEFAULT 'N',
  `rejected` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`,`referee`),
  KEY `member_referees_ibfk_2` (`referee`),
  CONSTRAINT `member_referees_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `member_referees_ibfk_2` FOREIGN KEY (`referee`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_saved_jobs`
--

DROP TABLE IF EXISTS `member_saved_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_saved_jobs` (
  `member` varchar(100) NOT NULL,
  `job` int(11) NOT NULL,
  `saved_on` datetime DEFAULT NULL,
  PRIMARY KEY (`member`,`job`),
  KEY `member_saved_jobs_ibfk_2` (`job`),
  CONSTRAINT `member_saved_jobs_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `member_saved_jobs_ibfk_2` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_sessions`
--

DROP TABLE IF EXISTS `member_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_sessions` (
  `member` varchar(100) NOT NULL,
  `sha1` char(40) NOT NULL,
  `last_login` date DEFAULT NULL,
  PRIMARY KEY (`member`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_unsubscribes`
--

DROP TABLE IF EXISTS `member_unsubscribes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_unsubscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `unsubscribed_on` datetime NOT NULL,
  `reason` mediumtext,
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  CONSTRAINT `member_unsubscribes_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `members` (
  `email_addr` varchar(100) NOT NULL,
  `password` char(32) NOT NULL,
  `forget_password_question` int(11) NOT NULL,
  `forget_password_answer` mediumtext NOT NULL,
  `phone_num` varchar(20) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(20) NOT NULL,
  `country` char(2) NOT NULL,
  `premium` enum('Y','N') DEFAULT 'N',
  `active` enum('Y','N','S') DEFAULT 'N',
  `like_newsletter` enum('Y','N') DEFAULT 'Y',
  `filter_jobs` enum('Y','N') DEFAULT 'N',
  `invites_available` int(10) unsigned DEFAULT '0',
  `primary_industry` int(10) unsigned DEFAULT NULL,
  `secondary_industry` int(10) unsigned DEFAULT NULL,
  `tertiary_industry` int(10) unsigned DEFAULT NULL,
  `joined_on` date DEFAULT NULL,
  `recommender` varchar(100) DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `checked_profile` enum('Y','N') DEFAULT 'N',
  `remarks` varchar(255) DEFAULT NULL,
  `individual_headhunter` enum('Y','N') DEFAULT 'N',
  `headhunter_reward_percentage` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`email_addr`),
  KEY `firstname` (`firstname`,`lastname`),
  KEY `lastname` (`lastname`),
  KEY `members_ibfk_1` (`country`),
  KEY `primary_industry` (`primary_industry`),
  KEY `secondary_industry` (`secondary_industry`),
  KEY `recommender` (`recommender`),
  KEY `added_by` (`added_by`),
  KEY `tertiary_industry` (`tertiary_industry`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_2` FOREIGN KEY (`primary_industry`) REFERENCES `industries` (`id`),
  CONSTRAINT `members_ibfk_3` FOREIGN KEY (`secondary_industry`) REFERENCES `industries` (`id`),
  CONSTRAINT `members_ibfk_4` FOREIGN KEY (`recommender`) REFERENCES `recommenders` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_5` FOREIGN KEY (`added_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_6` FOREIGN KEY (`tertiary_industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_questions`
--

DROP TABLE IF EXISTS `password_reset_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `privileged_referral_buffers`
--

DROP TABLE IF EXISTS `privileged_referral_buffers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privileged_referral_buffers` (
  `member` varchar(100) NOT NULL,
  `referee` varchar(100) NOT NULL,
  `job` int(11) NOT NULL,
  `resume` int(11) NOT NULL,
  `referred_on` datetime DEFAULT NULL,
  `referee_acknowledged_on` datetime DEFAULT NULL,
  `member_confirmed_on` datetime DEFAULT NULL,
  `member_read_resume_on` datetime DEFAULT NULL,
  `testimony` mediumtext,
  PRIMARY KEY (`member`,`referee`,`job`),
  KEY `referee` (`referee`),
  KEY `job` (`job`),
  KEY `resume` (`resume`),
  CONSTRAINT `privileged_referral_buffers_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `privileged_referral_buffers_ibfk_2` FOREIGN KEY (`referee`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `privileged_referral_buffers_ibfk_3` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `privileged_referral_buffers_ibfk_4` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recommender_industries`
--

DROP TABLE IF EXISTS `recommender_industries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recommender_industries` (
  `recommender` varchar(100) NOT NULL,
  `industry` int(10) unsigned NOT NULL,
  PRIMARY KEY (`recommender`,`industry`),
  KEY `industry` (`industry`),
  CONSTRAINT `recommender_industries_ibfk_1` FOREIGN KEY (`recommender`) REFERENCES `recommenders` (`email_addr`),
  CONSTRAINT `recommender_industries_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recommender_tokens`
--

DROP TABLE IF EXISTS `recommender_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recommender_tokens` (
  `referral` int(11) NOT NULL,
  `recommender` varchar(100) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `presented_on` datetime NOT NULL,
  PRIMARY KEY (`referral`,`recommender`),
  KEY `recommender` (`recommender`),
  CONSTRAINT `recommender_tokens_ibfk_1` FOREIGN KEY (`referral`) REFERENCES `referrals` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `recommender_tokens_ibfk_2` FOREIGN KEY (`recommender`) REFERENCES `recommenders` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recommenders`
--

DROP TABLE IF EXISTS `recommenders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recommenders` (
  `email_addr` varchar(100) NOT NULL,
  `phone_num` varchar(20) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` datetime DEFAULT NULL,
  PRIMARY KEY (`email_addr`),
  KEY `added_by` (`added_by`),
  CONSTRAINT `recommenders_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recommenders_members`
--

DROP TABLE IF EXISTS `recommenders_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recommenders_members` (
  `recommender` varchar(100) NOT NULL,
  `member` varchar(100) NOT NULL,
  PRIMARY KEY (`recommender`,`member`),
  KEY `member` (`member`),
  CONSTRAINT `recommenders_members_ibfk_1` FOREIGN KEY (`recommender`) REFERENCES `recommenders` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `recommenders_members_ibfk_2` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_replacements`
--

DROP TABLE IF EXISTS `referral_replacements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_replacements` (
  `id` int(11) NOT NULL,
  `referral` int(11) NOT NULL,
  `job` int(11) NOT NULL,
  `created_on` datetime DEFAULT NULL,
  `used_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_requests`
--

DROP TABLE IF EXISTS `referral_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `referrer` varchar(100) NOT NULL,
  `job` int(11) NOT NULL,
  `resume` int(11) NOT NULL,
  `requested_on` datetime NOT NULL,
  `referrer_acknowledged_on` datetime DEFAULT NULL,
  `acknowledged_by_others_on` datetime DEFAULT NULL,
  `rejected` enum('Y','N') DEFAULT 'N',
  `requests_counted` tinyint(1) DEFAULT '0',
  `referrer_read_resume_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`,`referrer`,`job`),
  KEY `referrer` (`referrer`),
  KEY `job` (`job`),
  KEY `resume` (`resume`),
  CONSTRAINT `referral_requests_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `referral_requests_ibfk_2` FOREIGN KEY (`referrer`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `referral_requests_ibfk_3` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `referral_requests_ibfk_4` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_rewards`
--

DROP TABLE IF EXISTS `referral_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referral` int(11) NOT NULL,
  `reward` decimal(9,2) unsigned NOT NULL DEFAULT '0.00',
  `paid_on` datetime NOT NULL,
  `paid_through` enum('CHQ','IBT','CSH','CDB') DEFAULT 'IBT',
  `bank` int(11) DEFAULT NULL,
  `cheque` varchar(10) DEFAULT NULL,
  `receipt` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referral_rewards_ibfk_1` (`referral`),
  KEY `referral_rewards_ibfk_2` (`bank`),
  CONSTRAINT `referral_rewards_ibfk_1` FOREIGN KEY (`referral`) REFERENCES `referrals` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `referral_rewards_ibfk_2` FOREIGN KEY (`bank`) REFERENCES `member_banks` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_token_rewards`
--

DROP TABLE IF EXISTS `referral_token_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_token_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referral` int(11) NOT NULL,
  `token` decimal(9,2) unsigned NOT NULL DEFAULT '0.00',
  `paid_on` datetime NOT NULL,
  `paid_through` enum('CHQ','IBT','CSH','CDB') DEFAULT 'IBT',
  `bank` int(11) DEFAULT NULL,
  `cheque` varchar(10) DEFAULT NULL,
  `receipt` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referral_token_rewards_ibfk_1` (`referral`),
  KEY `referral_token_rewards_ibfk_2` (`bank`),
  CONSTRAINT `referral_token_rewards_ibfk_1` FOREIGN KEY (`referral`) REFERENCES `referrals` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `referral_token_rewards_ibfk_2` FOREIGN KEY (`bank`) REFERENCES `member_banks` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `referee` varchar(100) NOT NULL,
  `job` int(11) NOT NULL,
  `resume` int(11) DEFAULT NULL,
  `referred_on` datetime NOT NULL,
  `referee_acknowledged_on` datetime DEFAULT NULL,
  `member_confirmed_on` datetime DEFAULT NULL,
  `referee_acknowledged_others_on` datetime DEFAULT NULL,
  `referee_confirmed_hired_on` datetime DEFAULT NULL,
  `employer_agreed_terms_on` datetime DEFAULT NULL,
  `member_read_resume_on` datetime DEFAULT NULL,
  `shortlisted_on` datetime DEFAULT NULL,
  `employment_contract_received_on` datetime DEFAULT NULL,
  `employed_on` datetime DEFAULT NULL,
  `work_commence_on` date DEFAULT NULL,
  `salary_per_annum` decimal(9,2) DEFAULT '1.00',
  `total_reward` decimal(9,2) DEFAULT NULL,
  `total_token_reward` decimal(9,2) DEFAULT NULL,
  `testimony` mediumtext,
  `employer_remarks` mediumtext,
  `employer_rejected_on` datetime DEFAULT NULL,
  `employer_removed_on` datetime DEFAULT NULL,
  `referee_rejected_on` datetime DEFAULT NULL,
  `member_rejected_on` datetime DEFAULT NULL,
  `used_suggested` enum('Y','N') DEFAULT 'N',
  `guarantee_expire_on` datetime DEFAULT NULL,
  `replacement_authorized_on` datetime DEFAULT NULL,
  `replaced_on` datetime DEFAULT NULL,
  `replaced_referral` int(11) DEFAULT NULL,
  `response_counted` tinyint(1) DEFAULT '0',
  `view_counted` tinyint(1) DEFAULT '0',
  `reward_counted` tinyint(1) DEFAULT '0',
  `request_counted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`,`referee`,`job`),
  KEY `referrals_ibfk_1` (`member`),
  KEY `referrals_ibfk_2` (`referee`),
  KEY `referrals_ibfk_3` (`job`),
  KEY `referrals_ibfk_4` (`resume`),
  KEY `replaced_referral` (`replaced_referral`),
  CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referee`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE,
  CONSTRAINT `referrals_ibfk_3` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `referrals_ibfk_4` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `referrals_ibfk_5` FOREIGN KEY (`replaced_referral`) REFERENCES `referrals` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referrer_invites`
--

DROP TABLE IF EXISTS `referrer_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrer_invites` (
  `referrer_email` varchar(100) NOT NULL,
  `member` varchar(100) NOT NULL,
  `invited_on` datetime DEFAULT NULL,
  `signed_up_on` datetime DEFAULT NULL,
  `requested_job` int(11) NOT NULL,
  `resume` int(11) NOT NULL,
  PRIMARY KEY (`referrer_email`,`member`,`requested_job`),
  KEY `member` (`member`),
  KEY `requested_job` (`requested_job`),
  KEY `resume` (`resume`),
  CONSTRAINT `referrer_invites_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`),
  CONSTRAINT `referrer_invites_ibfk_2` FOREIGN KEY (`requested_job`) REFERENCES `jobs` (`id`),
  CONSTRAINT `referrer_invites_ibfk_3` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_educations`
--

DROP TABLE IF EXISTS `resume_educations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_educations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resume` int(11) NOT NULL,
  `qualification` mediumtext NOT NULL,
  `completed_on` year(4) NOT NULL,
  `institution` varchar(50) NOT NULL,
  `country` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `instituition` (`institution`),
  KEY `resume_educations_ibfk_1` (`resume`),
  CONSTRAINT `resume_educations_ibfk_1` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_index`
--

DROP TABLE IF EXISTS `resume_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_index` (
  `resume` int(11) NOT NULL,
  `member` varchar(100) NOT NULL,
  `qualification` mediumtext,
  `cover_note` mediumtext,
  `skill` mediumtext,
  `technical_skill` varchar(50) DEFAULT NULL,
  `work_summary` mediumtext,
  `file_text` longtext,
  PRIMARY KEY (`resume`,`member`),
  FULLTEXT KEY `qualification` (`qualification`),
  FULLTEXT KEY `cover_note` (`cover_note`),
  FULLTEXT KEY `skill` (`skill`),
  FULLTEXT KEY `work_summary` (`work_summary`),
  FULLTEXT KEY `technical_skill` (`technical_skill`),
  FULLTEXT KEY `file_text` (`file_text`),
  FULLTEXT KEY `criteria_match` (`cover_note`,`skill`,`technical_skill`,`qualification`,`work_summary`,`file_text`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_skills`
--

DROP TABLE IF EXISTS `resume_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resume` int(11) NOT NULL,
  `skill` mediumtext,
  PRIMARY KEY (`id`),
  KEY `resume_skills_ibfk_1` (`resume`),
  CONSTRAINT `resume_skills_ibfk_1` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_technical_skills`
--

DROP TABLE IF EXISTS `resume_technical_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_technical_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resume` int(11) NOT NULL,
  `technical_skill` varchar(50) DEFAULT NULL,
  `level` enum('A','B','C') DEFAULT 'B',
  PRIMARY KEY (`id`),
  KEY `resume_technical_skills_ibfk_1` (`resume`),
  CONSTRAINT `resume_technical_skills_ibfk_1` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resume_work_experiences`
--

DROP TABLE IF EXISTS `resume_work_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resume_work_experiences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resume` int(11) NOT NULL,
  `industry` int(10) unsigned NOT NULL,
  `from` varchar(7) DEFAULT NULL,
  `to` varchar(7) DEFAULT NULL,
  `place` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `work_summary` mediumtext,
  `reason_for_leaving` mediumtext,
  PRIMARY KEY (`id`),
  KEY `industry` (`industry`),
  KEY `place` (`place`,`role`),
  KEY `resume_work_experiences_ibfk_1` (`resume`),
  CONSTRAINT `resume_work_experiences_ibfk_1` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `resume_work_experiences_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resumes`
--

DROP TABLE IF EXISTS `resumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `private` enum('Y','N') DEFAULT 'Y',
  `modified_on` date NOT NULL,
  `cover_note` mediumtext,
  `file_name` varchar(100) DEFAULT NULL,
  `file_hash` char(6) DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `deleted` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `resumes_ibfk_1` (`member`),
  CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search_log`
--

DROP TABLE IF EXISTS `search_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_log` (
  `from_ip_address` varchar(15) DEFAULT NULL,
  `from_country` char(2) DEFAULT NULL,
  `keywords` mediumtext,
  `filter_industry` int(10) unsigned DEFAULT NULL,
  `filter_country_code` char(2) DEFAULT NULL,
  `searched_on` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `security_clearances`
--

DROP TABLE IF EXISTS `security_clearances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_clearances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photos_create` tinyint(1) DEFAULT '0',
  `photos_remove` tinyint(1) DEFAULT '0',
  `photos_update` tinyint(1) DEFAULT '0',
  `photos_view` tinyint(1) DEFAULT '0',
  `invoices_create` tinyint(1) DEFAULT '0',
  `invoices_remove` tinyint(1) DEFAULT '0',
  `invoices_update` tinyint(1) DEFAULT '0',
  `invoices_view` tinyint(1) DEFAULT '0',
  `rewards_create` tinyint(1) DEFAULT '0',
  `rewards_remove` tinyint(1) DEFAULT '0',
  `rewards_update` tinyint(1) DEFAULT '0',
  `rewards_view` tinyint(1) DEFAULT '0',
  `employers_create` tinyint(1) DEFAULT '0',
  `employers_remove` tinyint(1) DEFAULT '0',
  `employers_update` tinyint(1) DEFAULT '0',
  `employers_view` tinyint(1) DEFAULT '0',
  `referrals_create` tinyint(1) DEFAULT '0',
  `referrals_remove` tinyint(1) DEFAULT '0',
  `referrals_update` tinyint(1) DEFAULT '0',
  `referrals_view` tinyint(1) DEFAULT '0',
  `members_create` tinyint(1) DEFAULT '0',
  `members_remove` tinyint(1) DEFAULT '0',
  `members_update` tinyint(1) DEFAULT '0',
  `members_view` tinyint(1) DEFAULT '0',
  `admin_employers_create` tinyint(1) DEFAULT '0',
  `admin_employers_remove` tinyint(1) DEFAULT '0',
  `admin_employers_update` tinyint(1) DEFAULT '0',
  `admin_employers_view` tinyint(1) DEFAULT '0',
  `replacements_create` tinyint(1) DEFAULT '0',
  `replacements_remove` tinyint(1) DEFAULT '0',
  `replacements_update` tinyint(1) DEFAULT '0',
  `replacements_view` tinyint(1) DEFAULT '0',
  `refer_requests_create` tinyint(1) DEFAULT '0',
  `refer_requests_remove` tinyint(1) DEFAULT '0',
  `refer_requests_update` tinyint(1) DEFAULT '0',
  `refer_requests_view` tinyint(1) DEFAULT '0',
  `prs_resumes_privileged_create` tinyint(1) DEFAULT '0',
  `prs_resumes_privileged_remove` tinyint(1) DEFAULT '0',
  `prs_resumes_privileged_update` tinyint(1) DEFAULT '0',
  `prs_resumes_privileged_view` tinyint(1) DEFAULT '0',
  `prs_resumes_create` tinyint(1) DEFAULT '0',
  `prs_resumes_remove` tinyint(1) DEFAULT '0',
  `prs_resumes_update` tinyint(1) DEFAULT '0',
  `prs_resumes_view` tinyint(1) DEFAULT '0',
  `prs_recommenders_create` tinyint(1) DEFAULT '0',
  `prs_recommenders_remove` tinyint(1) DEFAULT '0',
  `prs_recommenders_update` tinyint(1) DEFAULT '0',
  `prs_recommenders_view` tinyint(1) DEFAULT '0',
  `prs_referrals_create` tinyint(1) DEFAULT '0',
  `prs_referrals_remove` tinyint(1) DEFAULT '0',
  `prs_referrals_update` tinyint(1) DEFAULT '0',
  `prs_referrals_view` tinyint(1) DEFAULT '0',
  `prs_mailing_lists_create` tinyint(1) DEFAULT '0',
  `prs_mailing_lists_remove` tinyint(1) DEFAULT '0',
  `prs_mailing_lists_update` tinyint(1) DEFAULT '0',
  `prs_mailing_lists_view` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seeds`
--

DROP TABLE IF EXISTS `seeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seeds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `seed` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1001 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_contributed_resumes`
--

DROP TABLE IF EXISTS `users_contributed_resumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_contributed_resumes` (
  `job_id` int(11) NOT NULL,
  `referrer_email_addr` varchar(100) NOT NULL,
  `candidate_email_addr` varchar(100) NOT NULL,
  `referrer_phone_num` varchar(20) DEFAULT NULL,
  `referrer_firstname` varchar(50) DEFAULT NULL,
  `referrer_lastname` varchar(50) DEFAULT NULL,
  `referrer_zip` varchar(20) DEFAULT NULL,
  `referrer_country` char(2) DEFAULT NULL,
  `candidate_phone_num` varchar(20) DEFAULT NULL,
  `candidate_firstname` varchar(50) DEFAULT NULL,
  `candidate_lastname` varchar(50) DEFAULT NULL,
  `candidate_zip` varchar(20) DEFAULT NULL,
  `candidate_country` char(2) DEFAULT NULL,
  `file_hash` char(10) DEFAULT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_name` varchar(100) DEFAULT NULL,
  `file_size` int(10) unsigned DEFAULT NULL,
  `added_on` datetime DEFAULT NULL,
  PRIMARY KEY (`job_id`,`referrer_email_addr`,`candidate_email_addr`),
  KEY `referrer_country` (`referrer_country`),
  KEY `candidate_country` (`candidate_country`),
  CONSTRAINT `users_contributed_resumes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  CONSTRAINT `users_contributed_resumes_ibfk_2` FOREIGN KEY (`referrer_country`) REFERENCES `countries` (`country_code`),
  CONSTRAINT `users_contributed_resumes_ibfk_3` FOREIGN KEY (`candidate_country`) REFERENCES `countries` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `visitors`
--

DROP TABLE IF EXISTS `visitors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visitors` (
  `ip_address` varchar(15) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `visited_on` datetime DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `http_referer` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'yel2_dev'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-26 17:48:06
