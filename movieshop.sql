-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 03, 2025 at 06:03 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `movieshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `acheter`
--

DROP TABLE IF EXISTS `acheter`;
CREATE TABLE IF NOT EXISTS `acheter` (
  `id_acheter` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_film` int NOT NULL,
  `quantite` int NOT NULL,
  `date_achat` datetime NOT NULL,
  PRIMARY KEY (`id_acheter`),
  KEY `id_client` (`id_client`),
  KEY `id_film` (`id_film`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `acheter`
--

INSERT INTO `acheter` (`id_acheter`, `id_client`, `id_film`, `quantite`, `date_achat`) VALUES
(1, 1, 1, 1, '2025-05-12 14:17:34'),
(2, 1, 1, 1, '2025-05-12 14:31:49'),
(3, 1, 2, 1, '2025-05-12 15:26:20'),
(4, 3, 1, 2, '2025-05-12 20:02:01'),
(5, 4, 1, 2, '2025-05-12 20:05:07'),
(6, 1, 7, 2, '2025-04-21 20:02:37'),
(7, 2, 6, 5, '2025-04-24 15:03:11'),
(8, 3, 3, 1, '2025-04-15 11:03:32'),
(9, 4, 2, 3, '2025-04-30 09:04:04'),
(10, 5, 6, 2, '2025-05-13 01:23:21'),
(11, 5, 3, 2, '2025-03-18 01:23:21'),
(12, 3, 6, 1, '2025-05-20 23:21:31'),
(13, 1, 5, 1, '2025-05-20 23:32:36'),
(14, 1, 6, 1, '2025-05-20 23:32:36'),
(15, 5, 5, 1, '2025-05-20 23:56:04'),
(16, 5, 6, 1, '2025-05-20 23:56:04'),
(17, 5, 3, 1, '2025-05-20 23:56:04'),
(18, 6, 2, 1, '2025-05-20 23:59:57'),
(19, 6, 1, 1, '2025-05-20 23:59:57'),
(20, 7, 8, 1, '2025-05-20 22:18:31'),
(21, 7, 5, 1, '2025-05-20 22:18:31'),
(22, 7, 8, 1, '2025-05-20 22:32:44'),
(23, 2, 1, 1, '2025-05-20 22:36:39'),
(24, 1, 5, 1, '2025-05-20 22:43:16'),
(25, 2, 3, 1, '2025-05-20 22:45:34'),
(26, 4, 1, 1, '2025-05-20 23:08:49'),
(27, 4, 1, 1, '2025-05-20 23:10:10'),
(28, 8, 2, 1, '2025-05-21 02:13:13'),
(29, 5, 1, 1, '2025-05-27 22:04:50'),
(30, 1, 1, 1, '2025-05-12 00:00:00'),
(31, 14, 6, 1, '2025-06-02 10:24:45');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `nom_admin` varchar(50) NOT NULL,
  `prenom_admin` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `mdp_admin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nom_admin`, `prenom_admin`, `email`, `mdp_admin`) VALUES
(2, 'Vanilah', 'YO', 'vanilah@gmail.com', '$2y$10$xzOaAZioH3v1AjeEM54ya.1U2sNB5EMIN5s75801oqxPxD42AHP9G'),
(3, 'Nandrianina', 'Hery', 'nandrianina@gmail.com', '$2y$10$j3NdTB8VfL/dNRQTIbRXN.bPjh1.khSaMn2OVBw/VxJkXlf3JxMDq');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  PRIMARY KEY (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`id_client`, `nom`, `prenom`, `contact`) VALUES
(1, 'Chin', 'Woo', '0330000000'),
(2, 'Rova', 'Vanilah', '0344646454'),
(3, 'Shady', 'Geovanni', '0385912827'),
(4, 'Rovasoa', 'Vanilah', '0344646454'),
(5, 'Lol', 'Rakoto', '0340000000'),
(6, 'Marie', 'Rasoa', '0325445456'),
(7, 'Manana', 'Samy', '0385678794'),
(8, 'Randria', 'Charles', '0345012310'),
(9, 'Nandrianina', 'Hery', '0341245678'),
(14, 'Neny', 'Be', '0320200000'),
(11, 'Noro', 'LAla', '0364545645'),
(12, 'Geo', 'Vanni', '0344645455'),
(13, 'Mama', 'Raly', '0344444444'),
(15, 'Holy', 'Nirina', '0333333333'),
(16, 'Vanilah', 'Vanilah', '0325912827');

-- --------------------------------------------------------

--
-- Table structure for table `film`
--

DROP TABLE IF EXISTS `film`;
CREATE TABLE IF NOT EXISTS `film` (
  `id_film` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `prix` decimal(8,2) NOT NULL,
  `poster` varchar(200) NOT NULL,
  `genre` varchar(50) NOT NULL,
  PRIMARY KEY (`id_film`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `film`
--

INSERT INTO `film` (`id_film`, `titre`, `prix`, `poster`, `genre`) VALUES
(1, 'Mulan', 500.00, 'Mulan.jpg', 'Science-Fiction'),
(2, 'One Piece', 1000.00, '6821f50b9592a.jpg', 'Aventure'),
(3, 'Enola Holmes', 500.00, '682251146a0c5.jpg', 'Drame'),
(5, 'Friends', 1000.00, '68225e018cb42.jpg', 'Comédie'),
(6, 'Stranger Things', 1000.00, '68225f5e25b5f.jpg', 'Thriller'),
(7, 'The Nun', 500.00, '682260a7cc6e4.jpg', 'Horreur'),
(8, 'Poly', 500.00, '682cff8b7870b.jpg', 'Drame');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
