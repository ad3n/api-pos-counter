-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 10, 2018 at 10:18 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `db_nt_beta_1`
--

--
-- Dumping data for table `provinces`
--

INSERT INTO `countries` (`id`, `iso_code`, `name`, `created_at`, `idd_code`, `locale`, `timezone`) VALUES
('11', 'ID', 'Indonesia', '2018-08-09 09:52:59', '+62', 'id', 'Asia/Jakarta'),
('21', 'SG', 'Singapore', '2018-08-09 09:53:59','+65', 'en', 'Asia/Singapore'),
('31', 'MY', 'Malaysia', '2018-08-09 09:54:59', '+60', 'en', 'Asia/Kuala_Lumpur');
COMMIT;
