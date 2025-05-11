-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 05 mai 2025 à 16:15
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
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `grade` varchar(50) NOT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `name`, `grade`, `school_id`) VALUES
(1, 'Classe A', '', 1),
(2, 'Classe B', '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `exam`
--

CREATE TABLE `exam` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `exam`
--

INSERT INTO `exam` (`id`, `name`, `date`, `subject_id`, `teacher_id`) VALUES
(1, 'Examen de Mathématiques - Session 1', '2025-05-10', 1, 1),
(5, 'math 2', '2025-05-24', 1, 1),
(6, 'Examen de Physique - Session 1', '2025-05-12', 2, 2),
(7, 'Examen d\'Anglais - Session 1', '2025-05-14', 3, 3),
(8, 'Examen de Français - Session 1', '2025-05-16', 4, 4),
(9, 'Examen de Chimie - Session 1', '2025-05-18', 5, 5);

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
  `qcm` float DEFAULT NULL,
  `participation` float DEFAULT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notes`
--

INSERT INTO `notes` (`id`, `student_id`, `subject_id`, `grade`, `exam_id`, `qcm`, `participation`, `class_id`) VALUES
(1, 1, 1, 15.50, 1, 16, 14, 0),
(2, 1, 2, 17.00, 6, 13, 19, 0),
(3, 3, 1, 12.60, 1, NULL, NULL, 0),
(4, 4, 1, 16.20, 1, NULL, NULL, 0),
(5, 5, 1, 10.50, 1, NULL, NULL, 0),
(6, 6, 1, 13.40, 1, 13, NULL, 0),
(7, 7, 1, 11.80, 1, NULL, NULL, 0),
(8, 8, 1, 17.00, 1, NULL, NULL, 0),
(9, 9, 1, 9.60, 1, NULL, NULL, 0),
(10, 10, 1, 14.90, 1, NULL, NULL, 0);

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
(1, '1999', 'marrakech', ''),
(2, '1999', 'marrakech', '066666666');

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

--
-- Déchargement des données de la table `session`
--

INSERT INTO `session` (`id`, `exam_id`, `start_time`, `end_time`) VALUES
(1, 1, '2025-05-10 09:00:00', '2025-05-10 11:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `school_id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `students`
--

INSERT INTO `students` (`id`, `full_name`, `email`, `phone`, `school_id`, `password`) VALUES
(1, 'Alice Dupont', 'alice1@example.com', '0600000001', 1, '$2y$10$Vlaby8d8boh/CsZQzC7fEusosRiFOSnkhwhZqV/kl6TpPPElnAJry'),
(2, 'Bob Martin', 'bob2@example.com', '0600000002', 1, ''),
(3, 'Claire Durand', 'claire3@example.com', '0600000003', 1, ''),
(4, 'David Petit', 'david4@example.com', '0600000004', 1, ''),
(5, 'Emma Bernard', 'emma5@example.com', '0600000005', 1, ''),
(6, 'Florian Morel', 'florian6@example.com', '0600000006', 1, ''),
(7, 'Gabrielle Faure', 'gabrielle7@example.com', '0600000007', 1, ''),
(8, 'Hugo Lefevre', 'hugo8@example.com', '0600000008', 1, ''),
(9, 'Inès Colin', 'ines9@example.com', '0600000009', 1, ''),
(10, 'Jules Roche', 'jules10@example.com', '0600000010', 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `student_class_teacher`
--

CREATE TABLE `student_class_teacher` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `student_class_teacher`
--

INSERT INTO `student_class_teacher` (`id`, `student_id`, `class_id`, `teacher_id`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 1),
(3, 3, 1, 1),
(4, 4, 1, 1),
(5, 5, 1, 1),
(6, 6, 2, 1),
(7, 7, 2, 1),
(8, 8, 2, 1),
(9, 9, 2, 1),
(10, 10, 2, 1);

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
(1, 'Mathématiques', 'Cours de mathématiques pour les niveaux secondaires'),
(2, 'Physique', 'Cours de physique pour les niveaux secondaires'),
(3, 'Anglais', 'Cours d\'anglais - Langue étrangère'),
(4, 'Français', 'Cours de français et littérature'),
(5, 'Chimie', 'Cours de chimie pour les niveaux secondaires');

-- --------------------------------------------------------

--
-- Structure de la table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teachers`
--

INSERT INTO `teachers` (`id`, `full_name`, `email`, `phone`, `school_id`, `password`) VALUES
(1, 'John Doe', 'johndoe@example.com', '0612345678', 1, '123'),
(2, 'Marie Curie', 'mcurie@example.com', '0612345679', 1, '123'),
(3, 'Emma Watson', 'ewatson@example.com', '0612345680', 1, '123'),
(4, 'Victor Hugo', 'vhugo@example.com', '0612345681', 1, '123'),
(5, 'Antoine Lavoisier', 'alavoisier@example.com', '0612345682', 1, '123');

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
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5);

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
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_notes` (`student_id`,`subject_id`,`class_id`),
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
-- Index pour la table `student_class_teacher`
--
ALTER TABLE `student_class_teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `teacher_id` (`teacher_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `average`
--
ALTER TABLE `average`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `exam`
--
ALTER TABLE `exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `student_class_teacher`
--
ALTER TABLE `student_class_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `exam_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

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
-- Contraintes pour la table `student_class_teacher`
--
ALTER TABLE `student_class_teacher`
  ADD CONSTRAINT `student_class_teacher_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_class_teacher_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `student_class_teacher_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Contraintes pour la table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`);

--
-- Contraintes pour la table `teacher_subject`
--
ALTER TABLE `teacher_subject`
  ADD CONSTRAINT `teacher_subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `teacher_subject_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
