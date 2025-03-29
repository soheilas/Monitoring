-- MySQL dump 10.13  Distrib 5.7.43, for Linux (x86_64)
--
-- Host: localhost    Database: manitorvpn
-- ------------------------------------------------------
-- Server version	5.7.43-log

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
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `server_id` (`server_id`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,1,'سرور جدید اضافه شد','2025-03-24 01:11:44'),(2,1,'سرور ویرایش شد','2025-03-24 14:42:13'),(3,1,'سرور ویرایش شد','2025-03-24 14:42:28'),(4,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-24 14:58:44'),(5,1,'سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: 217.60.238.17','2025-03-24 15:02:03'),(6,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-24 15:05:15'),(7,1,'سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: 217.60.238.17','2025-03-24 15:07:03'),(8,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-24 16:03:15'),(9,1,'سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: 217.60.238.17','2025-03-24 16:04:32'),(10,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-26 16:42:14'),(11,1,'سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: 217.60.238.17','2025-03-26 16:50:09'),(12,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-27 10:42:14'),(13,1,'سرور مجدداً آنلاین شد. DNS به آی‌پی اصلی تغییر کرد: 217.60.238.17','2025-03-27 10:44:02'),(14,1,'سرور از دسترس خارج شد. DNS به آی‌پی بکاپ تغییر کرد: 217.60.238.16','2025-03-28 15:15:45');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subdomain` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `main_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `backup_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('up','down') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'up',
  `last_checked` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servers`
--

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
INSERT INTO `servers` VALUES (1,'ترکیه','testsystem.espressoman.ir','217.60.238.17','217.60.238.16','217.60.238.16','down','2025-03-29 00:03:02');
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'manitorvpn'
--

--
-- Dumping routines for database 'manitorvpn'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-03-29  0:03:28
