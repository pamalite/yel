-- MySQL dump 10.13  Distrib 5.1.37, for apple-darwin9.5.0 (i386)
--
-- Host: localhost    Database: yel3_dev
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
  `mailing_country` char(2) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_ibfk_1` (`country`),
  KEY `branches_ibfk_2` (`currency`),
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `branches_ibfk_2` FOREIGN KEY (`currency`) REFERENCES `currencies` (`symbol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Yellow Elevator Sdn Bhd','2008-01-02','1-12B-9, Suntech @ Penang Cybercity, \nLintang Mayang Pasir 3','Penang','11950','MY', 'MY', '+604 640 6363','+604 640 6366','MYR'),
(2,'Yellow Elevator Pty Ltd','2009-01-02','Suite 3, 22 Council Street, Hawthorn East','Victoria','3123','AU', 'AU','+613 9882 7164','+613 9882 9792','AUD'),(3,'Yellow Elevator Sdn Bhd','2010-02-12','1-12B-9, Suntech @ Penang Cybercity, \nLintang Mayang Pasir 3','Penang','11950','SG', 'SG','+604 640 6363','+604 640 6366','SGD');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;


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
-- Dumping data for table `business_groups`
--

LOCK TABLES `business_groups` WRITE;
/*!40000 ALTER TABLE `business_groups` DISABLE KEYS */;
INSERT INTO `business_groups` VALUES (1,'Information Technology',1,NULL),(2,'Sales and Marketing',2,NULL),(3,'Administration',3,NULL),(4,'Resume Processing',4,NULL);
/*!40000 ALTER TABLE `business_groups` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES ('AC','Ascension Island',NULL,'N'),('AD','Andorra',NULL,'N'),('AE','United Arab Emirates',NULL,'N'),('AF','Afghanistan',NULL,'N'),('AG','Antigua and Barbuda',NULL,'N'),('AI','Anguilla',NULL,'N'),('AL','Albania',NULL,'N'),('AM','Armenia',NULL,'N'),('AN','Netherlands Antilles',NULL,'N'),('AO','Angola',NULL,'N'),('AQ','Antarctica',NULL,'N'),('AR','Argentina',NULL,'N'),('AS','American Samoa',NULL,'N'),('AT','Austria',NULL,'N'),('AU','Australia',NULL,'N'),('AW','Aruba',NULL,'N'),('AX','Aland Islands',NULL,'N'),('AZ','Azerbaijan',NULL,'N'),('BA','Bosnia and Herzegovina',NULL,'N'),('BB','Barbados',NULL,'N'),('BD','Bangladesh',NULL,'N'),('BE','Belgium',NULL,'N'),('BF','Burkina Faso',NULL,'N'),('BG','Bulgaria',NULL,'N'),('BH','Bahrain',NULL,'N'),('BI','Burundi',NULL,'N'),('BJ','Benin',NULL,'N'),('BM','Bermuda',NULL,'N'),('BN','Brunei Darussalam',NULL,'N'),('BO','Bolivia',NULL,'N'),('BR','Brazil',NULL,'N'),('BS','Bahamas',NULL,'N'),('BT','Bhutan',NULL,'N'),('BV','Bouvet Island',NULL,'N'),('BW','Botswana',NULL,'N'),('BY','Belarus',NULL,'N'),('BZ','Belize',NULL,'N'),('CA','Canada',NULL,'N'),('CC','Cocos (Keeling) Islands',NULL,'N'),('CD','Congo, Democratic Republic',NULL,'N'),('CF','Central African Republic',NULL,'N'),('CG','Congo',NULL,'N'),('CH','Switzerland',NULL,'N'),('CI','Cote D\'Ivoire (Ivory Coast)\r\n',NULL,'N'),('CK','Cook Islands',NULL,'N'),('CL','Chile',NULL,'N'),('CM','Cameroon',NULL,'N'),('CN','China',NULL,'N'),('CO','Colombia',NULL,'N'),('CR','Costa Rica',NULL,'N'),('CS','Czechoslovakia (former)',NULL,'N'),('CU','Cuba',NULL,'N'),('CV','Cape Verde',NULL,'N'),('CX','Christmas Island',NULL,'N'),('CY','Cyprus',NULL,'N'),('CZ','Czech Republic',NULL,'N'),('DE','Germany',NULL,'N'),('DJ','Djibouti',NULL,'N'),('DK','Denmark',NULL,'N'),('DM','Dominica',NULL,'N'),('DO','Dominican Republic',NULL,'N'),('DZ','Algeria',NULL,'N'),('EC','Ecuador',NULL,'N'),('EE','Estonia',NULL,'N'),('EG','Egypt',NULL,'N'),('EH','Western Sahara',NULL,'N'),('ER','Eritrea',NULL,'N'),('ES','Spain',NULL,'N'),('ET','Ethiopia',NULL,'N'),('FI','Finland',NULL,'N'),('FJ','Fiji',NULL,'N'),('FK','Falkland Islands (Malvinas)',NULL,'N'),('FM','Micronesia',NULL,'N'),('FO','Faroe Islands',NULL,'N'),('FR','France',NULL,'N'),('FX','France, Metropolitan',NULL,'N'),('GA','Gabon',NULL,'N'),('GB','Great Britain (UK)',NULL,'N'),('GD','Grenada',NULL,'N'),('GE','Georgia',NULL,'N'),('GF','French Guiana',NULL,'N'),('GH','Ghana',NULL,'N'),('GI','Gibraltar',NULL,'N'),('GL','Greenland',NULL,'N'),('GM','Gambia',NULL,'N'),('GN','Guinea',NULL,'N'),('GP','Guadeloupe',NULL,'N'),('GQ','Equatorial Guinea',NULL,'N'),('GR','Greece',NULL,'N'),('GS','S. Georgia and S. Sandwich Isls.',NULL,'N'),('GT','Guatemala',NULL,'N'),('GU','Guam',NULL,'N'),('GW','Guinea-Bissau',NULL,'N'),('GY','Guyana',NULL,'N'),('HK','Hong Kong',NULL,'N'),('HM','Heard and McDonald Islands',NULL,'N'),('HN','Honduras',NULL,'N'),('HR','Croatia (Hrvatska)',NULL,'N'),('HT','Haiti',NULL,'N'),('HU','Hungary',NULL,'N'),('ID','Indonesia',NULL,'N'),('IE','Ireland',NULL,'N'),('IL','Israel',NULL,'N'),('IM','Isle of Man',NULL,'N'),('IN','India',NULL,'N'),('IO','British Indian Ocean Territory',NULL,'N'),('IQ','Iraq',NULL,'N'),('IR','Iran',NULL,'N'),('IS','Iceland',NULL,'N'),('IT','Italy',NULL,'N'),('JE','Jersey',NULL,'N'),('JM','Jamaica',NULL,'N'),('JO','Jordan',NULL,'N'),('JP','Japan',NULL,'N'),('KE','Kenya',NULL,'N'),('KG','Kyrgyzstan',NULL,'N'),('KH','Cambodia',NULL,'N'),('KI','Kiribati',NULL,'N'),('KM','Comoros',NULL,'N'),('KN','Saint Kitts and Nevis',NULL,'N'),('KP','Korea (North)',NULL,'N'),('KR','Korea (South)',NULL,'N'),('KW','Kuwait',NULL,'N'),('KY','Cayman Islands',NULL,'N'),('KZ','Kazakhstan',NULL,'N'),('LA','Laos',NULL,'N'),('LB','Lebanon',NULL,'N'),('LC','Saint Lucia',NULL,'N'),('LI','Liechtenstein',NULL,'N'),('LK','Sri Lanka',NULL,'N'),('LR','Liberia',NULL,'N'),('LS','Lesotho',NULL,'N'),('LT','Lithuania',NULL,'N'),('LU','Luxembourg',NULL,'N'),('LV','Latvia',NULL,'N'),('LY','Libya',NULL,'N'),('MA','Morocco',NULL,'N'),('MC','Monaco',NULL,'N'),('MD','Moldova',NULL,'N'),('ME','Montenegro',NULL,'N'),('MG','Madagascar',NULL,'N'),('MH','Marshall Islands',NULL,'N'),('MK','F.Y.R.O.M. (Macedonia)',NULL,'N'),('ML','Mali',NULL,'N'),('MM','Myanmar',NULL,'N'),('MN','Mongolia',NULL,'N'),('MO','Macau',NULL,'N'),('MP','Northern Mariana Islands',NULL,'N'),('MQ','Martinique',NULL,'N'),('MR','Mauritania',NULL,'N'),('MS','Montserrat',NULL,'N'),('MT','Malta',NULL,'N'),('MU','Mauritius',NULL,'N'),('MV','Maldives',NULL,'N'),('MW','Malawi',NULL,'N'),('MX','Mexico',NULL,'N'),('MY','Malaysia','MY','Y'),('MZ','Mozambique',NULL,'N'),('NA','Namibia',NULL,'N'),('NC','New Caledonia',NULL,'N'),('NE','Niger',NULL,'N'),('NF','Norfolk Island',NULL,'N'),('NG','Nigeria',NULL,'N'),('NI','Nicaragua',NULL,'N'),('NL','Netherlands',NULL,'N'),('NO','Norway',NULL,'N'),('NP','Nepal',NULL,'N'),('NR','Nauru',NULL,'N'),('NT','Neutral Zone',NULL,'N'),('NU','Niue',NULL,'N'),('NZ','New Zealand (Aotearoa)',NULL,'N'),('OM','Oman',NULL,'N'),('PA','Panama',NULL,'N'),('PE','Peru',NULL,'N'),('PF','French Polynesia',NULL,'N'),('PG','Papua New Guinea',NULL,'N'),('PH','Philippines',NULL,'N'),('PK','Pakistan',NULL,'N'),('PL','Poland',NULL,'N'),('PM','St. Pierre and Miquelon',NULL,'N'),('PN','Pitcairn',NULL,'N'),('PR','Puerto Rico',NULL,'N'),('PS','Palestinian Territory, Occupied',NULL,'N'),('PT','Portugal',NULL,'N'),('PW','Palau',NULL,'N'),('PY','Paraguay',NULL,'N'),('QA','Qatar',NULL,'N'),('RE','Reunion',NULL,'N'),('RO','Romania',NULL,'N'),('RS','Serbia',NULL,'N'),('RU','Russian Federation',NULL,'N'),('RW','Rwanda',NULL,'N'),('SA','Saudi Arabia',NULL,'N'),('SB','Solomon Islands',NULL,'N'),('SC','Seychelles',NULL,'N'),('SD','Sudan',NULL,'N'),('SE','Sweden',NULL,'N'),('SG','Singapore','SG','Y'),('SH','St. Helena',NULL,'N'),('SI','Slovenia',NULL,'N'),('SJ','Svalbard and Jan Mayen Islands',NULL,'N'),('SK','Slovak Republic',NULL,'N'),('SL','Sierra Leone',NULL,'N'),('SM','San Marino',NULL,'N'),('SN','Senegal',NULL,'N'),('SO','Somalia',NULL,'N'),('SR','Suriname',NULL,'N'),('ST','Sao Tome and Principe',NULL,'N'),('SU','USSR (former)',NULL,'N'),('SV','El Salvador',NULL,'N'),('SY','Syria',NULL,'N'),('SZ','Swaziland',NULL,'N'),('TC','Turks and Caicos Islands',NULL,'N'),('TD','Chad',NULL,'N'),('TF','French Southern Territories',NULL,'N'),('TG','Togo',NULL,'N'),('TH','Thailand',NULL,'N'),('TJ','Tajikistan',NULL,'N'),('TK','Tokelau',NULL,'N'),('TM','Turkmenistan',NULL,'N'),('TN','Tunisia',NULL,'N'),('TO','Tonga',NULL,'N'),('TP','East Timor',NULL,'N'),('TR','Turkey',NULL,'N'),('TT','Trinidad and Tobago',NULL,'N'),('TV','Tuvalu',NULL,'N'),('TW','Taiwan',NULL,'N'),('TZ','Tanzania',NULL,'N'),('UA','Ukraine',NULL,'N'),('UG','Uganda',NULL,'N'),('UK','United Kingdom',NULL,'N'),('UM','US Minor Outlying Islands',NULL,'N'),('US','United States',NULL,'N'),('UY','Uruguay',NULL,'N'),('UZ','Uzbekistan',NULL,'N'),('VA','Vatican City State (Holy See)',NULL,'N'),('VC','Saint Vincent & the Grenadines',NULL,'N'),('VE','Venezuela',NULL,'N'),('VG','British Virgin Islands',NULL,'N'),('VI','Virgin Islands (U.S.)',NULL,'N'),('VN','Vietnam',NULL,'N'),('VU','Vanuatu',NULL,'N'),('WF','Wallis and Futuna Islands',NULL,'N'),('WS','Samoa',NULL,'N'),('YE','Yemen',NULL,'N'),('YT','Mayotte',NULL,'N'),('YU','Yugoslavia (former)',NULL,'N'),('ZA','South Africa',NULL,'N'),('ZM','Zambia',NULL,'N'),('ZR','Zaire',NULL,'N'),('ZW','Zimbabwe',NULL,'N');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;


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
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES ('AUD','AU','Australian Dollar','1.3215'),('HKD','HK','Hong Kong Dollar','10.2692'),('MYR','MY','Malaysian Ringgit','4.0565'),('SGD','SG','Singapore Dollar','1.7066'),('USD','US','US Dollar','1.3213');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;


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
  KEY `employees_ibfk_2` (`branch`),
  KEY `employees_ibfk_3` (`created_by`),
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`branch`) REFERENCES `branches` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'2008-01-02','5d9c68c6c50ed3d02a2fcf54f63993b6','Ken S\'ng','Wong','3, Gerbang Sungai Ara 3  Bayan Lepas','Penang','11900','MY','1234567','4568900','pamalite@gmail.com','pamalite@yahoo.com','Chief Technical Officer',1,1,'2008-01-02 10:29:45'),(2,'2009-02-24','1fe949c554f4121a85301b277b63c55a','Sui Cheng','Wong','1-12B-9, SUNTECH @ Penang Cybercity,\nLintang Mayang Pasir 3,','Penang','11950','MY','604-640 6363','012-481-3968','sui.cheng.wong@yellowelevator.com','sui.cheng.wong@gmail.com','Chief Executive Officer',1,1,'2009-02-24 10:45:45'),(3,'2009-04-20','4c923a3bdb5d7d19ccfc189760c1b9af','Raymond','Ooi','2 Persiaran Cantonment','Penang','10350','MY',' 61410.435766',' 6012.4862717',NULL,'raymond777@gmail.com',NULL,2,1,'2009-04-20 11:17:44'),(4,'2009-10-01','9ce21d8f3992d89a325aa9dcf520a591','Jeff','Kuan','23 Jalan Pantai Jerjak 5','Penang','11950','MY','0146047460','0146047460','jeff.kuan@yellowelevator.com','jeff.kuan@yellowelevator.com','Account Manager',1,1,'2009-09-23 00:19:20'),(5,'2009-02-24','1fe949c554f4121a85301b277b63c55a','Sui Cheng','Wong','1-12B-9, SUNTECH @ Penang Cybercity,\nLintang Mayang Pasir 3','Penang','11950','SG','604-640 6363','012-481-3968','sui.cheng.wong@yellowelevator.com','sui.cheng.wong@gmail.com','Branch Manager',3,1,'2010-02-12 12:32:55'),(6,'2010-02-12','9ce21d8f3992d89a325aa9dcf520a591','Shireen','Lam',NULL,'Penang','11950','SG',NULL,NULL,NULL,NULL,'Account Manager',3,1,'2010-02-12 12:36:05'),(7,'2010-02-12','9ce21d8f3992d89a325aa9dcf520a591','Shireen','Lam',NULL,'Penang','11950','MY',NULL,NULL,NULL,NULL,'Account Manager',1,1,'2010-02-17 04:04:24'),(8,'2011-01-06','9ce21d8f3992d89a325aa9dcf520a591','Ann','Chuan',NULL,NULL,'11900','MY',NULL,NULL,'ann.chuan@yellowelevator.com',NULL,NULL,1,1,'2011-01-06 11:03:04'),(9,'2011-01-06','9ce21d8f3992d89a325aa9dcf520a591','Sofea','Alea',NULL,NULL,'11900','MY',NULL,NULL,'ann.chuan@yellowelevator.com',NULL,NULL,1,1,'2011-01-06 11:03:15');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;


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
-- Dumping data for table `employees_groups`
--

LOCK TABLES `employees_groups` WRITE;
/*!40000 ALTER TABLE `employees_groups` DISABLE KEYS */;
INSERT INTO `employees_groups` VALUES (1,1),(2,1),(5,1),(3,2),(4,2),(6,2),(7,2),(3,3),(4,3),(6,3),(7,3),(1,4),(2,4),(3,4),(4,4),(5,4),(6,4),(7,4);
/*!40000 ALTER TABLE `employees_groups` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `hr_contacts` varchar(255) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip` varchar(50) NOT NULL,
  `country` char(2) NOT NULL,
  `website_url` varchar(100) DEFAULT NULL,
  `logo_path` varchar(100) DEFAULT NULL,
  `about` mediumtext,
  `joined_on` date DEFAULT NULL,
  `active` enum('Y','N') DEFAULT 'Y',
  `is_new` tinyint(1) DEFAULT '1',
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
-- Dumping data for table `industries`
--

LOCK TABLES `industries` WRITE;
/*!40000 ALTER TABLE `industries` DISABLE KEYS */;
INSERT INTO `industries` VALUES (1,NULL,'Accounting / Finance',NULL),(2,1,'General/Cost Acct',NULL),(3,1,'Banking/Financial',NULL),(4,1,'Audit & Taxation',NULL),(5,1,'Finance/Investment',NULL),(6,NULL,'Admin / HR',NULL),(7,6,'Clerical/Admin',NULL),(8,6,'Human Resources',NULL),(9,6,'Secretarial',NULL),(10,6,'Top Management',NULL),(11,NULL,'Arts / Media / Comm',NULL),(12,11,'Arts/Creative Design',NULL),(13,11,'Advertising',NULL),(14,11,'Public Relations',NULL),(15,11,'Entertainment',NULL),(16,NULL,'Building / Construction',NULL),(17,16,'Civil/Construction',NULL),(18,16,'Architect/Interior',NULL),(19,16,'Quantity Survey',NULL),(20,16,'Property/Real Estate',NULL),(21,NULL,'Computer / IT',NULL),(22,21,'Software',NULL),(23,21,'Network/Sys/DB',NULL),(24,21,'Hardware',NULL),(25,NULL,'Education / Training',NULL),(26,25,'Education',NULL),(27,25,'Training & Dev.',NULL),(28,NULL,'Engineering',NULL),(29,28,'Mechanical/Automotive',NULL),(30,28,'Electrical',NULL),(31,28,'Electronics',NULL),(32,28,'Industrial Eng',NULL),(33,28,'Oil/Gas',NULL),(34,28,'Chemical Eng',NULL),(35,28,'Environmental',NULL),(36,28,'Others',NULL),(37,NULL,'Healthcare',NULL),(38,37,'Pharmacy',NULL),(39,37,'Nurse/Medical Support',NULL),(40,37,'Doctor/Diagnosis',NULL),(41,NULL,'Hotel / Restaurant',NULL),(42,41,'F & B',NULL),(43,41,'Hotel/Tourism',NULL),(44,NULL,'Manufacturing',NULL),(45,44,'Manufacturing',NULL),(46,44,'Purchasing',NULL),(47,44,'Quality Control',NULL),(48,44,'Maintenance',NULL),(49,44,'Process Control',NULL),(50,NULL,'Sales / Marketing',NULL),(51,50,'Marketing',NULL),(52,50,'Sales-Retail/General',NULL),(53,50,'Sales-Eng/Tech/IT',NULL),(54,50,'Sales-Corporate',NULL),(55,50,'Telesales/Telemarketing',NULL),(56,50,'Sales-Financial Services',NULL),(57,50,'Merchandising',NULL),(58,NULL,'Sciences',NULL),(59,58,'Chemistry',NULL),(60,58,'Food Tech/Nutritionist',NULL),(61,58,'Science & Tech',NULL),(62,58,'Actuarial/Statistics',NULL),(63,58,'Biotechnology',NULL),(64,58,'Agriculture',NULL),(65,58,'Geology',NULL),(66,58,'Aviation',NULL),(67,NULL,'Services',NULL),(68,67,'Customer Service',NULL),(69,67,'Logistics/Supply Chain',NULL),(70,67,'Tech & Helpdesk Support',NULL),(71,67,'Law/Legal Services',NULL),(72,67,'Personal Care',NULL),(73,67,'Armed Forces',NULL),(74,67,'Social Services',NULL),(75,NULL,'Others',NULL),(76,NULL,'General Work',NULL),(77,NULL,'Journalist/Editors',NULL),(78,NULL,'Publishing',NULL);
/*!40000 ALTER TABLE `industries` ENABLE KEYS */;
UNLOCK TABLES;


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
  `alternate_employer` varchar(100) DEFAULT NULL,
  `contact_carbon_copy` varchar(255) DEFAULT NULL,
  `industry` int(10) unsigned NOT NULL,
  `country` char(2) NOT NULL,
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
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `jobs_ibfk_1` (`employer`),
  KEY `jobs_ibfk_2` (`industry`),
  KEY `jobs_ibfk_3` (`country`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`employer`) REFERENCES `employers` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_3` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_index`
--

DROP TABLE IF EXISTS `member_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_index` (
  `member` varchar(100) NOT NULL,
  `notes` mediumtext,
  `seeking` mediumtext,
  `reason_for_leaving` mediumtext,
  PRIMARY KEY (`member`),
  FULLTEXT KEY `notes` (`notes`),
  FULLTEXT KEY `seeking` (`seeking`),
  FULLTEXT KEY `reason_for_leaving` (`reason_for_leaving`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_industries`
--

DROP TABLE IF EXISTS `member_industries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_industries` (
  `member` varchar(100) NOT NULL,
  `industry` int(10) unsigned NOT NULL,
  PRIMARY KEY (`member`,`industry`),
  KEY `industry` (`industry`),
  CONSTRAINT `member_industries_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`),
  CONSTRAINT `member_industries_ibfk_2` FOREIGN KEY (`industry`) REFERENCES `industries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
-- Table structure for table `member_job_profiles`
--

DROP TABLE IF EXISTS `member_job_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_job_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member` varchar(100) NOT NULL,
  `specialization` int(10) unsigned DEFAULT NULL,
  `position_title` varchar(50) DEFAULT NULL,
  `position_superior_title` varchar(50) DEFAULT NULL,
  `organization_size` int(10) unsigned DEFAULT '1',
  `work_from` date DEFAULT NULL,
  `work_to` date DEFAULT NULL,
  `employer` varchar(50) DEFAULT NULL,
  `employer_description` varchar(50) DEFAULT NULL,
  `employer_specialization` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `member` (`member`),
  KEY `specialization` (`specialization`),
  KEY `employer_specialization` (`employer_specialization`),
  CONSTRAINT `member_job_profiles_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON DELETE CASCADE,
  CONSTRAINT `member_job_profiles_ibfk_2` FOREIGN KEY (`specialization`) REFERENCES `industries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `member_job_profiles_ibfk_3` FOREIGN KEY (`employer_specialization`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_jobs`
--

DROP TABLE IF EXISTS `member_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applied_on` datetime NOT NULL,
  `member` varchar(100) NOT NULL,
  `referrer` varchar(100) DEFAULT NULL,
  `job` int(11) NOT NULL,
  `resume` int(11) DEFAULT NULL,
  `progress_notes` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `member` (`member`,`referrer`,`job`,`resume`),
  KEY `job` (`job`),
  KEY `referrer` (`referrer`),
  KEY `resume` (`resume`),
  CONSTRAINT `member_jobs_ibfk_1` FOREIGN KEY (`job`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `member_jobs_ibfk_2` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON DELETE CASCADE,
  CONSTRAINT `member_jobs_ibfk_3` FOREIGN KEY (`referrer`) REFERENCES `members` (`email_addr`) ON DELETE CASCADE,
  CONSTRAINT `member_jobs_ibfk_4` FOREIGN KEY (`resume`) REFERENCES `resumes` (`id`) ON DELETE CASCADE
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_referees`
--

DROP TABLE IF EXISTS `member_referees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `member_referees` (
  `member` varchar(100) NOT NULL,
  `referee` varchar(100) NOT NULL,
  PRIMARY KEY (`member`,`referee`),
  KEY `referee` (`referee`),
  CONSTRAINT `member_referees_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`),
  CONSTRAINT `member_referees_ibfk_2` FOREIGN KEY (`referee`) REFERENCES `members` (`email_addr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `zip` varchar(20) DEFAULT NULL,
  `country` char(2) DEFAULT NULL,
  `premium` enum('Y','N') DEFAULT 'N',
  `active` enum('Y','N','S') DEFAULT 'N',
  `like_newsletter` enum('Y','N') DEFAULT 'Y',
  `filter_jobs` enum('Y','N') DEFAULT 'N',
  `invites_available` int(10) unsigned DEFAULT '0',
  `joined_on` date DEFAULT NULL,
  `updated_on` date DEFAULT NULL,
  `added_by` int(11) DEFAULT NULL,
  `checked_profile` enum('Y','N') DEFAULT 'N',
  `individual_headhunter` enum('Y','N') DEFAULT 'N',
  `headhunter_reward_percentage` int(10) unsigned DEFAULT '0',
  `hrm_gender` enum('male','female') DEFAULT NULL,
  `hrm_ethnicity` varchar(50) DEFAULT NULL,
  `hrm_birthdate` date DEFAULT NULL,
  `total_work_years` int(11) DEFAULT '0',
  `is_active_seeking_job` tinyint(1) DEFAULT '0',
  `seeking` mediumtext,
  `expected_salary_currency` char(3) DEFAULT 'MYR',
  `expected_salary` int(10) unsigned DEFAULT '0',
  `expected_salary_end` int(10) unsigned DEFAULT '0',
  `can_travel_relocate` enum('Y','N') DEFAULT NULL,
  `reason_for_leaving` mediumtext,
  `current_position` mediumtext,
  `current_salary_currency` char(3) DEFAULT 'MYR',
  `current_salary` int(10) unsigned DEFAULT '0',
  `current_salary_end` int(10) unsigned DEFAULT '0',
  `notice_period` int(10) unsigned DEFAULT '0',
  `citizenship` char(2) DEFAULT NULL,
  `preferred_job_location_1` char(2) DEFAULT NULL,
  `preferred_job_location_2` char(2) DEFAULT NULL,
  PRIMARY KEY (`email_addr`),
  KEY `firstname` (`firstname`,`lastname`),
  KEY `lastname` (`lastname`),
  KEY `members_ibfk_1` (`country`),
  KEY `added_by` (`added_by`),
  KEY `citizenship` (`citizenship`),
  KEY `expected_salary_currency` (`expected_salary_currency`),
  KEY `current_salary_currency` (`current_salary_currency`),
  KEY `preferred_job_location_1` (`preferred_job_location_1`),
  KEY `preferred_job_location_2` (`preferred_job_location_2`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_10` FOREIGN KEY (`preferred_job_location_1`) REFERENCES `countries` (`country_code`),
  CONSTRAINT `members_ibfk_11` FOREIGN KEY (`preferred_job_location_2`) REFERENCES `countries` (`country_code`),
  CONSTRAINT `members_ibfk_5` FOREIGN KEY (`added_by`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_6` FOREIGN KEY (`citizenship`) REFERENCES `countries` (`country_code`),
  CONSTRAINT `members_ibfk_8` FOREIGN KEY (`expected_salary_currency`) REFERENCES `currencies` (`symbol`),
  CONSTRAINT `members_ibfk_9` FOREIGN KEY (`current_salary_currency`) REFERENCES `currencies` (`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES 
('team.au@yellowelevator.com','5d9c68c6c50ed3d02a2fcf54f63993b6',1,'The Usual Password','0','Yellow','Elevator','Not applicable','Not applicable','0','AU','N','Y','N','N',10,'2009-08-08','2009-08-08',NULL,'N','N',0,NULL,NULL,NULL,NULL,0,NULL,'AUD',0,0,NULL,NULL,NULL,'AUD',0,0,0,'AU',NULL,NULL),
('team.my@yellowelevator.com','3bdec2ce68e18126167dfa0778438f03',1,'The Usual Password','0','Yellow','Elevator','Not applicable','Not applicable','0','MY','N','Y','N','N',10,'2009-08-08','2009-08-08',NULL,'N','N',0,NULL,NULL,NULL,NULL,0,NULL,'MYR',0,0,NULL,NULL,NULL,'MYR',0,0,0,'MY',NULL,NULL),
('team.sg@yellowelevator.com','3bdec2ce68e18126167dfa0778438f03',1,'The Usual Password','0','Yellow','Elevator','Not applicable','Not applicable','0','SG','N','Y','N','N',10,'2009-08-08','2009-08-08',NULL,'N','N',0,NULL,NULL,NULL,NULL,0,NULL,'SGD',0,0,NULL,NULL,NULL,'SGD',0,0,0,'SG',NULL,NULL);
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;


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
-- Dumping data for table `password_reset_questions`
--

LOCK TABLES `password_reset_questions` WRITE;
/*!40000 ALTER TABLE `password_reset_questions` DISABLE KEYS */;
INSERT INTO `password_reset_questions` VALUES (1,'What is my mother\'s maiden name?'),(2,'What is my pet\'s name?');
/*!40000 ALTER TABLE `password_reset_questions` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Table structure for table `referral_buffers`
--

DROP TABLE IF EXISTS `referral_buffers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_buffers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requested_on` datetime DEFAULT NULL,
  `referrer_email` varchar(100) DEFAULT NULL,
  `referrer_phone` varchar(20) DEFAULT NULL,
  `referrer_name` varchar(100) DEFAULT NULL,
  `candidate_email` varchar(100) DEFAULT NULL,
  `candidate_phone` varchar(20) DEFAULT NULL,
  `candidate_name` varchar(100) DEFAULT NULL,
  `progress_notes` mediumtext,
  `job` int(11) DEFAULT NULL,
  `existing_resume_id` int(11) DEFAULT NULL,
  `resume_file_name` varchar(100) DEFAULT NULL,
  `resume_file_hash` char(6) DEFAULT NULL,
  `resume_file_type` varchar(50) DEFAULT NULL,
  `resume_file_size` int(11) DEFAULT NULL,
  `resume_file_text` longtext,
  `needs_indexing` tinyint(1) DEFAULT '0',
  `is_yel_uploaded` tinyint(1) DEFAULT '0',
  `referrer_remarks` mediumtext,
  `current_position` varchar(50) DEFAULT NULL,
  `current_employer` varchar(50) DEFAULT NULL,
  `deleted_by_referrer` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `referrer_name` (`referrer_name`),
  FULLTEXT KEY `candidate_name` (`candidate_name`),
  FULLTEXT KEY `resume_file_text` (`resume_file_text`),
  FULLTEXT KEY `criteria_match` (`resume_file_text`,`referrer_name`,`candidate_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_index`
--

DROP TABLE IF EXISTS `referral_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_index` (
  `referral` int(11) NOT NULL,
  `testimony` mediumtext,
  PRIMARY KEY (`referral`),
  FULLTEXT KEY `testimony` (`testimony`),
  FULLTEXT KEY `notes_2` (`testimony`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `gift` varchar(255) DEFAULT NULL,
  `paid_on` datetime NOT NULL,
  `paid_through` enum('CHQ','IBT','CSH','CDB') DEFAULT NULL,
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
  `need_approval` enum('Y','N') DEFAULT 'N',
  `rating` int(1) DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
  `is_yel_uploaded` tinyint(1) DEFAULT '0',
  `needs_indexing` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `resumes_ibfk_1` (`member`),
  CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`member`) REFERENCES `members` (`email_addr`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
-- Dumping data for table `security_clearances`
--

LOCK TABLES `security_clearances` WRITE;
/*!40000 ALTER TABLE `security_clearances` DISABLE KEYS */;
INSERT INTO `security_clearances` VALUES (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),(2,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),(3,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),(4,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1);
/*!40000 ALTER TABLE `security_clearances` ENABLE KEYS */;
UNLOCK TABLES;


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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
-- Dumping routines for database 'yel3_dev'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-01-06 22:16:36
