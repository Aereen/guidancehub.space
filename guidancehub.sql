/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.10-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: u406807013_guidancehub
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement`
--

/*!40000 ALTER TABLE `announcement` DISABLE KEYS */;
INSERT INTO `announcement` VALUES
(1,'University Guidance Week','Join us for a week of self-discovery and career guidance. Workshops and counseling sessions available!','2025-03-01 02:00:00'),
(2,'New Assessment Schedule Released','Check the latest schedule for personality and career assessments. Book your slots now.','2025-03-05 00:30:00'),
(3,'Mental Health Awareness Webinar','A special webinar on mental health and stress management will be held this Friday. Register now!','2025-03-10 07:00:00');
/*!40000 ALTER TABLE `announcement` ENABLE KEYS */;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `college` varchar(100) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `feelings` text DEFAULT NULL,
  `need_counselor` varchar(5) NOT NULL,
  `counseling_type` varchar(50) NOT NULL,
  `first_date` date NOT NULL,
  `first_time` time NOT NULL,
  `second_date` date NOT NULL,
  `second_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Pending','Schedule','Completed','Postponed') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES
(2,'CS-202503-002','ivan gaslang','stefgaslang@gmail.com','CCSE','4th Year','maby','Burned Out','Yes','Virtual','2025-03-18','14:00:00','2025-03-19','17:00:00','2025-03-17 10:16:28','2025-04-11 00:42:53',''),
(3,'CS-202503-003','All Cedrick G. Panganiban','eirene.armilla@gmail.com','CCIS','4th Year','AINS','Scared, Angry, Burned Out','Yes','Virtual','2025-03-28','16:00:00','2025-04-05','15:00:00','2025-03-17 14:46:24','2025-03-17 14:46:24','Pending'),
(7,'CS-202503-005','Jasmin H. Butalid','eirene.armilla@gmail.com','CCIS','3rd Year','AINS','Angry, Confused, Calm','Yes','Virtual','2025-04-04','15:00:00','2025-05-04','15:00:00','2025-03-18 03:45:11','2025-03-18 03:45:11','Pending'),
(11,'CS-202504-001','Ivan Gaslang','stefgaslang@gmail.com','CHK','5th Year','awd','Excited, Numb','Yes','Virtual','2025-04-19','14:13:00','2025-04-19','12:31:00','2025-04-18 16:10:12','2025-04-18 16:10:12','Pending'),
(13,'CS-202504-002','Eirene Grace Armilla','earmilla.a12035445@umak.edu.ph','CCIS','4th Year','AINS','Excited, Scared','Yes','Virtual','2025-04-30','15:00:00','2025-05-05','14:00:00','2025-04-21 08:33:22','2025-04-21 08:33:22','Pending');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;

--
-- Table structure for table `assessments`
--

DROP TABLE IF EXISTS `assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `test_type` enum('Personality','Traits','Intelligence','Emotional','Aptitude','Career','Behavioral') NOT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Scheduled','Completed','Postponed') DEFAULT 'Pending',
  `college` enum('CBFS','CCIS','CCSE','CGPP','CHK','CITE','CTM','CTHM','IOA','IAD','IIHS','ION','IOP','IOPsy','ISDNB','HSU','SOL') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assessments`
--

/*!40000 ALTER TABLE `assessments` DISABLE KEYS */;
INSERT INTO `assessments` VALUES
(1,'','Juan P. Dela Cruz','juan.delacruz@umak.edu.ph','Personality','2025-03-20','09:00:00','2025-03-13 05:42:30','Completed','CBFS'),
(2,'','Maria L. Santos','maria.santos@umak.edu.ph','Intelligence','2025-03-21','10:30:00','2025-03-13 05:42:30','Completed','CBFS'),
(3,'','Carlos M. Reyes','carlos.reyes@umak.edu.ph','Aptitude','2025-03-22','14:00:00','2025-03-13 05:42:30','Completed','CBFS'),
(4,'','Anna B. Mendoza','anna.mendoza@umak.edu.ph','Career','2025-03-23','08:30:00','2025-03-13 05:42:30','Postponed','CBFS'),
(5,'','David C. Villanueva','david.villanueva@umak.edu.ph','Behavioral','2025-03-24','11:00:00','2025-03-13 05:42:30','Scheduled','CBFS'),
(7,'AS-202504-001','Eirene Grace Armilla','earmilla.a12035445@umak.edu.ph','Personality','2025-04-30','14:30:00','2025-04-21 08:37:48','Pending','CCIS');
/*!40000 ALTER TABLE `assessments` ENABLE KEYS */;

--
-- Table structure for table `guidance_staff`
--

DROP TABLE IF EXISTS `guidance_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guidance_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  `qualifications` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guidance_staff`
--

/*!40000 ALTER TABLE `guidance_staff` DISABLE KEYS */;
INSERT INTO `guidance_staff` VALUES
(1,'Ms. Gichelle Hanna C. Roxas, RGC, RPm','Director','RGC, RPm','2025-03-17 01:16:36'),
(2,'Ms. Karen M. Rico, MAEd, RGC','Guidance Counselor','MAEd, RGC','2025-03-17 01:16:36'),
(3,'Ms. Maria Romanita C. Deborja, RPm, LPT','Guidance Coordinator','RPm, LPT','2025-03-17 01:16:36'),
(4,'Ma. Romanita C. De Borja, RPm, LPT','Guidance Coordinator','RPm, LPT','2025-03-17 01:16:36'),
(5,'Ms. Estella O. Obnamia, RPm, LPT','Guidance Coordinator','RPm, LPT','2025-03-17 01:16:36'),
(6,'Estella O. Obnamia, MP, RPm, LPT','Guidance Coordinator','MP, RPm, LPT','2025-03-17 01:16:36'),
(7,'Aiko B. Caguioa','Guidance Coordinator',NULL,'2025-03-17 01:16:36'),
(8,'Mr. Bowie L. Bello, RPm','Guidance Coordinator','RPm','2025-03-17 01:16:36'),
(9,'Ms. Janella N. Largadad, RPm, CHRA','Guidance Coordinator/ Guidance Secretary','RPm, CHRA','2025-03-17 01:16:36'),
(10,'Carolyn S.M. Balsamo, RSW','IEGAD Coordinator','RSW','2025-03-17 01:16:36'),
(11,'Prof. Maria Teresa D. Tabbu, Edp','Associate Guidance Coordinator','Edp','2025-03-17 01:16:36'),
(12,'Dr. Evangeline M. Alayon, RGC, LPT','Associate Guidance Coordinator','RGC, LPT','2025-03-17 01:16:36'),
(13,'Dr. Lucia B. Dela Cruz, RGC','Associate Guidance Coordinator','RGC','2025-03-17 01:16:36'),
(14,'Prof. Kim Patrick Magdangan, MAEd, RGC','Associate Guidance Coordinator','MAEd, RGC','2025-03-17 01:16:36'),
(15,'Dr. Francisco M. Lambojon, Jr., RPsy','Associate Psychologist','RPsy','2025-03-17 01:16:36');
/*!40000 ALTER TABLE `guidance_staff` ENABLE KEYS */;

--
-- Table structure for table `individual_inventory`
--

DROP TABLE IF EXISTS `individual_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `individual_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_name` varchar(255) DEFAULT NULL,
  `student_number` varchar(50) NOT NULL,
  `student_email` varchar(255) DEFAULT NULL,
  `student_contact` varchar(15) NOT NULL,
  `student_birthdate` date NOT NULL,
  `student_age` int(11) NOT NULL,
  `student_gender` enum('Male','Female','Intersex') NOT NULL,
  `civil_status` enum('Single','Married','Widow') NOT NULL,
  `address` text NOT NULL,
  `religion` enum('Catholic','Muslim','Iglesia ni Cristo','Atheist','Others') NOT NULL,
  `religion_specify` varchar(255) DEFAULT NULL,
  `college_dept` enum('CBFS','CCIS','CCSE','CGPP','CHK','CITE','CTM','CTHM','IOA','IAD','IIHS','ION','IOP','IOPsy','ISDNB','HSU','SOL') NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year','5th Year') NOT NULL,
  `elementary` varchar(255) NOT NULL,
  `elementary_year` int(11) NOT NULL,
  `junior_high` varchar(255) NOT NULL,
  `junior_year` int(11) NOT NULL,
  `senior_high` varchar(255) NOT NULL,
  `senior_year` int(11) NOT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `college_year` int(11) DEFAULT NULL,
  `national_exam` varchar(255) DEFAULT NULL,
  `board_exam` varchar(255) DEFAULT NULL,
  `spouse_name` varchar(255) DEFAULT NULL,
  `date_marriage` date DEFAULT NULL,
  `place_marriage` varchar(255) DEFAULT NULL,
  `spouse_occupation` varchar(255) DEFAULT NULL,
  `spouse_employer` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_number` (`student_number`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `individual_inventory`
--

/*!40000 ALTER TABLE `individual_inventory` DISABLE KEYS */;
INSERT INTO `individual_inventory` VALUES
(2,'Panganiba all cedrick G.','A12138583','aiglesia.a12343976@umak.edu.ph','09771581157','2000-03-24',24,'Male','Single','blk 17 lot3 ','Catholic','','','4th Year','Langkiwa elem school',2020,'Tibagan high ',2022,'Dksje',2021,NULL,2525,'','','','0000-00-00','','',''),
(4,'Restoles, Rose Ann Sergio','K12254674','rrestoles.k12254674@umak.edu.ph','09204015350','2005-09-29',19,'Female','Single','6060 Geberal Ricarte South Cembo, Taguig City','Catholic','','','1st Year','South  Cembo Elementary School',2018,'Pitogo High School',2022,'University of Makati',2024,NULL,2028,'NA','NA','','0000-00-00','','',''),
(5,'Cartel, Karen V.','K12255610','kcartel.k12255610@umak.edu.ph','09925598559','2005-09-09',19,'Female','Single','309 cruiser st. Brgy., Post proper southside, Taguig City','Catholic','','','1st Year','Palar Integrated School',2018,'Palar Integrated School',2022,'University of Makati ',2024,NULL,2028,'','','','0000-00-00','','',''),
(6,'Carreon, John Christian C.','K12046374','jcarreon.k12046374@umak.edu.ph','09661602389','2003-04-30',21,'Male','Single','138-A 8th Avenue, East Rembo, Taguig City','Catholic','','','3rd Year','PINESLIGHT SCHOOL OF MAKATI ',2016,'MARANATHA CHRISTIAN ACADEMY ',2020,'University of Makati ',2022,NULL,2025,'','','','0000-00-00','','',''),
(7,'MANGUIAT, Angelee Jhoie P.','K11936642','amanguuat.k11936642@umak.edu.ph','09685605611','2002-10-24',22,'Female','Single','132 Crusader A, Pinagsama, Taguig City','Catholic','','','4th Year','Rizal Elementary School',2015,'Fort Bonifacio High School',2019,'University of Makati',2021,NULL,2025,'None','NA','','0000-00-00','','',''),
(9,'Frivaldo, fionna Marie G.','K12254982','ffrivaldo.k12254982@umak.edu.ph','09772901174','2005-05-28',19,'Female','Single','Homonhon St. Pitogo, taguig city','Catholic','','','1st Year','Pitogo Elementary School',2011,'Pitogo High School',2020,'University of Makati',2024,NULL,2025,'NONE','None','','0000-00-00','','',''),
(11,'Arzadon, Jan Lee Jen, Bernacer','K12256187','jarzadon.k12256187@umak.edu.ph','09380748632','2003-06-22',21,'Female','Single','46-B Mabini Street West Rembo, Taguig City','Catholic','','','1st Year','Fort Bonifacio Elementary School',2009,'Benigno Ninoy S. Aquino High School',2016,'University of Makati ',2022,NULL,2025,'','','','0000-00-00','','',''),
(14,'Abalos, Jenelyn Mutia','a12240575','jabalos.a12240575@umak.edu.ph','09674228806','2005-01-18',20,'Female','Single','2045 Edison St., Brgy. San Isidro Makati City','Catholic','','','3rd Year','Epifanio Delos Santos Elementary Schools',2016,'Pasay City National High School Tramo',2020,'Pasay City National Highschool Tramo',2022,NULL,2025,'n/a','n/a','','0000-00-00','','',''),
(15,'Calipayan, Antonette, C.','A12240577','acalipayan.a12240577@umak.edu.ph','09511659117','2003-10-19',21,'Female','Single','2951 Balabac St. Pinagkaisahan, Makati City','Catholic','','','3rd Year','Tenement Elementary School ',2016,'Mother of Perpetual Help School',2020,'Army\'s Angel Integrated School',2022,NULL,2025,'N/A','N/A','','0000-00-00','','',''),
(23,'Lacanlale, Shannen Miranda','K12257783','slacanlale.k12257783@umak.edu.ph','09397180705','2006-09-10',18,'Female','Single','Blk 33 lot 3 umbel st. Pembo, Taguig','Atheist','','CCSE','1st Year','Maria Montessori Holy Christian School Inc.',2017,'Maria Montessori Holy Christian School Inc.',2020,'University Of Makati - Higher School Ng Umak',2024,NULL,1,'N/A','N/A','N/A','0000-00-00','','',''),
(27,'Eirene Grace Armilla','a12035445','earmilla.a12035445@umak.edu.ph','09217099305','2002-02-23',23,'Female','Single','4220 Mojica St. Bangkal, Makati City','Catholic','','CCIS','4th Year','Bangkal Elementary School',2014,'Bangkal High School',2018,'STI College Pasay-Edsa',2020,'',0,'','','','0000-00-00','','','');
/*!40000 ALTER TABLE `individual_inventory` ENABLE KEYS */;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` varchar(50) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `college` enum('CBFS','CCIS','CCSE','CGPP','CHK','CITE','CTM','CTHM','IOA','IAD','IIHS','ION','IOP','IOPsy','ISDNB','HSU','SOL') NOT NULL,
  `reason` text NOT NULL,
  `terms_accepted` tinyint(1) NOT NULL,
  `referrer_name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `college_dept` enum('CBFS','CCIS','CCSE','CGPP','CHK','CITE','CTM','CTHM','IOA','IAD','IIHS','ION','IOP','IOPsy','ISDNB','HSU','SOL') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `referrer_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
INSERT INTO `referrals` VALUES
(1,'RF-202503-001','Eirene Grace Q. Armilla','CCIS','Absenteeism',1,'Cecilia E. Tadeo','Professor','CCIS','2025-03-17 18:15:14',NULL),
(2,'RF-202503-002','Eirene Grace Armilla','CCIS','Absenteeism',1,'All Cedrick G. Panganiban','Professor','CCIS','2025-03-18 03:47:01',NULL),
(3,'RF-202504-001','Eunice Gale Armilla','CCIS','Behavioral',1,'Shane Estrella','Professor','IOA','2025-04-19 04:25:06',NULL),
(4,'RF-202504-002','Eirene Grace Armilla','CCIS','Behavioral',1,'Eunice Armilla','Professor','CCIS','2025-04-21 08:14:47',NULL),
(5,'RF-202504-003','HICETA','CCIS','Absences',1,'LILIBETH ARCALAS','Faculty','CCIS','2025-04-22 04:46:41',NULL);
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `resource_link` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resources`
--

/*!40000 ALTER TABLE `resources` DISABLE KEYS */;
INSERT INTO `resources` VALUES
(1,'Understanding Emotional Intelligence','Learn how emotional intelligence can improve your relationships, work, and mental health.','https://example.com/emotional-intelligence','2025-03-13 05:26:00'),
(2,'Career Pathways Guide','Discover different career paths based on your skills and interests.','https://example.com/career-guide','2025-03-13 05:26:00'),
(3,'Personality Test Breakdown','A detailed explanation of personality test results and what they mean.','https://example.com/personality-test','2025-03-13 05:26:00'),
(4,'Stress Management Tips','Simple and effective ways to manage stress in your daily life.','https://example.com/stress-management','2025-03-13 05:26:00'),
(5,'Comprehensive School Counseling Toolkit','A curated collection of evidence‑based lesson plans, intervention strategies, and printable worksheets designed to support K‑12 students’ academic, career, and social–emotional development. Includes step‑by‑step guidance for individual counseling sessions, small‑group activities, crisis response, and parent/teacher collaboration.','https://schoolcounselingtoolkit.org','2025-05-05 15:25:00');
/*!40000 ALTER TABLE `resources` ENABLE KEYS */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `id_number` varchar(50) NOT NULL,
  `role` enum('Counselor','Faculty','Student','Admin') NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `id_number` (`id_number`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(15,'GuidanceHub','guidancehub01@gmail.com','a12345678','Admin','GuidanceHub@2025','0000-00-00 00:00:00'),
(17,'Joie Delgado','jdelgado.k11829484@umak.edu.ph','K11829484','Student','Student@2025','0000-00-00 00:00:00'),
(18,'Lanz Siedric Albaytar','lalbaytar.k11939908@umak.edu.ph','K11939908','Student','Student@2025','0000-00-00 00:00:00'),
(19,'Renz Amante','ramante.a12137433@umak.edu.ph','A12137433','Student','Student@2025','0000-00-00 00:00:00'),
(20,'John Paul Arellano','jarellano.a12137436@umak.edu.ph','A12137436','Student','Student@2025','0000-00-00 00:00:00'),
(21,'Cris James Arias','carias.a12137437@umak.edu.ph','A12137437','Student','Student@2025','0000-00-00 00:00:00'),
(23,'Dimple Atilano','datilano.a12137439@umak.edu.ph','A12137439','Student','Student@2025','0000-00-00 00:00:00'),
(24,'Steven Rey Barquin','sbarquin.k11936154@umak.edu.ph','K11936154','Student','Student@2025','0000-00-00 00:00:00'),
(25,'Iverson Bayoneta','ibayoneta.a12137451@umak.edu.ph','A12137451','Student','Student@2025','0000-00-00 00:00:00'),
(26,'Jasmin Butalid','jbutalid.a12137483@umak.edu.ph','A12137483','Student','Student@2025','0000-00-00 00:00:00'),
(27,'Eldgien De la Cruz','edelacruz.a12137762@umak.edu.ph','A12137762','Student','Student@2025','0000-00-00 00:00:00'),
(28,'John Paul Dela Cruz','jdelacruz.a12138342@umak.edu.ph','A12138342','Student','Student@2025','0000-00-00 00:00:00'),
(29,'Kenneth Vincent Delos Santos','kdelossantos.k11939445@umak.edu.ph','K11939445','Student','Student@2025','0000-00-00 00:00:00'),
(30,'Dhenzel Michael Diokno','ddiokno.a12137773@umak.edu.ph','A12137773','Student','Student@2025','0000-00-00 00:00:00'),
(31,'Shane Estrella','sestrella.k11941397@umak.edu.ph','K11941397','Student','Student@2025','0000-00-00 00:00:00'),
(32,'Justhine Famini','jfamini.a12137794@umak.edu.ph','A12137794','Student','Student@2025','0000-00-00 00:00:00'),
(33,'Jarwin Geronca','jgeronca.a12138541@umak.edu.ph','A12138541','Student','Student@2025','0000-00-00 00:00:00'),
(34,'William Duncan Gonzalez','wgonzales.k11940579@umak.edu.ph','K11940579','Student','Student@2025','0000-00-00 00:00:00'),
(35,'Alroneah Lagunay','alagunay.a12138556@umak.edu.ph','A12138556','Student','Student@2025','0000-00-00 00:00:00'),
(36,'Aris Longalong','alongalong.a12138560@umak.edu.ph','A12138560','Student','Student@2025','0000-00-00 00:00:00'),
(37,'Neil Cristian Lonod','nlonod.k11940281@umak.edu.ph','K11940281','Student','Student@2025','0000-00-00 00:00:00'),
(38,'Denise Marie Mallorca','dmallorca.k11939061@umak.edu.ph','K11939061','Student','Student@2025','0000-00-00 00:00:00'),
(39,'Carlos Miguel Matocino','cmatocino.k11939321@umak.edu.ph','K11939321','Student','Student@2025','0000-00-00 00:00:00'),
(40,'Jerico Lorenzo Misolas','jmisolas.a12139029@umak.edu.ph','A12139029','Student','Student@2025','0000-00-00 00:00:00'),
(41,'Jan Joshua Mitas','jmitas.k11935909@umak.edu.ph','K11925909','Student','Student@2025','0000-00-00 00:00:00'),
(42,'John Anthony Pagarigan','jpagarigan.a12138578@umak.edu.ph','K11935909','Student','Student@2025','0000-00-00 00:00:00'),
(43,'All Cedrick Panganiban','apanganiban.a12138583@umak.edu.ph','A12138583','Student','Student@2025','0000-00-00 00:00:00'),
(44,'Danreb Josh Quisel','dquisel.k11938345@umak.edu.ph','K11938345','Student','Student@2025','0000-00-00 00:00:00'),
(45,'Julia Francesca Romero','jromero.k11834419@umak.edu.ph','K11834419','Student','Student@2025','0000-00-00 00:00:00'),
(46,'Juan Antonio Roquid','jroquid.k11935360@umak.edu.ph','K1193536','Student','Student@2025','0000-00-00 00:00:00'),
(47,'John Paul Santos','jsantos.k11830934@umak.edu.ph','K11830934','Student','Student@2025','0000-00-00 00:00:00'),
(48,'Angel Andreo Sillan','asillan.a12138609@umak.edu.ph','A12138609','Student','Student@2025','0000-00-00 00:00:00'),
(49,'Ma. Genecis Suriaga','msuriaga.k11834170@umak.edu.ph','K11834170','Student','Student@2025','0000-00-00 00:00:00'),
(50,'Jotham Ellison Tan','jtan.k11941596@umak.edu.ph','K11941596','Student','Student@2025','0000-00-00 00:00:00'),
(51,'Danica Tongo','dtongo.a12035567@umak.edu.ph','A120355677','Student','Student@2025','0000-00-00 00:00:00'),
(53,'Ivan Gaslang','stefgaslang@gmail.com','34565436b','Student','$2y$10$XQG3/omXlgz.AaWfsWMXX.FRvrdrvegvqc0Yevu0fNZOT09X0P3ia','2025-04-18 16:01:16'),
(55,'Ae Armilla','eirene.armilla@gmail.com','k12345678','Counselor','$2y$10$Z.tD4HKtpB7sfILuZ/8cnOY7GVDsHC4.StetPLQ0mHxcLVbuiSERy','2025-04-21 07:16:48'),
(56,'Eunice Armilla','armilla.eirenegrace@gmail.com','12345567a','Faculty','$2y$10$TPUcUYA5InkmzewDIrF.TeFgQzg5wdbIJrIul0y3xCtCnnB.ssfwi','2025-04-21 07:42:39'),
(57,'Eirene Grace Armilla','earmilla.a12035445@umak.edu.ph','a12035445','Student','$2y$10$VaOWDeaXiWvgeFww0UAbXOkyVseO6MP/WoxaXxKRMWOXPZtAUC/Qu','2025-04-21 07:55:18'),
(58,'LILIBETH ARCALAS','lilibeth.arcalas@umak.edu.ph','CCIS','Faculty','$2y$10$gMYS78NElR6.x2tHmlfP.ONykhvI8sROVpfx.9W5esOgV93IIeZTK','2025-04-22 04:37:34');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

--
-- Dumping routines for database 'u406807013_guidancehub'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-13  7:40:13
