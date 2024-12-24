-- MySQL dump 10.13  Distrib 8.0.39, for Linux (x86_64)
--
-- Host: localhost    Database: frontice_test
-- ------------------------------------------------------
-- Server version	8.0.39-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fullname` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('root','challenge','mentor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_login` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `admins_id_foreign` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES ('150a6572-9538-4d57-839b-5864b939eedd','Frontice Admin','root',0,'2024-10-23 15:40:20','2024-10-23 15:40:20');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challenge_solutions`
--

DROP TABLE IF EXISTS `challenge_solutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `challenge_solutions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pride_of` text COLLATE utf8mb4_unicode_ci,
  `challenge_overcome` text COLLATE utf8mb4_unicode_ci,
  `help_with` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pointed','pending','valid','deleted') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mentor_feedback` text COLLATE utf8mb4_unicode_ci,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenge_solutions_taskee_id_challenge_id_unique` (`taskee_id`,`challenge_id`),
  KEY `challenge_solutions_challenge_id_foreign` (`challenge_id`),
  KEY `challenge_solutions_admin_id_foreign` (`admin_id`),
  CONSTRAINT `challenge_solutions_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `challenge_solutions_challenge_id_foreign` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `challenge_solutions_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challenge_solutions`
--

LOCK TABLES `challenge_solutions` WRITE;
/*!40000 ALTER TABLE `challenge_solutions` DISABLE KEYS */;
INSERT INTO `challenge_solutions` VALUES ('38ebbf85-987d-3e4b-ab8b-08e9c8d2ae7b','2119a861-c414-3d05-a8de-e6530414c7a3','ef1b15cf-49d8-33eb-91bd-93930cfc218f','150a6572-9538-4d57-839b-5864b939eedd',NULL,NULL,NULL,'Similique ut quisquam non architecto itaque ea est esse. Laboriosam ut provident cum. Nostrum et neque quas voluptatem. Dolores illum saepe quas illum non.','Fugit amet cum quis. Nulla totam similique sunt voluptatem. Provident tempore ut quia voluptatem modi.','Dolorem explicabo saepe non animi accusamus minus. Unde consequatur provident quas reprehenderit molestias ad. Vel et delectus numquam ipsa vel nostrum corporis.','pointed',NULL,'2024-04-17 11:32:18','2024-10-23 15:40:23','2024-10-23 15:40:23'),('581a30f8-b82c-3656-908f-a0971cca75b6','2b5514da-caac-33e9-b4c3-c066132ee644','09a74bb5-f50c-3af4-9d88-401c7bbc157c','150a6572-9538-4d57-839b-5864b939eedd','Vel aliquid ea.',NULL,NULL,NULL,'Ipsum eum nesciunt ut et quo voluptatibus sequi tempora. Quia velit labore sint quo inventore eligendi voluptatem. Id optio ducimus soluta repudiandae.',NULL,'pointed',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('59857ad4-fd5b-3ed0-bd52-c64a6b35c15b','c3534964-0721-3ce1-aabd-02a88ef48d9d','ea685aea-e2bb-321d-89ed-0096fec37bc3','150a6572-9538-4d57-839b-5864b939eedd',NULL,NULL,NULL,NULL,'Modi cumque magnam et dolores mollitia. Soluta magni doloribus eligendi. Velit autem est et rem sit ullam sit. Amet sed dolorem eum qui tempora.',NULL,'deleted',NULL,'2024-06-29 12:54:52','2024-10-23 15:40:23','2024-10-23 15:40:23'),('7f069230-d932-30dc-9af1-c35af9be2e9d','771a0a93-60ec-3506-a38b-9c8336d717e5','ea685aea-e2bb-321d-89ed-0096fec37bc3',NULL,'Minus accusantium.',NULL,NULL,'Dolore dolor corporis nihil nisi voluptatum quas. Aspernatur nemo facilis voluptas eum. Ipsum velit rerum ratione ea et ut.',NULL,'Possimus neque accusamus corrupti porro voluptas aspernatur ut. Voluptates voluptatibus iusto ea. Facere sapiente nesciunt tempora maiores id. Facilis sint modi itaque eveniet.','deleted',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('87c2393a-e020-326b-bcdc-a460eedca7ec','de2910be-238b-3510-8d41-bbf56c4c9094','ea685aea-e2bb-321d-89ed-0096fec37bc3','150a6572-9538-4d57-839b-5864b939eedd',NULL,NULL,NULL,NULL,'Sed similique nihil nisi delectus necessitatibus et. Eos consectetur minus dolorem aut quae temporibus. Amet harum neque aut natus placeat in consectetur.','Sint quae magnam quaerat fugit perferendis sapiente. Voluptas dolores dolores nostrum id dignissimos veniam a nam. Omnis qui voluptatem eius voluptatem eos.','deleted',NULL,'2024-02-03 08:22:28','2024-10-23 15:40:23','2024-10-23 15:40:23'),('89d7b643-1e5d-3cb5-b3cb-1226504ed148','0688229b-a725-3be5-9d0e-13ac7a133ae7','09a74bb5-f50c-3af4-9d88-401c7bbc157c','150a6572-9538-4d57-839b-5864b939eedd','Neque veritatis voluptatem.','http://konopelski.com/perspiciatis-quos-consequatur-qui-quia-rerum-sed-ut.html','http://www.bayer.net/vero-quia-vel-ullam-provident','Reiciendis quo itaque velit quis eum. Cum quod dolores ut dicta impedit non cum. Dolores est nesciunt eaque itaque et cumque ducimus et. Qui quasi libero facere a ipsa ducimus veritatis qui. Ipsum architecto autem omnis qui.','Et pariatur cum hic sit odit laboriosam. Et eveniet fuga ipsam consequatur fuga. Quam dolores vel est dicta. Ex ut molestiae cumque beatae.',NULL,'deleted',NULL,'2024-09-01 06:25:16','2024-10-23 15:40:23','2024-10-23 15:40:23'),('9b71167e-ea78-35ab-bff0-1a9295a44003','bb6ebdba-7be3-329c-af46-1d8631b23561','09a74bb5-f50c-3af4-9d88-401c7bbc157c',NULL,NULL,'http://king.net/assumenda-provident-cupiditate-excepturi-sapiente',NULL,NULL,'Laborum nemo fugit quas sed. Est et ullam soluta nesciunt accusantium illum officiis consequatur. In molestias dolores fugiat eveniet aut non harum. Omnis voluptatem nihil voluptas.','Autem consequuntur cumque qui tempore eum nesciunt dignissimos porro. Ut similique dolorem saepe. Dolorem amet et tempora consectetur veritatis cupiditate et voluptate. Tenetur magni voluptate laudantium recusandae ratione ullam ut.','deleted',NULL,'2024-04-08 22:48:02','2024-10-23 15:40:23','2024-10-23 15:40:23'),('a16c205c-3007-38df-8a7e-f201c907542a','771a0a93-60ec-3506-a38b-9c8336d717e5','9f63648d-f1d3-3a59-a280-4c9da2335051','150a6572-9538-4d57-839b-5864b939eedd',NULL,NULL,'http://prosacco.com/iusto-consequatur-recusandae-a-enim.html',NULL,NULL,NULL,'deleted',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('aa6a0409-75a6-31a9-86a5-e4452b54db99','2dd34dd1-4c16-3be8-b525-89274774fd9e','a8028937-bb8f-3da5-90f0-b75dfc0e407c','150a6572-9538-4d57-839b-5864b939eedd','Similique autem possimus.',NULL,'http://ritchie.info/qui-repellendus-ea-qui-porro-nostrum-sed.html',NULL,'A pariatur quaerat vero reprehenderit molestiae quidem ut. Esse error aut est neque. Est tempore sint rerum cumque quas quam adipisci.','Iste ut quis pariatur id enim. Non aliquam est sed sequi. Quam aperiam illo aspernatur nesciunt aut molestiae architecto.','pointed',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('b154358b-4585-342b-a16f-d15021470760','e853f09f-ceca-3f6c-b5fc-cfa9a136e052','e802e32b-0349-3a02-b460-6ab46d57056f','150a6572-9538-4d57-839b-5864b939eedd',NULL,'http://www.balistreri.com/molestiae-voluptas-officia-architecto-sapiente',NULL,'Ut at molestiae nobis quibusdam. Debitis nisi autem quibusdam rerum ullam sed eligendi voluptatem. Voluptatem nihil pariatur nihil sequi aut placeat. Ut blanditiis facilis qui eum rem.',NULL,NULL,'deleted',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('b877faa8-4cad-358e-bdc8-4393a4b53d8d','f434028b-6d46-3972-8368-ab6ba514ee57','4845fbb8-8471-3d7d-a0b8-c60d7f7804f5','150a6572-9538-4d57-839b-5864b939eedd','Corporis ut voluptas cum qui.',NULL,NULL,'Quis voluptatem eum delectus quisquam. Aliquid ea velit vitae ad consequatur expedita. Sint ut iure perspiciatis ullam tenetur.',NULL,'Aut alias possimus dicta atque qui non corporis. Repellendus qui vitae occaecati vero est. Repellat distinctio praesentium aut placeat fugit in et.','pointed',NULL,'2024-06-14 03:21:48','2024-10-23 15:40:23','2024-10-23 15:40:23'),('ba416d3f-8a53-3181-adfc-f44240aedf35','bb6ebdba-7be3-329c-af46-1d8631b23561','920b7659-2044-3145-bbb3-4bdd602e5242',NULL,NULL,NULL,'http://pouros.com/blanditiis-et-ullam-eveniet-consequatur-ipsa-aut.html','Placeat ex ratione aut ipsum ea. Molestiae quo laboriosam corporis est facere expedita reprehenderit. Est aut dolorem quaerat eveniet. Voluptas ipsa blanditiis iste blanditiis et sint.','Molestiae voluptatem quas eius est. Quod sequi odit iure et impedit. Ea aspernatur non tenetur. Aspernatur ut rerum qui repudiandae corrupti qui.',NULL,'pointed',NULL,'2024-03-30 16:21:48','2024-10-23 15:40:23','2024-10-23 15:40:23'),('d1610090-aebe-3925-a3e9-d1fcc8b0dcb7','3a0f8cd0-0b88-3454-b38e-6bc2fbaa91d7','ef1b15cf-49d8-33eb-91bd-93930cfc218f',NULL,'Est est aliquid voluptatem facilis.','http://www.toy.biz/',NULL,NULL,'Cupiditate enim in qui et est. Harum ullam nihil similique saepe non voluptatibus.','Laboriosam soluta dicta porro voluptas. Quis et voluptatem laborum reprehenderit qui. Non impedit nobis quasi quis quod.','pointed',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23'),('e032e27f-8f6b-3813-9187-2272f22ebb65','c3534964-0721-3ce1-aabd-02a88ef48d9d','62838e92-fb49-331b-af2f-661e7b043bfe','150a6572-9538-4d57-839b-5864b939eedd',NULL,'http://www.oconnell.biz/nulla-laudantium-dolor-ipsa-ullam','http://dickens.com/aut-rerum-est-tempore-quisquam-perspiciatis-ut-architecto',NULL,NULL,'Aliquam veniam explicabo velit dicta quis et. Aut perspiciatis voluptatum sit vero sint eaque commodi ad. Distinctio exercitationem perspiciatis voluptatem est.','pointed',NULL,'2024-02-23 12:54:38','2024-10-23 15:40:23','2024-10-23 15:40:23'),('ec2103e8-c250-315f-92b8-f5a9f94ef399','2b5514da-caac-33e9-b4c3-c066132ee644','ef1b15cf-49d8-33eb-91bd-93930cfc218f',NULL,NULL,'http://boehm.com/harum-quae-distinctio-vitae-a-quod-doloribus-ducimus.html',NULL,NULL,'Voluptate et ab magni temporibus aut voluptate. Et eos consequatur quasi aliquam dicta ipsum facilis. Quam est ipsum tempora quia.',NULL,'deleted',NULL,NULL,'2024-10-23 15:40:23','2024-10-23 15:40:23');
/*!40000 ALTER TABLE `challenge_solutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challenges`
--

DROP TABLE IF EXISTS `challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `challenges` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `technical` json NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `figma` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `point` int NOT NULL,
  `short_des` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` json NOT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challenges_level_id_foreign` (`level_id`),
  KEY `challenges_admin_id_foreign` (`admin_id`),
  CONSTRAINT `challenges_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `challenges_level_id_foreign` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challenges`
--

LOCK TABLES `challenges` WRITE;
/*!40000 ALTER TABLE `challenges` DISABLE KEYS */;
INSERT INTO `challenges` VALUES ('0688229b-a725-3be5-9d0e-13ac7a133ae7','150a6572-9538-4d57-839b-5864b939eedd',1,'Reprehenderit vel a facere.','https://via.placeholder.com/640x480.png/008888?text=tech+ad','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.douglas.com/consectetur-deserunt-omnis-unde-corporis-cumque-quae-consequatur',NULL,18,'Adipisci voluptas ab aut. Vel consequatur beatae quo.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Modi nihil dicta et vel velit accusamus.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Velit architecto est pariatur corrupti ut eum impedit. Voluptas nihil maxime atque voluptas asperiores doloremque nihil. Est est ut porro labore quos. Vel voluptas rerum nostrum sunt repudiandae minus.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('0ff864f7-6004-35cb-971f-4923e3b6db25','150a6572-9538-4d57-839b-5864b939eedd',1,'Quis autem quo.','https://via.placeholder.com/640x480.png/009900?text=tech+pariatur','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://donnelly.com/blanditiis-ipsa-maxime-quo-similique-ut-ea-maiores','https://gutkowski.com/culpa-occaecati-nulla-iusto-exercitationem-eius-nulla-aliquid.html',14,'Dolorem est animi quibusdam. Sit id et eum deleniti quia et. Accusamus tempore quis itaque quia.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Odio repellat inventore nesciunt quaerat officiis.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Et quis modi laborum. Inventore dolores velit dolore aut iste veritatis. Sapiente magnam eveniet sequi explicabo. Perspiciatis ipsum aut non qui. Accusantium voluptate sunt provident dolorem aut et cupiditate.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('2119a861-c414-3d05-a8de-e6530414c7a3','150a6572-9538-4d57-839b-5864b939eedd',1,'Sequi sunt quis quis.','https://via.placeholder.com/640x480.png/005555?text=tech+consectetur','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','https://nicolas.com/assumenda-modi-consequatur-expedita-laudantium-quae-itaque-quia.html','http://predovic.com/animi-ut-quos-molestias-quae-saepe-qui-et.html',19,'Provident est eligendi eligendi qui voluptates repellat rerum. Sed odit aut pariatur velit nam.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Odit quia beatae aut optio maiores.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Autem consectetur nesciunt ut et corrupti eos mollitia. Atque ratione nisi nobis facere nemo nulla neque sed. Quaerat maiores labore minus similique soluta repellat. Ut non dicta necessitatibus. Blanditiis ipsum odio maxime harum.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('2a3fd6d7-b031-3371-85d5-66e59b832966','150a6572-9538-4d57-839b-5864b939eedd',1,'Possimus quia modi.','https://via.placeholder.com/640x480.png/003322?text=tech+libero','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.hoppe.com/voluptas-nam-omnis-aliquam',NULL,20,'Corrupti aut minima explicabo tempore. Maiores aut voluptate quia blanditiis ipsam quo.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Blanditiis iusto placeat ipsum sunt quas enim.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Molestiae aut beatae odio amet enim quia. Et et ad officia cumque veritatis ea velit sit. Est nulla porro ad aut ea assumenda harum dolorem. Animi et non ut fugit libero. Repellat repellat asperiores sit.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('2b5514da-caac-33e9-b4c3-c066132ee644','150a6572-9538-4d57-839b-5864b939eedd',1,'In eius corporis incidunt.','https://via.placeholder.com/640x480.png/00dd55?text=tech+similique','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.vandervort.net/facilis-doloribus-id-odio-non-commodi','https://gulgowski.com/voluptas-et-illo-nisi-officia-iure-aut-alias-qui.html',12,'Excepturi laboriosam vel molestiae nobis. Quaerat nihil doloribus voluptatem nihil impedit earum.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"At eos rerum repellendus minus sequi repellat eum.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Labore et atque quo assumenda voluptatem. Itaque modi blanditiis qui officia dolorem. Qui dolor reiciendis nulla delectus nobis similique ipsam. Voluptatem sequi aut impedit suscipit quidem praesentium aspernatur. Dolorem natus et dolorem consectetur debitis.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('2be37fa4-8763-3f51-a920-0f54e30b93f6','150a6572-9538-4d57-839b-5864b939eedd',1,'Voluptates nam.','https://via.placeholder.com/640x480.png/00ee33?text=tech+occaecati','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://shields.org/quod-dolorem-temporibus-recusandae','https://www.reinger.com/delectus-quo-corporis-ducimus-accusamus',14,'Omnis quae ut natus quos consequatur nobis. Velit optio architecto occaecati.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Quidem est quia omnis voluptatibus rerum.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Pariatur sit eligendi voluptas saepe sit ab. Aut nemo laboriosam et consequatur soluta repellat. Quia culpa esse rerum nihil in hic. Inventore sit nemo neque. Quidem suscipit beatae vel fugit optio magni.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('2dd34dd1-4c16-3be8-b525-89274774fd9e','150a6572-9538-4d57-839b-5864b939eedd',1,'Beatae quasi nam.','https://via.placeholder.com/640x480.png/0077ee?text=tech+hic','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.wilderman.com/aliquam-voluptatem-sed-non-temporibus-voluptas.html',NULL,11,'Voluptatem soluta eos rem minus illo nulla. Sint nesciunt rerum id sit.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Asperiores veritatis earum minima labore voluptatem accusantium cum.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Fugiat sequi corrupti inventore aut possimus. Recusandae nobis nam pariatur id qui similique. Possimus id dicta quod aut dolorem. Nihil vel omnis possimus. Voluptate qui dolores laborum est. Natus et dolor magnam iure perspiciatis.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('32540961-0caa-3752-a52b-bc3c90a635e5','150a6572-9538-4d57-839b-5864b939eedd',1,'Consectetur consequatur.','https://via.placeholder.com/640x480.png/008811?text=tech+est','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://kohler.info/est-reprehenderit-tempore-voluptatem-voluptatem-inventore-repellat-molestiae-et','http://www.harber.com/',19,'Autem veniam maxime velit voluptas quam nisi ad vel. Similique quos provident iste laborum.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Dolor deleniti voluptatum possimus error.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Sed eaque et aperiam odit et saepe culpa sit. Ut repellat earum nobis porro adipisci quisquam. Laudantium expedita rem voluptatibus. Adipisci et asperiores debitis ipsum voluptas. Autem qui laborum autem quia nulla rem nemo. Reprehenderit ea officiis velit reprehenderit nam.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('3a0f8cd0-0b88-3454-b38e-6bc2fbaa91d7','150a6572-9538-4d57-839b-5864b939eedd',1,'Eius ad qui.','https://via.placeholder.com/640x480.png/000088?text=tech+odio','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','https://pollich.com/quia-quae-enim-omnis-alias-iste-rem-laudantium.html','http://keeling.info/',18,'Molestias soluta sed soluta aliquid nihil sit. Quia vel sint autem dolorem est iste.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Fuga aperiam consequatur et minus expedita.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Autem natus iste porro dolorem aspernatur eum officiis. Mollitia fugiat provident voluptates voluptatibus possimus. Illo veritatis dolor et sunt est. Sed quia eius esse est repellendus. Ut fugiat deserunt autem maxime voluptas. Neque et et architecto ut vero.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('771a0a93-60ec-3506-a38b-9c8336d717e5','150a6572-9538-4d57-839b-5864b939eedd',1,'Quaerat cumque alias facere.','https://via.placeholder.com/640x480.png/00ee00?text=tech+nam','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','https://www.kunze.com/voluptas-illo-non-magni-fuga-tenetur-praesentium','http://zieme.com/quisquam-recusandae-earum-aut-id',13,'Quis illum aliquam officiis optio. Est doloremque ullam repellendus officia eum in corporis quas.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Facilis quia ea iusto consequuntur dolorum voluptatum.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Ullam eum nesciunt nobis ut. Exercitationem esse molestias sed aut beatae commodi voluptate. Molestias tenetur consequuntur voluptate vel aspernatur sequi ut. Repellendus fugiat ipsam voluptatem adipisci vero et maxime eum.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('80687b67-84ea-3dea-9c0b-5b4efe3b19a7','150a6572-9538-4d57-839b-5864b939eedd',1,'Est natus explicabo ut.','https://via.placeholder.com/640x480.png/00ee11?text=tech+asperiores','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://kulas.com/adipisci-iusto-doloribus-voluptate-maiores-odit-tenetur-repellendus.html',NULL,18,'Ut magni quos accusamus. Eligendi velit quis consequatur vel et.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"At officiis qui officiis voluptatem.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Beatae voluptas accusantium quibusdam quo totam. Eum consequatur in ut omnis tempore doloremque. Sit adipisci eligendi nemo provident deleniti et.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('ac8c4d59-f5b1-37a1-863a-985774bfe50f','150a6572-9538-4d57-839b-5864b939eedd',1,'Architecto vitae omnis quo.','https://via.placeholder.com/640x480.png/007766?text=tech+quia','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','https://mayert.com/inventore-tenetur-voluptatibus-facere-omnis-omnis-et-et-voluptatem.html',NULL,11,'Aliquam aut sit amet magni atque. Officia aliquam nobis non autem nesciunt reiciendis.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Molestiae quis vel magni ex natus sequi.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Minima ratione temporibus assumenda autem porro molestiae ut. Adipisci est temporibus similique id. Labore eos numquam velit. Sequi nemo sed sed deleniti neque laboriosam ab. Commodi nihil vel necessitatibus et in. Et non eum sapiente eum aut.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('ae7b9acc-6d32-3783-95b3-44891d6ef7b0','150a6572-9538-4d57-839b-5864b939eedd',1,'Nesciunt sint molestiae.','https://via.placeholder.com/640x480.png/00bbaa?text=tech+distinctio','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.kautzer.net/',NULL,18,'Qui architecto eaque quia soluta veritatis laudantium. Eum sequi libero temporibus laborum.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Aperiam quia atque et error.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Nulla culpa architecto nostrum repudiandae sunt sit. Velit voluptates doloribus soluta. Et totam quidem nemo ad amet dolorum earum sunt. Inventore est deleniti velit.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('bb6ebdba-7be3-329c-af46-1d8631b23561','150a6572-9538-4d57-839b-5864b939eedd',1,'Aperiam nesciunt qui repellat.','https://via.placeholder.com/640x480.png/0088cc?text=tech+recusandae','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','https://www.fritsch.com/asperiores-ut-similique-architecto-quis-quidem-debitis-voluptas-nesciunt','http://gaylord.org/quo-sunt-facilis-doloremque-asperiores-veniam.html',25,'Expedita rerum ex et ad possimus. Vel quia earum enim minima quas harum. Et aut ipsam eum ipsam.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Harum enim aperiam corporis ipsam.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Ex non et tempore ad ut maiores deleniti sunt. Repellat voluptas iure occaecati sapiente reiciendis voluptas voluptas sed. Pariatur magnam sequi esse. Accusamus maiores quod maiores consequuntur unde quasi molestiae incidunt. Architecto asperiores non enim quibusdam aliquid officiis voluptate.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('c3534964-0721-3ce1-aabd-02a88ef48d9d','150a6572-9538-4d57-839b-5864b939eedd',1,'Qui voluptas occaecati.','https://via.placeholder.com/640x480.png/00bbdd?text=tech+libero','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.fahey.com/',NULL,10,'Maxime aut perferendis nobis autem odit aliquam et. Ratione illo soluta ducimus.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Similique dolorum est iusto numquam sunt eveniet.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Repudiandae ut dolorem id nesciunt. Aut magni quia esse qui dolores quod. Quia autem voluptatibus repudiandae officia exercitationem neque. Est veniam error atque odio consequatur sed sit.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('c9738067-d218-3e72-b7b7-5c025cdddcca','150a6572-9538-4d57-839b-5864b939eedd',1,'Neque officiis eveniet nesciunt.','https://via.placeholder.com/640x480.png/003355?text=tech+velit','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.beier.com/et-odio-qui-molestiae-pariatur-incidunt-doloribus.html',NULL,16,'Et velit accusamus libero dolor aut. Quas dolorem numquam cupiditate.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Est aut officiis dolorem aut voluptate in cupiditate debitis.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Dicta et id dolorem doloribus. Reprehenderit quos officiis perspiciatis laudantium. Aspernatur tempore impedit doloribus eum. Temporibus aut est quas dolorem.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('de2910be-238b-3510-8d41-bbf56c4c9094','150a6572-9538-4d57-839b-5864b939eedd',1,'Rem dolor quo at.','https://via.placeholder.com/640x480.png/008866?text=tech+architecto','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://cormier.com/natus-laboriosam-sunt-omnis-cum-repellat-esse-et-beatae.html','http://www.boyer.com/enim-et-quisquam-odio-saepe-alias',13,'Et explicabo sequi autem amet sed dicta. Aperiam autem officiis aut exercitationem.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Aliquam in et incidunt cum aut est.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Possimus quisquam vel quo maxime assumenda. Et sed eos adipisci eum voluptatibus ipsam praesentium. Quia non modi illo officia assumenda quod. Ullam doloribus necessitatibus id et aperiam. Enim omnis saepe vel dignissimos. Amet ut molestias voluptates hic excepturi vel sit tempora.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('e1452e9d-3973-3e87-a9f3-8ee84108d5a9','150a6572-9538-4d57-839b-5864b939eedd',1,'Illum natus consequatur.','https://via.placeholder.com/640x480.png/00ddff?text=tech+sequi','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.hudson.com/adipisci-quaerat-sequi-quisquam-fugit-molestias.html','http://turcotte.biz/non-iure-aut-pariatur-consequuntur-et-temporibus-id',15,'Temporibus eos sit quasi consequatur. Nisi sit eligendi reprehenderit harum quis aut sint.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Qui minima natus ut deserunt ut cupiditate expedita.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Enim aut possimus amet nostrum sit. Ex sit et rem veniam doloribus nobis cupiditate molestiae. Et quaerat consequatur eos est dolore. Enim provident optio sapiente culpa provident. Iste molestiae et sint. Est aliquid animi ut vel dolorem vitae voluptatibus esse.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('e853f09f-ceca-3f6c-b5fc-cfa9a136e052','150a6572-9538-4d57-839b-5864b939eedd',1,'Et consequatur ipsum ut.','https://via.placeholder.com/640x480.png/009999?text=tech+dolorem','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.becker.com/cum-inventore-consequatur-esse','https://www.hauck.com/ut-quo-ullam-officiis-voluptatum-dignissimos-ea',16,'Et dolore molestiae cum porro iure eos temporibus. Nam et velit esse eius deleniti velit.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Delectus ullam quia labore et explicabo aut illo.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Quidem est incidunt iure. Temporibus rerum recusandae corrupti est ut dolor. Quia enim recusandae accusamus qui. Natus magni quasi perspiciatis itaque est. Tempore sequi culpa est in provident.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('f434028b-6d46-3972-8368-ab6ba514ee57','150a6572-9538-4d57-839b-5864b939eedd',1,'Non praesentium at.','https://via.placeholder.com/640x480.png/00bb55?text=tech+et','\"{\\\"technical\\\":[\\\"HTML\\\",\\\"CSS\\\"]}\"','http://www.ohara.info/quae-eum-aspernatur-et-repellendus-est-facilis','http://www.friesen.com/',14,'Eligendi error accusantium nesciunt aut. Quia autem quo officiis nostrum. Omnis aut odit quos.','\"{\\\"time\\\":1729672822,\\\"blocks\\\":[{\\\"type\\\":\\\"header\\\",\\\"data\\\":{\\\"text\\\":\\\"Eum ut quis numquam impedit ipsa quos.\\\",\\\"level\\\":2}},{\\\"type\\\":\\\"paragraph\\\",\\\"data\\\":{\\\"text\\\":\\\"Enim minus debitis perspiciatis natus facere ea hic. Et veniam explicabo et repellendus. Harum voluptatum commodi sit ut. Non animi voluptatem sed inventore excepturi aliquam velit modi. Dolor ea non reiciendis tempore.\\\"}}],\\\"version\\\":\\\"2.19.0\\\"}\"',0,'2024-10-23 15:40:22','2024-10-23 15:40:22');
/*!40000 ALTER TABLE `challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_solution_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `left` int NOT NULL,
  `right` int NOT NULL,
  `is_edit` tinyint(1) NOT NULL DEFAULT '0',
  `is_remove` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_taskee_id_foreign` (`taskee_id`),
  KEY `comments_challenge_solution_id_foreign` (`challenge_solution_id`),
  CONSTRAINT `comments_challenge_solution_id_foreign` FOREIGN KEY (`challenge_solution_id`) REFERENCES `challenge_solutions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `comments_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usage_limit` int NOT NULL,
  `value` int NOT NULL,
  `expired` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discounts_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discounts`
--

LOCK TABLES `discounts` WRITE;
/*!40000 ALTER TABLE `discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followers`
--

DROP TABLE IF EXISTS `followers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `followers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tasker_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `followers_taskee_id_tasker_id_unique` (`taskee_id`,`tasker_id`),
  KEY `followers_tasker_id_foreign` (`tasker_id`),
  CONSTRAINT `followers_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `followers_tasker_id_foreign` FOREIGN KEY (`tasker_id`) REFERENCES `taskers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followers`
--

LOCK TABLES `followers` WRITE;
/*!40000 ALTER TABLE `followers` DISABLE KEYS */;
/*!40000 ALTER TABLE `followers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interactions`
--

DROP TABLE IF EXISTS `interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interactions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `challenge_solution_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('like','dislike') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactions_taskee_id_challenge_solution_id_unique` (`taskee_id`,`challenge_solution_id`),
  KEY `interactions_challenge_solution_id_foreign` (`challenge_solution_id`),
  CONSTRAINT `interactions_challenge_solution_id_foreign` FOREIGN KEY (`challenge_solution_id`) REFERENCES `challenge_solutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interactions_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interactions`
--

LOCK TABLES `interactions` WRITE;
/*!40000 ALTER TABLE `interactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `interactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `levels`
--

DROP TABLE IF EXISTS `levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_point` int NOT NULL,
  `required_point` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `levels_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `levels`
--

LOCK TABLES `levels` WRITE;
/*!40000 ALTER TABLE `levels` DISABLE KEYS */;
INSERT INTO `levels` VALUES (1,'Newbie',0,0,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(2,'Bronze',50,100,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(3,'Silver',100,150,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(4,'Gold',150,450,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(5,'Diamond',200,1050,'2024-10-23 15:40:20','2024-10-23 15:40:20');
/*!40000 ALTER TABLE `levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_07_31_033754_create_admins_table',1),(5,'2024_07_31_033827_create_taskees_table',1),(6,'2024_07_31_033836_create_taskers_table',1),(7,'2024_07_31_034145_create_levels_table',1),(8,'2024_07_31_034249_create_challenges_table',1),(9,'2024_07_31_091044_create_challenge_solutions_table',1),(10,'2024_07_31_091253_create_comments_table',1),(11,'2024_07_31_091400_create_tasks_table',1),(12,'2024_07_31_091455_create_task_solutions_table',1),(13,'2024_07_31_091518_create_task_comments_table',1),(14,'2024_08_02_003153_create_interactions_table',1),(15,'2024_08_03_100147_create_personal_access_tokens_table',1),(16,'2024_09_17_141153_create_reports_table',1),(17,'2024_10_02_075001_create_services_table',1),(18,'2024_10_07_110954_create_discounts_table',1),(19,'2024_10_07_112554_create_followers_table',1),(20,'2024_10_08_140132_create_subscriptions_table',1),(21,'2024_10_15_150919_create_notifications_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comment_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challenge_comment_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challenge_solution_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_solution_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('seen','unseen') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unseen',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_from_foreign` (`from`),
  KEY `notifications_to_foreign` (`to`),
  KEY `notifications_comment_id_foreign` (`comment_id`),
  KEY `notifications_challenge_comment_id_foreign` (`challenge_comment_id`),
  KEY `notifications_challenge_solution_id_foreign` (`challenge_solution_id`),
  KEY `notifications_task_id_foreign` (`task_id`),
  KEY `notifications_task_solution_id_foreign` (`task_solution_id`),
  CONSTRAINT `notifications_challenge_comment_id_foreign` FOREIGN KEY (`challenge_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_challenge_solution_id_foreign` FOREIGN KEY (`challenge_solution_id`) REFERENCES `challenge_solutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `task_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_from_foreign` FOREIGN KEY (`from`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_task_solution_id_foreign` FOREIGN KEY (`task_solution_id`) REFERENCES `task_solutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_to_foreign` FOREIGN KEY (`to`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reason` (`task_id`,`taskee_id`),
  KEY `reports_taskee_id_foreign` (`taskee_id`),
  CONSTRAINT `reports_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reports`
--

LOCK TABLES `reports` WRITE;
/*!40000 ALTER TABLE `reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('monthly','3-monthly','6-monthly','yearly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Gold Monthly','monthly',59000,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(2,'Gold 3-Monthly','3-monthly',168000,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(3,'Gold 6-Monthly','6-monthly',318000,'2024-10-23 15:40:20','2024-10-23 15:40:20'),(4,'Gold Yearly','yearly',609000,'2024-10-23 15:40:20','2024-10-23 15:40:20');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `discount_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired` timestamp NOT NULL,
  `gold_expired` timestamp NOT NULL,
  `amount_paid` int NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','success','fail') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_taskee_id_foreign` (`taskee_id`),
  KEY `subscriptions_service_id_foreign` (`service_id`),
  KEY `subscriptions_discount_id_foreign` (`discount_id`),
  CONSTRAINT `subscriptions_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscriptions_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_comments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_solution_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `left` int NOT NULL,
  `right` int NOT NULL,
  `is_edit` tinyint(1) NOT NULL DEFAULT '0',
  `is_remove` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_user_id_foreign` (`user_id`),
  KEY `task_comments_task_solution_id_foreign` (`task_solution_id`),
  CONSTRAINT `task_comments_task_solution_id_foreign` FOREIGN KEY (`task_solution_id`) REFERENCES `task_solutions` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comments`
--

LOCK TABLES `task_comments` WRITE;
/*!40000 ALTER TABLE `task_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_solutions`
--

DROP TABLE IF EXISTS `task_solutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_solutions` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taskee_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('Chưa nộp','Đã nộp','Đã xem','Đạt','Chưa đạt') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Chưa nộp',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_solutions_taskee_id_foreign` (`taskee_id`),
  KEY `task_solutions_task_id_foreign` (`task_id`),
  CONSTRAINT `task_solutions_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `task_solutions_taskee_id_foreign` FOREIGN KEY (`taskee_id`) REFERENCES `taskees` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_solutions`
--

LOCK TABLES `task_solutions` WRITE;
/*!40000 ALTER TABLE `task_solutions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_solutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taskees`
--

DROP TABLE IF EXISTS `taskees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taskees` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `cv` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `points` int NOT NULL DEFAULT '0',
  `gold_expired` timestamp NULL DEFAULT NULL,
  `gold_registration_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `taskees_id_foreign` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taskees`
--

LOCK TABLES `taskees` WRITE;
/*!40000 ALTER TABLE `taskees` DISABLE KEYS */;
INSERT INTO `taskees` VALUES ('09a74bb5-f50c-3af4-9d88-401c7bbc157c','Arvilla','Adams',NULL,NULL,'Nemo tempora et vero doloribus voluptatem velit facilis. Cupiditate enim rem consequatur id. Est eveniet veniam molestiae saepe necessitatibus deserunt. Sequi error qui beatae pariatur beatae id consequatur.',NULL,0,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('4845fbb8-8471-3d7d-a0b8-c60d7f7804f5','Kobe','Bahringer',NULL,NULL,NULL,'https://www.lockman.com/a-perferendis-alias-dolor-voluptatem',14,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:50:27'),('486de926-5aa6-46e7-8892-f628e1363f76','Anh Tien','Phan','0987564321',NULL,NULL,'cv/taskee/486de926-5aa6-46e7-8892-f628e1363f76/tientasker0.pdf',0,NULL,NULL,'2024-10-23 15:46:49','2024-10-23 15:48:50'),('5f91501b-0859-328c-8504-cf912401e836','Edward','Boehm',NULL,'http://lakin.com/accusantium-itaque-aperiam-quo-tempore','Ducimus sint cupiditate omnis repudiandae quaerat explicabo animi enim. Et autem architecto esse eligendi fugiat aperiam. Iste doloremque repellat vitae ratione debitis.','http://www.barton.info/velit-quo-autem-ipsum-similique',0,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('62838e92-fb49-331b-af2f-661e7b043bfe','Zaria','Kunde',NULL,'http://becker.com/hic-dolor-perferendis-numquam-ipsum-quia.html','Et ut praesentium inventore vero. Quaerat quod culpa repellat corporis dolorem cupiditate.',NULL,10,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:50:27'),('920b7659-2044-3145-bbb3-4bdd602e5242','Nola','Collier',NULL,NULL,NULL,'http://www.ryan.net/ut-ad-quod-pariatur-eveniet-cupiditate.html',25,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:50:27'),('9f63648d-f1d3-3a59-a280-4c9da2335051','Buck','Moen',NULL,'http://www.gleichner.com/repellat-consequatur-laboriosam-minima-veniam-consequatur-quaerat.html','Nostrum aut non fugiat deserunt dolorem voluptas. Dolorem officia ratione consequatur nisi tenetur dolorem odit vero. Sint animi magni aliquam ea quia fugit temporibus. Temporibus corporis vitae dolor quo.','https://www.witting.com/dolor-rem-expedita-corporis-dignissimos-occaecati-aspernatur',0,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('a8028937-bb8f-3da5-90f0-b75dfc0e407c','Maymie','Hamill',NULL,'http://champlin.com/pariatur-dignissimos-quidem-pariatur-et-voluptatem-voluptatem-error',NULL,NULL,11,NULL,NULL,'2024-10-23 15:40:20','2024-10-23 15:50:27'),('e802e32b-0349-3a02-b460-6ab46d57056f','Jacey','Lesch',NULL,NULL,NULL,'https://champlin.com/quis-delectus-laudantium-nobis-nesciunt-molestiae-vitae-fuga-ut.html',0,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('ea685aea-e2bb-321d-89ed-0096fec37bc3','Alden','Kemmer',NULL,NULL,NULL,NULL,0,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('ef1b15cf-49d8-33eb-91bd-93930cfc218f','Camren','Rohan',NULL,NULL,'Autem natus aperiam animi id. Ut voluptas vero aut similique alias. Autem ullam inventore esse excepturi deserunt quidem ipsum. Facilis quidem quis voluptas fuga.',NULL,0,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22');
/*!40000 ALTER TABLE `taskees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taskers`
--

DROP TABLE IF EXISTS `taskers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taskers` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `taskers_id_foreign` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taskers`
--

LOCK TABLES `taskers` WRITE;
/*!40000 ALTER TABLE `taskers` DISABLE KEYS */;
/*!40000 ALTER TABLE `taskers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tasker_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `technical` json NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `figma` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_point` int NOT NULL,
  `short_des` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc` json NOT NULL,
  `expired` timestamp NOT NULL,
  `status` enum('published','draft') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_skip` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_tasker_id_foreign` (`tasker_id`),
  CONSTRAINT `tasks_tasker_id_foreign` FOREIGN KEY (`tasker_id`) REFERENCES `taskers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `forgot_password_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','tasker','taskee') COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp` char(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otp_expired` timestamp NULL DEFAULT NULL,
  `token_reset_password` char(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_reset_expired` timestamp NULL DEFAULT NULL,
  `token_verify_password` char(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_verify_expired` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_github_id_unique` (`github_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('09a74bb5-f50c-3af4-9d88-401c7bbc157c','ruth.dietrich','jerel.heller@example.net','2024-10-23 15:40:22',NULL,'$2y$12$wEIw6FLDpTf.2sS.STK5wOMLuMHnH10xpVk9iHz5718eKVw7VUzZy','tasker',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('150a6572-9538-4d57-839b-5864b939eedd','admin','admin@frontice.com','2024-10-23 15:40:20',NULL,'$2y$12$R7Xy/ML7BKoN6.QE7Iqcd.AUhLLlmoAGidrmdf1MTOWLk10BICXtK','admin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:20','2024-10-23 15:40:20'),('4845fbb8-8471-3d7d-a0b8-c60d7f7804f5','larson.joanie','zemlak.keara@example.com','2024-10-23 15:40:21',NULL,'$2y$12$kimRlg4S9BSRUh2uSSpWPe00I7JRGJPSROZ9jQcHzDPM23k2o2bAS','taskee','https://via.placeholder.com/640x480.png/00ccff?text=people+qui',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('486de926-5aa6-46e7-8892-f628e1363f76','tientasker0','patworks34@gmail.com','2024-10-23 15:47:23',NULL,'$2y$12$d1anWnOozz7.t7mVWAEqSeNwuLUzJudx226JLlA4Jt3.ZLulhaaJG','taskee','images/taskee/486de926-5aa6-46e7-8892-f628e1363f76/health-benefits-broccoli-2000-43161d91ce004befaed383f4a92b3399.jpg',NULL,'624246','2024-10-23 15:51:49',NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:46:49','2024-10-23 15:48:50'),('5f91501b-0859-328c-8504-cf912401e836','catherine34','laurence.heaney@example.org','2024-10-23 15:40:21',NULL,'$2y$12$Y4h95LEd17COlq5LJHR.fuPMXg6cRM6fH8vx7rPhQaGQTTbGl6nUu','tasker','https://via.placeholder.com/640x480.png/007722?text=people+debitis',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('62838e92-fb49-331b-af2f-661e7b043bfe','lwisoky','rosemary.botsford@example.org','2024-10-23 15:40:21',NULL,'$2y$12$46sXYsqjZwROGlTP/YxRQeKFKS2B57G3pPOVvrLGAfP8YsXGwmpSy','taskee',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('920b7659-2044-3145-bbb3-4bdd602e5242','dbradtke','clotilde.bergnaum@example.org','2024-10-23 15:40:22',NULL,'$2y$12$edx0ZeB7ma8qmdaXRZMZweQYv0wPhicEMvi9qtiYxjz7FgFKRxvuK','taskee',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('9f63648d-f1d3-3a59-a280-4c9da2335051','jmaggio','spencer.zion@example.com','2024-10-23 15:40:21',NULL,'$2y$12$I6K6J5nNVLmNpulYHqE7CeinekPXpk9msYraRuqJfdt7aP6ONM2/2','taskee','https://via.placeholder.com/640x480.png/0000cc?text=people+suscipit',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('a8028937-bb8f-3da5-90f0-b75dfc0e407c','nschulist','christopher65@example.net','2024-10-23 15:40:20',NULL,'$2y$12$ITjcbg1gT/v4fOtIqSkqE.OsSPZLthI9Bj3ya/QexWNfjfZUCod1K','taskee',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('e802e32b-0349-3a02-b460-6ab46d57056f','hfriesen','mertz.celestino@example.org','2024-10-23 15:40:21',NULL,'$2y$12$LazhktvnRRN9oILDstNXHuzBwDEadwohvvGTM/AfhYse6seyXq0qm','tasker',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:21','2024-10-23 15:40:21'),('ea685aea-e2bb-321d-89ed-0096fec37bc3','lizzie17','horn@example.org','2024-10-23 15:40:22',NULL,'$2y$12$8JbfbBwmIrxhj4oiEcez0OMApQ6mV63KcXESe93Y9utTFRxaGzF3O','taskee','https://via.placeholder.com/640x480.png/006677?text=people+sapiente',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22'),('ef1b15cf-49d8-33eb-91bd-93930cfc218f','jamaal01','swift.kaleigh@example.net','2024-10-23 15:40:22',NULL,'$2y$12$vmnEfz4ECZI1W6AUGFvkru4Uqmh6/WCNNSoVC5RddMzrYtHSu46Je','tasker','https://via.placeholder.com/640x480.png/00ee22?text=people+est',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2024-10-23 15:40:22','2024-10-23 15:40:22');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-23  8:52:32
