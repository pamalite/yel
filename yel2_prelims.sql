-- MySQL dump 10.13  Distrib 5.1.30, for apple-darwin9.5.0 (i386)
--
-- Host: localhost    Database: yel2_dev
-- ------------------------------------------------------
-- Server version	5.1.30

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Yellow Elevator Sdn Bhd','2008-01-02','1-12B-9, Suntech @ Penang Cybercity, \nLintang Mayang Pasir 3','Penang','11950','MY','+604 640 6363','+604 640 6366','MYR'),(2,'Yellow Elevator Pte Ltd','2009-01-02','Unknown Street','Unknown City','00000','AU','',NULL,'AUD');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_groups`
--

DROP TABLE IF EXISTS `business_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `business_groups`
--

LOCK TABLES `business_groups` WRITE;
/*!40000 ALTER TABLE `business_groups` DISABLE KEYS */;
INSERT INTO `business_groups` VALUES (1,'Information Technology',1,NULL),(2,'Sales and Marketing',2,NULL),(3,'Administration',3,NULL);
/*!40000 ALTER TABLE `business_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `countries` (
  `country_code` char(2) NOT NULL,
  `country` varchar(200) DEFAULT NULL,
  `branch_country_code` char(2) DEFAULT NULL,
  `show_in_list` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `countries`
--

LOCK TABLES `countries` WRITE;
/*!40000 ALTER TABLE `countries` DISABLE KEYS */;
INSERT INTO `countries` VALUES ('AC','Ascension Island',NULL,'N'),('AD','Andorra',NULL,'N'),('AE','United Arab Emirates',NULL,'N'),('AF','Afghanistan',NULL,'N'),('AG','Antigua and Barbuda',NULL,'N'),('AI','Anguilla',NULL,'N'),('AL','Albania',NULL,'N'),('AM','Armenia',NULL,'N'),('AN','Netherlands Antilles',NULL,'N'),('AO','Angola',NULL,'N'),('AQ','Antarctica',NULL,'N'),('AR','Argentina',NULL,'N'),('AS','American Samoa',NULL,'N'),('AT','Austria',NULL,'N'),('AU','Australia',NULL,'N'),('AW','Aruba',NULL,'N'),('AX','Aland Islands',NULL,'N'),('AZ','Azerbaijan',NULL,'N'),('BA','Bosnia and Herzegovina',NULL,'N'),('BB','Barbados',NULL,'N'),('BD','Bangladesh',NULL,'N'),('BE','Belgium',NULL,'N'),('BF','Burkina Faso',NULL,'N'),('BG','Bulgaria',NULL,'N'),('BH','Bahrain',NULL,'N'),('BI','Burundi',NULL,'N'),('BJ','Benin',NULL,'N'),('BM','Bermuda',NULL,'N'),('BN','Brunei Darussalam',NULL,'N'),('BO','Bolivia',NULL,'N'),('BR','Brazil',NULL,'N'),('BS','Bahamas',NULL,'N'),('BT','Bhutan',NULL,'N'),('BV','Bouvet Island',NULL,'N'),('BW','Botswana',NULL,'N'),('BY','Belarus',NULL,'N'),('BZ','Belize',NULL,'N'),('CA','Canada',NULL,'N'),('CC','Cocos (Keeling) Islands',NULL,'N'),('CD','Congo, Democratic Republic',NULL,'N'),('CF','Central African Republic',NULL,'N'),('CG','Congo',NULL,'N'),('CH','Switzerland',NULL,'N'),('CI','Cote D\'Ivoire (Ivory Coast)\r\n',NULL,'N'),('CK','Cook Islands',NULL,'N'),('CL','Chile',NULL,'N'),('CM','Cameroon',NULL,'N'),('CN','China',NULL,'N'),('CO','Colombia',NULL,'N'),('CR','Costa Rica',NULL,'N'),('CS','Czechoslovakia (former)',NULL,'N'),('CU','Cuba',NULL,'N'),('CV','Cape Verde',NULL,'N'),('CX','Christmas Island',NULL,'N'),('CY','Cyprus',NULL,'N'),('CZ','Czech Republic',NULL,'N'),('DE','Germany',NULL,'N'),('DJ','Djibouti',NULL,'N'),('DK','Denmark',NULL,'N'),('DM','Dominica',NULL,'N'),('DO','Dominican Republic',NULL,'N'),('DZ','Algeria',NULL,'N'),('EC','Ecuador',NULL,'N'),('EE','Estonia',NULL,'N'),('EG','Egypt',NULL,'N'),('EH','Western Sahara',NULL,'N'),('ER','Eritrea',NULL,'N'),('ES','Spain',NULL,'N'),('ET','Ethiopia',NULL,'N'),('FI','Finland',NULL,'N'),('FJ','Fiji',NULL,'N'),('FK','Falkland Islands (Malvinas)',NULL,'N'),('FM','Micronesia',NULL,'N'),('FO','Faroe Islands',NULL,'N'),('FR','France',NULL,'N'),('FX','France, Metropolitan',NULL,'N'),('GA','Gabon',NULL,'N'),('GB','Great Britain (UK)',NULL,'N'),('GD','Grenada',NULL,'N'),('GE','Georgia',NULL,'N'),('GF','French Guiana',NULL,'N'),('GH','Ghana',NULL,'N'),('GI','Gibraltar',NULL,'N'),('GL','Greenland',NULL,'N'),('GM','Gambia',NULL,'N'),('GN','Guinea',NULL,'N'),('GP','Guadeloupe',NULL,'N'),('GQ','Equatorial Guinea',NULL,'N'),('GR','Greece',NULL,'N'),('GS','S. Georgia and S. Sandwich Isls.',NULL,'N'),('GT','Guatemala',NULL,'N'),('GU','Guam',NULL,'N'),('GW','Guinea-Bissau',NULL,'N'),('GY','Guyana',NULL,'N'),('HK','Hong Kong',NULL,'N'),('HM','Heard and McDonald Islands',NULL,'N'),('HN','Honduras',NULL,'N'),('HR','Croatia (Hrvatska)',NULL,'N'),('HT','Haiti',NULL,'N'),('HU','Hungary',NULL,'N'),('ID','Indonesia',NULL,'N'),('IE','Ireland',NULL,'N'),('IL','Israel',NULL,'N'),('IM','Isle of Man',NULL,'N'),('IN','India',NULL,'N'),('IO','British Indian Ocean Territory',NULL,'N'),('IQ','Iraq',NULL,'N'),('IR','Iran',NULL,'N'),('IS','Iceland',NULL,'N'),('IT','Italy',NULL,'N'),('JE','Jersey',NULL,'N'),('JM','Jamaica',NULL,'N'),('JO','Jordan',NULL,'N'),('JP','Japan',NULL,'N'),('KE','Kenya',NULL,'N'),('KG','Kyrgyzstan',NULL,'N'),('KH','Cambodia',NULL,'N'),('KI','Kiribati',NULL,'N'),('KM','Comoros',NULL,'N'),('KN','Saint Kitts and Nevis',NULL,'N'),('KP','Korea (North)',NULL,'N'),('KR','Korea (South)',NULL,'N'),('KW','Kuwait',NULL,'N'),('KY','Cayman Islands',NULL,'N'),('KZ','Kazakhstan',NULL,'N'),('LA','Laos',NULL,'N'),('LB','Lebanon',NULL,'N'),('LC','Saint Lucia',NULL,'N'),('LI','Liechtenstein',NULL,'N'),('LK','Sri Lanka',NULL,'N'),('LR','Liberia',NULL,'N'),('LS','Lesotho',NULL,'N'),('LT','Lithuania',NULL,'N'),('LU','Luxembourg',NULL,'N'),('LV','Latvia',NULL,'N'),('LY','Libya',NULL,'N'),('MA','Morocco',NULL,'N'),('MC','Monaco',NULL,'N'),('MD','Moldova',NULL,'N'),('ME','Montenegro',NULL,'N'),('MG','Madagascar',NULL,'N'),('MH','Marshall Islands',NULL,'N'),('MK','F.Y.R.O.M. (Macedonia)',NULL,'N'),('ML','Mali',NULL,'N'),('MM','Myanmar',NULL,'N'),('MN','Mongolia',NULL,'N'),('MO','Macau',NULL,'N'),('MP','Northern Mariana Islands',NULL,'N'),('MQ','Martinique',NULL,'N'),('MR','Mauritania',NULL,'N'),('MS','Montserrat',NULL,'N'),('MT','Malta',NULL,'N'),('MU','Mauritius',NULL,'N'),('MV','Maldives',NULL,'N'),('MW','Malawi',NULL,'N'),('MX','Mexico',NULL,'N'),('MY','Malaysia','MY','Y'),('MZ','Mozambique',NULL,'N'),('NA','Namibia',NULL,'N'),('NC','New Caledonia',NULL,'N'),('NE','Niger',NULL,'N'),('NF','Norfolk Island',NULL,'N'),('NG','Nigeria',NULL,'N'),('NI','Nicaragua',NULL,'N'),('NL','Netherlands',NULL,'N'),('NO','Norway',NULL,'N'),('NP','Nepal',NULL,'N'),('NR','Nauru',NULL,'N'),('NT','Neutral Zone',NULL,'N'),('NU','Niue',NULL,'N'),('NZ','New Zealand (Aotearoa)',NULL,'N'),('OM','Oman',NULL,'N'),('PA','Panama',NULL,'N'),('PE','Peru',NULL,'N'),('PF','French Polynesia',NULL,'N'),('PG','Papua New Guinea',NULL,'N'),('PH','Philippines',NULL,'N'),('PK','Pakistan',NULL,'N'),('PL','Poland',NULL,'N'),('PM','St. Pierre and Miquelon',NULL,'N'),('PN','Pitcairn',NULL,'N'),('PR','Puerto Rico',NULL,'N'),('PS','Palestinian Territory, Occupied',NULL,'N'),('PT','Portugal',NULL,'N'),('PW','Palau',NULL,'N'),('PY','Paraguay',NULL,'N'),('QA','Qatar',NULL,'N'),('RE','Reunion',NULL,'N'),('RO','Romania',NULL,'N'),('RS','Serbia',NULL,'N'),('RU','Russian Federation',NULL,'N'),('RW','Rwanda',NULL,'N'),('SA','Saudi Arabia',NULL,'N'),('SB','Solomon Islands',NULL,'N'),('SC','Seychelles',NULL,'N'),('SD','Sudan',NULL,'N'),('SE','Sweden',NULL,'N'),('SG','Singapore',NULL,'N'),('SH','St. Helena',NULL,'N'),('SI','Slovenia',NULL,'N'),('SJ','Svalbard and Jan Mayen Islands',NULL,'N'),('SK','Slovak Republic',NULL,'N'),('SL','Sierra Leone',NULL,'N'),('SM','San Marino',NULL,'N'),('SN','Senegal',NULL,'N'),('SO','Somalia',NULL,'N'),('SR','Suriname',NULL,'N'),('ST','Sao Tome and Principe',NULL,'N'),('SU','USSR (former)',NULL,'N'),('SV','El Salvador',NULL,'N'),('SY','Syria',NULL,'N'),('SZ','Swaziland',NULL,'N'),('TC','Turks and Caicos Islands',NULL,'N'),('TD','Chad',NULL,'N'),('TF','French Southern Territories',NULL,'N'),('TG','Togo',NULL,'N'),('TH','Thailand',NULL,'N'),('TJ','Tajikistan',NULL,'N'),('TK','Tokelau',NULL,'N'),('TM','Turkmenistan',NULL,'N'),('TN','Tunisia',NULL,'N'),('TO','Tonga',NULL,'N'),('TP','East Timor',NULL,'N'),('TR','Turkey',NULL,'N'),('TT','Trinidad and Tobago',NULL,'N'),('TV','Tuvalu',NULL,'N'),('TW','Taiwan',NULL,'N'),('TZ','Tanzania',NULL,'N'),('UA','Ukraine',NULL,'N'),('UG','Uganda',NULL,'N'),('UK','United Kingdom',NULL,'N'),('UM','US Minor Outlying Islands',NULL,'N'),('US','United States',NULL,'N'),('UY','Uruguay',NULL,'N'),('UZ','Uzbekistan',NULL,'N'),('VA','Vatican City State (Holy See)',NULL,'N'),('VC','Saint Vincent & the Grenadines',NULL,'N'),('VE','Venezuela',NULL,'N'),('VG','British Virgin Islands',NULL,'N'),('VI','Virgin Islands (U.S.)',NULL,'N'),('VN','Vietnam',NULL,'N'),('VU','Vanuatu',NULL,'N'),('WF','Wallis and Futuna Islands',NULL,'N'),('WS','Samoa',NULL,'N'),('YE','Yemen',NULL,'N'),('YT','Mayotte',NULL,'N'),('YU','Yugoslavia (former)',NULL,'N'),('ZA','South Africa',NULL,'N'),('ZM','Zambia',NULL,'N'),('ZR','Zaire',NULL,'N'),('ZW','Zimbabwe',NULL,'N');
/*!40000 ALTER TABLE `countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `currencies` (
  `symbol` char(3) NOT NULL,
  `country_code` char(2) NOT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `rate` decimal(6,4) NOT NULL DEFAULT '1.0000',
  PRIMARY KEY (`symbol`,`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES ('AUD','AU','Australian Dollar','1.9404'),('HKD','HK','Hong Kong Dollar','9.9411'),('MYR','MY','Malaysian Ringgit','4.6221'),('SGD','SG','Singapore Dollar','1.9334'),('USD','US','US Dollar','1.2823');
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_sessions`
--

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'2008-01-02','5d9c68c6c50ed3d02a2fcf54f63993b6','Ken S\'ng','Wong','3, Gerbang Sungai Ara 3  Bayan Lepas','Penang','11900','MY','1234567','4568900','pamalite@gmail.com','pamalite@yahoo.com','Chief Technical Officer',1,1,'2008-01-02 10:29:45'),(2,'2009-02-24','5d9c68c6c50ed3d02a2fcf54f63993b6','Sui Cheng','Wong','Taman Jesselton','Penang','11975','MY','1234567','4568900','sui.cheng.wong@yellowelevator.com','sui.cheng.wong@gmail.com','Chief Executive Officer',1,1,'2009-02-24 10:45:45');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees_groups`
--

DROP TABLE IF EXISTS `employees_groups`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `employees_groups` (
  `employee` int(11) NOT NULL,
  `business_group` int(11) NOT NULL,
  PRIMARY KEY (`employee`,`business_group`),
  KEY `employees_groups_ibfk_2` (`business_group`),
  CONSTRAINT `employees_groups_ibfk_1` FOREIGN KEY (`employee`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `employees_groups_ibfk_2` FOREIGN KEY (`business_group`) REFERENCES `business_groups` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `employees_groups`
--

LOCK TABLES `employees_groups` WRITE;
/*!40000 ALTER TABLE `employees_groups` DISABLE KEYS */;
INSERT INTO `employees_groups` VALUES (1,1),(2,2);
/*!40000 ALTER TABLE `employees_groups` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `industries`
--

DROP TABLE IF EXISTS `industries`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `industries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL,
  `description` tinytext,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `industries_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `industries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `industries`
--

LOCK TABLES `industries` WRITE;
/*!40000 ALTER TABLE `industries` DISABLE KEYS */;
INSERT INTO `industries` VALUES (1,NULL,'Accounting / Finance',NULL),(2,1,'General/Cost Acct',NULL),(3,1,'Banking/Financial',NULL),(4,1,'Audit & Taxation',NULL),(5,1,'Finance/Investment',NULL),(6,NULL,'Admin / HR',NULL),(7,6,'Clerical/Admin',NULL),(8,6,'Human Resources',NULL),(9,6,'Secretarial',NULL),(10,6,'Top Management',NULL),(11,NULL,'Arts / Media / Comm',NULL),(12,11,'Arts/Creative Design',NULL),(13,11,'Advertising',NULL),(14,11,'Public Relations',NULL),(15,11,'Entertainment',NULL),(16,NULL,'Building / Construction',NULL),(17,16,'Civil/Construction',NULL),(18,16,'Architect/Interior',NULL),(19,16,'Quantity Survey',NULL),(20,16,'Property/Real Estate',NULL),(21,NULL,'Computer / IT',NULL),(22,21,'Software',NULL),(23,21,'Network/Sys/DB',NULL),(24,21,'Hardware',NULL),(25,NULL,'Education / Training',NULL),(26,25,'Education',NULL),(27,25,'Training & Dev.',NULL),(28,NULL,'Engineering',NULL),(29,28,'Mechanical/Automotive',NULL),(30,28,'Electrical',NULL),(31,28,'Electronics',NULL),(32,28,'Industrial Eng',NULL),(33,28,'Oil/Gas',NULL),(34,28,'Chemical Eng',NULL),(35,28,'Environmental',NULL),(36,28,'Others',NULL),(37,NULL,'Healthcare',NULL),(38,37,'Pharmacy',NULL),(39,37,'Nurse/Medical Support',NULL),(40,37,'Doctor/Diagnosis',NULL),(41,NULL,'Hotel / Restaurant',NULL),(42,41,'F & B',NULL),(43,41,'Hotel/Tourism',NULL),(44,NULL,'Manufacturing',NULL),(45,44,'Manufacturing',NULL),(46,44,'Purchasing',NULL),(47,44,'Quality Control',NULL),(48,44,'Maintenance',NULL),(49,44,'Process Control',NULL),(50,NULL,'Sales / Marketing',NULL),(51,50,'Marketing',NULL),(52,50,'Sales-Retail/General',NULL),(53,50,'Sales-Eng/Tech/IT',NULL),(54,50,'Sales-Corporate',NULL),(55,50,'Telesales/Telemarketing',NULL),(56,50,'Sales-Financial Services',NULL),(57,50,'Merchandising',NULL),(58,NULL,'Sciences',NULL),(59,58,'Chemistry',NULL),(60,58,'Food Tech/Nutritionist',NULL),(61,58,'Science & Tech',NULL),(62,58,'Actuarial/Statistics',NULL),(63,58,'Biotechnology',NULL),(64,58,'Agriculture',NULL),(65,58,'Geology',NULL),(66,58,'Aviation',NULL),(67,NULL,'Services',NULL),(68,67,'Customer Service',NULL),(69,67,'Logistics/Supply Chain',NULL),(70,67,'Tech & Helpdesk Support',NULL),(71,67,'Law/Legal Services',NULL),(72,67,'Personal Care',NULL),(73,67,'Armed Forces',NULL),(74,67,'Social Services',NULL),(75,NULL,'Others',NULL),(76,NULL,'General Work',NULL),(77,NULL,'Journalist/Editors',NULL),(78,NULL,'Publishing',NULL);
/*!40000 ALTER TABLE `industries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_clearances`
--

DROP TABLE IF EXISTS `security_clearances`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
  `refer_requests_remove` tinyint(1) DEFAULT '0',
  `refer_requests_update` tinyint(1) DEFAULT '0',
  `refer_requests_view` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

  --
  -- Dumping data for table `security_clearances`
  --

  LOCK TABLES `security_clearances` WRITE;
  /*!40000 ALTER TABLE `security_clearances` DISABLE KEYS */;
  INSERT INTO `security_clearances` VALUES (1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),(2,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,1),(3,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,0,0,0,0);
/*!40000 ALTER TABLE `security_clearances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_questions`
--

DROP TABLE IF EXISTS `password_reset_questions`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `password_reset_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `password_reset_questions`
--

LOCK TABLES `password_reset_questions` WRITE;
/*!40000 ALTER TABLE `password_reset_questions` DISABLE KEYS */;
INSERT INTO `password_reset_questions` VALUES (1,'What is my mother\'s maiden name?'),(2,'What is my pet\'s name?');
/*!40000 ALTER TABLE `password_reset_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `members` (
  `email_addr` varchar(100) NOT NULL,
  `password` char(32) NOT NULL,
  `personal_id` varchar(20) NOT NULL DEFAULT '0',
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
  `invites_available` int(10) unsigned DEFAULT '0',
  `primary_industry` int(10) unsigned DEFAULT NULL,
  `secondary_industry` int(10) unsigned DEFAULT NULL,
  `joined_on` date DEFAULT NULL,
  PRIMARY KEY (`email_addr`),
  UNIQUE KEY `personal_id` (`email_addr`,`personal_id`),
  KEY `firstname` (`firstname`,`lastname`),
  KEY `lastname` (`lastname`),
  KEY `members_ibfk_1` (`country`),
  KEY `primary_industry` (`primary_industry`),
  KEY `secondary_industry` (`secondary_industry`),
  CONSTRAINT `members_ibfk_1` FOREIGN KEY (`country`) REFERENCES `countries` (`country_code`) ON UPDATE CASCADE,
  CONSTRAINT `members_ibfk_2` FOREIGN KEY (`primary_industry`) REFERENCES `industries` (`id`),
  CONSTRAINT `members_ibfk_3` FOREIGN KEY (`secondary_industry`) REFERENCES `industries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES ('initial@yellowelevator.com','3bdec2ce68e18126167dfa0778438f03','0',1,'The Usual Password','0','Yellow','Elevator','Not applicable','Not applicable','0','MY','N','Y','N',10,1,1,'2009-08-08');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_banks`
--

DROP TABLE IF EXISTS `member_banks`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `member_banks`
--

LOCK TABLES `member_banks` WRITE;
/*!40000 ALTER TABLE `member_banks` DISABLE KEYS */;
INSERT INTO `member_banks` VALUES (1,'initial@yellowelevator.com','OCBC','123456789012','Y');
/*!40000 ALTER TABLE `member_banks` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-02-23 11:12:29
