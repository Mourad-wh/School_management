-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 11 mai 2025 à 14:02
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `school_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `absence`
--

CREATE TABLE `absence` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `hours_absent` int(11) DEFAULT 0,
  `justification_file` varchar(255) DEFAULT NULL,
  `is_validated` tinyint(1) DEFAULT 0,
  `alert_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `absence`
--

INSERT INTO `absence` (`id`, `student_id`, `class_id`, `date`, `hours_absent`, `justification_file`, `is_validated`, `alert_sent`) VALUES
(6, 23, 18, '2025-05-15', 2, NULL, 0, 1),
(7, 28, 19, '2025-05-10', 1, NULL, 0, 1),
(8, 35, 20, '2025-05-12', 3, NULL, 0, 0),
(9, 42, 21, '2025-05-08', 2, NULL, 1, 0),
(10, 49, 22, '2025-05-11', 1, NULL, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `first_name`, `last_name`, `email`, `phone`, `school_id`, `password`, `username`) VALUES
(1, 'Mourad', 'El Ouatik', 'mouradelouatik05@gmail.com', '0623189074', 2202387, 'mouradelouatik', 'mourad');

-- --------------------------------------------------------

--
-- Structure de la table `average`
--

CREATE TABLE `average` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `average` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `average`
--

INSERT INTO `average` (`id`, `student_id`, `subject_id`, `average`) VALUES
(1, 23, 11, 14.50),
(2, 24, 11, 16.75),
(3, 25, 11, 12.25),
(4, 26, 11, 18.00),
(5, 27, 11, 15.50),
(6, 28, 12, 13.75),
(7, 29, 12, 17.25),
(8, 30, 12, 11.50),
(9, 31, 12, 19.00),
(10, 32, 12, 14.75),
(11, 33, 13, 15.00),
(12, 34, 13, 16.50),
(13, 35, 13, 12.75),
(14, 36, 13, 18.25),
(15, 37, 13, 14.50);

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `grade` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `name`, `school_id`, `grade`) VALUES
(18, 'GI3-A', 2202387, '3'),
(19, 'GI3-B', 2202387, '3'),
(20, 'GI4-A', 2202387, '4'),
(21, 'GI4-B', 2202387, '4'),
(22, 'GI5-A', 2202387, '5'),
(23, 'GI5-B', 2202387, '5'),
(24, 'GCDSTE3-A', 2202387, '3');

-- --------------------------------------------------------

--
-- Structure de la table `exam`
--

CREATE TABLE `exam` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `exam_type` enum('qcm','onsite') NOT NULL,
  `hour` time DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `exam`
--

INSERT INTO `exam` (`id`, `name`, `date`, `subject_id`, `teacher_id`, `exam_type`, `hour`, `class_id`) VALUES
(8, 'Database Midterm', '2025-06-10', 11, 9, 'onsite', '09:00:00', 18),
(9, 'Web Development Final', '2025-06-15', 12, 10, 'onsite', '10:30:00', 19),
(10, 'AI Quiz 1', '2025-06-05', 13, 11, 'qcm', '14:00:00', 20),
(11, 'Networks Midterm', '2025-06-12', 14, 12, 'onsite', '11:00:00', 21),
(12, 'Software Engineering Project', '2025-06-20', 15, 13, 'onsite', '13:30:00', 22),
(13, 'OS Final Exam', '2025-06-18', 16, 14, 'onsite', '09:00:00', 23),
(14, 'Data Structures Quiz', '2025-06-08', 17, 15, 'qcm', '15:30:00', 24);

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `qcm` decimal(5,2) DEFAULT NULL,
  `participation` decimal(5,2) DEFAULT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notes`
--

INSERT INTO `notes` (`id`, `student_id`, `subject_id`, `grade`, `exam_id`, `qcm`, `participation`, `class_id`) VALUES
(9, 23, 11, 14.50, 8, 12.00, 15.00, 18),
(10, 24, 11, 16.75, 8, 15.00, 17.50, 18),
(11, 25, 11, 12.25, 8, 10.50, 13.00, 18),
(12, 26, 11, 18.00, 8, 17.00, 18.50, 18),
(13, 27, 11, 15.50, 8, 14.00, 16.00, 18),
(14, 28, 12, 13.75, 9, 12.50, 14.00, 19),
(15, 29, 12, 17.25, 9, 16.00, 17.50, 19),
(16, 30, 12, 11.50, 9, 10.00, 12.00, 19),
(17, 31, 12, 19.00, 9, 18.50, 19.00, 19),
(18, 32, 12, 14.75, 9, 13.50, 15.00, 19),
(19, 33, 13, 15.00, 10, 14.00, 15.50, 20),
(20, 34, 13, 16.50, 10, 15.50, 17.00, 20),
(21, 35, 13, 12.75, 10, 11.50, 13.00, 20),
(22, 36, 13, 18.25, 10, 17.00, 18.50, 20),
(23, 37, 13, 14.50, 10, 13.50, 15.00, 20);

-- --------------------------------------------------------

--
-- Structure de la table `school`
--

CREATE TABLE `school` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `school`
--

INSERT INTO `school` (`id`, `name`, `address`, `phone`) VALUES
(2202387, 'ENSA Safi', 'Avenue de l\'Université, Safi', '0524612345'),
(2202388, 'ENSA Tanger', 'Route Ziaten, Tanger', '0539384756');

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id`, `email`, `phone`, `school_id`, `full_name`, `password`, `username`) VALUES
(23, 'student1@ensam.ma', '0611111111', 2202387, 'Yassin El Amrani', '$2y$10$pass1', 'yelamrani'),
(24, 'student2@ensam.ma', '0622222222', 2202387, 'Hafsa Bouzidi', '$2y$10$pass2', 'hbouzidi'),
(25, 'student3@ensam.ma', '0633333333', 2202387, 'Omar El Fassi', '$2y$10$pass3', 'oelfassi'),
(26, 'student4@ensam.ma', '0644444444', 2202387, 'Amina Cherkaoui', '$2y$10$pass4', 'acherkaoui'),
(27, 'student5@ensam.ma', '0655555555', 2202387, 'Mehdi Benjelloun', '$2y$10$pass5', 'mbenjelloun'),
(28, 'student6@ensam.ma', '0666666666', 2202387, 'Nadia El Mansouri', '$2y$10$pass6', 'nemansouri'),
(29, 'student7@ensam.ma', '0677777777', 2202387, 'Karim Bennis', '$2y$10$pass7', 'kbennis'),
(30, 'student8@ensam.ma', '0688888888', 2202387, 'Fatima Zahra El Amrani', '$2y$10$pass8', 'fzelamrani'),
(31, 'student9@ensam.ma', '0699999999', 2202387, 'Youssef Bouzidi', '$2y$10$pass9', 'ybouzidi'),
(32, 'student10@ensam.ma', '0600000000', 2202387, 'Leila El Fassi', '$2y$10$pass10', 'lelfassi'),
(33, 'student11@ensam.ma', '0612345678', 2202387, 'Ahmed Cherkaoui', '$2y$10$pass11', 'acherkaoui2'),
(34, 'student12@ensam.ma', '0623456789', 2202387, 'Samira Benjelloun', '$2y$10$pass12', 'sbenjelloun'),
(35, 'student13@ensam.ma', '0634567890', 2202387, 'Hicham El Mansouri', '$2y$10$pass13', 'helmansouri'),
(36, 'student14@ensam.ma', '0645678901', 2202387, 'Nadia Bennis', '$2y$10$pass14', 'nbennis2'),
(37, 'student15@ensam.ma', '0656789012', 2202387, 'Mehdi El Amrani', '$2y$10$pass15', 'melamrani'),
(38, 'student16@ensam.ma', '0667890123', 2202387, 'Fatima Bouzidi', '$2y$10$pass16', 'fbouzidi'),
(39, 'student17@ensam.ma', '0678901234', 2202387, 'Omar El Fassi', '$2y$10$pass17', 'oelfassi2'),
(40, 'student18@ensam.ma', '0689012345', 2202387, 'Amina Cherkaoui', '$2y$10$pass18', 'acherkaoui3'),
(41, 'student19@ensam.ma', '0690123456', 2202387, 'Youssef Benjelloun', '$2y$10$pass19', 'ybenjelloun'),
(42, 'student20@ensam.ma', '0601234567', 2202387, 'Leila El Mansouri', '$2y$10$pass20', 'lelmansouri'),
(43, 'student21@ensam.ma', '0611122222', 2202387, 'Karim Bennis', '$2y$10$pass21', 'kbennis2'),
(44, 'student22@ensam.ma', '0622233333', 2202387, 'Hafsa El Amrani', '$2y$10$pass22', 'helamrani'),
(45, 'student23@ensam.ma', '0633344444', 2202387, 'Yassin Bouzidi', '$2y$10$pass23', 'ybouzidi2'),
(46, 'student24@ensam.ma', '0644455555', 2202387, 'Samira El Fassi', '$2y$10$pass24', 'selfassi'),
(47, 'student25@ensam.ma', '0655566666', 2202387, 'Mehdi Cherkaoui', '$2y$10$pass25', 'mcherkaoui'),
(48, 'student26@ensam.ma', '0666677777', 2202387, 'Nadia Benjelloun', '$2y$10$pass26', 'nbenjelloun'),
(49, 'student27@ensam.ma', '0677788888', 2202387, 'Hicham El Mansouri', '$2y$10$pass27', 'helmansouri2'),
(50, 'student28@ensam.ma', '0688899999', 2202387, 'Fatima Bennis', '$2y$10$pass28', 'fbennis'),
(51, 'student29@ensam.ma', '0699900000', 2202387, 'Omar El Amrani', '$2y$10$pass29', 'oelamrani'),
(52, 'student30@ensam.ma', '0600011111', 2202387, 'Amina Bouzidi', '$2y$10$pass30', 'abouzidi'),
(53, 'student31@ensam.ma', '0611223344', 2202387, 'Youssef El Fassi', '$2y$10$pass31', 'yelfassi'),
(54, 'student32@ensam.ma', '0622334455', 2202387, 'Leila Cherkaoui', '$2y$10$pass32', 'lcherkaoui2'),
(55, 'student33@ensam.ma', '0633445566', 2202387, 'Karim Benjelloun', '$2y$10$pass33', 'kbenjelloun'),
(56, 'student34@ensam.ma', '0644556677', 2202387, 'Samira El Mansouri', '$2y$10$pass34', 'selmansouri'),
(57, 'student35@ensam.ma', '0655667788', 2202387, 'Mehdi Bennis', '$2y$10$pass35', 'mbennis');

-- --------------------------------------------------------

--
-- Structure de la table `student_class`
--

CREATE TABLE `student_class` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `student_class`
--

INSERT INTO `student_class` (`id`, `student_id`, `class_id`) VALUES
(6, 23, 18),
(7, 24, 18),
(8, 25, 18),
(9, 26, 18),
(10, 27, 18),
(11, 28, 19),
(12, 29, 19),
(13, 30, 19),
(14, 31, 19),
(15, 32, 19),
(16, 33, 20),
(17, 34, 20),
(18, 35, 20),
(19, 36, 20),
(20, 37, 20),
(21, 38, 21),
(22, 39, 21),
(23, 40, 21),
(24, 41, 21),
(25, 42, 21),
(26, 43, 22),
(27, 44, 22),
(28, 45, 22),
(29, 46, 22),
(30, 47, 22),
(31, 48, 23),
(32, 49, 23),
(33, 50, 23),
(34, 51, 23),
(35, 52, 23),
(36, 53, 24),
(37, 54, 24),
(38, 55, 24),
(39, 56, 24),
(40, 57, 24);

-- --------------------------------------------------------

--
-- Structure de la table `student_overall_average`
--

CREATE TABLE `student_overall_average` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `overall_average` decimal(5,2) NOT NULL,
  `result` enum('passed','failed') NOT NULL,
  `calculation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `student_overall_average`
--

INSERT INTO `student_overall_average` (`id`, `student_id`, `class_id`, `overall_average`, `result`, `calculation_date`) VALUES
(0, 23, 18, 14.50, 'passed', '2025-05-11 11:54:20'),
(0, 24, 18, 16.75, 'passed', '2025-05-11 11:54:20'),
(0, 25, 18, 12.25, 'passed', '2025-05-11 11:54:20'),
(0, 26, 18, 18.00, 'passed', '2025-05-11 11:54:20'),
(0, 27, 18, 15.50, 'passed', '2025-05-11 11:54:20'),
(0, 28, 19, 13.75, 'passed', '2025-05-11 11:54:20'),
(0, 29, 19, 17.25, 'passed', '2025-05-11 11:54:20'),
(0, 30, 19, 11.50, 'failed', '2025-05-11 11:54:20'),
(0, 31, 19, 19.00, 'passed', '2025-05-11 11:54:20'),
(0, 32, 19, 14.75, 'passed', '2025-05-11 11:54:20'),
(0, 33, 20, 15.00, 'passed', '2025-05-11 11:54:20'),
(0, 34, 20, 16.50, 'passed', '2025-05-11 11:54:20'),
(0, 35, 20, 12.75, 'passed', '2025-05-11 11:54:20'),
(0, 36, 20, 18.25, 'passed', '2025-05-11 11:54:20'),
(0, 37, 20, 14.50, 'passed', '2025-05-11 11:54:20'),
(0, 55, 24, 0.00, 'failed', '2025-05-11 11:58:27'),
(0, 54, 24, 0.00, 'failed', '2025-05-11 11:58:27'),
(0, 57, 24, 0.00, 'failed', '2025-05-11 11:58:27'),
(0, 56, 24, 0.00, 'failed', '2025-05-11 11:58:27'),
(0, 53, 24, 0.00, 'failed', '2025-05-11 11:58:27');

-- --------------------------------------------------------

--
-- Structure de la table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`) VALUES
(11, 'Database Systems', 'Fundamentals of database design and implementation'),
(12, 'Web Development', 'Building modern web applications'),
(13, 'Artificial Intelligence', 'Introduction to AI and machine learning'),
(14, 'Networks', 'Computer networks and protocols'),
(15, 'Software Engineering', 'Software development methodologies'),
(16, 'Operating Systems', 'Principles of operating systems'),
(17, 'Data Structures', 'Fundamental data structures and algorithms');

-- --------------------------------------------------------

--
-- Structure de la table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teachers`
--

INSERT INTO `teachers` (`id`, `email`, `phone`, `school_id`, `full_name`, `password`, `username`) VALUES
(9, 'prof1@ensam.ma', '0612345678', 2202387, 'Ahmed Benali', '$2y$10$abc123', 'abenali'),
(10, 'prof2@ensam.ma', '0623456789', 2202387, 'Fatima Zahra', '$2y$10$def456', 'fzahra'),
(11, 'prof3@ensam.ma', '0634567890', 2202387, 'Karim El Mansouri', '$2y$10$ghi789', 'kmansouri'),
(12, 'prof4@ensam.ma', '0645678901', 2202387, 'Leila Cherkaoui', '$2y$10$jkl012', 'lcherkaoui'),
(13, 'prof5@ensam.ma', '0656789012', 2202387, 'Youssef Amrani', '$2y$10$mno345', 'yamrani'),
(14, 'prof6@ensam.ma', '0667890123', 2202387, 'Nadia Bennis', '$2y$10$pqr678', 'nbennis'),
(15, 'prof7@ensam.ma', '0678901234', 2202387, 'Mehdi El Fassi', '$2y$10$stu901', 'melfassi');

-- --------------------------------------------------------

--
-- Structure de la table `teacher_class`
--

CREATE TABLE `teacher_class` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teacher_class`
--

INSERT INTO `teacher_class` (`id`, `teacher_id`, `class_id`) VALUES
(17, 9, 18),
(18, 10, 19),
(19, 11, 20),
(20, 12, 21),
(21, 13, 22),
(22, 14, 23),
(23, 15, 24);

-- --------------------------------------------------------

--
-- Structure de la table `teacher_subject`
--

CREATE TABLE `teacher_subject` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teacher_subject`
--

INSERT INTO `teacher_subject` (`id`, `teacher_id`, `subject_id`) VALUES
(32, 9, 11),
(33, 10, 12),
(34, 11, 13),
(35, 12, 14),
(36, 13, 15),
(37, 14, 16),
(38, 15, 17);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `absence`
--
ALTER TABLE `absence`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `school_id` (`school_id`);

--
-- Index pour la table `average`
--
ALTER TABLE `average`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `school_id` (`school_id`);

--
-- Index pour la table `exam`
--
ALTER TABLE `exam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `fk_class` (`class_id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Index pour la table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Index pour la table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- Index pour la table `student_class`
--
ALTER TABLE `student_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `school_id` (`school_id`);

--
-- Index pour la table `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Index pour la table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `absence`
--
ALTER TABLE `absence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `average`
--
ALTER TABLE `average`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `exam`
--
ALTER TABLE `exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2202389;

--
-- AUTO_INCREMENT pour la table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `student_class`
--
ALTER TABLE `student_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `teacher_class`
--
ALTER TABLE `teacher_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `absence`
--
ALTER TABLE `absence`
  ADD CONSTRAINT `absence_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absence_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`);

--
-- Contraintes pour la table `average`
--
ALTER TABLE `average`
  ADD CONSTRAINT `average_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `average_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`);

--
-- Contraintes pour la table `exam`
--
ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `fk_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `notes_ibfk_3` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`id`);

--
-- Contraintes pour la table `session`
--
ALTER TABLE `session`
  ADD CONSTRAINT `session_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam` (`id`);

--
-- Contraintes pour la table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`);

--
-- Contraintes pour la table `student_class`
--
ALTER TABLE `student_class`
  ADD CONSTRAINT `student_class_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`);

--
-- Contraintes pour la table `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD CONSTRAINT `teacher_class_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD CONSTRAINT `teacher_subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `teacher_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);
COMMIT;

-- Remove first_name column
ALTER TABLE admin DROP COLUMN first_name;

-- Rename last_name to full_name
ALTER TABLE admin CHANGE COLUMN last_name full_name VARCHAR(100) NOT NULL;

INSERT INTO admin (username, password, email, phone, school_id, id, full_name)
VALUES ('saad', '$argon2i$v=19$m=65536,t=4,p=1$eHdKdWlMM0F0T2JaR0prYw$xQG8VAP7WzJg9gpsXhqNoqUMxYEH+SA8kabhf0guq2E', 'saadchaoulid0@example.com', '0655818229', '2202387', '5', 'Saad CHAOULID');


UPDATE teachers
SET username = 'teacher', password = '$argon2i$v=19$m=65536,t=4,p=1$MWxtLjVQQWE2ZzFHSGEuWA$Uy0WpjweM6JA7HgNrei8cM5cvd+naJrnH6LQAyZyCLI'
WHERE username = 'fzahra';

UPDATE students
SET username = 'student', password = '$argon2i$v=19$m=65536,t=4,p=1$MW5IZmc2cEJEMmEya2Q0eA$IexDq6pFrucpN8rfhNpV/a2cpyjWETNhnuHXvZHt3ZM'
WHERE username = 'yelamrani';

INSERT INTO admin (username, password, email, phone, school_id, id, full_name)
VALUES ('admin', '$argon2i$v=19$m=65536,t=4,p=1$Um9pWDJ6QXQzU0hZYnJJeA$Q7QXajqRJFsZ3f3gxeGv8yLG0S4SogmMIETZc13v1o4', 'admin@example.com', '0655818229', '2202387', '10', 'admin');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
