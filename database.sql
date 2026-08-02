-- Adminer 4.8.4 MySQL 10.4.32-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `legacy_projects`;
CREATE TABLE `legacy_projects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `pda_code` varchar(255) NOT NULL,
  `data_uploaded` int(11) NOT NULL DEFAULT 0,
  `rate` double(8,2) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `upload_pda` int(11) NOT NULL DEFAULT 0,
  `state` enum('Capex','Planification','Execution','Finished') NOT NULL DEFAULT 'Planification',
  `investments` enum('Innovation','Efficiency & Saving','Replacement & Restructuring','Quality & Hygiene','Health & Safety','Environment','Maintenance','Capacity Increase') NOT NULL DEFAULT 'Innovation',
  `classification_of_investments` enum('Buildings','Furniture','General Install','Land','Machines & Equipm','Office Hardware Software','Other','Vehicles','Vessel & Fishing Equipment','Warenhouse & Distrib') NOT NULL DEFAULT 'Buildings',
  `justification` enum('Normal Capex','Special Project') NOT NULL DEFAULT 'Normal Capex',
  `start_date` date NOT NULL,
  `quartile_date` date NOT NULL,
  `finish_date` date NOT NULL,
  `approve_date` date DEFAULT NULL,
  `close_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `legacy_projects` (`id`, `name`, `pda_code`, `data_uploaded`, `rate`, `file_name`, `upload_pda`, `state`, `investments`, `classification_of_investments`, `justification`, `start_date`, `quartile_date`, `finish_date`, `approve_date`, `close_date`, `created_at`, `updated_at`) VALUES
(1,	'Replacement of CIESA 2 Fish Skinning Cooling System',	'DI_GCG-CIESA-2023-402',	1,	1.07,	'1701833979.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2023-10-23',	'2023-10-23',	'2024-02-29',	NULL,	NULL,	'2023-10-14 09:17:43',	'2024-05-28 15:16:53'),
(2,	'Improve of chilling room and new cooling system with fans to improve fish yields',	'DI_GCG-CIESA-2023-201',	1,	1.07,	'1701833927.pdf',	1,	'Finished',	'Efficiency & Saving',	'Buildings',	'Normal Capex',	'2023-10-23',	'2023-10-23',	'2024-03-22',	NULL,	NULL,	'2023-10-14 09:18:55',	'2024-11-28 08:53:31'),
(3,	'Anti Spills Safety Kits ',	'DI_GCG-CIESA-2023-503',	1,	1.07,	'1701834412.pdf',	1,	'Finished',	'Health & Safety',	'General Install',	'Normal Capex',	'2023-09-26',	'2023-09-26',	'2023-12-08',	NULL,	NULL,	'2023-10-14 09:19:33',	'2024-04-03 18:44:43'),
(5,	'Safety guards for moving parts',	'DI_GCG-CIESA-2023-508',	1,	1.07,	'1701834764.pdf',	1,	'Finished',	'Health & Safety',	'Buildings',	'Normal Capex',	'2023-04-18',	'2023-04-18',	'2023-12-31',	NULL,	NULL,	'2023-10-14 09:21:31',	'2024-02-14 12:50:20'),
(6,	'Civil work refurbishment of medical department',	'DI_GCG-CIESA-2023-507',	1,	1.07,	'1701835271.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2023-09-04',	'2023-09-04',	'2023-12-15',	NULL,	NULL,	'2023-10-14 09:22:58',	'2024-04-08 15:29:08'),
(7,	'Roof Replacement ',	'DI_GCG-CIESA-2023-304',	1,	1.07,	'1701834798.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2023-08-15',	'2023-08-15',	'2023-11-03',	NULL,	NULL,	'2023-10-14 09:23:38',	'2024-02-14 12:52:48'),
(8,	'Replacement of 183 fish storage tanks',	'DI_GCG-CIESA-2023-302',	1,	1.07,	'1701834865.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2023-03-30',	'2023-03-30',	'2023-11-01',	NULL,	NULL,	'2023-10-14 09:24:16',	'2024-01-12 21:36:59'),
(9,	'Can Depalletizer ciesa 1',	'DI_GCG-CIESA-2023-314',	1,	1.07,	'1698466238.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2023-04-18',	'2023-04-18',	'2023-12-31',	NULL,	NULL,	'2023-10-14 09:24:57',	'2024-01-12 21:37:10'),
(10,	'Retorts Isolation ',	'DI_GCG-CIESA-2023-602',	1,	1.07,	'1698459613.pdf',	1,	'Finished',	'Environment',	'Buildings',	'Normal Capex',	'2023-05-01',	'2023-05-01',	'2023-06-27',	NULL,	NULL,	'2023-10-14 09:25:39',	'2024-02-14 12:52:58'),
(11,	'New Tuna Pouches line Part 2',	'DI_GCG-CIESA-2023-101c',	1,	1.07,	'1701835262.pdf',	1,	'Finished',	'Innovation',	'Buildings',	'Normal Capex',	'2023-07-24',	'2023-07-24',	'2023-11-15',	NULL,	NULL,	'2023-10-14 09:27:29',	'2024-02-14 12:50:04'),
(12,	'Wastewater Pool Infrastructure Replacement',	'DI_GCG-CIESA-2023-501',	1,	1.07,	'1701834109.pdf',	1,	'Finished',	'Replacement & Restructuring',	'General Install',	'Normal Capex',	'2023-09-18',	'2023-09-18',	'2023-12-22',	NULL,	NULL,	'2023-10-24 04:40:23',	'2024-01-12 21:37:20'),
(13,	'Can washer machine replace',	'DI_GCG-CIESA-2023-307',	1,	0.94,	'1698459742.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2023-04-01',	'2023-04-01',	'2023-07-30',	NULL,	NULL,	'2023-10-27 05:16:16',	'2024-01-12 17:05:19'),
(14,	'New tuna pouches line',	'DI_GCG-CIESA-2023-101b',	1,	1.07,	'1701835673.pdf',	1,	'Finished',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2023-04-14',	'2023-04-14',	'2023-11-15',	NULL,	NULL,	'2023-10-29 20:24:09',	'2024-08-14 17:37:36'),
(15,	'Ammonia Compressor Replacement',	'DI_GCG-CIESA-2023-303',	1,	1.07,	'1705178137.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2023-08-29',	'2023-08-29',	'2024-03-22',	NULL,	NULL,	'2024-01-13 19:49:57',	'2024-05-28 15:16:31'),
(16,	'Strategic Column Replacement - CIESA2',	'DI_GCG-CIESA-2023-701',	1,	1.07,	'1705178105.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2023-12-22',	'2023-12-22',	'2024-04-19',	NULL,	NULL,	'2024-01-13 20:34:35',	'2024-08-14 17:47:14'),
(17,	'PowerCore Transformer Area Refurbishment',	'DI_GCG-CIESA-2023-506',	1,	1.07,	'1705179017.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2023-12-22',	'2023-12-22',	'2024-07-19',	NULL,	NULL,	'2024-01-13 20:49:36',	'2024-11-28 08:53:26'),
(18,	'LoTo Pilot Implementation - Line 1A',	'DI_GCG-CIESA-2023-504',	1,	1.07,	'1705179794.pdf',	1,	'Finished',	'Health & Safety',	'General Install',	'Normal Capex',	'2023-12-17',	'2023-12-17',	'2024-04-28',	NULL,	NULL,	'2024-01-13 20:57:44',	'2024-09-16 09:19:17'),
(19,	'Casepacker',	'DI_GCG-CIESA-2022-801',	1,	1.07,	'1705411053.pdf',	1,	'Finished',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2022-11-01',	'2022-11-01',	'2023-08-31',	NULL,	NULL,	'2024-01-16 13:17:05',	'2024-04-03 18:44:10'),
(20,	'Precision Pipeline Upgrade for Retorts',	'DI_GCG-CIESA-2023-301e',	1,	1.07,	'1705512588.pdf',	1,	'Finished',	'Replacement & Restructuring',	'General Install',	'Normal Capex',	'2023-12-22',	'2023-12-22',	'2024-02-10',	NULL,	NULL,	'2024-01-16 21:55:18',	'2024-02-14 12:49:43'),
(21,	'Twoo X Ray Equipment ',	'DI_GCG-CIESA-2022-401',	1,	1.10,	'1710862463.pdf',	1,	'Finished',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2022-11-28',	'2022-11-28',	'2024-11-30',	NULL,	NULL,	'2024-03-18 18:05:39',	'2025-01-07 18:39:03'),
(22,	'DeptTech Upgrade',	'DI_GCG-CIESA-2024-309',	1,	1.10,	'1712609815.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2024-03-06',	'2024-03-06',	'2024-05-21',	NULL,	NULL,	'2024-04-08 15:42:33',	'2024-08-02 14:23:02'),
(23,	'Fish Containers',	'DI_GCG-CIESA-2024-301a',	1,	1.10,	'1712612094.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2024-03-15',	'2024-03-15',	'2024-05-22',	NULL,	NULL,	'2024-04-08 16:34:18',	'2024-08-02 14:22:42'),
(24,	'Architectural finishes: Ref of the medical department',	'DI_GCG-CIESA-2024-301b',	1,	1.10,	'1712866102.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2024-04-01',	'2024-04-01',	'2024-05-10',	NULL,	NULL,	'2024-04-11 14:57:45',	'2024-08-02 14:23:39'),
(25,	'Techado Escuela 10 de Agosto',	'Sin Codigo',	1,	1.00,	NULL,	0,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2022-12-01',	'2022-12-01',	'2024-04-19',	NULL,	NULL,	'2024-04-15 14:14:07',	'2024-08-13 21:58:24'),
(26,	'Industrial plant LPG system installation',	'DI_GCG-CIESA-2024-604',	1,	1.10,	'1713559248.pdf',	1,	'Execution',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2024-03-29',	'2024-03-29',	'2024-10-21',	NULL,	NULL,	'2024-04-19 15:38:47',	'2024-04-19 15:40:48'),
(27,	'Retrofitting of labelers 1 and 2',	'DI_GCG-CIESA-2024-508',	1,	1.10,	'1714176745.pdf',	1,	'Finished',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2024-04-30',	'2024-04-30',	'2024-11-10',	NULL,	NULL,	'2024-04-26 17:13:11',	'2024-11-28 08:56:41'),
(28,	'Redesign and overhual of Ciesa 1 cut fish conveyor and grating metal belt',	'DI_GCG-CIESA-2024-204',	1,	1.10,	'1715621957.pdf',	1,	'Finished',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2024-04-29',	'2024-04-29',	'2024-09-10',	NULL,	NULL,	'2024-04-27 14:27:20',	'2024-08-02 14:24:00'),
(29,	'Software and Equipment for Blue-Collar Incentive Program',	'DI_GCG-CIESA-2024-208',	1,	1.10,	'1715622044.pdf',	1,	'Execution',	'Efficiency & Saving',	'Office Hardware Software',	'Normal Capex',	'2024-04-12',	'2024-04-12',	'2024-10-11',	NULL,	NULL,	'2024-04-27 14:36:32',	'2024-05-13 12:40:44'),
(30,	'Forklift Procurement for Cost Efficiency and Operational Excellence',	'DI_GCG-CIESA-2024-310',	1,	1.10,	'1715782407.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2024-04-29',	'2024-04-29',	'2024-12-10',	NULL,	NULL,	'2024-04-27 14:47:53',	'2025-01-07 18:43:19'),
(31,	'Change of heat exchangers from CIESA - 2',	'DI_GCG-CIESA-2024-207',	1,	1.10,	'1715622069.pdf',	1,	'Finished',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2024-05-11',	'2024-05-11',	'2024-10-11',	NULL,	NULL,	'2024-05-09 14:36:23',	'2024-11-28 08:55:37'),
(32,	'CIESA 2 PLC kitchen technology upgrade',	'DI_GCG-CIESA-2024-307',	1,	1.10,	'1720282786.pdf',	1,	'Finished',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2024-05-10',	'2024-05-10',	'2024-12-30',	NULL,	NULL,	'2024-05-09 14:42:06',	'2025-01-07 18:42:50'),
(33,	'Riomare Maxi',	'DI_GCG-CIESA-2024-801',	1,	1.10,	'1715622115.pdf',	1,	'Finished',	'Capacity Increase',	'General Install',	'Normal Capex',	'2024-05-10',	'2024-05-10',	'2024-11-10',	NULL,	NULL,	'2024-05-09 14:48:09',	'2025-01-07 18:42:24'),
(40,	'Countermeasures to safety issues discovered in the observations and talks',	'DI_GCG-CIESA-2024-501',	1,	1.10,	'1720281508.pdf',	1,	'Finished',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2024-07-03',	'2024-07-03',	'2024-10-08',	NULL,	NULL,	'2024-07-06 10:58:04',	'2024-11-28 08:55:22'),
(41,	'Can coders and Quality equipment laboratory',	'DI_GCG-CIESA-2024-305',	1,	1.10,	'1720282820.pdf',	1,	'Finished',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2024-07-05',	'2024-05-10',	'2024-11-29',	NULL,	NULL,	'2024-07-06 11:16:30',	'2025-06-05 18:13:30'),
(42,	'Quality Assurance in unloading fish areas',	'DI_GCG-CIESA-2024-301c',	1,	1.10,	'1720291478.pdf',	1,	'Finished',	'Quality & Hygiene',	'General Install',	'Normal Capex',	'2024-07-05',	'2024-07-05',	'2024-08-30',	NULL,	NULL,	'2024-07-06 13:39:53',	'2024-08-13 23:33:14'),
(43,	'Deaerator Tank and Water Lines',	'DI_GCG-CIESA-2024-203',	1,	1.10,	'1725493457.pdf',	1,	'Finished',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2024-08-16',	'2024-07-01',	'2025-02-15',	NULL,	NULL,	'2024-08-13 23:56:21',	'2025-01-07 18:41:46'),
(44,	'Tuna can seamer machine for line #6 ',	'DI_GCG-CIESA-2024-201',	1,	1.10,	'1734792664.pdf',	1,	'Execution',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2024-07-01',	'2024-07-01',	'2024-12-31',	NULL,	NULL,	'2024-08-14 12:53:57',	'2024-12-21 09:51:04'),
(45,	'Synchronization system for CIESA 2 - Stage 1 Replace Labeling mac',	'DI_GCG-CIESA-2024-209',	1,	1.10,	'1734795687.pdf',	1,	'Finished',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2024-09-02',	'2024-07-01',	'2024-12-20',	NULL,	NULL,	'2024-08-14 12:55:17',	'2024-12-21 10:41:27'),
(46,	'Generator Overhaul 3512',	'DI_GCG-CIESA-2024-303',	1,	1.10,	'1725496494.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2024-09-02',	'2024-07-01',	'2024-12-15',	NULL,	NULL,	'2024-08-14 13:05:56',	'2024-11-28 08:54:21'),
(47,	'Safety Industrial System: Ammonia Leakage Control',	'DI_GCG-CIESA-2024-502',	1,	1.10,	'1725496207.pdf',	1,	'Finished',	'Health & Safety',	'Buildings',	'Normal Capex',	'2024-09-02',	'2024-07-01',	'2024-12-31',	NULL,	NULL,	'2024-08-14 13:06:33',	'2025-01-07 18:41:29'),
(48,	'Lab Safety Upgrade: Gas Extraction Hood & Equipment Procurement',	'DI_GCG-CIESA-2024-506',	1,	1.10,	'1725494801.pdf',	1,	'Finished',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2024-09-02',	'2024-06-01',	'2024-12-30',	NULL,	NULL,	'2024-08-14 13:08:34',	'2025-01-07 18:41:12'),
(49,	'Sanitary water systems for bathrooms, dining rooms and laundry rooms',	'DI_GCG-CIESA-2024-701',	1,	1.10,	'1734794286.pdf',	1,	'Execution',	'Health & Safety',	'Buildings',	'Normal Capex',	'2024-10-01',	'2024-10-01',	'2024-12-31',	NULL,	NULL,	'2024-08-14 13:09:52',	'2024-12-21 10:18:06'),
(50,	'Subway rainwater drainage system to river \"Rio Bravo\"',	'DI_GCG-CIESA-2024-703',	1,	1.10,	'1734794449.pdf',	1,	'Finished',	'Innovation',	'Buildings',	'Normal Capex',	'2024-10-01',	'2024-10-01',	'2024-12-31',	NULL,	NULL,	'2024-08-14 13:10:35',	'2025-01-07 18:41:00'),
(51,	'Substation design for 69/13.8 KV (10/12.5 Mva) Seafman - Ciesa',	'DI_GCG-CIESA-2024-704',	1,	1.10,	'1734794969.pdf',	1,	'Execution',	'Health & Safety',	'General Install',	'Normal Capex',	'2024-12-04',	'2024-07-01',	'2025-03-15',	NULL,	NULL,	'2024-08-14 13:11:20',	'2025-05-18 23:43:02'),
(52,	'Replacement of olmar boiler with a new 600BHP boiler with dual bio gas burner',	'CIESA-2025-701',	1,	1.05,	'1756819348.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-06-30',	'2025-01-01',	'2026-05-31',	NULL,	NULL,	'2024-08-14 15:12:31',	'2025-10-09 00:41:20'),
(54,	'X-ray equipment for CIESA line 2',	'CIESA-2025-401',	1,	1.05,	'1764346856.pdf',	1,	'Execution',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2025-01-01',	'2025-01-01',	'2025-12-12',	NULL,	NULL,	'2024-08-14 15:14:31',	'2025-11-28 11:20:56'),
(55,	'Overhaul generator CAT 3512 power 850 KVA. ',	'CIESA-2025-801',	1,	1.10,	NULL,	0,	'Capex',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2025-01-01',	'2025-01-01',	'2025-12-30',	NULL,	NULL,	'2024-08-14 15:15:21',	'2025-05-18 23:25:49'),
(57,	'Renovation of industrial floors in cold chambers 5,4, and 7.',	'CIESA-2025-703',	1,	1.05,	NULL,	0,	'Execution',	'Innovation',	'Buildings',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-09-09',	NULL,	NULL,	'2024-08-14 15:16:55',	'2025-11-28 10:30:54'),
(58,	'Forklift Procurement for Cost Efficiency',	'CIESA-2025-707',	1,	1.10,	NULL,	0,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-11-11',	NULL,	NULL,	'2024-08-14 15:17:54',	'2025-06-30 00:17:47'),
(60,	'Extraction and ventilation system in kitchen area ',	'CIESA-2025-705',	1,	1.10,	NULL,	0,	'Planification',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2025-01-01',	'2025-01-01',	'2025-09-15',	NULL,	NULL,	'2024-08-14 15:20:05',	'2025-12-09 12:06:49'),
(61,	'CIESA 1 PLC kitchen technology upgrade (5 units)',	'CIESA-2025-704',	1,	1.10,	NULL,	0,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-01-01',	'2025-01-01',	'2025-09-01',	NULL,	NULL,	'2024-08-14 15:21:06',	'2025-06-30 00:17:37'),
(63,	'Acquisition of a 30hp variable speed air compressor, air dryer, and air conditioning for compressor room.',	'CIESA-2025-102',	1,	1.05,	'1764346508.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-11-28',	'2025-01-01',	'2026-01-30',	NULL,	NULL,	'2024-08-14 15:22:43',	'2025-11-28 11:15:08'),
(64,	'Synchronism of ciesa 2 generators and synchronism board',	'CIESA-2025-201',	1,	1.05,	'1764343137.pdf',	1,	'Execution',	'Efficiency & Saving',	'General Install',	'Normal Capex',	'2025-01-01',	'2025-01-01',	'2025-10-01',	NULL,	NULL,	'2024-08-14 15:30:00',	'2025-12-09 12:08:02'),
(65,	'Oil fillers line 1A, 1B',	'CIESA-2025-103',	1,	1.05,	'1764346821.pdf',	1,	'Execution',	'Capacity Increase',	'Machines & Equipm',	'Normal Capex',	'2025-02-01',	'2025-02-01',	'2025-08-01',	NULL,	NULL,	'2024-08-14 15:31:46',	'2025-12-09 12:08:10'),
(67,	'Minor Projects - Fish Containers',	'CIESA-2025-301a',	1,	1.05,	'1747521258.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-05-24',	'2025-10-01',	'2025-06-18',	NULL,	NULL,	'2024-08-14 15:33:47',	'2025-05-17 17:34:18'),
(71,	'Software and Equipment for Blue-Collar Incentive Program: PART 2',	'CIESA-2025-202',	1,	1.10,	NULL,	0,	'Planification',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-02-01',	'2025-02-01',	'2025-11-01',	NULL,	NULL,	'2024-08-14 15:38:44',	'2026-01-12 14:45:51'),
(72,	'Automated Data System in finished product warehouse and temperature adquisition in cold storage chambers',	'CIESA-2025-711',	0,	1.10,	NULL,	0,	'Capex',	'Innovation',	'Warenhouse & Distrib',	'Normal Capex',	'2025-02-01',	'2025-02-01',	'2025-09-01',	NULL,	NULL,	'2024-08-14 15:39:51',	'2025-11-28 10:55:51'),
(75,	'Instalacion de economizador para la caldera superior. ',	'DI_GCG-CIESA-2025-024',	1,	1.10,	NULL,	0,	'Capex',	'Environment',	'Buildings',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-10-01',	NULL,	NULL,	'2024-08-14 15:42:05',	'2024-10-29 11:20:47'),
(76,	'Steam Optimization: Implementacion de nueva linea de  vapor con sistema de trampeo - CIESA 2. ',	'CIESA-2025-602',	1,	1.05,	'1764346982.pdf',	1,	'Execution',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-08-01',	NULL,	NULL,	'2024-08-14 15:42:45',	'2025-12-09 12:07:16'),
(77,	'Osmosis Rejection Water Flow Measurement– EPAM Compliance',	'CIESA-2025-802',	1,	1.05,	NULL,	0,	'Execution',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2025-12-04',	'2025-02-01',	'2026-03-15',	NULL,	NULL,	'2024-08-14 15:43:27',	'2025-11-28 10:42:46'),
(78,	'Double sealing equipment for CIESA 2 (laboratory)',	'CIESA-2025-402',	1,	1.05,	'1764346907.pdf',	1,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-08-01',	'2025-08-01',	'2025-12-01',	NULL,	NULL,	'2024-08-14 15:44:06',	'2025-11-28 11:21:47'),
(80,	'DeptTech Upgrade: Laptops, tablets, server, security',	'CIESA-2025-708',	1,	1.05,	'1747515583.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Office Hardware Software',	'Normal Capex',	'2025-05-14',	'2025-07-01',	'2025-07-30',	NULL,	NULL,	'2024-08-14 15:46:43',	'2025-05-17 15:59:43'),
(82,	'Additional Scraper Conveyor Tables (22) and Fixed Stainless Steel Benches for Ciesa 1 and 2',	'CIESA-2025-501',	0,	1.10,	NULL,	0,	'Capex',	'Capacity Increase',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-09-01',	NULL,	NULL,	'2024-08-14 16:40:53',	'2025-11-28 11:22:14'),
(83,	'Replacement of Cummings KAT19 generator with one similar to the 750 KVA CAT C18. ',	'CIESA-2025-702',	1,	1.10,	NULL,	0,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-12-30',	NULL,	NULL,	'2024-08-14 16:41:32',	'2025-10-09 00:40:31'),
(87,	'Countermeasures to safety issues discovered in the obsevations and talks',	'CIESA-2025-501',	1,	1.05,	'1747515503.pdf',	1,	'Execution',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2025-05-24',	'2025-07-01',	'2025-06-18',	NULL,	NULL,	'2024-08-14 16:44:34',	'2025-05-17 15:58:23'),
(90,	'Overhaul labeler machine Ciesa 2 - TAXTA 3',	'CIESA-2025-803',	1,	1.05,	'1747515386.pdf',	1,	'Execution',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-09-01',	NULL,	NULL,	'2024-08-14 16:47:02',	'2025-06-30 00:17:10'),
(91,	'BMX tools for CIESA 2 Jar and Pouches line',	'CIESA-2025-709',	1,	1.05,	'1747515279.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2025-07-01',	'2025-07-01',	'2025-11-01',	NULL,	NULL,	'2024-08-14 16:48:12',	'2025-06-30 00:17:02'),
(92,	'Manufacture of new tank turner with its elements -Ciesa 2',	'CIESA-2025-710',	1,	1.05,	NULL,	0,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-10-01',	'2025-10-01',	'2025-12-15',	NULL,	NULL,	'2024-08-14 16:49:07',	'2025-11-28 10:47:26'),
(95,	'Kitchen Vacuum Pump - CIESA 2',	'DI_GCG-CIESA-2024-301e',	1,	1.10,	'1733961876.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2024-12-05',	'2024-12-05',	'2025-01-15',	NULL,	NULL,	'2024-12-11 18:38:27',	'2025-01-07 18:39:57'),
(96,	'CIESA 2 Generation System Complementing Upgrade',	'DI_GCG-CIESA-2024-301d',	1,	1.10,	'1734795621.pdf',	1,	'Finished',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2024-11-12',	'2024-11-12',	'2024-12-12',	NULL,	NULL,	'2024-12-21 10:35:34',	'2025-01-07 18:39:39'),
(99,	'Minor Project Remanent',	'301x',	1,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2025-05-07',	'2025-05-07',	'2025-12-30',	NULL,	NULL,	'2025-05-07 15:28:59',	'2025-05-07 15:31:34'),
(100,	'PL-Pouch line',	'SEAF-25-01',	1,	1.05,	'1749442643.pdf',	1,	'Execution',	'Capacity Increase',	'Buildings',	'Special Project',	'2025-04-14',	'2025-04-14',	'2026-03-31',	NULL,	NULL,	'2025-05-17 11:33:06',	'2026-05-07 05:40:53'),
(101,	'Metal separator for canning line',	'SEAF-25-04',	1,	1.05,	'1747501519.pdf',	1,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-03-25',	'2025-03-25',	'2025-07-31',	NULL,	NULL,	'2025-05-17 12:04:49',	'2025-12-09 12:06:10'),
(102,	'Retorts capacity increasing',	'SEAF-25-07',	1,	1.05,	'1747502686.pdf',	1,	'Execution',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-03-24',	'2025-03-24',	'2025-07-30',	NULL,	NULL,	'2025-05-17 12:21:41',	'2025-12-09 12:05:57'),
(103,	'Pre-cooker basket liners',	'SEAF-25-10',	1,	1.05,	'1747503075.pdf',	1,	'Execution',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-08-17',	NULL,	NULL,	'2025-05-17 12:30:42',	'2025-12-09 12:05:51'),
(104,	'Mycom compressors overhaul',	'SEAF-25-11',	1,	1.05,	'1747503861.pdf',	1,	'Execution',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2025-03-24',	'2025-03-24',	'2025-08-31',	NULL,	NULL,	'2025-05-17 12:43:47',	'2025-05-17 12:50:35'),
(106,	'Luthi 7916.5 formats',	'SEAF-25-12',	1,	1.05,	'1747504373.pdf',	1,	'Execution',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-09-30',	NULL,	NULL,	'2025-05-17 12:52:33',	'2025-05-17 15:14:18'),
(107,	'500kVA distribution board',	'SEAF-25-13',	1,	1.05,	'1747513259.pdf',	1,	'Execution',	'Capacity Increase',	'Machines & Equipm',	'Normal Capex',	'2025-03-24',	'2025-03-24',	'2025-06-16',	NULL,	NULL,	'2025-05-17 15:19:13',	'2025-12-09 12:05:40'),
(108,	'Butchering area - viscera recollection improvement',	'SEAF-25-14',	1,	1.05,	'1747513727.pdf',	1,	'Execution',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-06-29',	NULL,	NULL,	'2025-05-17 15:25:27',	'2025-12-09 12:05:32'),
(109,	'NH3 receiver replacement at yard #1',	'SEAF-25-20',	1,	1.05,	'1747513780.pdf',	1,	'Execution',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2025-03-31',	'2025-03-31',	'2025-12-31',	NULL,	NULL,	'2025-05-17 15:29:20',	'2025-12-09 12:04:14'),
(111,	'Electrical forklift replacement',	'SEAF-25-16',	1,	1.05,	'1747514366.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-12-31',	NULL,	NULL,	'2025-05-17 15:37:17',	'2025-12-09 12:05:21'),
(112,	'Overhaul FMC1',	'SEAF-25-23',	1,	1.05,	'1747514571.pdf',	1,	'Execution',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2025-04-01',	'2025-04-01',	'2025-08-18',	NULL,	NULL,	'2025-05-17 15:42:24',	'2025-12-09 12:03:53'),
(113,	'Replacement air compressors (#4)',	'SEAF-25-15',	1,	1.05,	'1747514760.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-05-01',	'2025-05-01',	'2025-08-17',	NULL,	NULL,	'2025-05-17 15:45:45',	'2025-12-09 12:05:27'),
(114,	'Replacement and new computer equipment',	'SEAF-25-35',	1,	1.05,	'1747514932.pdf',	1,	'Planification',	'Replacement & Restructuring',	'Office Hardware Software',	'Normal Capex',	'2025-04-24',	'2025-04-24',	'2025-10-31',	NULL,	NULL,	'2025-05-17 15:48:26',	'2025-12-09 12:01:41'),
(115,	'Server Backup System 2da fase',	'SEAF-25-36',	1,	1.05,	'1747515205.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Office Hardware Software',	'Normal Capex',	'2025-04-24',	'2025-04-24',	'2025-10-31',	NULL,	NULL,	'2025-05-17 15:53:00',	'2025-12-09 12:01:29'),
(116,	'Butchering area acess',	'SEAF-25-05',	1,	1.05,	'1747515879.pdf',	1,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-03-24',	'2025-03-24',	'2025-09-21',	NULL,	NULL,	'2025-05-17 16:04:15',	'2025-12-09 12:06:03'),
(117,	'WH-Security office relocation and securty CCTV system',	'SEAF-25-17',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2025-04-22',	'2025-04-22',	'2025-11-30',	NULL,	NULL,	'2025-05-17 16:08:55',	'2025-05-17 16:14:49'),
(118,	'Loins cold storage room refurbishment',	'SEAF-25-19',	1,	1.05,	'1765276409.pdf',	1,	'Execution',	'Quality & Hygiene',	'Warenhouse & Distrib',	'Normal Capex',	'2025-05-26',	'2025-05-26',	'2025-10-20',	NULL,	NULL,	'2025-05-17 16:19:06',	'2025-12-09 12:04:25'),
(119,	'Foodgrade strainer at steam line for precookers & can seamer machines',	'SEAF-25-21',	1,	1.05,	NULL,	0,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-06-02',	'2025-06-02',	'2025-11-30',	NULL,	NULL,	'2025-05-17 16:26:40',	'2025-12-09 12:04:08'),
(120,	'WH-New entrance gate for rawfish at yard # 1',	'SEAF-25-22',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2025-10-31',	NULL,	NULL,	'2025-05-17 16:29:35',	'2025-05-17 16:30:41'),
(121,	'New Canning line 4',	'SEAF-25-24',	1,	1.05,	NULL,	0,	'Execution',	'Capacity Increase',	'Machines & Equipm',	'Normal Capex',	'2025-06-02',	'2025-06-02',	'2025-11-30',	NULL,	NULL,	'2025-05-17 16:34:40',	'2025-12-09 12:03:45'),
(122,	'WH-Administration area expansion',	'SEAF-25-25',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2025-12-31',	NULL,	NULL,	'2025-05-17 16:36:44',	'2025-05-17 16:49:59'),
(123,	'LPG facility for boilers',	'SEAF-25-26',	1,	1.05,	NULL,	0,	'Planification',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2025-06-02',	'2025-06-02',	'2026-04-30',	NULL,	NULL,	'2025-05-17 16:39:34',	'2025-12-09 12:03:25'),
(124,	'WH-Labeling area expansion',	'SEAF-25-27',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2025-11-30',	NULL,	NULL,	'2025-05-17 16:43:48',	'2025-05-17 16:49:42'),
(125,	'13.8 kV incoming cells & Distribution board at yard #2',	'SEAF-25-28',	1,	1.05,	NULL,	0,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-06-02',	'2025-06-02',	'2025-11-30',	NULL,	NULL,	'2025-05-17 16:46:19',	'2025-12-09 12:04:46'),
(126,	'WH-Depaletising facilities relocation',	'SEAF-25-29',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2025-12-31',	NULL,	NULL,	'2025-05-17 16:48:04',	'2025-05-17 16:49:25'),
(127,	'SEAFMAN Substation portion (SEAFMAN & CIESA project)',	'SEAF-25-30',	1,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Special Project',	'2025-10-13',	'2025-10-13',	'2026-06-30',	NULL,	NULL,	'2025-05-17 16:51:41',	'2025-05-17 16:52:42'),
(128,	'WH-HR & Cafeteria facilities relocation',	'SEAF-25-31',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2025-12-31',	NULL,	NULL,	'2025-05-17 16:53:19',	'2025-05-17 16:54:13'),
(129,	'Auxiliary GENSET for yard #1 (1778 kVA)',	'SEAF-25-32',	1,	1.05,	NULL,	0,	'Planification',	'Maintenance',	'Machines & Equipm',	'Special Project',	'2025-06-02',	'2025-06-02',	'2026-06-30',	NULL,	NULL,	'2025-05-17 16:54:53',	'2025-12-09 12:02:57'),
(130,	'WH-Finished goods Warehouse',	'SEAF-25-33',	1,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Special Project',	'2025-06-02',	'2025-06-02',	'2026-06-30',	NULL,	NULL,	'2025-05-17 16:56:50',	'2025-05-17 16:57:52'),
(131,	'Software enlatado PIER',	'SEAF-25-34',	1,	1.05,	NULL,	0,	'Execution',	'Efficiency & Saving',	'Office Hardware Software',	'Normal Capex',	'2025-06-02',	'2025-06-02',	'2025-12-31',	NULL,	NULL,	'2025-05-17 16:58:35',	'2025-12-09 12:02:48'),
(132,	'New Jars Line with incentive program',	'CIESA-2025-101',	1,	1.05,	'1749442683.pdf',	1,	'Execution',	'Capacity Increase',	'Machines & Equipm',	'Special Project',	'2025-04-24',	'2025-04-24',	'2026-03-18',	NULL,	NULL,	'2025-05-18 22:56:02',	'2025-09-28 19:40:12'),
(133,	'Construction of substation 10MVA',	'CIESA-2025-901',	1,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Buildings',	'Special Project',	'2025-06-30',	'2025-06-30',	'2026-06-30',	NULL,	NULL,	'2025-05-18 23:52:02',	'2025-05-19 15:58:38'),
(134,	'Product Line Regular ',	'GRALCO-25-02',	1,	1.05,	NULL,	0,	'Capex',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-11-30',	NULL,	NULL,	'2025-05-19 00:37:00',	'2025-06-21 12:41:19'),
(135,	'x-ray equipment',	'GRALCO-25-04',	1,	1.05,	'1750275613.pdf',	1,	'Execution',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2025-05-26',	'2025-05-26',	'2026-02-28',	NULL,	NULL,	'2025-05-19 00:55:39',	'2025-07-22 18:27:28'),
(136,	'Canning line optimization',	'GRALCO-25-05',	1,	1.05,	NULL,	0,	'Capex',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-12-30',	NULL,	NULL,	'2025-05-19 00:59:01',	'2025-06-21 12:41:45'),
(137,	'Cold room #4 cooling system improvement',	'GRALCO-25-06',	1,	1.05,	'1750275590.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-11-30',	NULL,	NULL,	'2025-05-19 01:02:09',	'2025-07-22 18:27:16'),
(138,	'Cold room 4 floor refurbishment',	'GRALCO-25-09',	1,	1.05,	'1750527924.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-12-15',	NULL,	NULL,	'2025-05-19 01:03:09',	'2025-07-22 18:27:04'),
(139,	'Deboner',	'GRALCO-25-11',	1,	1.05,	'1750275560.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-08-04',	NULL,	NULL,	'2025-05-19 01:06:55',	'2025-07-22 18:26:52'),
(140,	'Process & Laboratory offices refurbishment',	'GRALCO-25-14',	1,	1.05,	NULL,	0,	'Capex',	'Quality & Hygiene',	'Buildings',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-12-29',	NULL,	NULL,	'2025-05-19 01:17:23',	'2025-05-19 01:18:14'),
(141,	'Minor projects',	'GRALCO-25-13',	1,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2025-05-19',	'2025-05-19',	'2025-11-10',	NULL,	NULL,	'2025-05-19 01:18:56',	'2025-05-19 01:19:39'),
(142,	'Evaporative condenser replacement No.2',	'GRALCO-25-12',	0,	1.05,	'1749680760.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-05-12',	'2025-05-12',	'2025-11-11',	NULL,	NULL,	'2025-05-19 01:21:00',	'2025-07-22 18:28:03'),
(144,	'Outbound logistics IT Restructuring',	'GRALCO-25-13A',	0,	1.05,	'1751747564.pdf',	1,	'Execution',	'Replacement & Restructuring',	'Office Hardware Software',	'Normal Capex',	'2025-06-19',	'2025-06-19',	'2025-08-31',	NULL,	NULL,	'2025-06-21 12:47:29',	'2025-07-22 18:27:57'),
(145,	'Maintenance Software',	'GRALCO-25-13B',	0,	1.05,	'1751747596.pdf',	1,	'Execution',	'Maintenance',	'Office Hardware Software',	'Normal Capex',	'2025-06-19',	'2025-06-19',	'2025-12-18',	NULL,	NULL,	'2025-06-21 12:48:45',	'2025-07-22 18:27:50'),
(146,	'Study for traceability system',	'GRALCO-25-13C',	1,	1.05,	'1751747630.pdf',	1,	'Execution',	'Quality & Hygiene',	'Office Hardware Software',	'Normal Capex',	'2025-06-19',	'2025-06-19',	'2025-12-31',	NULL,	NULL,	'2025-06-21 12:52:53',	'2025-07-22 18:26:39'),
(148,	'COLD STORAGE',	'SEAF-26-01',	1,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Special Project',	'2025-12-01',	'2025-12-01',	'2027-05-01',	NULL,	NULL,	'2025-10-01 10:06:31',	'2025-10-08 08:08:34'),
(149,	'USA Project',	'GRALCO-24-28',	1,	1.05,	NULL,	0,	'Execution',	'Capacity Increase',	'Machines & Equipm',	'Normal Capex',	'2024-12-25',	'2025-01-31',	'2025-11-30',	NULL,	NULL,	'2025-10-28 15:15:05',	'2025-11-11 11:54:40'),
(150,	'Study for restructuring traceability system',	'GRALCO-25-13C',	0,	1.05,	NULL,	0,	'Execution',	'Quality & Hygiene',	'Office Hardware Software',	'Normal Capex',	'2025-06-24',	'2025-06-24',	'2025-12-17',	NULL,	NULL,	'2025-10-28 17:13:01',	'2025-10-28 17:13:01'),
(151,	'Maintenance Software',	'GRALCO-25-13b',	0,	1.05,	NULL,	0,	'Execution',	'Maintenance',	'Office Hardware Software',	'Normal Capex',	'2025-06-19',	'2025-06-19',	'2025-12-18',	NULL,	NULL,	'2025-10-28 17:14:20',	'2025-10-28 17:14:20'),
(152,	'Outbound logistics IT Restructuring',	'GRALCO-25-13a',	0,	1.05,	NULL,	0,	'Finished',	'Replacement & Restructuring',	'Office Hardware Software',	'Normal Capex',	'2025-06-19',	'2025-06-19',	'2025-10-31',	NULL,	NULL,	'2025-10-28 17:17:48',	'2025-10-28 17:17:48'),
(153,	'Safety Structures and Access Optimization',	'CIESA-2025-301c',	1,	1.05,	NULL,	0,	'Execution',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2025-11-28',	'2025-11-28',	'2026-01-06',	NULL,	NULL,	'2025-12-03 16:29:32',	'2025-12-03 16:31:33'),
(154,	'Reliability Improvement and Control System Upgrade for Superior Boiler',	'CIESA-2025-301b',	1,	1.05,	NULL,	0,	'Execution',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2025-11-24',	'2025-11-24',	'2026-01-08',	NULL,	NULL,	'2025-12-04 12:28:06',	'2025-12-04 12:31:54'),
(155,	'Bios gas kit for Cleaver brooks boiler ( Mixto)',	'26-XXX',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2025-12-01',	'2025-12-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:15:02',	'2025-12-10 15:30:44'),
(156,	'Economizer',	'26-XX',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:16:08',	'2025-12-10 15:30:39'),
(157,	'Steam condensate return',	'27-XX',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:17:17',	'2025-12-10 15:31:05'),
(158,	'Replacement of can dryers',	'27-xx',	0,	1.00,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2027-01-01',	'2027-01-01',	'2027-12-12',	NULL,	NULL,	'2025-12-10 15:20:15',	'2025-12-10 15:30:32'),
(159,	'Replace amonia compresor Howdern ',	'27-xx',	0,	1.00,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2027-01-01',	'2027-01-01',	'2027-12-12',	NULL,	NULL,	'2025-12-10 15:23:14',	'2025-12-10 15:30:25'),
(161,	'Cooling towers and recovery water system on vacuum pumps ',	'26-xx',	0,	1.00,	NULL,	0,	'Capex',	'Innovation',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:25:29',	'2025-12-10 15:31:12'),
(162,	'Linea de vapor y condensado en marmitas',	'27-xx',	0,	1.00,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2027-01-01',	'2027-01-01',	'2027-12-12',	NULL,	NULL,	'2025-12-10 15:26:48',	'2025-12-10 15:31:27'),
(163,	'Install a new flash drying system to fast caged Line2',	'27-xx',	0,	1.00,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2027-01-01',	'2027-01-01',	'2027-12-12',	NULL,	NULL,	'2025-12-10 15:27:44',	'2025-12-10 15:31:18'),
(164,	'Hydraulic firefighting system ',	'CIESA-26-900',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:32:41',	'2025-12-10 15:32:41'),
(165,	'Main Overhaul water treatment plant fluence - Part 1',	'CIESA-26-800',	0,	1.05,	NULL,	0,	'Capex',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:33:39',	'2025-12-10 15:33:39'),
(166,	'Safety system for saw cutters (sistema de protección)',	'CIESA-26-500',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:35:12',	'2025-12-10 15:35:12'),
(167,	'X-Ray Equipment ( hasta 400 gr)',	'CIESA-26-400',	0,	1.05,	NULL,	0,	'Capex',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:35:45',	'2025-12-10 16:23:36'),
(168,	'Cool chamber sealing panels replacement (2000m2) - Part 1 ',	'CIESA-26-801',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:36:15',	'2025-12-10 15:36:15'),
(169,	'Pre-cooker cooling system - CIESA 2',	'CIESA-26-200',	0,	1.05,	NULL,	0,	'Capex',	'Efficiency & Saving',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:36:49',	'2025-12-10 15:36:49'),
(170,	'CIESA 2 raw material defrosting area',	'CIESA-26-201',	0,	1.05,	NULL,	0,	'Capex',	'Efficiency & Saving',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:37:22',	'2025-12-10 15:37:22'),
(171,	'Steam system optimization',	'CIESA-26-202',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:38:10',	'2025-12-10 15:38:10'),
(172,	'New Dosing machine for line 6 and 7',	'CIESA-26-203',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:38:42',	'2025-12-10 15:38:42'),
(173,	'Cooling towers and recovery water system on vacuum pumps ',	'CIESA-26-600',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:39:18',	'2025-12-10 15:39:18'),
(174,	'Upgrade of C1 electrical process transformer ',	'CIESA-26-805',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:47:27',	'2025-12-10 15:47:27'),
(175,	'Brain water piping replace (salmuera)',	'CIESA-26-703',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:47:54',	'2025-12-10 15:47:54'),
(176,	'Optimization of storage space in cold rooms',	'CIESA-26-205',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:48:39',	'2025-12-10 15:48:39'),
(177,	'Estructural steel & concreate columns recostruction ',	'CIESA-26-803',	0,	1.05,	NULL,	0,	'Capex',	'Maintenance',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:49:17',	'2025-12-10 15:49:17'),
(178,	'COUNTERMEASURES TO SAFETY ISSUES DISCOVERED IN THE OBSERVATIONS AND TALKS',	'CIESA-26-401',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:49:53',	'2025-12-10 16:11:24'),
(179,	'Bios gas kit for Cleaver brooks boiler ( Mixto)',	'CIESA-26-602',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:50:19',	'2025-12-10 16:11:16'),
(180,	'Replace obsolete refrigeration HVAC system in fish cleanning area (evaporators)',	'CIESA-26-705',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:50:42',	'2025-12-10 15:50:42'),
(181,	'SUPPLIES STORAGE ROOM',	'CIESA-26-707',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:51:13',	'2025-12-10 16:11:03'),
(182,	'Reconstruction of wastewater well C1',	'CIESA-26-709',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:51:36',	'2025-12-10 16:10:58'),
(183,	'Replace cooking kettles C1 and C2.',	'CIESA-26-710',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:53:37',	'2025-12-10 16:10:52'),
(184,	'Raw fish tray conveyor in Ciesa 2',	'CIESA-26-804',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:54:10',	'2025-12-10 16:10:46'),
(185,	'Replace water feeding tank for boilers ',	'CIESA-26-711',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 15:55:05',	'2025-12-10 16:10:41'),
(186,	'Depht tech Upgrade: Laptops, server and IT equipments',	'CIESA-26-712',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Other',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:06:21',	'2025-12-10 16:06:21'),
(187,	'Raw fish buffer conveyor in Ciesa 1',	'CIESA-26-713',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:06:51',	'2025-12-10 16:06:51'),
(188,	'Payroll system integration ',	'CIESA-26-501',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:07:14',	'2025-12-10 16:07:14'),
(189,	'Implementation of 1 backup inkjet printers',	'CIESA-26-714',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:07:43',	'2025-12-10 16:07:43'),
(190,	'Safety equipment: Fall protection, tarpaulin, and printer for signage',	'CIESA-26-901',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:08:12',	'2025-12-10 16:08:12'),
(191,	'Overhaul de Cerradora Fmc 2',	'SEAF-26-801',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:11:56',	'2025-12-10 16:11:56'),
(192,	'Pre-Chamber Floor Replacement – Yard 2',	'SEAF-26-800',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:12:23',	'2025-12-10 16:12:23'),
(193,	'Mycom N8WB #6 Compressor Overhaul',	'SEAF-26-804',	0,	1.05,	NULL,	0,	'Capex',	'Maintenance',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:12:57',	'2025-12-10 16:12:57'),
(194,	'Cooling Tower Setup for Excess Water Transfer (Cistern 2 → Cistern 1)',	'SEAF-26-204',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:13:27',	'2025-12-10 16:13:27'),
(195,	'Deaerator Tank and Water Lines',	'SEAF-26-207',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:14:02',	'2025-12-10 16:14:02'),
(196,	'Enhanced Illumination in Process Area',	'SEAF-26-903',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:15:30',	'2025-12-10 16:15:30'),
(197,	'Evaporative Condenser Replacement – Chamber 1',	'SEAF-26-707',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:16:25',	'2025-12-10 16:16:25'),
(198,	'Acquisition of Quality and Microbiology Laboratory Equipment',	'SEAF-26-403',	0,	1.05,	NULL,	0,	'Capex',	'Quality & Hygiene',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:17:01',	'2025-12-10 16:17:01'),
(199,	'Shrink-Wrap and Pack Line Upgrade',	'SEAF-26-206',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:17:53',	'2025-12-10 16:17:53'),
(200,	'Process Room Acoustic Treatment phase 1-2',	'SEAF-26-901',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:19:40',	'2025-12-10 16:19:40'),
(201,	'Water Softener Tank',	'SEAF-26-718',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:20:22',	'2025-12-10 16:20:22'),
(202,	'CCTV Upgrade – Yards 1, 2 & 3 Phase I',	'SEAF-26-709',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:21:16',	'2025-12-10 16:21:16'),
(203,	'Energy Measurement System (ISO 50001)',	'SEAF-26-802',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:21:44',	'2025-12-10 16:21:44'),
(204,	'Flow Measurement Sensors Implementation',	'SEAF-26-900',	0,	1.05,	NULL,	0,	'Capex',	'Environment',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:22:22',	'2025-12-10 16:22:38'),
(205,	'Labeling Efficiency Enhancement (OEE)',	'SEAF-26-203',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:31:49',	'2025-12-10 16:31:49'),
(206,	'Electric Forklift Replacement ',	'SEAF-26-710',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:32:26',	'2025-12-10 16:32:26'),
(207,	'Roof Renewal – Canning Section',	'SEAF-26-706',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:32:56',	'2025-12-10 16:32:56'),
(208,	'Chillroom Equipment Replacement / Partial',	'SEAF-26-701',	0,	1.05,	NULL,	0,	'Capex',	'Replacement & Restructuring',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:33:28',	'2025-12-10 16:33:28'),
(209,	'307 Format – Setup Initiative (Instalar cerradora altern fase 1-2)',	'SEAF-26-201',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:34:04',	'2025-12-10 16:34:04'),
(210,	'Raw Material System Technological Modernization',	'SEAF-26-602',	0,	1.05,	NULL,	0,	'Capex',	'Quality & Hygiene',	'Buildings',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:35:12',	'2025-12-10 16:35:12'),
(211,	'Replacement and Acquisition of New IT Equipment (PCs, Laptops & Tablets)',	'SEAF-26-715',	0,	1.05,	NULL,	0,	'Capex',	'Innovation',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	NULL,	NULL,	'2025-12-10 16:35:44',	'2025-12-10 16:35:44'),
(212,	'COUNTERMEASURES TO SAFETY ISSUES DISCOVERED IN THE OBSERVATIONS AND TALKS',	'SEAF-26-500',	0,	1.05,	NULL,	0,	'Capex',	'Health & Safety',	'Machines & Equipm',	'Normal Capex',	'2026-01-01',	'2026-01-01',	'2026-12-12',	'2026-07-31',	'2026-08-29',	'2025-12-10 16:47:25',	'2026-07-22 00:25:23'),
(219,	'test',	'test',	0,	1.00,	NULL,	0,	'Capex',	'Innovation',	'Buildings',	'Normal Capex',	'2026-07-02',	'2026-07-02',	'2026-08-27',	'2026-08-10',	'2026-11-22',	'2026-07-16 22:04:59',	'2026-07-16 22:05:58');

-- Transform the legacy dump to the current Laravel projects schema.
-- Company mapping is derived exclusively from pda_code:
--   *CIESA*  -> companies.company_code = CIESA
--   *GRALCO* -> companies.company_code = GRALCO
--   *SEAF*   -> companies.company_code = SEAFMAN
--
-- Rows without one of these identifiers are intentionally not imported.

SET @ciesa_company_id = (
  SELECT `id`
  FROM `companies`
  WHERE UPPER(`company_code`) LIKE 'CIESA%'
  LIMIT 1
);

SET @gralco_company_id = (
  SELECT `id`
  FROM `companies`
  WHERE UPPER(`company_code`) = 'GRALCO'
  LIMIT 1
);

SET @seafman_company_id = (
  SELECT `id`
  FROM `companies`
  WHERE UPPER(`company_code`) = 'SEAFMAN'
  LIMIT 1
);

SET @default_created_by = (
  SELECT `id`
  FROM `users`
  ORDER BY `id`
  LIMIT 1
);

INSERT INTO `projects` (
  `id`,
  `company_id`,
  `created_by`,
  `responsible_id`,
  `name`,
  `pda_code`,
  `rate`,
  `state`,
  `investments`,
  `justification`,
  `classification_of_investments`,
  `data_uploaded`,
  `quartile_date`,
  `forecast_start_date`,
  `forecast_end_date`,
  `file_name`,
  `upload_pda`,
  `approve_date`,
  `close_date`,
  `created_at`,
  `updated_at`
)
SELECT
  legacy.`id`,
  CASE
    WHEN UPPER(legacy.`pda_code`) LIKE '%CIESA%'
      THEN @ciesa_company_id
    WHEN UPPER(legacy.`pda_code`) LIKE '%GRALCO%'
      THEN @gralco_company_id
    WHEN UPPER(legacy.`pda_code`) LIKE '%SEAF%'
      THEN @seafman_company_id
  END AS `company_id`,
  @default_created_by AS `created_by`,
  NULL AS `responsible_id`,
  legacy.`name`,
  CASE
    WHEN (
      SELECT COUNT(*)
      FROM `legacy_projects` AS duplicate
      WHERE duplicate.`pda_code` = legacy.`pda_code`
    ) > 1
      THEN CONCAT(legacy.`pda_code`, '-', legacy.`id`)
    ELSE legacy.`pda_code`
  END AS `pda_code`,
  legacy.`rate`,
  CASE
    WHEN legacy.`state` = 'Planification' THEN 'Planning'
    ELSE legacy.`state`
  END AS `state`,
  legacy.`investments`,
  legacy.`justification`,
  legacy.`classification_of_investments`,
  IF(legacy.`data_uploaded` <> 0, 1, 0) AS `data_uploaded`,
  legacy.`quartile_date`,
  legacy.`start_date`,
  legacy.`finish_date`,
  legacy.`file_name`,
  CASE
    WHEN legacy.`upload_pda` <> 0 THEN legacy.`file_name`
    ELSE NULL
  END AS `upload_pda`,
  legacy.`approve_date`,
  legacy.`close_date`,
  legacy.`created_at`,
  legacy.`updated_at`
FROM `legacy_projects` AS legacy
WHERE
  UPPER(legacy.`pda_code`) LIKE '%CIESA%'
  OR UPPER(legacy.`pda_code`) LIKE '%GRALCO%'
  OR UPPER(legacy.`pda_code`) LIKE '%SEAF%';

DROP TABLE `legacy_projects`;

SET foreign_key_checks = 1;

-- 2026-07-27 22:40:02
