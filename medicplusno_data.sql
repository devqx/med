-- MySQL dump 10.13  Distrib 5.7.19, for Linux (x86_64)
--
-- Host: localhost    Database: medicplus_main
-- ------------------------------------------------------
-- Server version	5.7.19-0ubuntu0.16.04.1

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
-- Table structure for table `admission_config`
--

DROP TABLE IF EXISTS `admission_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admission_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alert`
--

DROP TABLE IF EXISTS `alert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `message` varchar(200) DEFAULT NULL,
  `read_by` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=313 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `allergen_category`
--

DROP TABLE IF EXISTS `allergen_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `allergen_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antenatal_assessment`
--

DROP TABLE IF EXISTS `antenatal_assessment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antenatal_assessment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_user_id` int(11) NOT NULL,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `fundusHeight` float DEFAULT NULL,
  `fhr` float DEFAULT NULL,
  `fetal_lie` enum('Longitudinal','Oblique','Transverse') DEFAULT NULL,
  `fetal_presentation_id` int(11) DEFAULT NULL,
  `fetal_brain_relationship_id` int(11) DEFAULT NULL,
  `comments` text,
  `lab_request_code` varchar(20) DEFAULT NULL,
  `scan_request_code` varchar(20) DEFAULT NULL,
  `nextAppointmentDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antenatal_notes`
--

DROP TABLE IF EXISTS `antenatal_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antenatal_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `antenatal_enrollment_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `type` enum('normal','general_condition','respiratory_system','cardiovascular','vaginal','breast_nipples','abnormalities') NOT NULL DEFAULT 'normal',
  `antenatal_assesment_id` int(11) DEFAULT NULL,
  `entered_on` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `entered_by` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antenatal_package_item`
--

DROP TABLE IF EXISTS `antenatal_package_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antenatal_package_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `type` enum('Lab','Scan','Consultation','Drug','Procedure') DEFAULT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `item_usage` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item` (`package_id`,`item_id`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antenatal_packages`
--

DROP TABLE IF EXISTS `antenatal_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antenatal_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `billing_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointment`
--

DROP TABLE IF EXISTS `appointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `attended_time` datetime DEFAULT NULL,
  `status` enum('Missed','Completed','Cancelled','Scheduled','Active') NOT NULL DEFAULT 'Scheduled',
  `editor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=324 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `appointment_update_trig` AFTER UPDATE ON `appointment`
 FOR EACH ROW INSERT INTO log_appointment (`aid`, `group_id`, `start_time`, `end_time`, `attended_time`, `status`, `editor_id`) VALUES (OLD.id, OLD.group_id, OLD.start_time, OLD.end_time, OLD.attended_time, OLD.status, OLD.editor_id) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `appointment_delete_trig` AFTER DELETE ON `appointment`
 FOR EACH ROW INSERT INTO log_appointment (`aid`, `group_id`, `start_time`, `end_time`, `attended_time`, `status`, `editor_id`, `trig_type`) VALUES (OLD.id, OLD.group_id, OLD.start_time, OLD.end_time, OLD.attended_time, OLD.status, OLD.editor_id, 'Delete') */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `appointment_clinic`
--

DROP TABLE IF EXISTS `appointment_clinic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_clinic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `a_limit` int(11) NOT NULL,
  `queue_type` varchar(75) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointment_group`
--

DROP TABLE IF EXISTS `appointment_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL,
  `type` enum('Surgery','Visit','Meeting','Vaccination','Antenatal') NOT NULL DEFAULT 'Visit',
  `clinic_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT '1',
  `resource_id` int(11) DEFAULT NULL,
  `description` varchar(512) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointment_invitee`
--

DROP TABLE IF EXISTS `appointment_invitee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_invitee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `appointment_resource`
--

DROP TABLE IF EXISTS `appointment_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `approved_queue`
--

DROP TABLE IF EXISTS `approved_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approved_queue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `type` enum('Lab','Imaging','Pharmacy','Ophthalmology','Dentistry','Medical Report') NOT NULL,
  `request_id` int(11) NOT NULL,
  `approved_time` datetime NOT NULL,
  `queue_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=848 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_consulting`
--

DROP TABLE IF EXISTS `arv_consulting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_consulting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `comment` text,
  `create_user_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `next_appointment` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_consulting_data`
--

DROP TABLE IF EXISTS `arv_consulting_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_consulting_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arv_consulting_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `type_data_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_drug`
--

DROP TABLE IF EXISTS `arv_drug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_drug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_drug_data`
--

DROP TABLE IF EXISTS `arv_drug_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_drug_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `arv_drug_id` int(11) NOT NULL,
  `type` enum('ARV Line 1','ARV Line 2','Cotrimoxazole','INH') NOT NULL,
  `dose` varchar(11) NOT NULL,
  `state` enum('active','switched','stopped','interrupted','changed') NOT NULL,
  `prescribed_by` int(11) NOT NULL,
  `date_prescribed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_history`
--

DROP TABLE IF EXISTS `arv_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arv_template_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_history_template`
--

DROP TABLE IF EXISTS `arv_history_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_history_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_history_template_data`
--

DROP TABLE IF EXISTS `arv_history_template_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_history_template_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arv_history_template_id` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `datatype` enum('text','integer','float','model','date','boolean','selection') NOT NULL,
  `relation` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_patient_history`
--

DROP TABLE IF EXISTS `arv_patient_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_patient_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `arv_history_id` int(11) NOT NULL,
  `create_uid` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arv_patient_history_data`
--

DROP TABLE IF EXISTS `arv_patient_history_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `arv_patient_history_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `arv_patient_history_id` int(11) NOT NULL,
  `arv_history_template_data_id` int(11) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attachment_category`
--

DROP TABLE IF EXISTS `attachment_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attachment_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `role_ids` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `audit_log`
--

DROP TABLE IF EXISTS `audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `object` varchar(50) NOT NULL,
  `object_id` int(11) NOT NULL,
  `field` varchar(50) NOT NULL,
  `old_value` text NOT NULL,
  `new_value` text NOT NULL,
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_group`
--

DROP TABLE IF EXISTS `auth_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_group_permissions`
--

DROP TABLE IF EXISTS `auth_group_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_group_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_group_permissions_group_id_permission_id_0cd325b0_uniq` (`group_id`,`permission_id`),
  KEY `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` (`permission_id`),
  CONSTRAINT `auth_group_permissio_permission_id_84c5c92e_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `auth_group_permissions_group_id_b120cbf9_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_permission`
--

DROP TABLE IF EXISTS `auth_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content_type_id` int(11) NOT NULL,
  `codename` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_permission_content_type_id_codename_01ab375a_uniq` (`content_type_id`,`codename`),
  CONSTRAINT `auth_permission_content_type_id_2f476e4b_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=967 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_user`
--

DROP TABLE IF EXISTS `auth_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(128) NOT NULL,
  `last_login` datetime(6) DEFAULT NULL,
  `is_superuser` tinyint(1) NOT NULL,
  `username` varchar(150) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(254) NOT NULL,
  `is_staff` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `date_joined` datetime(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_user_groups`
--

DROP TABLE IF EXISTS `auth_user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_user_groups_user_id_group_id_94350c0c_uniq` (`user_id`,`group_id`),
  KEY `auth_user_groups_group_id_97559544_fk_auth_group_id` (`group_id`),
  CONSTRAINT `auth_user_groups_group_id_97559544_fk_auth_group_id` FOREIGN KEY (`group_id`) REFERENCES `auth_group` (`id`),
  CONSTRAINT `auth_user_groups_user_id_6a12ed8b_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_user_user_permissions`
--

DROP TABLE IF EXISTS `auth_user_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_user_user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_user_user_permissions_user_id_permission_id_14a6b632_uniq` (`user_id`,`permission_id`),
  KEY `auth_user_user_permi_permission_id_1fbb5f2c_fk_auth_perm` (`permission_id`),
  CONSTRAINT `auth_user_user_permi_permission_id_1fbb5f2c_fk_auth_perm` FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`id`),
  CONSTRAINT `auth_user_user_permissions_user_id_a95ead1b_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authorization_code`
--

DROP TABLE IF EXISTS `authorization_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorization_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `status` enum('pending','received','stalled','canceled','expired') NOT NULL DEFAULT 'pending',
  `creator_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `receive_date` datetime DEFAULT NULL,
  `code` varchar(70) DEFAULT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `channel_address` varchar(50) DEFAULT NULL,
  `scheme_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authorization_code_note`
--

DROP TABLE IF EXISTS `authorization_code_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authorization_code_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authorization_code_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authtoken_token`
--

DROP TABLE IF EXISTS `authtoken_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authtoken_token` (
  `key` varchar(40) NOT NULL,
  `created` datetime(6) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `authtoken_token_user_id_35299eff_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `badge`
--

DROP TABLE IF EXISTS `badge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `icon` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bed`
--

DROP TABLE IF EXISTS `bed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `available` tinyint(1) DEFAULT '1',
  `description` varchar(150) NOT NULL COMMENT 'Relevant Description',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bed_charge`
--

DROP TABLE IF EXISTS `bed_charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bed_charge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `in_patient_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `bed_id` int(11) DEFAULT NULL,
  `date_admitted` datetime DEFAULT NULL,
  `stage` int(11) NOT NULL,
  `run_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bills` (
  `bill_id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` datetime DEFAULT NULL,
  `description` text,
  `bill_source_id` int(11) DEFAULT NULL,
  `bill_sub_source_id` int(11) DEFAULT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `transaction_type` enum('credit','debit','discount','refund','reversal','write-off','transfer-credit','transfer-debit','transfer-credit-rev','transfer-debit-rev') NOT NULL DEFAULT 'credit',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `copay` decimal(10,2) DEFAULT NULL,
  `balance` float NOT NULL DEFAULT '0',
  `price_type` enum('selling_price','followUpPrice','theatrePrice','anaesthesiaPrice','surgeonPrice') NOT NULL DEFAULT 'selling_price',
  `discounted` enum('YES','NO') DEFAULT 'NO',
  `discounted_by` int(11) DEFAULT NULL,
  `invoiced` enum('yes','no') DEFAULT 'no',
  `receiver` int(10) unsigned zerofill DEFAULT NULL,
  `auth_code` varchar(70) DEFAULT NULL,
  `reviewed` tinyint(1) NOT NULL DEFAULT '1',
  `transferred` tinyint(1) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  `voucher_id` int(11) DEFAULT NULL,
  `hospid` int(11) DEFAULT NULL,
  `billed_to` int(11) DEFAULT NULL COMMENT 'The scheme responsible for paying the bill',
  `payment_method_id` int(11) DEFAULT NULL,
  `payment_reference` varchar(20) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `cost_centre_id` int(11) DEFAULT NULL,
  `revenue_account_id` int(11) DEFAULT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `insurance_code` varchar(20) DEFAULT NULL,
  `quantity` float NOT NULL DEFAULT '1',
  `unit_price` int(11) NOT NULL DEFAULT '0',
  `encounter_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `cancelled_on` datetime DEFAULT NULL,
  `cancelled_by` int(11) DEFAULT NULL,
  `misc` tinyint(1) NOT NULL DEFAULT '0',
  `bill_active` enum('bill_active','not_active') NOT NULL DEFAULT 'bill_active',
  PRIMARY KEY (`bill_id`),
  KEY `receiver` (`receiver`),
  KEY `billed_to` (`billed_to`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `bills_ibfk_1` (`patient_id`),
  KEY `patient_id` (`patient_id`,`transaction_date`,`bill_source_id`,`in_patient_id`,`transaction_type`,`amount`,`discounted`,`invoiced`,`receiver`,`reviewed`,`billed_to`,`payment_method_id`,`cost_centre_id`),
  CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`receiver`) REFERENCES `staff_directory` (`staffId`),
  CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`billed_to`) REFERENCES `insurance_schemes` (`id`),
  CONSTRAINT `bills_ibfk_4` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3363 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bills_source`
--

DROP TABLE IF EXISTS `bills_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bills_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `block`
--

DROP TABLE IF EXISTS `block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `hospital_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blood_group`
--

DROP TABLE IF EXISTS `blood_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blood_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `body_part`
--

DROP TABLE IF EXISTS `body_part`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `body_part` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `care_team`
--

DROP TABLE IF EXISTS `care_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `care_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `channel`
--

DROP TABLE IF EXISTS `channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `description` varchar(128) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `claim`
--

DROP TABLE IF EXISTS `claim`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `claim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `reason` text,
  `line_ids` text,
  `patient_id` int(11) NOT NULL,
  `signature_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) NOT NULL,
  `type` enum('op','ip') NOT NULL,
  `status` enum('SIGNED','OPEN') DEFAULT 'OPEN',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinic`
--

DROP TABLE IF EXISTS `clinic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinic` (
  `clinicID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `address` varchar(70) NOT NULL,
  `state_id` int(11) NOT NULL,
  `lga_id` varchar(50) DEFAULT NULL,
  `hosp_code` varchar(10) DEFAULT NULL,
  `folio_prefix` varchar(10) DEFAULT NULL,
  `location_lat` decimal(20,10) NOT NULL,
  `location_long` decimal(20,10) NOT NULL,
  `class` enum('PHC','Hosp') NOT NULL,
  `phone_no` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`clinicID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinical_task`
--

DROP TABLE IF EXISTS `clinical_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `objective` varchar(255) NOT NULL DEFAULT 'Normal routine check',
  `status` enum('Discharged','Ended','Cancelled','Active') NOT NULL DEFAULT 'Active',
  `source` varchar(10) DEFAULT NULL,
  `source_instance_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `discharge_clinical_tasks` AFTER UPDATE ON `clinical_task`
 FOR EACH ROW BEGIN

    UPDATE clinical_task_data SET status = NEW.status WHERE clinical_task_id = NEW.id;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `clinical_task_chart`
--

DROP TABLE IF EXISTS `clinical_task_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_task_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admission_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `clinical_task_data_id` int(11) NOT NULL,
  `nursing_service_id` int(11) DEFAULT NULL,
  `value` varchar(10) DEFAULT NULL,
  `comment` text,
  `collected_by` int(11) NOT NULL,
  `collected_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admission_id` (`admission_id`,`patient_id`,`clinical_task_data_id`)
) ENGINE=InnoDB AUTO_INCREMENT=326 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinical_task_combo`
--

DROP TABLE IF EXISTS `clinical_task_combo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_task_combo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinical_task_combo_data`
--

DROP TABLE IF EXISTS `clinical_task_combo_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_task_combo_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clinical_task_combo_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `frequency` int(11) NOT NULL,
  `interval` int(11) NOT NULL,
  `task_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clinical_task_combo_id` (`clinical_task_combo_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clinical_task_data`
--

DROP TABLE IF EXISTS `clinical_task_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clinical_task_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clinical_task_id` int(11) NOT NULL,
  `drug_id` int(11) DEFAULT NULL,
  `drug_generic_id` int(11) DEFAULT NULL,
  `dose` varchar(11) DEFAULT NULL,
  `frequency` varchar(16) NOT NULL DEFAULT '0' COMMENT 'Value in minutes',
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_round_time` timestamp NULL DEFAULT NULL,
  `next_round_time` datetime DEFAULT NULL,
  `end_round_time` timestamp NULL DEFAULT NULL,
  `task_count` int(11) NOT NULL,
  `round_count` int(11) NOT NULL DEFAULT '0',
  `status` enum('Discharged','Ended','Cancelled','Active') NOT NULL DEFAULT 'Active',
  `billed` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` int(11) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `cancel_reason` varchar(254) DEFAULT NULL,
  `cancelled_by` int(11) unsigned zerofill DEFAULT NULL,
  `cancel_time` timestamp NULL DEFAULT NULL,
  `created_by` int(11) unsigned zerofill NOT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `clinical_task_id` (`clinical_task_id`,`entry_time`),
  KEY `next_round_time` (`next_round_time`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `set_new_task_next_round_time` BEFORE INSERT ON `clinical_task_data`
 FOR EACH ROW BEGIN
SET NEW.next_round_time=(NEW.entry_time + INTERVAL NEW.frequency MINUTE);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `check_conclude_task` BEFORE UPDATE ON `clinical_task_data`
 FOR EACH ROW BEGIN
IF NEW.task_count = NEW.round_count THEN SET NEW.status = 'Ended';
END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `country_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `type` enum('mobile','home','work') NOT NULL DEFAULT 'mobile',
  `primary` tinyint(1) NOT NULL DEFAULT '0',
  `relation` enum('self','kin') NOT NULL DEFAULT 'self',
  PRIMARY KEY (`id`),
  KEY `country_id` (`country_id`),
  KEY `patient_id` (`patient_id`),
  CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_demograph` (`patient_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=1413 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_demograph_phone1` AFTER INSERT ON `contact`
 FOR EACH ROW BEGIN
UPDATE patient_demograph p SET p.phonenumber = (SELECT CONCAT('0',c.phone) FROM contact c WHERE c.patient_id=NEW.patient_id AND relation='self' AND `primary` IS TRUE) WHERE p.patient_ID=NEW.patient_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `cost_centre`
--

DROP TABLE IF EXISTS `cost_centre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cost_centre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `analytical_code` varchar(10) DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_name` varchar(50) NOT NULL DEFAULT '',
  `iso_alpha2_code` char(2) NOT NULL DEFAULT '',
  `iso_alpha3_code` char(3) DEFAULT NULL,
  `dialing_code` varchar(10) DEFAULT NULL,
  `iso_numeric` char(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_name` (`country_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_limit`
--

DROP TABLE IF EXISTS `credit_limit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_limit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expiration` date NOT NULL,
  `set_by` int(11) DEFAULT NULL,
  `reason` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `audit_credit_limit` BEFORE UPDATE ON `credit_limit`
 FOR EACH ROW BEGIN
INSERT INTO credit_limit_audit (amount, expiration, patient_id, reason, set_by) VALUES (NEW.amount, NEW.expiration, NEW.patient_id, NEW.reason, NEW.set_by);
SET NEW.reason = NULL;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `credit_limit_audit`
--

DROP TABLE IF EXISTS `credit_limit_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `credit_limit_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expiration` date NOT NULL,
  `set_by` int(11) DEFAULT NULL,
  `reason` text,
  `date_` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `code` varchar(3) NOT NULL,
  `symbol_left` varchar(12) NOT NULL,
  `symbol_right` varchar(12) NOT NULL,
  `decimal_place` char(1) NOT NULL,
  `value` float(15,8) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `date_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `death`
--

DROP TABLE IF EXISTS `death`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `death` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cert_number` varchar(25) DEFAULT NULL,
  `age_at_death` int(11) NOT NULL,
  `datetime_of_death` datetime NOT NULL,
  `patient_id` int(11) NOT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `primary_cause_id` int(11) DEFAULT NULL,
  `secondary_cause_id` int(11) DEFAULT NULL,
  `validated_by_id` int(11) unsigned zerofill DEFAULT NULL,
  `validate_on` datetime DEFAULT NULL,
  `create_uid` int(11) unsigned zerofill NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_death_certificate_id` BEFORE INSERT ON `death`
 FOR EACH ROW BEGIN 
SET NEW.cert_number = (SELECT CONCAT("DT", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `death` WHERE MONTH(create_date) = MONTH(NOW()));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_dead_patient` AFTER INSERT ON `death`
 FOR EACH ROW BEGIN 
UPDATE patient_demograph SET deceased=1 WHERE patient_ID= NEW.patient_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `dentistry`
--

DROP TABLE IF EXISTS `dentistry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dentistry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dentistry_category`
--

DROP TABLE IF EXISTS `dentistry_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dentistry_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dentistry_template`
--

DROP TABLE IF EXISTS `dentistry_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dentistry_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `body_part` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `cost_centre_id` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diagnoses`
--

DROP TABLE IF EXISTS `diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diagnoses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `type` enum('icd10','icpc-2') NOT NULL DEFAULT 'icd10',
  `case` varchar(500) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `oi` tinyint(1) NOT NULL DEFAULT '1',
  `hospid` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=128613 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `diagnoses_full`
--

DROP TABLE IF EXISTS `diagnoses_full`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `diagnoses_full` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) DEFAULT NULL,
  `type` enum('icd10','icpc-2') NOT NULL DEFAULT 'icd10',
  `case` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `discharge_template`
--

DROP TABLE IF EXISTS `discharge_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `discharge_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispensed_drugs`
--

DROP TABLE IF EXISTS `dispensed_drugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispensed_drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unfilled_quantity` int(11) NOT NULL DEFAULT '0',
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `billed_to` int(11) NOT NULL,
  `date_dispensed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pharmacist_id` varchar(11) NOT NULL,
  `service_center_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dispensed_items`
--

DROP TABLE IF EXISTS `dispensed_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dispensed_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `batch_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `unfilled_quantity` int(11) NOT NULL DEFAULT '0',
  `transaction_type` varchar(20) DEFAULT NULL,
  `billed_to` int(11) NOT NULL,
  `dispensed_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `service_center_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distribution_list`
--

DROP TABLE IF EXISTS `distribution_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distribution_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `sql_query` text NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distribution_list_contacts`
--

DROP TABLE IF EXISTS `distribution_list_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distribution_list_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) NOT NULL,
  `patient_id` varchar(11) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `district`
--

DROP TABLE IF EXISTS `district`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `district` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `state_id` int(11) NOT NULL,
  `name` varchar(70) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state_id` (`state_id`),
  CONSTRAINT `district_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_admin_log`
--

DROP TABLE IF EXISTS `django_admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action_time` datetime(6) NOT NULL,
  `object_id` longtext,
  `object_repr` varchar(200) NOT NULL,
  `action_flag` smallint(5) unsigned NOT NULL,
  `change_message` longtext NOT NULL,
  `content_type_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `django_admin_log_content_type_id_c4bce8eb_fk_django_co` (`content_type_id`),
  KEY `django_admin_log_user_id_c564eba6_fk_auth_user_id` (`user_id`),
  CONSTRAINT `django_admin_log_content_type_id_c4bce8eb_fk_django_co` FOREIGN KEY (`content_type_id`) REFERENCES `django_content_type` (`id`),
  CONSTRAINT `django_admin_log_user_id_c564eba6_fk_auth_user_id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_content_type`
--

DROP TABLE IF EXISTS `django_content_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_content_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app_label` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `django_content_type_app_label_model_76bd3d3b_uniq` (`app_label`,`model`)
) ENGINE=InnoDB AUTO_INCREMENT=323 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_messages_message`
--

DROP TABLE IF EXISTS `django_messages_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_messages_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(140) NOT NULL,
  `body` longtext NOT NULL,
  `sent_at` datetime(6) DEFAULT NULL,
  `read_at` datetime(6) DEFAULT NULL,
  `replied_at` datetime(6) DEFAULT NULL,
  `sender_deleted_at` datetime(6) DEFAULT NULL,
  `recipient_deleted_at` datetime(6) DEFAULT NULL,
  `parent_msg_id` int(11) DEFAULT NULL,
  `recipient_id` int(11) DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `django_messages_mess_parent_msg_id_3a04ba96_fk_django_me` (`parent_msg_id`),
  KEY `django_messages_message_recipient_id_bdfe9b23_fk_auth_user_id` (`recipient_id`),
  KEY `django_messages_message_sender_id_abbb5a51_fk_auth_user_id` (`sender_id`),
  CONSTRAINT `django_messages_mess_parent_msg_id_3a04ba96_fk_django_me` FOREIGN KEY (`parent_msg_id`) REFERENCES `django_messages_message` (`id`),
  CONSTRAINT `django_messages_message_recipient_id_bdfe9b23_fk_auth_user_id` FOREIGN KEY (`recipient_id`) REFERENCES `auth_user` (`id`),
  CONSTRAINT `django_messages_message_sender_id_abbb5a51_fk_auth_user_id` FOREIGN KEY (`sender_id`) REFERENCES `auth_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_migrations`
--

DROP TABLE IF EXISTS `django_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `applied` datetime(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_session`
--

DROP TABLE IF EXISTS `django_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_session` (
  `session_key` varchar(40) NOT NULL,
  `session_data` longtext NOT NULL,
  `expire_date` datetime(6) NOT NULL,
  PRIMARY KEY (`session_key`),
  KEY `django_session_expire_date_a5c62663` (`expire_date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `django_site`
--

DROP TABLE IF EXISTS `django_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `django_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `django_site_domain_a2e37b91_uniq` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `doctor_who_saw_who`
--

DROP TABLE IF EXISTS `doctor_who_saw_who`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctor_who_saw_who` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amount` float NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `scheme_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=719 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `doctors_subscribed`
--

DROP TABLE IF EXISTS `doctors_subscribed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctors_subscribed` (
  `roomID` int(11) NOT NULL AUTO_INCREMENT,
  `staffID` varchar(15) DEFAULT NULL,
  `timestamp` int(13) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`roomID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drt`
--

DROP TABLE IF EXISTS `drt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  `billing_code` varchar(30) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user_id` int(11) DEFAULT NULL,
  `description` text CHARACTER SET utf8,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_batch`
--

DROP TABLE IF EXISTS `drug_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `drug_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiration_date` date NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=300 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_body_systems`
--

DROP TABLE IF EXISTS `drug_body_systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_body_systems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_category`
--

DROP TABLE IF EXISTS `drug_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT 'category',
  `who_cat_label` varchar(15) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `complementary` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=785 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_formulary`
--

DROP TABLE IF EXISTS `drug_formulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_formulary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_formulary_data`
--

DROP TABLE IF EXISTS `drug_formulary_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_formulary_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `drug_formulary_id` int(11) NOT NULL,
  `generic_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `drug_formulary_id` (`drug_formulary_id`,`generic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_generics`
--

DROP TABLE IF EXISTS `drug_generics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_generics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(200) DEFAULT NULL,
  `category_ids` varchar(32) DEFAULT NULL,
  `service_centre_ids` varchar(32) DEFAULT NULL,
  `who_cat_labels` varchar(15) DEFAULT NULL,
  `body_systems_rel` varchar(50) DEFAULT NULL,
  `weight` varchar(100) DEFAULT NULL,
  `form` varchar(70) DEFAULT NULL,
  `description` varchar(70) DEFAULT NULL,
  `low_stock_level` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=336 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_manufacturers`
--

DROP TABLE IF EXISTS `drug_manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_manufacturers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=371 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_requisition`
--

DROP TABLE IF EXISTS `drug_requisition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_requisition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_date` datetime NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `status` enum('Draft','Validated','Approved','Received') NOT NULL,
  `last_action_user` int(11) DEFAULT NULL,
  `last_action` varchar(20) DEFAULT NULL,
  `last_action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `track_requisition_create` BEFORE INSERT ON `drug_requisition`
 FOR EACH ROW BEGIN
INSERT INTO drug_requisition_audit (`item_id`, `status`, `last_action_user`, `last_action`, `last_action_time` ) VALUES (NEW.`id`, NEW.`status`, NEW.`last_action_user`, NEW.`last_action`, NEW.`last_action_time`);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `track_requisition_update` AFTER UPDATE ON `drug_requisition`
 FOR EACH ROW BEGIN
INSERT INTO drug_requisition_audit (`item_id`, `status`, `last_action_user`, `last_action`, `last_action_time` ) VALUES (NEW.`id`, NEW.`status`, NEW.`last_action_user`, NEW.`last_action`, NEW.`last_action_time`);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `drug_requisition_audit`
--

DROP TABLE IF EXISTS `drug_requisition_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_requisition_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `last_action_user` int(11) DEFAULT NULL,
  `last_action` varchar(50) DEFAULT NULL,
  `last_action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_requisition_line`
--

DROP TABLE IF EXISTS `drug_requisition_line`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_requisition_line` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requisition_id` int(11) NOT NULL,
  `drug_id` int(11) DEFAULT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `quantity` float NOT NULL DEFAULT '0',
  `batch_name` varchar(50) DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_super_generic`
--

DROP TABLE IF EXISTS `drug_super_generic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_super_generic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified_by` int(11) DEFAULT NULL,
  `last_modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drug_super_generic_data`
--

DROP TABLE IF EXISTS `drug_super_generic_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drug_super_generic_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `super_generic_id` int(11) DEFAULT NULL,
  `drug_generic_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prevent_repeat_adding` (`super_generic_id`,`drug_generic_id`),
  KEY `drug_generic_id` (`drug_generic_id`),
  CONSTRAINT `drug_super_generic_data_ibfk_1` FOREIGN KEY (`drug_generic_id`) REFERENCES `drug_generics` (`id`),
  CONSTRAINT `drug_super_generic_data_ibfk_2` FOREIGN KEY (`super_generic_id`) REFERENCES `drug_super_generic` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drugs`
--

DROP TABLE IF EXISTS `drugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drugs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `drug_generic_id` int(11) NOT NULL,
  `manufacturer_id` int(11) NOT NULL,
  `stock_uom` varchar(30) DEFAULT NULL,
  `erp_product_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=527 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `embrayo_stage`
--

DROP TABLE IF EXISTS `embrayo_stage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `embrayo_stage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encounter`
--

DROP TABLE IF EXISTS `encounter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encounter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL,
  `initiator_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `open` tinyint(1) NOT NULL DEFAULT '1',
  `canceled` tinyint(1) NOT NULL DEFAULT '0',
  `follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  `signed_by` int(11) DEFAULT NULL,
  `signed_on` datetime DEFAULT NULL,
  `triaged_on` datetime DEFAULT NULL,
  `triaged_by` int(11) DEFAULT NULL,
  `scheme_id` int(11) DEFAULT NULL,
  `bill_line_id` varchar(100) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encounter_addendum`
--

DROP TABLE IF EXISTS `encounter_addendum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encounter_addendum` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_id` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `note` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `encounter_form`
--

DROP TABLE IF EXISTS `encounter_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `encounter_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `encounter_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `time_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encounter_id` (`encounter_id`,`form_id`),
  KEY `encounter_id_2` (`encounter_id`,`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enrollments_antenatal`
--

DROP TABLE IF EXISTS `enrollments_antenatal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments_antenatal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestCode` varchar(20) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `enrolled_at` int(11) NOT NULL,
  `enrolled_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrolled_by` int(11) unsigned zerofill NOT NULL,
  `booking_indication` varchar(15) NOT NULL,
  `complication_note` text,
  `obgyn_id` int(11) unsigned zerofill DEFAULT NULL,
  `lmp_date` date DEFAULT NULL,
  `lmp_at_enrollment` date DEFAULT NULL,
  `lmp_source` varchar(20) DEFAULT NULL,
  `ed_date` date DEFAULT NULL,
  `baby_father_name` varchar(100) DEFAULT NULL,
  `baby_father_phone` varchar(50) DEFAULT NULL,
  `baby_father_blood_group` varchar(10) DEFAULT NULL,
  `gravida` varchar(3) NOT NULL,
  `para` varchar(3) NOT NULL,
  `alive` varchar(3) NOT NULL,
  `abortions` varchar(3) NOT NULL,
  `date_closed` datetime DEFAULT NULL,
  `close_note` text,
  `recommendation` text,
  `closed_by` int(11) DEFAULT NULL,
  `service_center_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_ante_request_code` BEFORE INSERT ON `enrollments_antenatal`
 FOR EACH ROW SET NEW.requestCode = (SELECT CONCAT("ANC", date_format(now(), '%y/'), LPAD( COUNT(*)+1, 5, 0)) FROM enrollments_antenatal WHERE MONTH(enrolled_on) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `enrollments_immunization`
--

DROP TABLE IF EXISTS `enrollments_immunization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments_immunization` (
  `patient_id` varchar(15) NOT NULL,
  `enrolled_at` int(11) NOT NULL,
  `enrolled_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrolled_by` varchar(15) NOT NULL,
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `draw_chart_immediately` AFTER INSERT ON `enrollments_immunization`
 FOR EACH ROW BEGIN
CALL new_vaccine_updater(NEW.patient_id);
CALL new_patient_set_vaccine_boosters(NEW.patient_id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `enrollments_ivf`
--

DROP TABLE IF EXISTS `enrollments_ivf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments_ivf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `ivf_file_no` varchar(20) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `husband_id` int(11) DEFAULT NULL,
  `date_enrolled` datetime NOT NULL,
  `enrolled_by_id` int(11) NOT NULL,
  `indication` text,
  `hormone_fsh` varchar(50) DEFAULT NULL,
  `hormone_lh` varchar(50) DEFAULT NULL,
  `hormone_prol` varchar(50) DEFAULT NULL,
  `hormone_amh` varchar(50) DEFAULT NULL,
  `husband_hormone_fsh` varchar(50) DEFAULT NULL,
  `husband_hormone_lh` varchar(50) DEFAULT NULL,
  `husband_hormone_prol` varchar(50) DEFAULT NULL,
  `husband_hormone_testosterone` varchar(50) DEFAULT NULL,
  `sfa_count` varchar(50) DEFAULT NULL,
  `sfa_motility` varchar(50) DEFAULT NULL,
  `sfa_morphology` varchar(50) DEFAULT NULL,
  `serology_hiv` varchar(50) DEFAULT NULL,
  `serology_hep_b` varchar(50) DEFAULT NULL,
  `serology_hep_c` varchar(50) DEFAULT NULL,
  `serology_vdrl` varchar(50) DEFAULT NULL,
  `serology_chlamydia` varchar(50) DEFAULT NULL,
  `husband_serology_hiv` varchar(50) DEFAULT NULL,
  `husband_serology_hep_b` varchar(50) DEFAULT NULL,
  `husband_serology_hep_c` varchar(50) DEFAULT NULL,
  `husband_serology_vdrl` varchar(50) DEFAULT NULL,
  `husband_serology_rbs` varchar(50) DEFAULT NULL,
  `husband_serology_fbs` varchar(50) DEFAULT NULL,
  `andrology_details` text,
  `stimulation_cycle` varchar(10) DEFAULT NULL,
  `stimulation_lmp_date` date DEFAULT NULL,
  `stimulation_method` int(11) DEFAULT NULL,
  `stimulation_suprefact` varchar(50) DEFAULT NULL,
  `stimulation_zoladex` varchar(50) DEFAULT NULL,
  `stimulation_fsh` varchar(50) DEFAULT NULL,
  `stimulation_hmg` varchar(50) DEFAULT NULL,
  `closed_on` datetime DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `package_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_ivf_file_no` BEFORE INSERT ON `enrollments_ivf`
 FOR EACH ROW SET NEW.ivf_file_no =  (SELECT CONCAT("IVF", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `enrollments_ivf` WHERE MONTH(`date_enrolled`) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `enrollments_labour`
--

DROP TABLE IF EXISTS `enrollments_labour`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments_labour` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `enrolled_at` int(11) DEFAULT NULL,
  `enrolled_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enrolled_by` int(11) unsigned zerofill DEFAULT NULL,
  `date_closed` datetime DEFAULT NULL,
  `lmpDate` date DEFAULT NULL,
  `baby_father_name` varchar(70) DEFAULT NULL,
  `baby_father_phone` varchar(13) DEFAULT NULL,
  `baby_father_blood_group` varchar(4) DEFAULT NULL,
  `gravida` int(11) DEFAULT NULL,
  `para` int(11) DEFAULT NULL,
  `alive` int(11) DEFAULT NULL,
  `abortions` int(11) DEFAULT NULL,
  `current_pregnancy` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `active_enrollment` (`active`,`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `enrollments_sti`
--

DROP TABLE IF EXISTS `enrollments_sti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enrollments_sti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `patient_id` int(11) NOT NULL,
  `unique_id` varchar(20) DEFAULT NULL,
  `care_entry_point_id` int(11) DEFAULT NULL,
  `date_hiv_confirmed` date NOT NULL,
  `mode_of_test_id` int(11) DEFAULT NULL,
  `location_of_test` text,
  `prior_art_id` int(11) DEFAULT NULL,
  `enrolled_on` date NOT NULL,
  `enrolled_at` varchar(75) NOT NULL,
  `enrolled_by_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estimated_bill_lines`
--

DROP TABLE IF EXISTS `estimated_bill_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estimated_bill_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimated_bill_id` int(11) NOT NULL,
  `services_id` int(11) NOT NULL,
  `unit_price` decimal(10,0) NOT NULL DEFAULT '0',
  `item_description` varchar(200) NOT NULL,
  `item_cost_id` int(11) NOT NULL,
  `service_description` varchar(200) NOT NULL,
  `item_code` varchar(11) NOT NULL,
  `item_insurance_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `estimated_bills`
--

DROP TABLE IF EXISTS `estimated_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `estimated_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `es_code` varchar(50) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `valid_till` date NOT NULL,
  `total_estimate` decimal(10,0) NOT NULL DEFAULT '0',
  `date_created` datetime NOT NULL,
  `last_modified` datetime NOT NULL,
  `inpatient_id` int(11) DEFAULT NULL,
  `scheme_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `narration` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_report_template`
--

DROP TABLE IF EXISTS `exam_report_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_report_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `body_part` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_rooms`
--

DROP TABLE IF EXISTS `exam_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_name` varchar(50) NOT NULL,
  `available` tinyint(1) NOT NULL DEFAULT '1',
  `consultant_id` int(11) unsigned zerofill DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`room_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_template`
--

DROP TABLE IF EXISTS `exam_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `exam_template_category`
--

DROP TABLE IF EXISTS `exam_template_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_template_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eye`
--

DROP TABLE IF EXISTS `eye`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eye` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `shape` varchar(10) DEFAULT NULL,
  `coords` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eye_review`
--

DROP TABLE IF EXISTS `eye_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eye_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `category_id` int(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fake_contact`
--

DROP TABLE IF EXISTS `fake_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fake_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `primary` tinyint(1) DEFAULT '0',
  `fake_patient_id` int(11) DEFAULT NULL,
  `nation_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fake_contact_e37963e7` (`fake_patient_id`),
  KEY `fake_contact_5f138da4` (`nation_id`),
  CONSTRAINT `fake_contact_fake_patient_id_25eac8e9_fk_fake_patient_id` FOREIGN KEY (`fake_patient_id`) REFERENCES `fake_patient` (`id`),
  CONSTRAINT `fake_contact_nation_id_95835555_fk_countries_id` FOREIGN KEY (`nation_id`) REFERENCES `countries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fake_patient`
--

DROP TABLE IF EXISTS `fake_patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fake_patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `fname` varchar(150) DEFAULT NULL,
  `lname` varchar(150) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `occupation` varchar(20) DEFAULT NULL,
  `work_address` longtext,
  `res_address` longtext,
  `blood_group` varchar(10) DEFAULT NULL,
  `geno_type` varchar(10) DEFAULT NULL,
  `next_kin_fname` varchar(30) DEFAULT NULL,
  `next_kin_lname` varchar(30) DEFAULT NULL,
  `next_kin_phone` varchar(15) DEFAULT NULL,
  `next_kin_address` longtext,
  `country_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `industry_id` int(11) DEFAULT NULL,
  `lga_id` int(11) DEFAULT NULL,
  `relationship_id` int(11) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `res_state_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `res_lga_id` int(11) DEFAULT NULL,
  `res_dist_id` int(11) DEFAULT NULL,
  `phone_search` varchar(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fake_patient_country_id_8a2b3738_fk_countries_id` (`country_id`),
  KEY `fake_patient_district_id_cec126e6_fk_district_id` (`district_id`),
  KEY `fake_patient_industry_id_301a9889_fk_industry_id` (`industry_id`),
  KEY `fake_patient_lga_id_b2045889_fk_lga_id` (`lga_id`),
  KEY `fake_patient_relationship_id_88063112_fk_kin_relation_id` (`relationship_id`),
  KEY `fake_patient_religion_id_4df42f14_fk_religion_id` (`religion_id`),
  KEY `fake_patient_res_state_id_f09ce1a3_fk_state_id` (`res_state_id`),
  KEY `fake_patient_state_id_183fa8da_fk_state_id` (`state_id`),
  KEY `fake_patient_lga_id_fk` (`res_lga_id`),
  KEY `fake_patient_district_id_fk` (`res_dist_id`),
  CONSTRAINT `fake_patient_country_id_8a2b3738_fk_countries_id` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`),
  CONSTRAINT `fake_patient_district_id_cec126e6_fk_district_id` FOREIGN KEY (`district_id`) REFERENCES `district` (`id`),
  CONSTRAINT `fake_patient_district_id_fk` FOREIGN KEY (`res_dist_id`) REFERENCES `district` (`id`),
  CONSTRAINT `fake_patient_industry_id_301a9889_fk_industry_id` FOREIGN KEY (`industry_id`) REFERENCES `industry` (`id`),
  CONSTRAINT `fake_patient_lga_id_b2045889_fk_lga_id` FOREIGN KEY (`lga_id`) REFERENCES `lga` (`id`),
  CONSTRAINT `fake_patient_lga_id_fk` FOREIGN KEY (`res_lga_id`) REFERENCES `lga` (`id`),
  CONSTRAINT `fake_patient_relationship_id_88063112_fk_kin_relation_id` FOREIGN KEY (`relationship_id`) REFERENCES `kin_relation` (`id`),
  CONSTRAINT `fake_patient_religion_id_4df42f14_fk_religion_id` FOREIGN KEY (`religion_id`) REFERENCES `religion` (`id`),
  CONSTRAINT `fake_patient_res_state_id_f09ce1a3_fk_state_id` FOREIGN KEY (`res_state_id`) REFERENCES `state` (`id`),
  CONSTRAINT `fake_patient_state_id_183fa8da_fk_state_id` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fetal_brain_relationship`
--

DROP TABLE IF EXISTS `fetal_brain_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fetal_brain_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fetal_presentation`
--

DROP TABLE IF EXISTS `fetal_presentation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fetal_presentation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fluid_chart`
--

DROP TABLE IF EXISTS `fluid_chart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fluid_chart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `in_patient_id` int(11) NOT NULL,
  `route_id` int(11) NOT NULL,
  `vol` float NOT NULL,
  `type` varchar(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fluid_route`
--

DROP TABLE IF EXISTS `fluid_route`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fluid_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `type` enum('input','output') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form`
--

DROP TABLE IF EXISTS `form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_component`
--

DROP TABLE IF EXISTS `form_component`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `form_question_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_patient_question`
--

DROP TABLE IF EXISTS `form_patient_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_patient_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `form_question_id` int(11) NOT NULL,
  `create_uid` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_patient_question_answer`
--

DROP TABLE IF EXISTS `form_patient_question_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_patient_question_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_patient_question_id` int(11) NOT NULL,
  `form_question_option_id` int(11) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_question`
--

DROP TABLE IF EXISTS `form_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_question_template_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_question_option`
--

DROP TABLE IF EXISTS `form_question_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_question_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_question_template_id` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `datatype` enum('text','integer','float','model','date','boolean','selection','radio') NOT NULL,
  `relation` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `form_question_template`
--

DROP TABLE IF EXISTS `form_question_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_question_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_lab`
--

DROP TABLE IF EXISTS `genetic_lab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_lab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `genetic_template_id` int(11) NOT NULL,
  `print_layout` enum('portrait','landscape') NOT NULL,
  `quality_control_ids` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_lab_request`
--

DROP TABLE IF EXISTS `genetic_lab_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_lab_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_code` varchar(20) DEFAULT NULL,
  `female_patient_id` int(11) DEFAULT NULL,
  `male_patient_id` int(11) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `reason` text,
  `genetic_lab_id` int(11) NOT NULL,
  `genetic_specimen_id` int(11) DEFAULT NULL,
  `specimen_received_on` datetime DEFAULT NULL,
  `specimen_received_by` int(11) DEFAULT NULL,
  `status` enum('cancelled','draft','result_approved','awaiting_review') NOT NULL DEFAULT 'draft',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_ivf_lab_code` BEFORE INSERT ON `genetic_lab_request`
 FOR EACH ROW SET NEW.request_code =  (SELECT CONCAT("GL", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `genetic_lab_request` WHERE MONTH(request_date) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `genetic_lab_result`
--

DROP TABLE IF EXISTS `genetic_lab_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_lab_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `genetic_lab_request_id` int(11) NOT NULL,
  `note` text,
  `user_id` int(11) NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_quality_control`
--

DROP TABLE IF EXISTS `genetic_quality_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_quality_control` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `quality_control_type_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_quality_control_types`
--

DROP TABLE IF EXISTS `genetic_quality_control_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_quality_control_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_reagent`
--

DROP TABLE IF EXISTS `genetic_reagent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_reagent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_request_reagent`
--

DROP TABLE IF EXISTS `genetic_request_reagent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_request_reagent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `reagent_id` int(11) NOT NULL,
  `lot_number` varchar(20) NOT NULL,
  `date_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_specimen`
--

DROP TABLE IF EXISTS `genetic_specimen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_specimen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `genetic_template`
--

DROP TABLE IF EXISTS `genetic_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genetic_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_template`
--

DROP TABLE IF EXISTS `history_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history_template_data`
--

DROP TABLE IF EXISTS `history_template_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_template_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `history_template_id` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `datatype` enum('text','integer','float','model','date') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hx_template`
--

DROP TABLE IF EXISTS `hx_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hx_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hx_template_category`
--

DROP TABLE IF EXISTS `hx_template_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hx_template_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `imaging_template`
--

DROP TABLE IF EXISTS `imaging_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imaging_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `body_part` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `imaging_template_category`
--

DROP TABLE IF EXISTS `imaging_template_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imaging_template_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `in_patient`
--

DROP TABLE IF EXISTS `in_patient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `in_patient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `bed_id` int(11) DEFAULT NULL,
  `bed_assign_date` datetime DEFAULT NULL,
  `date_admitted` datetime DEFAULT NULL,
  `admitted_by` varchar(11) DEFAULT NULL,
  `status` enum('Active','Discharging','Discharged') NOT NULL DEFAULT 'Active',
  `reason` text,
  `date_discharged` datetime DEFAULT NULL,
  `date_discharged_full` datetime DEFAULT NULL,
  `anticipated_discharge_date` datetime NOT NULL,
  `discharge_note` text,
  `discharged_by` varchar(11) DEFAULT NULL,
  `discharged_by_full` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `bill_status` enum('Cleared','Uncomputed','Out Standing') NOT NULL DEFAULT 'Uncomputed',
  `claimed` tinyint(1) NOT NULL DEFAULT '0',
  `ward_id` int(11) DEFAULT NULL,
  `labour_enrollment_id` int(11) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medication_code` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`,`bed_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `industry`
--

DROP TABLE IF EXISTS `industry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `industry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance`
--

DROP TABLE IF EXISTS `insurance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `insurance_scheme` int(11) DEFAULT NULL,
  `policy_number` varchar(20) DEFAULT NULL,
  `enrollee_number` varchar(20) DEFAULT NULL,
  `coverage_type` varchar(25) DEFAULT NULL,
  `insurance_expiration` date DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `dependent_id` int(11) DEFAULT NULL,
  `parent_enrollee_id` varchar(20) DEFAULT NULL,
  `principal_external` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_ID` (`patient_id`),
  CONSTRAINT `insurance_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient_demograph` (`patient_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_billable_items`
--

DROP TABLE IF EXISTS `insurance_billable_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_billable_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(20) DEFAULT NULL,
  `item_description` text,
  `item_group_category_id` int(11) DEFAULT NULL,
  `hospid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_code` (`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1883 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_drug_tmp`
--

DROP TABLE IF EXISTS `insurance_drug_tmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_drug_tmp` (
  `Item_code` text,
  `insurance_code` varchar(20) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `drug_name` varchar(150) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `extras` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_items_cost`
--

DROP TABLE IF EXISTS `insurance_items_cost`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_items_cost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(20) DEFAULT NULL,
  `selling_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `followUpPrice` decimal(12,2) NOT NULL DEFAULT '0.00',
  `theatrePrice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `anaesthesiaPrice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `surgeonPrice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `co_pay` decimal(10,0) NOT NULL DEFAULT '0',
  `insurance_scheme_id` int(11) NOT NULL,
  `insurance_code` varchar(50) DEFAULT NULL,
  `type` enum('primary','secondary') NOT NULL DEFAULT 'primary',
  `capitated` tinyint(1) NOT NULL DEFAULT '0',
  `hospid` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`,`insurance_scheme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1873 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_owners`
--

DROP TABLE IF EXISTS `insurance_owners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(50) NOT NULL,
  `address` varchar(200) DEFAULT NULL,
  `contact_phone` varchar(15) DEFAULT NULL,
  `contact_email` varchar(75) DEFAULT NULL,
  `hospid` int(11) NOT NULL DEFAULT '1',
  `partner_id` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_schemes`
--

DROP TABLE IF EXISTS `insurance_schemes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_schemes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scheme_name` varchar(75) DEFAULT NULL,
  `badge_id` int(11) DEFAULT NULL,
  `scheme_owner_id` int(11) DEFAULT NULL,
  `pay_type` enum('self','insurance') NOT NULL DEFAULT 'self',
  `insurance_type_id` int(11) DEFAULT NULL,
  `credit_limit` decimal(10,2) NOT NULL,
  `reg_cost_individual` decimal(10,2) NOT NULL,
  `reg_cost_company` decimal(10,2) NOT NULL,
  `hospid` int(11) NOT NULL COMMENT 'Hospital registering this scheme',
  `receivables_account_id` int(11) DEFAULT NULL,
  `discount_account_id` int(11) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `logo_url` text,
  `clinical_services_rate` float NOT NULL,
  `enrolees_max` int(11) NOT NULL DEFAULT '1000',
  `is_reference` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `scheme_owner_id` (`scheme_owner_id`),
  CONSTRAINT `insurance_schemes_ibfk_1` FOREIGN KEY (`scheme_owner_id`) REFERENCES `insurance_owners` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `insurance_type`
--

DROP TABLE IF EXISTS `insurance_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice`
--

DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice` (
  `id` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) unsigned zerofill DEFAULT NULL,
  `scheme_id` int(11) DEFAULT NULL,
  `cashier_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invoice_line`
--

DROP TABLE IF EXISTS `invoice_line`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_line` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_bill` (`invoice_id`,`bill_id`)
) ENGINE=InnoDB AUTO_INCREMENT=936 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ip_observation`
--

DROP TABLE IF EXISTS `ip_observation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_observation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `in_patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `generic_id` int(11) DEFAULT NULL,
  `billing_code` varchar(10) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `erp_product_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=565 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_batch`
--

DROP TABLE IF EXISTS `item_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiration_date` date NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_category`
--

DROP TABLE IF EXISTS `item_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=269 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_generic`
--

DROP TABLE IF EXISTS `item_generic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_generic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_generic_data`
--

DROP TABLE IF EXISTS `item_generic_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_generic_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `generic` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_group`
--

DROP TABLE IF EXISTS `item_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_group_data`
--

DROP TABLE IF EXISTS `item_group_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_group_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_group` int(11) NOT NULL,
  `generic_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_group_sc`
--

DROP TABLE IF EXISTS `item_group_sc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_group_sc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_center_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_analysis_templates`
--

DROP TABLE IF EXISTS `ivf_analysis_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_analysis_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `type` enum('Pre Sperm Analysis','Post sperm Analysis','Egg Collection') DEFAULT NULL,
  `note` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_drug`
--

DROP TABLE IF EXISTS `ivf_drug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_drug` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_egg_collection`
--

DROP TABLE IF EXISTS `ivf_egg_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_egg_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `collection_time` datetime DEFAULT NULL,
  `method_id` int(11) DEFAULT NULL,
  `done_by_id` int(11) DEFAULT NULL,
  `total_left` int(11) DEFAULT NULL,
  `total_right` int(11) DEFAULT NULL,
  `witness_ids` varchar(20) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_egg_collection_follicle_data`
--

DROP TABLE IF EXISTS `ivf_egg_collection_follicle_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_egg_collection_follicle_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `egg_collection_id` int(11) NOT NULL,
  `size_id` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_egg_collection_method`
--

DROP TABLE IF EXISTS `ivf_egg_collection_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_egg_collection_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_embryo_assessment`
--

DROP TABLE IF EXISTS `ivf_embryo_assessment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_embryo_assessment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `comment` text,
  `witness_ids` text COMMENT 'comma separated ids of staffs',
  PRIMARY KEY (`id`),
  UNIQUE KEY `instance_id` (`instance_id`,`day`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_embryo_assessment_data`
--

DROP TABLE IF EXISTS `ivf_embryo_assessment_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_embryo_assessment_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ivf_embryo_assessment_id` int(11) DEFAULT NULL,
  `embryo_no` int(11) DEFAULT NULL,
  `cell_no` int(11) DEFAULT NULL,
  `quality` varchar(120) DEFAULT NULL,
  `morula` int(11) DEFAULT NULL,
  `blastocyst` int(11) DEFAULT NULL,
  `state` varchar(120) DEFAULT NULL,
  `stage` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_fertilization`
--

DROP TABLE IF EXISTS `ivf_fertilization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_fertilization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `method_id` int(11) DEFAULT NULL,
  `zygote_type` varchar(10) DEFAULT NULL,
  `cell_no` int(11) DEFAULT NULL,
  `witness_ids` varchar(50) DEFAULT NULL,
  `comment` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `instance_id` (`instance_id`,`method_id`,`zygote_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_follicle_size`
--

DROP TABLE IF EXISTS `ivf_follicle_size`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_follicle_size` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_grade_quality`
--

DROP TABLE IF EXISTS `ivf_grade_quality`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_grade_quality` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_insemination`
--

DROP TABLE IF EXISTS `ivf_insemination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_insemination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `instance_id` int(11) DEFAULT NULL,
  `method_id` int(11) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `total_eggs` float DEFAULT NULL,
  `total_sperm` float DEFAULT NULL,
  `comment` text,
  `witness_ids` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_methods`
--

DROP TABLE IF EXISTS `ivf_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_note`
--

DROP TABLE IF EXISTS `ivf_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_note_template`
--

DROP TABLE IF EXISTS `ivf_note_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_note_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_package`
--

DROP TABLE IF EXISTS `ivf_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `billing_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_protocol`
--

DROP TABLE IF EXISTS `ivf_protocol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_protocol` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_sample_source`
--

DROP TABLE IF EXISTS `ivf_sample_source`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_sample_source` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_sample_state`
--

DROP TABLE IF EXISTS `ivf_sample_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_sample_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_simulation`
--

DROP TABLE IF EXISTS `ivf_simulation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_simulation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrolment_id` int(11) DEFAULT NULL,
  `record_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `recorded_by_id` int(11) DEFAULT NULL,
  `day` int(11) DEFAULT NULL,
  `endo` int(11) DEFAULT NULL,
  `e2` int(11) DEFAULT NULL,
  `gnrha` decimal(10,2) DEFAULT NULL,
  `hmg` decimal(10,2) DEFAULT NULL,
  `ant` decimal(10,2) DEFAULT NULL,
  `fsh` decimal(10,2) DEFAULT NULL,
  `remarks` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `ivf_simulation_`
--

DROP TABLE IF EXISTS `ivf_simulation_`;
/*!50001 DROP VIEW IF EXISTS `ivf_simulation_`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `ivf_simulation_` AS SELECT 
 1 AS `id`,
 1 AS `enrolment_id`,
 1 AS `record_date`,
 1 AS `recorded_by_id`,
 1 AS `day`,
 1 AS `endo`,
 1 AS `e2`,
 1 AS `gnrha`,
 1 AS `ant`,
 1 AS `fsh`,
 1 AS `hmg`,
 1 AS `remarks`,
 1 AS `totals_left`,
 1 AS `totals_right`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `ivf_simulation_data`
--

DROP TABLE IF EXISTS `ivf_simulation_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_simulation_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ivf_simulation_id` int(11) NOT NULL,
  `right_side` int(11) NOT NULL,
  `left_side` int(11) NOT NULL,
  `size_index_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_sperm_analysis`
--

DROP TABLE IF EXISTS `ivf_sperm_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_sperm_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `instance_id` int(11) DEFAULT NULL,
  `volume` float DEFAULT NULL,
  `cell_no` int(11) DEFAULT NULL,
  `density` float DEFAULT NULL,
  `motility` float DEFAULT NULL,
  `prog` varchar(50) DEFAULT NULL,
  `abnormal` float DEFAULT NULL,
  `mar` varchar(50) DEFAULT NULL,
  `aggl` varchar(50) DEFAULT NULL,
  `comment` text,
  `witness_ids` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_sperm_collection`
--

DROP TABLE IF EXISTS `ivf_sperm_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_sperm_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance_id` int(11) DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `donor_code` varchar(20) DEFAULT NULL,
  `procedure_id` int(11) DEFAULT NULL,
  `abstinence_days` int(11) NOT NULL,
  `collection_date` datetime DEFAULT NULL,
  `witness_ids` varchar(20) DEFAULT NULL,
  `analysis_post_report` text,
  `analysis_pre_report` text,
  `production_time` datetime DEFAULT NULL,
  `analysis_time` datetime DEFAULT NULL,
  `preparation_method` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_sperm_procedure`
--

DROP TABLE IF EXISTS `ivf_sperm_procedure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_sperm_procedure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_transfer`
--

DROP TABLE IF EXISTS `ivf_transfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_transfer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_user_id` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `instance_id` int(11) NOT NULL,
  `day` int(11) NOT NULL,
  `comment` text,
  `witness_ids` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_transfer_data`
--

DROP TABLE IF EXISTS `ivf_transfer_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_transfer_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_id` int(11) NOT NULL,
  `cell` int(11) DEFAULT NULL,
  `num_transferred` int(11) NOT NULL,
  `transfer_type_id` int(11) NOT NULL,
  `embrayo_stage` varchar(20) DEFAULT NULL,
  `quality` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_transfer_type`
--

DROP TABLE IF EXISTS `ivf_transfer_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_transfer_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ivf_treatment`
--

DROP TABLE IF EXISTS `ivf_treatment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ivf_treatment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `date` datetime DEFAULT NULL,
  `day_of_cycle` int(11) DEFAULT NULL,
  `drug_id` int(11) DEFAULT NULL,
  `value` varchar(70) DEFAULT NULL,
  `findings` text,
  `comment` text,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kin_relation`
--

DROP TABLE IF EXISTS `kin_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kin_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_combo`
--

DROP TABLE IF EXISTS `lab_combo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_combo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_combo_data`
--

DROP TABLE IF EXISTS `lab_combo_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_combo_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_combo_id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_method`
--

DROP TABLE IF EXISTS `lab_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` varchar(25) NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_notes`
--

DROP TABLE IF EXISTS `lab_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_notes` (
  `lab_group_id` varchar(11) DEFAULT NULL,
  `lab_note` text,
  `when` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `who` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_requests`
--

DROP TABLE IF EXISTS `lab_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_group_id` varchar(50) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `request_note` text,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `preferred_specimens` text,
  `hospid` int(11) DEFAULT '0',
  `referral_id` int(11) DEFAULT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `urgent` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `lab_group_id` (`lab_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_result`
--

DROP TABLE IF EXISTS `lab_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_template_id` int(11) NOT NULL,
  `patient_lab_id` int(11) NOT NULL,
  `abnormal_lab_value` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by` varchar(15) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_lab_id` (`patient_lab_id`),
  KEY `lab_result_ibfk_2` (`lab_template_id`),
  KEY `lab_result_patient_lab_fk` (`patient_lab_id`),
  CONSTRAINT `lab_result_ibfk_2` FOREIGN KEY (`lab_template_id`) REFERENCES `lab_template` (`id`),
  CONSTRAINT `lab_result_patient_lab_fk` FOREIGN KEY (`patient_lab_id`) REFERENCES `patient_labs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=685 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_approved_lab` AFTER UPDATE ON `lab_result`
 FOR EACH ROW BEGIN DECLARE pid INTEGER; IF NEW.approved = 1 THEN SET pid = (SELECT patient_id FROM `patient_labs` WHERE id=OLD.patient_lab_id); INSERT INTO approved_queue (`patient_id`, `type`, `request_id`, `approved_time`) VALUES (pid, 'Lab', OLD.patient_lab_id, NEW.approved_date); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `lab_result_data`
--

DROP TABLE IF EXISTS `lab_result_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_result_data` (
  `lab_result_id` int(11) NOT NULL,
  `lab_template_data_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`lab_result_id`,`lab_template_data_id`),
  KEY `lab_result_data_ibfk_2` (`lab_template_data_id`),
  CONSTRAINT `lab_result_data_ibfk_1` FOREIGN KEY (`lab_result_id`) REFERENCES `lab_result` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lab_result_data_ibfk_2` FOREIGN KEY (`lab_template_data_id`) REFERENCES `lab_template_data` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_specimen`
--

DROP TABLE IF EXISTS `lab_specimen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_specimen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_template`
--

DROP TABLE IF EXISTS `lab_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_template_data`
--

DROP TABLE IF EXISTS `lab_template_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_template_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_template_id` int(11) NOT NULL,
  `label` varchar(32) NOT NULL,
  `lab_method_id` int(11) DEFAULT NULL,
  `reference` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lab_template_id` (`lab_template_id`,`lab_method_id`),
  KEY `lab_template_data_ibfk_1` (`lab_template_id`),
  CONSTRAINT `lab_template_data_ibfk_1` FOREIGN KEY (`lab_template_id`) REFERENCES `lab_template` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=474 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_tests_group`
--

DROP TABLE IF EXISTS `lab_tests_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_tests_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `test_ids` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='a group of tests that go together as a request. eg. Widal = Malaria+Typhoid';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labtests_config`
--

DROP TABLE IF EXISTS `labtests_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labtests_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `lab_template_id` int(11) NOT NULL,
  `testUnit_Symbol` varchar(30) DEFAULT NULL,
  `reference` text COMMENT 'Range of test result',
  `hospid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labtests_config_ibfk_1` (`category_id`),
  CONSTRAINT `labtests_config_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `labtests_config_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=latin1 COMMENT='contains the configurable lab test objects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `labtests_config_category`
--

DROP TABLE IF EXISTS `labtests_config_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labtests_config_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lga`
--

DROP TABLE IF EXISTS `lga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lga` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `state_id` int(10) NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `state_id` (`state_id`),
  CONSTRAINT `FK` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf32 COMMENT='Local governments in Nigeria.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `life_style`
--

DROP TABLE IF EXISTS `life_style`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `life_style` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_appointment`
--

DROP TABLE IF EXISTS `log_appointment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_appointment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `attended_time` datetime DEFAULT NULL,
  `status` enum('Missed','Completed','Canceled','Scheduled','Active') NOT NULL DEFAULT 'Scheduled',
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editor_id` int(11) NOT NULL,
  `trig_type` enum('Delete','Insert','Update') NOT NULL DEFAULT 'Update',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=315 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medical_exam`
--

DROP TABLE IF EXISTS `medical_exam`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `medical_exam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `labs` varchar(500) DEFAULT NULL,
  `procedures` varchar(500) DEFAULT NULL,
  `imagings` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_dispatch`
--

DROP TABLE IF EXISTS `message_dispatch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_dispatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` varchar(12) NOT NULL,
  `message` text NOT NULL,
  `sms_channel_address` varchar(20) NOT NULL,
  `sms_delivery_status` tinyint(1) NOT NULL DEFAULT '0',
  `email_channel_address` varchar(200) NOT NULL,
  `email_delivery_status` tinyint(1) NOT NULL DEFAULT '0',
  `voice_channel_address` varchar(20) DEFAULT NULL,
  `voice_delivery_status` tinyint(1) NOT NULL DEFAULT '0',
  `export_status` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_distribution_list`
--

DROP TABLE IF EXISTS `message_distribution_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_distribution_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(11) NOT NULL,
  `list` varchar(50) NOT NULL,
  `date_added` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_queue_temp`
--

DROP TABLE IF EXISTS `message_queue_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_queue_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_id` int(11) NOT NULL COMMENT 'the external id from the source table',
  `date_generated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `source` varchar(20) NOT NULL,
  `message_content` varchar(320) NOT NULL,
  `message_status` tinyint(1) NOT NULL DEFAULT '0',
  `patient` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_subscription`
--

DROP TABLE IF EXISTS `message_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient` varchar(64) NOT NULL,
  `channel_subscribed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_template`
--

DROP TABLE IF EXISTS `message_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL,
  `template_text` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text_type` enum('justdue','over') NOT NULL DEFAULT 'justdue',
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  CONSTRAINT `message_template_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `staffId` varchar(11) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `type` enum('queue','others') DEFAULT 'queue',
  `when` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('1','0') DEFAULT '0',
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nursing_service`
--

DROP TABLE IF EXISTS `nursing_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nursing_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `service_name` varchar(50) DEFAULT NULL,
  `hospid` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_code` (`billing_code`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `nursing_template`
--

DROP TABLE IF EXISTS `nursing_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nursing_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `onlinestatus`
--

DROP TABLE IF EXISTS `onlinestatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlinestatus` (
  `staffId` int(11) NOT NULL,
  `is_online` tinyint(4) NOT NULL DEFAULT '0',
  `last_seen` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`staffId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology`
--

DROP TABLE IF EXISTS `ophthalmology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `ophthalmology_template_id` int(11) DEFAULT NULL,
  `unit_symbol` varchar(30) DEFAULT NULL,
  `reference` text COMMENT 'Range of test result',
  `hospid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labtests_config_ibfk_1` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='contains the configurable lab test objects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_category`
--

DROP TABLE IF EXISTS `ophthalmology_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_item`
--

DROP TABLE IF EXISTS `ophthalmology_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='contains the configurable lab test objects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_item_batch`
--

DROP TABLE IF EXISTS `ophthalmology_item_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_item_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_items_request`
--

DROP TABLE IF EXISTS `ophthalmology_items_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_items_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `received_by` datetime DEFAULT NULL,
  `delivered_by` datetime DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_received` datetime DEFAULT NULL,
  `time_delivered` datetime DEFAULT NULL,
  `amount` float NOT NULL,
  `status` enum('Open','Received','Delivered','Cancelled') NOT NULL DEFAULT 'Open',
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_items_request_data`
--

DROP TABLE IF EXISTS `ophthalmology_items_request_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_items_request_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_requests`
--

DROP TABLE IF EXISTS `ophthalmology_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` varchar(7) NOT NULL,
  `patient_id` varchar(11) DEFAULT NULL,
  `requested_by` varchar(15) DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `preferred_specimens` text,
  `hospid` int(11) DEFAULT '0',
  `referral_id` int(11) DEFAULT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `_group_id` (`group_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_result`
--

DROP TABLE IF EXISTS `ophthalmology_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ophthalmology_template_id` int(11) NOT NULL,
  `patient_ophthalmology_id` int(11) NOT NULL,
  `abnormal_ophthalmology_value` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by` varchar(15) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ophthalmology_result_ibfk_2` (`ophthalmology_template_id`),
  KEY `ophthalmology_result_patient_lab_fk` (`patient_ophthalmology_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_approved_ophthalmology` AFTER UPDATE ON `ophthalmology_result`
 FOR EACH ROW BEGIN DECLARE pid INTEGER; IF NEW.approved = 1 THEN SET pid = (SELECT patient_id FROM patient_ophthalmology WHERE id=OLD.patient_ophthalmology_id); INSERT INTO approved_queue (`patient_id`, `type`, `request_id`, `approved_time`) VALUES (pid, 'Ophthalmology', OLD.patient_ophthalmology_id, NEW.approved_date); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ophthalmology_result_data`
--

DROP TABLE IF EXISTS `ophthalmology_result_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_result_data` (
  `ophthalmology_result_id` int(11) NOT NULL,
  `ophthalmology_template_data_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`ophthalmology_result_id`,`ophthalmology_template_data_id`),
  KEY `ophthalmology_result_data_ibfk_2` (`ophthalmology_template_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_specimen`
--

DROP TABLE IF EXISTS `ophthalmology_specimen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_specimen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_template`
--

DROP TABLE IF EXISTS `ophthalmology_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ophthalmology_template_data`
--

DROP TABLE IF EXISTS `ophthalmology_template_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ophthalmology_template_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ophthalmology_template_id` int(11) NOT NULL,
  `label` varchar(32) NOT NULL,
  `reference` varchar(128) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ophthalmology_template_data_ibfk_1` (`ophthalmology_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package`
--

DROP TABLE IF EXISTS `package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `expiration` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(70) NOT NULL,
  `billing_code` varchar(30) DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_category`
--

DROP TABLE IF EXISTS `package_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_item`
--

DROP TABLE IF EXISTS `package_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `package_id` (`package_id`,`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_subscription`
--

DROP TABLE IF EXISTS `package_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_subscription` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `date_subscribed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `create_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_token`
--

DROP TABLE IF EXISTS `package_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `original_quantity` int(11) NOT NULL,
  `quantity_left` int(11) NOT NULL,
  `item_code` varchar(12) NOT NULL,
  `date_bought` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `package_token_usage`
--

DROP TABLE IF EXISTS `package_token_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `package_token_usage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `item_code` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `use_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `responsible_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_allergen`
--

DROP TABLE IF EXISTS `patient_allergen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_allergen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `patient_ID` varchar(11) NOT NULL,
  `allergen` varchar(50) DEFAULT NULL COMMENT 'if we r 2 pull up this from datasource, change this field to be the id then map this id here to the id in the allergens table [when/to be] created',
  `reaction` longtext NOT NULL,
  `severity` enum('unknown','intolerable','mild','moderate','severe') NOT NULL,
  `noted_by` int(11) unsigned zerofill NOT NULL,
  `date_noted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hospid` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `drug_super_gen_id` int(11) DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_antenatal`
--

DROP TABLE IF EXISTS `patient_antenatal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_antenatal` (
  `patient_id` varchar(15) DEFAULT NULL,
  `family_id` varchar(15) DEFAULT NULL,
  `family_role` enum('mother','father','daughter','son') DEFAULT NULL,
  `antenatal_status` enum('active','discharged') DEFAULT NULL,
  `date_antenated` date DEFAULT NULL,
  `date_last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_antenatal_items`
--

DROP TABLE IF EXISTS `patient_antenatal_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_antenatal_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `chart_item_id` int(11) NOT NULL,
  `chart_item_level` int(11) NOT NULL,
  `type` enum('vaccines','labs','counsels','exams','medications') NOT NULL,
  `due_date` date NOT NULL,
  `date_taken` date DEFAULT NULL,
  `taken_by` varchar(15) DEFAULT NULL,
  `expiration_date` date NOT NULL,
  `note` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_antenatal_usages`
--

DROP TABLE IF EXISTS `patient_antenatal_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_antenatal_usages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `item_type` enum('Lab','Scan','Consultation','Drug') NOT NULL,
  `usages` int(11) NOT NULL,
  `date_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_attachment`
--

DROP TABLE IF EXISTS `patient_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `note` varchar(255) NOT NULL,
  `document_url` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `user_add_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_care_member`
--

DROP TABLE IF EXISTS `patient_care_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_care_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `in_patient_id` int(11) NOT NULL,
  `care_member_id` int(11) unsigned zerofill DEFAULT NULL,
  `care_team_id` int(11) DEFAULT NULL,
  `created_by` int(11) unsigned zerofill NOT NULL,
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changed_by` int(11) unsigned zerofill DEFAULT NULL,
  `change_time` timestamp NULL DEFAULT NULL,
  `status` enum('Active','Cancelled') NOT NULL DEFAULT 'Active',
  `type` enum('Member','Team') NOT NULL,
  `primary_care_id` int(11) DEFAULT NULL,
  `primary_care_type` enum('Team','Member') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_demograph`
--

DROP TABLE IF EXISTS `patient_demograph`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_demograph` (
  `patient_ID` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `deceased` tinyint(1) NOT NULL DEFAULT '0',
  `legacy_patient_id` varchar(20) DEFAULT NULL,
  `title` varchar(75) DEFAULT NULL,
  `login_id` int(11) DEFAULT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `mname` varchar(20) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `dob_estimated` tinyint(1) NOT NULL DEFAULT '0',
  `sex` enum('male','female') NOT NULL,
  `email` varchar(128) NOT NULL,
  `address` varchar(70) NOT NULL,
  `nationality` int(11) NOT NULL DEFAULT '1',
  `occupation` text,
  `work_address` text,
  `industry_id` int(11) DEFAULT NULL,
  `religion_id` int(11) DEFAULT NULL,
  `lga_id` int(11) NOT NULL,
  `district_id` int(11) DEFAULT NULL,
  `state_id` int(11) NOT NULL DEFAULT '33',
  `state_res_id` int(11) NOT NULL,
  `lga_res_id` int(11) NOT NULL,
  `district_res_id` int(11) DEFAULT NULL,
  `KinsFirstName` varchar(20) NOT NULL,
  `KinsLastName` varchar(20) NOT NULL,
  `KinsPhone` varchar(20) NOT NULL,
  `KinsAddress` varchar(70) NOT NULL,
  `kin_relation_id` int(11) DEFAULT NULL,
  `registered_By` varchar(11) NOT NULL COMMENT 'the staff under who this patient was admitted to the system',
  `phonenumber` varchar(17) DEFAULT NULL,
  `foreign_number` varchar(20) DEFAULT NULL,
  `bloodgroup` varchar(10) DEFAULT NULL,
  `bloodtype` varchar(10) DEFAULT NULL,
  `basehospital` varchar(70) DEFAULT NULL,
  `transferedto` varchar(70) DEFAULT NULL COMMENT 'New base hospital',
  `enrollment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `referral_id` int(11) DEFAULT NULL,
  `referral_company_id` int(11) DEFAULT NULL,
  `socio_economic` int(11) DEFAULT NULL,
  `lifestyle` varchar(20) DEFAULT NULL,
  `care_manager_id` int(11) DEFAULT NULL,
  `scheme_at_registration_id` int(11) DEFAULT NULL,
  `last_modified_by` int(11) DEFAULT NULL,
  `last_modified_date` datetime DEFAULT NULL,
  `cum_annual_days_on_admission` int(11) NOT NULL DEFAULT '0',
  `portal` enum('open','enabled','disabled','') NOT NULL DEFAULT 'open',
  `language_id` int(11) DEFAULT NULL,
  `ethnic` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`patient_ID`),
  KEY `legacy_id` (`legacy_patient_id`),
  KEY `fname` (`fname`,`lname`,`mname`,`phonenumber`) USING BTREE,
  FULLTEXT KEY `name_full_search` (`fname`,`lname`,`legacy_patient_id`,`mname`,`phonenumber`)
) ENGINE=InnoDB AUTO_INCREMENT=333 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_dentistry`
--

DROP TABLE IF EXISTS `patient_dentistry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_dentistry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestCode` varchar(20) DEFAULT NULL,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `dentistry_ids` varchar(200) NOT NULL,
  `request_note` varchar(120) DEFAULT NULL,
  `requested_by_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by_id` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `date_last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `cancel_date` datetime DEFAULT NULL,
  `canceled_by_id` int(11) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_dentistry_request_code` BEFORE INSERT ON `patient_dentistry`
 FOR EACH ROW SET NEW.requestCode =  (SELECT CONCAT("DT", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `patient_dentistry` WHERE MONTH(request_date) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_approved_request` AFTER UPDATE ON `patient_dentistry`
 FOR EACH ROW BEGIN IF NEW.approved = 1 THEN INSERT INTO approved_queue (`patient_id`, `type`, `request_id`, `approved_time`) VALUES (OLD.patient_id, 'Dentistry', OLD.id, NEW.approved_date); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_dentistry_notes`
--

DROP TABLE IF EXISTS `patient_dentistry_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_dentistry_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_dentistry_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `note_area` varchar(50) NOT NULL,
  `create_uid` varchar(15) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_diagnoses`
--

DROP TABLE IF EXISTS `patient_diagnoses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_diagnoses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_ID` int(11) NOT NULL,
  `date_of_entry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `diagnosed_by` int(11) DEFAULT NULL,
  `diagnosisNote` longtext,
  `diag-type` varchar(2) DEFAULT NULL COMMENT 'just dummy. this balances the visit_notes table in a joined query',
  `diagnosis` varchar(300) DEFAULT NULL,
  `_status` enum('differential','confirmed','history','query') DEFAULT NULL,
  `severity` enum('acute','chronic','recurrent') NOT NULL DEFAULT 'acute',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `hospital_diagnosed` varchar(50) DEFAULT NULL COMMENT 'Where diagnoses took place',
  `encounter_id` int(11) DEFAULT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `body_part_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=396 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_history`
--

DROP TABLE IF EXISTS `patient_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assessment_id` int(11) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `history_id` int(11) NOT NULL,
  `create_uid` int(11) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `instance_id` int(11) DEFAULT NULL,
  `type` enum('antenatal') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_history_data`
--

DROP TABLE IF EXISTS `patient_history_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_history_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_history_id` int(11) NOT NULL,
  `history_template_data_id` int(11) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_in_room`
--

DROP TABLE IF EXISTS `patient_in_room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_in_room` (
  `roomID` varchar(5) DEFAULT NULL,
  `patientID` varchar(15) DEFAULT NULL,
  `queue_for` enum('doctor','nurse') NOT NULL DEFAULT 'doctor',
  `time_in` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_item_request`
--

DROP TABLE IF EXISTS `patient_item_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_item_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `group_code` varchar(50) DEFAULT NULL,
  `requested_by` int(11) NOT NULL,
  `requested_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service_center_id` int(11) NOT NULL,
  `inpatient_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `procedure_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_item_request_data`
--

DROP TABLE IF EXISTS `patient_item_request_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_item_request_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` varchar(20) NOT NULL,
  `generic_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `filled_date` datetime DEFAULT NULL,
  `status` enum('open','filled','cancelled','completed') NOT NULL DEFAULT 'open',
  `cancelled_by` int(11) DEFAULT NULL,
  `cancelled_on` datetime DEFAULT NULL,
  `hosp_id` int(11) DEFAULT NULL,
  `cancelled_note` varchar(500) DEFAULT NULL,
  `filled_by` int(11) DEFAULT NULL,
  `filled_qty` int(11) DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_labs`
--

DROP TABLE IF EXISTS `patient_labs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_labs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `test_id` int(11) DEFAULT NULL,
  `lab_group_id` varchar(50) NOT NULL,
  `performed_by` varchar(11) DEFAULT NULL,
  `test_notes` text,
  `test_specimen_ids` varchar(30) DEFAULT NULL,
  `test_date` datetime DEFAULT NULL COMMENT 'test result was entered on:',
  `specimen_collected_by` varchar(15) DEFAULT NULL,
  `specimen_notes` varchar(50) DEFAULT NULL,
  `specimen_date` datetime DEFAULT NULL,
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `specimen_received_by` int(10) unsigned zerofill DEFAULT NULL,
  `_status` enum('open','cancelled') NOT NULL DEFAULT 'open',
  `bill_line_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_group_id` (`lab_group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=825 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_medical_report`
--

DROP TABLE IF EXISTS `patient_medical_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_medical_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestCode` varchar(20) DEFAULT NULL,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `exam_id` int(11) NOT NULL,
  `request_note` varchar(120) DEFAULT NULL,
  `requested_by_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by_id` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `date_last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `cancel_date` datetime DEFAULT NULL,
  `canceled_by_id` int(11) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `labs` int(11) DEFAULT NULL,
  `imagings` varchar(100) DEFAULT NULL,
  `procedures` varchar(100) DEFAULT NULL,
  `bill_line_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_m_report_request_code` BEFORE INSERT ON `patient_medical_report`
 FOR EACH ROW SET NEW.requestCode = (SELECT CONCAT("MER", date_format(now(), '%y/%m/%d/'), LPAD( COUNT(*)+1, 4, 0)) FROM `patient_medical_report` WHERE DATE(request_date) = DATE(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_approved_m_report` AFTER UPDATE ON `patient_medical_report`
 FOR EACH ROW BEGIN IF NEW.approved = 1 THEN INSERT INTO approved_queue (`patient_id`, `type`, `request_id`, `approved_time`) VALUES (OLD.patient_id, 'Medical Report', OLD.id, NEW.approved_date); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_medical_report_note`
--

DROP TABLE IF EXISTS `patient_medical_report_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_medical_report_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_medical_report_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `create_uid` varchar(15) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_ophthalmology`
--

DROP TABLE IF EXISTS `patient_ophthalmology`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_ophthalmology` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) DEFAULT NULL,
  `ophthalmology_id` int(11) DEFAULT NULL,
  `ophthalmology_group_code` varchar(7) NOT NULL,
  `performed_by` varchar(11) DEFAULT NULL,
  `test_notes` text,
  `ophthalmology_specimen_ids` varchar(30) DEFAULT NULL,
  `test_date` datetime DEFAULT NULL COMMENT 'test result was entered on:',
  `specimen_collected_by` varchar(15) DEFAULT NULL,
  `specimen_notes` varchar(50) DEFAULT NULL,
  `specimen_date` datetime DEFAULT NULL,
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `specimen_received_by` int(10) unsigned zerofill DEFAULT NULL,
  `_status` enum('open','cancelled') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_physical_assessments`
--

DROP TABLE IF EXISTS `patient_physical_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_physical_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `HEENT` varchar(200) NOT NULL,
  `HEENT_Note` varchar(70) DEFAULT NULL,
  `Heart` varchar(200) NOT NULL,
  `Heart_Note` varchar(70) DEFAULT NULL,
  `Lungs` varchar(200) NOT NULL,
  `Lungs_Note` varchar(70) DEFAULT NULL,
  `Abdomen` varchar(200) NOT NULL,
  `Abdomen_Note` varchar(70) DEFAULT NULL,
  `Extremites` varchar(200) NOT NULL,
  `Extremites_Note` varchar(70) DEFAULT NULL,
  `Skin` varchar(200) NOT NULL,
  `Skin_Note` varchar(70) DEFAULT NULL,
  `Neuro` varchar(200) NOT NULL,
  `Neuro_Note` varchar(70) DEFAULT NULL,
  `assessed_by` varchar(15) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_physical_examination`
--

DROP TABLE IF EXISTS `patient_physical_examination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_physical_examination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `physical_examination_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_pre_conditions`
--

DROP TABLE IF EXISTS `patient_pre_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_pre_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `_condition` varchar(150) NOT NULL,
  `diag_date` date DEFAULT NULL,
  `severity` int(11) NOT NULL,
  `therapy` varchar(20) NOT NULL,
  `therapy_start_date` date DEFAULT NULL,
  `response` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `history` tinyint(1) NOT NULL DEFAULT '0',
  `entered_by` varchar(15) NOT NULL,
  `date_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hospid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure`
--

DROP TABLE IF EXISTS `patient_procedure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `procedure_id` int(11) NOT NULL,
  `request_id` varchar(20) NOT NULL,
  `request_date` datetime NOT NULL,
  `request_note` text,
  `condition_ids` varchar(70) DEFAULT NULL,
  `_status` enum('open','scheduled','started','closed','cancelled') NOT NULL,
  `closing_text` varchar(255) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `requested_by_id` int(11) NOT NULL,
  `billed` tinyint(1) NOT NULL DEFAULT '1',
  `theatre_id` int(11) DEFAULT NULL,
  `has_anesthesiologist` tinyint(1) NOT NULL DEFAULT '0',
  `anesthesiologist_id` int(11) DEFAULT NULL,
  `has_surgeon` tinyint(1) NOT NULL DEFAULT '0',
  `surgeon_id` int(11) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `bodypart_id` int(11) DEFAULT NULL,
  `source` varchar(20) DEFAULT NULL,
  `source_instance_id` int(11) DEFAULT NULL,
  `scheduled_resource_ids` varchar(120) DEFAULT NULL,
  `time_start` datetime DEFAULT NULL,
  `time_stop` datetime DEFAULT NULL,
  `scheduled_on` datetime DEFAULT NULL,
  `scheduled_by` int(11) DEFAULT NULL,
  `time_started` datetime DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_request_id` BEFORE INSERT ON `patient_procedure`
 FOR EACH ROW SET NEW.request_id  = (SELECT CONCAT("RQ", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `patient_procedure` WHERE MONTH(request_date) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_procedure_items`
--

DROP TABLE IF EXISTS `patient_procedure_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `generic_id` int(11) DEFAULT NULL,
  `service_center_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure_note`
--

DROP TABLE IF EXISTS `patient_procedure_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `note` longtext,
  `staff_id` int(10) unsigned zerofill NOT NULL,
  `note_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note_type` enum('Pre-Procedure','Post-Procedure','Findings') DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure_nursing_task`
--

DROP TABLE IF EXISTS `patient_procedure_nursing_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_nursing_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `create_uid` int(11) NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  `date_` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure_regimen`
--

DROP TABLE IF EXISTS `patient_procedure_regimen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_regimen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `generic_id` int(11) NOT NULL,
  `quantity` varchar(30) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `request_user_id` int(11) NOT NULL,
  `generic_note` text,
  `drugs` int(11) DEFAULT NULL,
  `batch` int(11) DEFAULT NULL,
  `units` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `bill_line_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure_report`
--

DROP TABLE IF EXISTS `patient_procedure_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_procedure_resource`
--

DROP TABLE IF EXISTS `patient_procedure_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_procedure_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `resource_type_id` int(11) DEFAULT NULL,
  `create_uid` int(11) NOT NULL,
  `date_` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_queue`
--

DROP TABLE IF EXISTS `patient_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `type` enum('Bed','Billing','Nursing','Pharmacy','Lab','Imaging','Doctors','Vaccination','Procedure','Ophthalmology','Dentistry','Antenatal') NOT NULL,
  `sub_type` varchar(50) DEFAULT NULL,
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attended_time` timestamp NULL DEFAULT NULL,
  `tag_no` int(3) unsigned zerofill NOT NULL,
  `blocked_by` int(11) DEFAULT NULL,
  `seen_by` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `status` enum('Active','Attended','Cancelled','Blocked') DEFAULT 'Active',
  `cancelled_by` int(11) DEFAULT NULL,
  `amount` float DEFAULT NULL COMMENT 'just to hold the amount for the trigger to use',
  `follow_up` tinyint(1) NOT NULL DEFAULT '0',
  `review` tinyint(1) NOT NULL DEFAULT '0',
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`,`type`,`sub_type`,`entry_time`,`attended_time`,`blocked_by`,`department_id`,`specialization_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1871 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `doctor_who_saw_who` AFTER UPDATE ON `patient_queue`
 FOR EACH ROW BEGIN IF NEW.status='Attended' AND OLD.status <> 'Attended' THEN INSERT INTO doctor_who_saw_who (doctor_id, patient_id, specialization_id, type, amount, department_id, scheme_id) VALUES (NEW.seen_by, NEW.patient_id, NEW.specialization_id, NEW.type, NEW.amount, NEW.department_id, (SELECT insurance_scheme FROM insurance WHERE patient_id=NEW.patient_id)); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_regimens`
--

DROP TABLE IF EXISTS `patient_regimens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_regimens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `external` tinyint(1) NOT NULL DEFAULT '0',
  `patient_id` int(11) unsigned zerofill NOT NULL,
  `when` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_code` varchar(20) NOT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `prescribed_by` varchar(50) DEFAULT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `note` text,
  `hospid` int(11) DEFAULT NULL,
  `refill_off` int(11) DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_code_2` (`group_code`),
  KEY `group_code` (`group_code`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_regimens_data`
--

DROP TABLE IF EXISTS `patient_regimens_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_regimens_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` varchar(20) NOT NULL COMMENT 'this is used to group the regimens for the patient',
  `drug_id` int(11) DEFAULT NULL COMMENT 'format this from the application',
  `drug_generic_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `dose` varchar(200) NOT NULL COMMENT 'format this from the application',
  `duration` int(11) DEFAULT NULL COMMENT 'Value in days',
  `comment` text,
  `batch_id` int(11) DEFAULT NULL,
  `frequency` varchar(20) NOT NULL COMMENT 'format this from the application',
  `refillable` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('open','filled','cancelled','completed','history','substituted') NOT NULL DEFAULT 'open',
  `requested_by` varchar(14) DEFAULT NULL,
  `modified_by` varchar(14) DEFAULT NULL,
  `filled_by` varchar(14) DEFAULT NULL,
  `filled_on` datetime DEFAULT NULL,
  `completed_by` varchar(14) DEFAULT NULL,
  `completed_on` datetime DEFAULT NULL,
  `cancelled_by` varchar(14) DEFAULT NULL,
  `cancelled_on` datetime DEFAULT NULL,
  `cancel_note` text,
  `hospid` int(11) DEFAULT NULL,
  `bodypart_id` int(11) DEFAULT NULL,
  `external_source` tinyint(1) NOT NULL DEFAULT '0',
  `refill_date` datetime DEFAULT NULL,
  `refill_number` int(11) DEFAULT NULL,
  `bill_line_id` varchar(100) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `diagnoses_id` int(11) DEFAULT NULL,
  `substituted_by` int(11) DEFAULT NULL,
  `substituted_on` datetime DEFAULT NULL,
  `substitution_reason` text,
  PRIMARY KEY (`id`),
  KEY `group_code` (`group_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=368 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `fix_zero_filled_prescription_data` BEFORE UPDATE ON `patient_regimens_data`
 FOR EACH ROW BEGIN IF NEW.quantity = 0 AND NEW.status = "filled" THEN
SET NEW.status = "open";
END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_scan`
--

DROP TABLE IF EXISTS `patient_scan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestCode` varchar(20) DEFAULT NULL,
  `patient_id` int(10) unsigned zerofill NOT NULL,
  `scan_ids` varchar(200) NOT NULL,
  `request_note` varchar(120) DEFAULT NULL,
  `requested_by_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `approved_by_id` int(11) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `date_last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `cancel_date` datetime DEFAULT NULL,
  `canceled_by_id` int(11) DEFAULT NULL,
  `referral_id` int(11) DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  `captured` tinyint(1) NOT NULL DEFAULT '0',
  `captured_date` datetime DEFAULT NULL,
  `captured_by_id` int(11) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `schedule_date_start` datetime DEFAULT NULL,
  `schedule_date_end` datetime DEFAULT NULL,
  `scheduled_on` datetime DEFAULT NULL,
  `scheduled_by_id` int(11) DEFAULT NULL,
  `bill_line_id` varchar(100) DEFAULT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=450 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_request_code` BEFORE INSERT ON `patient_scan`
 FOR EACH ROW SET NEW.requestCode = (SELECT CONCAT("SC", date_format(now(), '%y/%m/%d/'), LPAD( COUNT(*)+1, 4, 0)) FROM `patient_scan` WHERE DATE(request_date) = DATE(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_approved_scan` AFTER UPDATE ON `patient_scan`
 FOR EACH ROW BEGIN IF NEW.approved = 1 THEN INSERT INTO approved_queue (`patient_id`, `type`, `request_id`, `approved_time`) VALUES (OLD.patient_id, 'Imaging', OLD.id, NEW.approved_date); END IF; END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `patient_scan_attachment`
--

DROP TABLE IF EXISTS `patient_scan_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_scan_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_scan_id` int(11) NOT NULL,
  `attachment_url` text NOT NULL,
  `note` varchar(255) NOT NULL,
  `timeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_uid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_scan_notes`
--

DROP TABLE IF EXISTS `patient_scan_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_scan_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_scan_id` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `is_comment` tinyint(1) NOT NULL DEFAULT '0',
  `note_area` varchar(50) NOT NULL,
  `create_uid` varchar(15) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_scan_types`
--

DROP TABLE IF EXISTS `patient_scan_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_scan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_systems_review`
--

DROP TABLE IF EXISTS `patient_systems_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_systems_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `systems_review_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `assessment_id` int(11) DEFAULT NULL,
  `antenatal_instance_id` int(11) DEFAULT NULL,
  `type` enum('antenatal') DEFAULT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_vaccine`
--

DROP TABLE IF EXISTS `patient_vaccine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_vaccine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `is_booster` tinyint(1) NOT NULL DEFAULT '0',
  `vaccine_level` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `billed` tinyint(1) NOT NULL DEFAULT '0',
  `entry_date` date DEFAULT NULL,
  `taken_by` varchar(15) DEFAULT NULL,
  `take_type` set('n','m','p') NOT NULL DEFAULT 'm',
  `internal` tinyint(1) NOT NULL DEFAULT '1',
  `route` enum('im','sc','id','in','or') NOT NULL COMMENT 'also __site__',
  `site` varchar(50) DEFAULT NULL,
  `dosage` varchar(10) DEFAULT NULL,
  `real_administer_date` date DEFAULT NULL,
  `expiration_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_vaccine_booster`
--

DROP TABLE IF EXISTS `patient_vaccine_booster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_vaccine_booster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `vaccinebooster_id` int(11) NOT NULL,
  `start_date` date DEFAULT NULL,
  `next_due_date` date DEFAULT NULL,
  `last_taken` date DEFAULT NULL,
  `charged` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_vaccine_booster_history`
--

DROP TABLE IF EXISTS `patient_vaccine_booster_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_vaccine_booster_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patientvaccinebooster_id` int(11) NOT NULL,
  `date_taken` date NOT NULL,
  `taken_by` varchar(15) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_vaccine_temp`
--

DROP TABLE IF EXISTS `patient_vaccine_temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_vaccine_temp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `vaccine_id` int(11) NOT NULL,
  `vaccine_level` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `paid` int(1) NOT NULL DEFAULT '0',
  `date_taken` date DEFAULT NULL,
  `taken_by` varchar(15) DEFAULT NULL,
  `expiration_date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_visit_notes`
--

DROP TABLE IF EXISTS `patient_visit_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_visit_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_ID` int(11) NOT NULL,
  `date_of_entry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `noted_by` int(11) NOT NULL,
  `description` longtext NOT NULL,
  `note_type` char(2) DEFAULT NULL,
  `reason` set('normal','admission','antenatal') NOT NULL DEFAULT 'normal' COMMENT 'note subject can be null,',
  `hospitalvisited` varchar(70) DEFAULT NULL COMMENT 'Hospital visited by this patient',
  `sourceapp` varchar(15) DEFAULT NULL COMMENT 'mobile or desktop',
  `module` varchar(25) NOT NULL DEFAULT 'basic',
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_ID` (`patient_ID`,`note_type`),
  KEY `encounter_id` (`encounter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3243 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `patient_vital_preference`
--

DROP TABLE IF EXISTS `patient_vital_preference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_vital_preference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3494 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `type` set('cash','bank','refund','discount','voucher') NOT NULL DEFAULT 'cash',
  `ledger_id` varchar(50) DEFAULT NULL,
  `hospid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physical_assessment_groups`
--

DROP TABLE IF EXISTS `physical_assessment_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physical_assessment_groups` (
  `group_id` int(10) unsigned zerofill NOT NULL,
  `patient_id` varchar(15) NOT NULL,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `assessed_by` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physical_examination`
--

DROP TABLE IF EXISTS `physical_examination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physical_examination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `abbr` varchar(100) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physical_examination_category`
--

DROP TABLE IF EXISTS `physical_examination_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physical_examination_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physiotherapy_booking`
--

DROP TABLE IF EXISTS `physiotherapy_booking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_booking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestCode` varchar(25) DEFAULT NULL,
  `patient_id` int(11) NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `specialization_id` int(11) NOT NULL,
  `count` int(11) NOT NULL,
  `booked_by` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`,`specialization_id`,`active`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_pt_request_code` BEFORE INSERT ON `physiotherapy_booking`
 FOR EACH ROW SET NEW.requestCode = (SELECT CONCAT("PT", date_format(now(), '%y/%m/'), LPAD( COUNT(*)+1, 4, 0)) FROM `physiotherapy_booking` WHERE MONTH(booking_date) = MONTH(NOW())) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `physiotherapy_item`
--

DROP TABLE IF EXISTS `physiotherapy_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='contains the configurable lab test objects';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physiotherapy_item_batch`
--

DROP TABLE IF EXISTS `physiotherapy_item_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_item_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physiotherapy_items_request`
--

DROP TABLE IF EXISTS `physiotherapy_items_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_items_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `requested_by` int(11) DEFAULT NULL,
  `received_by` datetime DEFAULT NULL,
  `delivered_by` datetime DEFAULT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `time_received` datetime DEFAULT NULL,
  `time_delivered` datetime DEFAULT NULL,
  `amount` float NOT NULL,
  `status` enum('Open','Received','Delivered','Cancelled') NOT NULL DEFAULT 'Open',
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physiotherapy_items_request_data`
--

DROP TABLE IF EXISTS `physiotherapy_items_request_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_items_request_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `physiotherapy_session`
--

DROP TABLE IF EXISTS `physiotherapy_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `physiotherapy_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `session_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` text NOT NULL,
  `noted_by` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure`
--

DROP TABLE IF EXISTS `procedure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `icd_code` varchar(10) NOT NULL,
  `description` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_action_list`
--

DROP TABLE IF EXISTS `procedure_action_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_action_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entered_by` int(11) NOT NULL,
  `done` tinyint(1) NOT NULL,
  `done_by` int(11) DEFAULT NULL,
  `done_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_attachment`
--

DROP TABLE IF EXISTS `procedure_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_procedure_id` int(11) NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `entered_by` int(11) NOT NULL,
  `url` text,
  `mimetype` varchar(70) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_category`
--

DROP TABLE IF EXISTS `procedure_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(70) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_checklist_template`
--

DROP TABLE IF EXISTS `procedure_checklist_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_checklist_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_resource_type`
--

DROP TABLE IF EXISTS `procedure_resource_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_resource_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_specialty`
--

DROP TABLE IF EXISTS `procedure_specialty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_specialty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_template`
--

DROP TABLE IF EXISTS `procedure_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_template_category`
--

DROP TABLE IF EXISTS `procedure_template_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `procedure_template_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `progress_note`
--

DROP TABLE IF EXISTS `progress_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progress_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `in_patient_id` int(11) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `note` text NOT NULL,
  `note_type` char(2) DEFAULT NULL,
  `noted_by` varchar(16) NOT NULL,
  `entry_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purged_drugs`
--

DROP TABLE IF EXISTS `purged_drugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purged_drugs` (
  `drug_id` bigint(20) NOT NULL,
  `quantity` bigint(20) NOT NULL,
  `amountlost` float NOT NULL,
  `purgedby` varchar(60) NOT NULL,
  `purge_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referer_template_category`
--

DROP TABLE IF EXISTS `referer_template_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referer_template_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral`
--

DROP TABLE IF EXISTS `referral`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `referral_company_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `account_number` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_company`
--

DROP TABLE IF EXISTS `referral_company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(15) DEFAULT NULL,
  `email` varchar(75) DEFAULT NULL,
  `bank_name` varchar(15) DEFAULT NULL,
  `account_number` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referral_template`
--

DROP TABLE IF EXISTS `referral_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referral_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `category_id` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `referrals_queue`
--

DROP TABLE IF EXISTS `referrals_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acknowledged` tinyint(1) NOT NULL DEFAULT '0',
  `note` text NOT NULL,
  `external` tinyint(1) NOT NULL DEFAULT '1',
  `specialization_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `religion`
--

DROP TABLE IF EXISTS `religion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `religion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource`
--

DROP TABLE IF EXISTS `resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `type` enum('Theatre','Conference','MRI','Imaging','Ward','Equipment') NOT NULL,
  `modality` char(2) DEFAULT NULL,
  `ae_title` varchar(50) DEFAULT NULL,
  `station_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `revenue_account`
--

DROP TABLE IF EXISTS `revenue_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `revenue_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_source_id` int(11) NOT NULL,
  `insurance_scheme_id` int(11) NOT NULL,
  `receivable_account_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `room`
--

DROP TABLE IF EXISTS `room`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `ward_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `room_type`
--

DROP TABLE IF EXISTS `room_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `room_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  `hospital_id` int(11) NOT NULL COMMENT 'Hospital with this bed type',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `round_notification`
--

DROP TABLE IF EXISTS `round_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `round_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `round_id` int(11) NOT NULL,
  `round` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scan`
--

DROP TABLE IF EXISTS `scan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `billing_code` varchar(10) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=426 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scan_category`
--

DROP TABLE IF EXISTS `scan_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scan_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_centre`
--

DROP TABLE IF EXISTS `service_centre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_centre` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL,
  `cost_centre_id` int(10) unsigned NOT NULL,
  `type` enum('Lab','Pharmacy','Ophthalmology','Dentistry','Voucher','Procedure','Physiotherapy','Imaging','Item','General','Consultation','MedicalReport','Vaccine','Antenatal','Nursing') NOT NULL,
  `name` varchar(70) NOT NULL,
  `erp_location_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL,
  `access` int(10) unsigned DEFAULT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform`
--

DROP TABLE IF EXISTS `sform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform_answer`
--

DROP TABLE IF EXISTS `sform_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform_answer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  `question_id` int(11) NOT NULL,
  `time_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encounter_id_2` (`encounter_id`,`question_id`),
  KEY `patient_id` (`patient_id`),
  KEY `encounter_id` (`encounter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform_answer_option`
--

DROP TABLE IF EXISTS `sform_answer_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform_answer_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sform_answer_id` int(11) NOT NULL,
  `answer_text` varchar(255) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sform_answer_id` (`sform_answer_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform_category`
--

DROP TABLE IF EXISTS `sform_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform_option`
--

DROP TABLE IF EXISTS `sform_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sform_question_id` int(11) NOT NULL,
  `text` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sform_question_id` (`sform_question_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sform_question`
--

DROP TABLE IF EXISTS `sform_question`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sform_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sform_id` int(11) NOT NULL,
  `text` text NOT NULL,
  `type` varchar(25) DEFAULT NULL,
  `page` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `sform_id` (`sform_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `signature`
--

DROP TABLE IF EXISTS `signature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT NULL,
  `signature` longblob,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `simulation_size`
--

DROP TABLE IF EXISTS `simulation_size`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simulation_size` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `socio_economic_status`
--

DROP TABLE IF EXISTS `socio_economic_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `socio_economic_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `special_event`
--

DROP TABLE IF EXISTS `special_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `special_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `note` varchar(200) NOT NULL,
  `noted_by` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dismissed` tinyint(1) DEFAULT '0',
  `alert_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `spoken_language`
--

DROP TABLE IF EXISTS `spoken_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spoken_language` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_care_team`
--

DROP TABLE IF EXISTS `staff_care_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_care_team` (
  `team_id` int(11) NOT NULL,
  `staff_id` int(10) unsigned zerofill NOT NULL,
  PRIMARY KEY (`team_id`,`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_directory`
--

DROP TABLE IF EXISTS `staff_directory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_directory` (
  `staffId` int(11) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `clinic_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `pswd` varchar(100) NOT NULL COMMENT 'this is the hashed string value for the password',
  `profession` enum('Doctor','Nurse','Pharmacist','Lab Scientist','Admin','Accounts Officer','Medical Records','Radiographer','Phlebotomist','Physiotherapist') DEFAULT NULL,
  `username` varchar(50) NOT NULL COMMENT 'just not needed right now since op''s can login with a combination of email and pswd',
  `roles` text COMMENT 'a |-separated value list that describes what things the user can see',
  `status` enum('active','disabled','reset') DEFAULT NULL,
  `sip_user_name` varchar(8) DEFAULT NULL,
  `sip_password` varchar(8) DEFAULT NULL,
  `sip_extension` varchar(8) DEFAULT NULL,
  `folio_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`staffId`),
  UNIQUE KEY `username` (`username`),
  KEY `clinic_id` (`clinic_id`),
  CONSTRAINT `staff_directory_ibfk_1` FOREIGN KEY (`clinic_id`) REFERENCES `clinic` (`clinicID`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_online_status_on_update` AFTER UPDATE ON `staff_directory`
 FOR EACH ROW BEGIN
	if NEW.`status` = 'disabled' THEN 
		DELETE FROM onlinestatus WHERE staffId = OLD.staffId;
	ELSEIF NEW.`status` = 'active' THEN
		IF ((SELECT COUNT(staffId) FROM onlinestatus) < 1) THEN
			INSERT INTO onlinestatus (staffId) VALUES (OLD.staffId);
		END if;
	END if;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_online_status_on_update` AFTER DELETE ON `staff_directory`
 FOR EACH ROW BEGIN
	DELETE FROM onlinestatus WHERE staffId = OLD.staffId;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `staff_roles`
--

DROP TABLE IF EXISTS `staff_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(30) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_specialization`
--

DROP TABLE IF EXISTS `staff_specialization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff_specialization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(16) NOT NULL,
  `staff_type` varchar(50) DEFAULT NULL,
  `hospid` int(11) NOT NULL DEFAULT '1',
  `inpatient` tinyint(1) NOT NULL DEFAULT '0',
  `outpatient` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_code` (`billing_code`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state` (
  `id` int(10) NOT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COMMENT='States in Nigeria.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sti_care_entry_point`
--

DROP TABLE IF EXISTS `sti_care_entry_point`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sti_care_entry_point` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sti_prior_art`
--

DROP TABLE IF EXISTS `sti_prior_art`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sti_prior_art` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sti_test_mode`
--

DROP TABLE IF EXISTS `sti_test_mode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sti_test_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(75) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `substitution_code`
--

DROP TABLE IF EXISTS `substitution_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `substitution_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `systems_review`
--

DROP TABLE IF EXISTS `systems_review`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systems_review` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `systems_review_category`
--

DROP TABLE IF EXISTS `systems_review_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systems_review_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` enum('antenatal-exam','antenatal-lab') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test`
--

DROP TABLE IF EXISTS `test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `test_key`
--

DROP TABLE IF EXISTS `test_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nr` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nr` (`nr`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vaccine_levels`
--

DROP TABLE IF EXISTS `vaccine_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vaccine_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vaccine_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `start_index` int(11) NOT NULL DEFAULT '0',
  `end_index` int(11) NOT NULL DEFAULT '0',
  `start_age` int(11) NOT NULL DEFAULT '0',
  `end_age` int(11) NOT NULL DEFAULT '0',
  `duration` int(11) NOT NULL,
  `start_age_scale` enum('WEEK','MONTH','YEAR','DAY') NOT NULL DEFAULT 'DAY',
  `end_age_scale` enum('WEEK','MONTH','YEAR','DAY') NOT NULL DEFAULT 'DAY',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `recompute_patient_vaccine` AFTER INSERT ON `vaccine_levels`
 FOR EACH ROW begin
CALL new_vaccine_updater('0');
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vaccines`
--

DROP TABLE IF EXISTS `vaccines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vaccines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_code` varchar(15) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar(1024) NOT NULL DEFAULT '',
  `default_price` decimal(13,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vaccines_booster`
--

DROP TABLE IF EXISTS `vaccines_booster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vaccines_booster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vaccine_id` int(11) NOT NULL,
  `start_age` int(11) NOT NULL DEFAULT '0',
  `start_age_scale` enum('YEAR','MONTH','WEEK','DAY') NOT NULL,
  `interval_` int(11) NOT NULL,
  `interval_scale` enum('YEAR','MONTH','WEEK','DAY') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_booster_vaccine_for_patients_enrolled` AFTER INSERT ON `vaccines_booster`
 FOR EACH ROW BEGIN
CALL set_patients_new_vaccine_booster(NEW.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vital`
--

DROP TABLE IF EXISTS `vital`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vital` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `unit` varchar(15) DEFAULT NULL,
  `min_val` varchar(11) DEFAULT NULL,
  `max_val` varchar(11) DEFAULT NULL,
  `pattern` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vital_sign`
--

DROP TABLE IF EXISTS `vital_sign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vital_sign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `read_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` varchar(16) NOT NULL,
  `in_patient_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL DEFAULT '1',
  `read_by` int(11) unsigned zerofill NOT NULL,
  `encounter_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2000 DEFAULT CHARSET=latin1 COMMENT='vital sign';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `date_used` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `voucher_batch`
--

DROP TABLE IF EXISTS `voucher_batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voucher_batch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(11) NOT NULL,
  `amount` float NOT NULL,
  `type` enum('refund','payment','discount') NOT NULL DEFAULT 'payment',
  `generator_id` int(11) NOT NULL,
  `description` text,
  `date_generated` datetime NOT NULL,
  `expiration_date` date NOT NULL,
  `service_centre_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ward`
--

DROP TABLE IF EXISTS `ward`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `cost_centre_id` int(11) NOT NULL DEFAULT '1',
  `block_id` int(11) NOT NULL,
  `billing_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ward_round`
--

DROP TABLE IF EXISTS `ward_round`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ward_round` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(15) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `frequency` int(11) NOT NULL DEFAULT '0' COMMENT 'Value in minutes',
  `entry_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_round_time` timestamp NULL DEFAULT NULL,
  `end_round_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `round_count` int(11) NOT NULL DEFAULT '0',
  `status` enum('Discharged','Ended','Cancelled','Active') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `zygote_type_ivf`
--

DROP TABLE IF EXISTS `zygote_type_ivf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zygote_type_ivf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `fertilized` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'medicplus_main'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `admission_charges` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8 */ ;;
/*!50003 SET character_set_results = utf8 */ ;;
/*!50003 SET collation_connection  = utf8_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `admission_charges` ON SCHEDULE EVERY 12 HOUR STARTS '2015-01-26 13:45:00' ON COMPLETION PRESERVE ENABLE DO BEGIN 
CALL bed_charge(); 
CALL other_admission_charges(); 
END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `clean_active_physio_bookings` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `clean_active_physio_bookings` ON SCHEDULE EVERY 1 MINUTE STARTS '2016-02-20 16:06:50' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE physiotherapy_booking SET active = FALSE WHERE id IN (SELECT m.interestedId FROM (SELECT b.id AS interestedId, b.`count` FROM physiotherapy_booking b LEFT JOIN physiotherapy_session s ON s.booking_id=b.id GROUP BY b.id HAVING COUNT(s.id) = b.`count`) m) AND active IS TRUE */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `close_old_antenatal` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `close_old_antenatal` ON SCHEDULE EVERY 1 WEEK STARTS '2017-10-27 00:00:00' ON COMPLETION PRESERVE ENABLE DO UPDATE enrollments_antenatal SET date_closed=NOW(), close_note='System', closed_by=1 WHERE DATE(NOW()) > DATE(date_add(ed_date, interval 4 week)) */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `expire_pa_code` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `expire_pa_code` ON SCHEDULE EVERY 1 DAY STARTS '2017-08-21 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN
UPDATE authorization_code SET `status`='expired' WHERE DATEDIFF(NOW(), receive_date)>30 AND `status` <> 'expired';
END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `lab_requests_recon` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `lab_requests_recon` ON SCHEDULE EVERY 5 MINUTE STARTS '2016-03-16 11:36:43' ON COMPLETION NOT PRESERVE ENABLE DO CALL lab_requests_recon() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `medicament_schedular` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `medicament_schedular` ON SCHEDULE EVERY 1 HOUR STARTS '2013-10-13 02:00:00' ON COMPLETION PRESERVE ENABLE DO call medicament_procudure() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `patient_vaccine_update` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `patient_vaccine_update` ON SCHEDULE EVERY 10 MINUTE STARTS '2014-02-25 00:00:00' ON COMPLETION PRESERVE DISABLE DO CALL new_vaccine_updater(0) */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `reset_cum_annual_days_on_admission` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `reset_cum_annual_days_on_admission` ON SCHEDULE EVERY 1 YEAR STARTS '2016-01-01 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
UPDATE patient_demograph SET cum_annual_days_on_admission = 0;
END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `reset_expired_insurance` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `reset_expired_insurance` ON SCHEDULE EVERY 1 DAY STARTS '2014-08-29 14:58:09' ON COMPLETION NOT PRESERVE ENABLE DO call reset_expired_insurance() */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `retire_credit_limit` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = '' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `retire_credit_limit` ON SCHEDULE EVERY 1 MINUTE STARTS '2015-12-23 00:00:00' ON COMPLETION PRESERVE ENABLE DO BEGIN 
CALL reset_credit_limit_fn();
END */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'medicplus_main'
--
/*!50003 DROP FUNCTION IF EXISTS `ANY_VALUE_` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `ANY_VALUE_`(`parameter0` TEXT) RETURNS text CHARSET latin1
    NO SQL
BEGIN 

RETURN parameter0;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `BED_COST_CENTRE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `BED_COST_CENTRE`(`bedID` INT) RETURNS int(11)
    NO SQL
BEGIN 
DECLARE cost_centre INT(11);

SELECT w.cost_centre_id FROM ward w LEFT JOIN room r ON r.ward_id=w.id LEFT JOIN bed b ON b.room_id=r.id WHERE b.id=bedID INTO cost_centre;

RETURN cost_centre;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `COVERAGE_TYPE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `COVERAGE_TYPE`(`itemCode` VARCHAR(20), `scheme_id` INT) RETURNS varchar(100) CHARSET latin1
    NO SQL
BEGIN 
DECLARE cType VARCHAR(100);

SELECT `type` FROM insurance_items_cost WHERE item_code = itemCode AND insurance_scheme_id=scheme_id INTO cType;

RETURN cType;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `ENCOUNTER_NOTES_COUNT` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `ENCOUNTER_NOTES_COUNT`(`ecId` INT(11), `cType` VARCHAR(20)) RETURNS int(11)
    NO SQL
BEGIN 
DECLARE count_type INT(11);

IF cType = 'complaints' THEN
SELECT COUNT(*) FROM patient_visit_notes WHERE encounter_id = ecId AND note_type in ('s', 'd') INTO count_type;
ELSEIF cType = 'plans' THEN 
SELECT COUNT(*) FROM patient_visit_notes WHERE encounter_id = ecId AND note_type  = 'p' INTO count_type;
ELSEIF cType = 'diagnoses' THEN
SELECT COUNT(*) FROM patient_visit_notes WHERE encounter_id = ecId AND note_type in ('a', 'g') INTO count_type;
END IF;

RETURN count_type;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `getFirstDayOfWeekDate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getFirstDayOfWeekDate`(`reverse` TINYINT(1)) RETURNS date
BEGIN
	IF reverse THEN
		SELECT CASE DAYOFWEEK(NOW())
			WHEN 1 THEN DATE(NOW())
			WHEN 2 THEN DATE_ADD(NOW(),INTERVAL -1 DAY)
			WHEN 3 THEN DATE_ADD(NOW(),INTERVAL -2 DAY)
			WHEN 4 THEN DATE_ADD(NOW(),INTERVAL -3 DAY)
			WHEN 5 THEN DATE_ADD(NOW(),INTERVAL -4 DAY)
			WHEN 6 THEN DATE_ADD(NOW(),INTERVAL -5 DAY)
			ELSE DATE_ADD(NOW(),INTERVAL -6 DAY) END INTO @date;
	ELSE
		SELECT CASE DAYOFWEEK(NOW())
		WHEN 1 THEN DATE_ADD(NOW(), INTERVAL 6 DAY)
		WHEN 2 THEN DATE_ADD(NOW(),INTERVAL 5 DAY)
		WHEN 3 THEN DATE_ADD(NOW(),INTERVAL 4 DAY)
		WHEN 4 THEN DATE_ADD(NOW(),INTERVAL 3 DAY)
		WHEN 5 THEN DATE_ADD(NOW(),INTERVAL 2 DAY)
		WHEN 6 THEN DATE_ADD(NOW(),INTERVAL 1 DAY)
		ELSE DATE(NOW()) END INTO @date;
	END IF;
	RETURN DATE(@date);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `getMinute` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getMinute`(`mid` INT) RETURNS int(11)
    NO SQL
BEGIN
	DECLARE unit VARCHAR(20);
    
	SELECT every INTO unit FROM patient_medicament WHERE id=mid;
    
    IF (unit = "Minute") THEN
    	RETURN 1;
    ELSEIF (unit = "Hour") THEN
    	RETURN 60;
    ELSEIF (unit = "Day") THEN
    	RETURN 1440;
    ELSEIF (unit = "Week") THEN
    	RETURN 10080;
    ELSEIF (unit = "Month") THEN
    	RETURN 40320;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `getVaccinePrice` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `getVaccinePrice`(`vid` INT(11)) RETURNS float
    NO SQL
BEGIN
	DECLARE vPrice FLOAT;
    
	SELECT default_price INTO vPrice FROM vaccines WHERE id=vid;
    RETURN vPrice;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `get_next_age` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_next_age`(`vid` INT, `s_age` INT) RETURNS varchar(16) CHARSET latin1
BEGIN
	DECLARE  scale VARCHAR(15);
	DECLARE age INT DEFAULT 0;	
	DECLARE next_age INT DEFAULT -1;	
		
		DECLARE done, found INT DEFAULT FALSE;
		DECLARE cur CURSOR FOR SELECT start_age, age_scale FROM vaccine_levels WHERE vaccine_id = vid ORDER BY level;
			
		block: BEGIN
			DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=TRUE;

			OPEN cur;			
				for_each_level: LOOP
					FETCH cur INTO age, scale;	
					IF (done) THEN
						LEAVE for_each_level;	
					END IF;
			
					IF (found) THEN
						SET next_age = age;
						LEAVE for_each_level;
					ELSEIF (age=s_age) THEN
						SET found=TRUE;
					END IF;						

				END LOOP for_each_level;
			CLOSE cur;
	END block;
	RETURN CONCAT(next_age, "|",scale);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `IS_ADMITTED` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `IS_ADMITTED`(`pid` INT) RETURNS tinyint(1)
    NO SQL
BEGIN 
DECLARE isAdmitted BOOLEAN;
DECLARE numRows INT;

SELECT COUNT(patient_id) FROM in_patient WHERE patient_id = pid AND `status` = 'Active' INTO numRows;

SELECT IF(numRows=1, TRUE, FALSE) INTO isAdmitted;

RETURN isAdmitted;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `MASK` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `MASK`(`formatted` VARCHAR(70)) RETURNS varchar(40) CHARSET latin1
    NO SQL
BEGIN 
RETURN CONCAT(SUBSTRING(formatted, 1, LENGTH(formatted)-7), '', SUBSTRING(SUBSTRING(formatted, -7), 1, 3), '', SUBSTRING(formatted, -4));   
              
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `PATIENT_SCHEME` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `PATIENT_SCHEME`(`pid` INT(11)) RETURNS varchar(100) CHARSET latin1
    NO SQL
BEGIN 
DECLARE schemeName VARCHAR(100);

SELECT iss.scheme_name FROM insurance ins LEFT JOIN insurance_schemes iss ON iss.id=ins.insurance_scheme WHERE ins.patient_id=pid INTO schemeName;

RETURN schemeName;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `PRIMARY_PHONE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `PRIMARY_PHONE`(`pid` INT) RETURNS varchar(25) CHARSET latin1
    NO SQL
BEGIN
DECLARE phone_ VARCHAR(25);

SELECT CONCAT('0', phone) FROM contact WHERE `primary` IS TRUE AND relation = 'self' AND patient_id = pid INTO phone_;

RETURN phone_;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `QUEUE_TRIAGED` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `QUEUE_TRIAGED`(`encounter_id` INT) RETURNS tinyint(4)
    NO SQL
BEGIN 
DECLARE triaged BOOLEAN;

IF encounter_id IS NULL THEN
RETURN FALSE;
ELSE 

    SELECT IF(triaged_by IS NOT NULL AND triaged_on IS NOT NULL, TRUE, FALSE) FROM encounter WHERE id=encounter_id INTO triaged;

    RETURN triaged;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `bed_charge` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `bed_charge`()
BEGIN    
DECLARE _patient VARCHAR(15) DEFAULT NULL;
DECLARE _id INT DEFAULT 0;
DECLARE _bed INT DEFAULT 0;
DECLARE _scheme INT DEFAULT 0;
DECLARE _payee INT DEFAULT 0;
DECLARE _bed_assign_date DATE;
DECLARE _amount DECIMAL(10, 2);
DECLARE _item_code VARCHAR(15);
DECLARE done_1 INT DEFAULT FALSE;
DECLARE _adm_date DATE;
DECLARE cur_1 CURSOR FOR
SELECT id, patient_id, bed_id, date_admitted FROM in_patient WHERE NOW() > DATE_SUB(bed_assign_date, INTERVAL 12 HOUR) AND DATE(bed_assign_date) <> CURRENT_DATE AND date_discharged IS NULL AND bed_id IS NOT NULL AND `status` = 'Active';
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1 = TRUE;
OPEN cur_1;
REPEAT
FETCH cur_1 INTO _id, _patient, _bed, _adm_date;
IF (!done_1) THEN
BEGIN
DECLARE item_covered BOOLEAN DEFAULT TRUE;
DECLARE _outstandingCr DECIMAL(10,2);
DECLARE _outstandingDr DECIMAL(10,2);
DECLARE CONTINUE HANDLER FOR NOT FOUND SET item_covered=0 ;
SELECT ic.selling_price, ic.insurance_scheme_id, ic.item_code INTO _amount, _scheme, _item_code FROM insurance_items_cost ic LEFT JOIN insurance i ON i.insurance_scheme = ic.insurance_scheme_id LEFT JOIN room_type rt ON rt.billing_code = ic.item_code LEFT JOIN room r ON r.type_id = rt.id LEFT JOIN bed b ON b.room_id = r.id WHERE b.id = _bed AND i.patient_id = _patient;

SELECT COALESCE(SUM(amount),0) FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = _patient AND (transaction_type = 'debit' OR transaction_type = 'discount' OR transaction_type = 'reversal' OR transaction_type = 'write-off' OR transaction_type = 'transfer-debit' ) AND insurance_schemes.pay_type = 'self' AND cancelled_on IS NULL INTO _outstandingDr;
SELECT COALESCE(SUM(amount),0) AS amount FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = _patient AND (transaction_type = 'credit' OR transaction_type = 'refund' OR transaction_type = 'transfer-credit') AND insurance_schemes.pay_type = 'self' AND cancelled_on IS NULL INTO _outstandingCr;

IF _amount IS NULL THEN
SELECT itc.selling_price, itc.insurance_scheme_id, itc.item_code INTO _amount, _scheme, _item_code
FROM insurance_items_cost itc LEFT JOIN room_type rt ON itc.item_code = rt.billing_code LEFT JOIN room r ON rt.id = r.type_id LEFT JOIN bed b ON b.room_id = r.id WHERE b.id = _bed AND itc.insurance_scheme_id = 1;
END IF;
INSERT INTO bills (`patient_id`, transaction_date, due_date, description, bill_source_id, in_patient_id, transaction_type, amount, balance, billed_to, discounted, hospid, item_code, cost_centre_id)
VALUES (_patient, NOW(), NOW(), 'Admission Bed Charge', (SELECT id FROM bills_source WHERE name = 'admissions'), _id, 'credit', _amount, (_outstandingDr + _outstandingCr), _scheme, 'no', 1, _item_code, BED_COST_CENTRE(_bed));

SET _amount = NULL;
SET _scheme = NULL;
SET _item_code = NULL;
END;
END IF;

UPDATE patient_demograph SET cum_annual_days_on_admission  =  cum_annual_days_on_admission + 1 WHERE patient_ID = _patient;

UNTIL done_1 END REPEAT;
CLOSE cur_1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `generate_vaccine_message` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_vaccine_message`()
BEGIN
	DECLARE v_label, pid  VARCHAR(128);
	DECLARE v_due_date, v_exp_date  DATE;
	DECLARE pvid, vid, v_lev  INT DEFAULT 0;
	DECLARE v_price DECIMAL DEFAULT 0;
	
	DECLARE done INT DEFAULT FALSE;
	DECLARE cur CURSOR FOR 
		

	SELECT v.label, v.default_price, pv.id, pv.patient_id, pv.vaccine_id, pv.vaccine_level, pv.due_date, pv.expiration_date 
		FROM vaccines v JOIN patient_vaccine pv ON (v.id=pv.vaccine_id AND  (pv.id NOT IN (SELECT data_id FROM message_queue_temp) )  )
			WHERE date(NOW()) BETWEEN due_date AND expiration_date AND entry_date is NULL ;

		block: BEGIN
			DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=TRUE;

			OPEN cur;			
				for_each_due_vaccine: LOOP
					FETCH cur INTO v_label, v_price, pvid, pid, vid, v_lev, v_due_date, v_exp_date;	
					IF (done) THEN
						LEAVE for_each_due_vaccine;	
					END IF;
					
					select template_text into @m_template from message_template where text_type = 'justdue';
					INSERT INTO message_queue_temp (data_id, source, message_content, patient) VALUES (pvid, "vaccine", replace(replace(replace(@m_template,'{DUE_DATE}',DATE_FORMAT(date(v_due_date),"%D %b, %Y")),'{VACCINE}',v_label), '{COST}', v_price), pid) ;
									
			END LOOP for_each_due_vaccine;
		CLOSE cur;
	END block;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `lab_requests_recon` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `lab_requests_recon`()
BEGIN
    DECLARE GROUP_CODE VARCHAR(50);
    DECLARE x INT DEFAULT 0;

    DECLARE done_1 INT DEFAULT FALSE;
    DECLARE cur_1 CURSOR FOR
      SELECT COUNT(*), lab_group_id FROM lab_requests GROUP BY lab_group_id HAVING COUNT(*) > 1;

      block_1: BEGIN
      DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1 = TRUE;

      OPEN cur_1;
      for_each_requests_to_normalize: LOOP
        FETCH cur_1 INTO x, GROUP_CODE;
        IF (done_1) THEN
          LEAVE for_each_requests_to_normalize;
        END IF;

          block_2: BEGIN
          DECLARE PatientID INT;
          DECLARE Lab_group_name VARCHAR(50);
          DECLARE newVal VARCHAR(50);
          DECLARE y INT DEFAULT 0;
          DECLARE done_2 INT DEFAULT FALSE;
          DECLARE cur_2 CURSOR FOR SELECT patient_id, lab_group_id FROM lab_requests WHERE lab_group_id = GROUP_CODE;
          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_2 = TRUE;
          SET y=0;

          OPEN cur_2;
          group_x_to_normalize: LOOP
            FETCH cur_2 INTO PatientID, Lab_group_name;
            SET y=y+1;
            IF (done_2) THEN
              LEAVE group_x_to_normalize;
            END IF;

            SELECT CONCAT(Lab_group_name, '/', y) INTO newVal;
            

            UPDATE lab_requests SET lab_group_id = newVal WHERE lab_group_id = Lab_group_name AND patient_id = PatientID;
            UPDATE patient_labs SET lab_group_id = newVal WHERE lab_group_id = Lab_group_name AND patient_id = PatientID;

          END LOOP group_x_to_normalize;
        CLOSE cur_2;
      END block_2;
    END LOOP for_each_requests_to_normalize;
    CLOSE cur_1;
  END block_1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `new_patient_set_vaccine_boosters` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `new_patient_set_vaccine_boosters`(IN pid VARCHAR(15))
BEGIN
  DECLARE p_dob DATE;
  DECLARE done INT DEFAULT FALSE;
  DECLARE patients CURSOR FOR SELECT p.date_of_birth FROM enrollments_immunization i LEFT JOIN patient_demograph p ON i.patient_id = p.patient_ID WHERE i.patient_id = pid;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=TRUE;
  OPEN patients;
    for_each_patient_under_immunisation: LOOP
      FETCH patients INTO p_dob;
      IF (done) THEN
        LEAVE for_each_patient_under_immunisation;
      END IF;

      block_1: BEGIN
        DECLARE start_a, vid INT;
        DECLARE start_a_scale VARCHAR(20);
        DECLARE booster_date DATE;

        DECLARE done_1 INT DEFAULT FALSE;
        DECLARE bvaccines CURSOR FOR SELECT v.id, v.start_age, v.start_age_scale FROM vaccines_booster v;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1=TRUE;

        OPEN bvaccines;
          for_each_booster_vaccine: LOOP
            FETCH bvaccines INTO vid, start_a, start_a_scale;
            IF(done_1) THEN
              LEAVE for_each_booster_vaccine;
            END IF;

            CASE start_a_scale
              WHEN "DAY" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a DAY);
              WHEN "WEEK" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a WEEK);
              WHEN "MONTH" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a MONTH);
              WHEN "YEAR" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a YEAR);
            END CASE;

            INSERT INTO patient_vaccine_booster(patient_id, vaccinebooster_id, start_date, next_due_date) VALUES (pid, vid, booster_date, booster_date);
          END LOOP for_each_booster_vaccine;
        CLOSE bvaccines;
      END block_1;
    END LOOP for_each_patient_under_immunisation;
  CLOSE patients;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `new_vaccine_updater` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `new_vaccine_updater`(IN `pid` VARCHAR(15))
BEGIN
	DECLARE pat_id VARCHAR(15);
	DECLARE enrollmentD, p_dob DATE;
	DECLARE x,y INT DEFAULT 0;

	DECLARE done_1 INT DEFAULT FALSE;
	DECLARE cur_1 CURSOR FOR

		SELECT i.patient_id, i.enrolled_on, p.date_of_birth FROM enrollments_immunization i, patient_demograph p WHERE IF (pid= '0',  (i.patient_id = p.patient_ID),  (i.patient_id = p.patient_ID AND i.patient_id = pid));

		block_1: BEGIN
			DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1=TRUE;

			OPEN cur_1;
				for_each_petient_under_immunisation: LOOP
					FETCH cur_1 INTO pat_id, enrollmentD, p_dob;
					IF (done_1) THEN
						LEAVE for_each_petient_under_immunisation;
					END IF;

					block_2: BEGIN
						DECLARE level_id, vac_id, lev, start_a, end_a INT;
						DECLARE days_diff INT DEFAULT 0;
						DECLARE v_name, start_a_scale, end_a_scale VARCHAR(120);
						DECLARE due_date, exp_date DATE;

						DECLARE done_2 INT DEFAULT FALSE;
						DECLARE cur_2 CURSOR FOR  SELECT vl.id, vl.vaccine_id, vl.level, vl.start_age, vl.end_age, vl.start_age_scale, vl.end_age_scale, v.label FROM vaccine_levels vl, vaccines v WHERE (vl.vaccine_id = v.id);
						DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_2=TRUE;

						OPEN cur_2;
							for_each_vaccine_level: LOOP
								FETCH cur_2 INTO level_id, vac_id, lev, start_a, end_a, start_a_scale, end_a_scale, v_name;
								IF (done_2) THEN
									LEAVE for_each_vaccine_level;
								END IF;

								SELECT CASE start_a_scale
									WHEN "WEEK" THEN
										DATE_ADD(p_dob, INTERVAL start_a WEEK)
									WHEN "MONTH" THEN
										DATE_ADD(p_dob, INTERVAL start_a MONTH)
                                    WHEN "YEAR" THEN
									    DATE_ADD(p_dob, INTERVAL start_a YEAR) END INTO due_date;

								SELECT CASE end_a_scale
									WHEN "WEEK" THEN
										DATE_ADD(p_dob, INTERVAL end_a WEEK)
									WHEN "MONTH" THEN
										DATE_ADD(p_dob, INTERVAL end_a MONTH)
									WHEN "YEAR" THEN
                                    	DATE_ADD(p_dob, INTERVAL end_a YEAR) END INTO exp_date;

								SET x=0;
								SELECT COUNT(*) INTO x FROM patient_vaccine WHERE ((vaccine_level = lev) AND (patient_id = pat_id) AND (vaccine_id = vac_id));
								IF(x = 0) THEN
									INSERT INTO patient_vaccine (patient_id, vaccine_id, vaccine_level, due_date, expiration_date) VALUES (pat_id, vac_id, lev, due_date, exp_date);
								END IF;

							END LOOP for_each_vaccine_level;
						CLOSE cur_2;
					END block_2;
				END LOOP for_each_petient_under_immunisation;
			CLOSE cur_1;
		END block_1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `other_admission_charges` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `other_admission_charges`()
BEGIN
DECLARE _patient VARCHAR(15) DEFAULT NULL;
DECLARE _id INT DEFAULT 0;
DECLARE _bed INT DEFAULT 0;
DECLARE _scheme INT DEFAULT 0;
DECLARE _payee INT DEFAULT 0;
DECLARE _bed_assign_date DATE;
DECLARE _amount DECIMAL(10,2);
DECLARE _adFee DECIMAL(10,2);
DECLARE done_1 INT DEFAULT FALSE;
DECLARE done_2 INT DEFAULT FALSE;

DECLARE _bill_desc VARCHAR(100) DEFAULT NULL;
DECLARE _item_code VARCHAR(10) DEFAULT NULL;

DECLARE cur_1 CURSOR FOR
SELECT id, patient_id, bed_id FROM in_patient WHERE NOW() > DATE_SUB(bed_assign_date, INTERVAL 12 HOUR) AND DATE(bed_assign_date) <> CURRENT_DATE AND date_discharged IS NULL AND bed_id IS NOT NULL AND `status` = 'Active';
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1 = TRUE;


OPEN cur_1;
REPEAT
FETCH cur_1 INTO _id, _patient, _bed;
IF ( ! done_1 ) THEN
BEGIN
DECLARE num_rows INT DEFAULT 0;
DECLARE cur_2 CURSOR FOR SELECT ac.item_name, ac.billing_code FROM admission_config ac;
DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_2 = TRUE;

SELECT _patient;
OPEN cur_2;
SELECT FOUND_ROWS() INTO num_rows; 

feesLoop: LOOP
FETCH cur_2 INTO _bill_desc, _item_code;

BEGIN

IF (num_rows <> 0) THEN
BEGIN

DECLARE item_covered BOOLEAN DEFAULT TRUE;
DECLARE _outstandingCr DECIMAL(10,2);
DECLARE _outstandingDr DECIMAL(10,2);
DECLARE CONTINUE HANDLER FOR NOT FOUND SET item_covered=0 ;
SELECT selling_price, insurance_scheme_id INTO @amount, @payee FROM insurance_items_cost c LEFT JOIN insurance i ON i.insurance_scheme=c.insurance_scheme_id WHERE c.item_code=_item_code AND i.patient_id=_patient;
SELECT COALESCE(SUM(amount),0) FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = _patient AND (transaction_type = 'debit' OR transaction_type = 'discount' OR transaction_type = 'reversal' OR transaction_type = 'write-off' OR transaction_type = 'transfer-debit' ) AND insurance_schemes.pay_type = 'self' AND cancelled_on IS NULL INTO _outstandingDr;
SELECT COALESCE(SUM(amount),0) AS amount FROM bills b LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to WHERE patient_id = _patient AND (transaction_type = 'credit' OR transaction_type = 'refund' OR transaction_type = 'transfer-credit') AND insurance_schemes.pay_type = 'self' AND cancelled_on IS NULL INTO _outstandingCr;
IF @amount IS NOT NULL THEN
INSERT INTO bills (`patient_id`, transaction_date, due_date, description, bill_source_id, in_patient_id, transaction_type, amount, balance, billed_to, discounted, hospid, item_code, cost_centre_id) VALUES (_patient, NOW(), NOW(), _bill_desc, (SELECT id FROM bills_source WHERE name = 'admissions'), _id, 'credit', @amount, (_outstandingDr + _outstandingCr), @payee, 'no', 1, _item_code, BED_COST_CENTRE(_bed));
ELSE
SELECT ic.selling_price INTO _amount FROM admission_config ac LEFT JOIN insurance_items_cost ic ON ac.billing_code = ic.item_code WHERE ac.billing_code = _item_code AND ic.insurance_scheme_id = 1;
INSERT INTO bills (`patient_id`, transaction_date, due_date, description, bill_source_id, in_patient_id, transaction_type, amount, balance, billed_to, discounted, hospid, item_code, cost_centre_id) VALUES (_patient, NOW(), NOW(), _bill_desc, (SELECT id FROM bills_source WHERE name = 'admissions'), _id, 'credit', _amount, (_outstandingDr + _outstandingCr), 1, 'no', 1, _item_code, BED_COST_CENTRE(_bed));
END IF;
SET @amount = NULL;
SET @payee = NULL;
END;
SELECT _bill_desc, _item_code, _patient;
SET num_rows = num_rows - 1;
ELSE
LEAVE feesLoop;
END IF;
END;
END LOOP feesLoop;
close cur_2;
END;
END IF;
UNTIL done_1 END REPEAT;
CLOSE cur_1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `reset_credit_limit_fn` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_credit_limit_fn`()
    NO SQL
UPDATE `credit_limit` SET amount = 0, expiration = NOW(), set_by = 1 WHERE amount > 0 AND DATE(expiration) < DATE(NOW()) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `reset_expired_insurance` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `reset_expired_insurance`()
    NO SQL
BEGIN

update insurance i left join insurance_schemes s on s.id=i.insurance_scheme set i.active=false where date(now()) > date(insurance_expiration) and s.pay_type = 'insurance'; 
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `set_patients_new_vaccine_booster` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `set_patients_new_vaccine_booster`(IN bid INT)
BEGIN
  DECLARE pat_id VARCHAR(15);
  DECLARE p_dob DATE;
  DECLARE x INT DEFAULT 0;

  DECLARE done INT DEFAULT FALSE;
  DECLARE patients CURSOR FOR SELECT i.patient_id, p.date_of_birth FROM enrollments_immunization i LEFT JOIN patient_demograph p ON i.patient_id = p.patient_ID;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=TRUE;

  OPEN patients;
    for_each_patient_under_immunisation: LOOP
      FETCH patients INTO pat_id, p_dob;
      IF (done) THEN
        LEAVE for_each_patient_under_immunisation;
      END IF;

      block_1: BEGIN
        DECLARE start_a INT;
        DECLARE start_a_scale VARCHAR(20);
        DECLARE booster_date DATE;

        DECLARE done_1 INT DEFAULT FALSE;
        DECLARE bvaccines CURSOR FOR SELECT v.start_age, v.start_age_scale FROM vaccines_booster v WHERE v.id = bid;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done_1=TRUE;

        OPEN bvaccines;
          FETCH bvaccines INTO start_a, start_a_scale;
          IF(done_1) THEN
            LEAVE for_each_patient_under_immunisation;
          END IF;

          CASE start_a_scale
            WHEN "DAY" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a DAY);
            WHEN "WEEK" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a WEEK);
            WHEN "MONTH" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a MONTH);
            WHEN "YEAR" THEN
              SET booster_date = DATE_ADD(p_dob, INTERVAL start_a YEAR);
          END CASE;

          SET x=0;
          SELECT COUNT(*) INTO x FROM enrollments_immunization;
          IF(x <> 0) THEN
            INSERT INTO patient_vaccine_booster(patient_id, vaccinebooster_id, start_date, next_due_date) VALUES (pat_id, bid, booster_date, booster_date);
          END IF;
        CLOSE bvaccines;
      END block_1;
    END LOOP for_each_patient_under_immunisation;
  CLOSE patients;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `ivf_simulation_`
--

/*!50001 DROP VIEW IF EXISTS `ivf_simulation_`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `ivf_simulation_` AS select `s`.`id` AS `id`,`s`.`enrolment_id` AS `enrolment_id`,`s`.`record_date` AS `record_date`,`s`.`recorded_by_id` AS `recorded_by_id`,`s`.`day` AS `day`,`s`.`endo` AS `endo`,`s`.`e2` AS `e2`,`s`.`gnrha` AS `gnrha`,`s`.`ant` AS `ant`,`s`.`fsh` AS `fsh`,`s`.`hmg` AS `hmg`,`s`.`remarks` AS `remarks`,(select sum(`sd`.`left_side`) AS `left_totals` from `ivf_simulation_data` `sd` where (`sd`.`ivf_simulation_id` = `s`.`id`)) AS `totals_left`,(select sum(`sd`.`right_side`) AS `left_totals` from `ivf_simulation_data` `sd` where (`sd`.`ivf_simulation_id` = `s`.`id`)) AS `totals_right` from `ivf_simulation` `s` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-03-13 13:53:48
