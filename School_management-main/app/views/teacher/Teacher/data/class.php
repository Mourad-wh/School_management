<?php
/**
 * Récupère les classes assignées à un professeur
 * @param int $teacher_id - ID du professeur
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des classes ou 0 si aucune
 */
function getClassesByTeacherId($teacher_id, $conn) {
    $sql = "SELECT c.id, c.name, c.grade 
            FROM classes c
            JOIN teacher_class tc ON c.id = tc.class_id
            WHERE tc.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$teacher_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
}

/**
 * Compte le nombre d'étudiants dans une classe
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return int - Nombre d'étudiants
 */
function countStudentsInClass($class_id, $conn) {
    $sql = "SELECT COUNT(*) 
            FROM student_class
            WHERE class_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$class_id]);
    return $stmt->fetchColumn() ?: 0;
}

/**
 * Récupère les informations d'une classe par son ID
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array|null - Informations de la classe ou null si non trouvée
 */
function getClassById($class_id, $conn) {
    $sql = "SELECT * FROM classes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$class_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Récupère les étudiants d'une classe spécifique
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des étudiants ou 0 si aucun
 */
function getStudentsByClass($class_id, $conn) {
    $sql = "SELECT s.id, s.full_name, s.email, s.phone
            FROM students s
            JOIN student_class sc ON s.id = sc.student_id
            WHERE sc.class_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$class_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
}

/**
 * Récupère toutes les classes de l'école
 * @param PDO $conn - Connexion à la base de données
 * @return array - Liste des classes
 */
function getAllClasses($conn) {
    $sql = "SELECT * FROM classes";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Récupère les matières enseignées par un professeur
 * @param int $teacher_id - ID du professeur
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des matières ou 0 si aucune
 */
function getSubjectsByTeacherId($teacher_id, $conn) {
    $sql = "SELECT s.id, s.name, s.description
            FROM subjects s
            JOIN teacher_subject ts ON s.id = ts.subject_id
            WHERE ts.teacher_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$teacher_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
}

/**
 * Récupère les notes d'un étudiant dans une classe spécifique
 * @param int $student_id - ID de l'étudiant
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des notes ou 0 si aucune
 */
function getStudentGrades($student_id, $class_id, $conn) {
    $sql = "SELECT n.*, s.name as subject_name 
            FROM notes n
            JOIN subjects s ON n.subject_id = s.id
            WHERE n.student_id = ? AND n.class_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$student_id, $class_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
}

/**
 * Récupère les absences d'un étudiant dans une classe spécifique
 * @param int $student_id - ID de l'étudiant
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des absences ou 0 si aucune
 */
function getStudentAbsences($student_id, $class_id, $conn) {
    $sql = "SELECT * FROM absence 
            WHERE student_id = ? AND class_id = ?
            ORDER BY date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$student_id, $class_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : 0;
}
/**
 * Vérifie si un professeur est assigné à une classe
 * @param int $teacher_id - ID du professeur
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return bool - True si assigné, false sinon
 */
function isTeacherAssignedToClass($teacher_id, $class_id, $conn) {
    try {
        $sql = "SELECT COUNT(*) FROM teacher_class 
                WHERE teacher_id = ? AND class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$teacher_id, $class_id]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Erreur dans isTeacherAssignedToClass: " . $e->getMessage());
        return false;
    }
}