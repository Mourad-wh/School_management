<?php
/**
 * Récupère une classe par son ID
 * @param int $id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array|null - Informations de la classe ou null si non trouvée
 */
function getClassById($id, $conn) {
    try {
        $sql = "SELECT id, name, grade FROM classes WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Erreur getClassById: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupère les étudiants d'une classe spécifique
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return array - Liste des étudiants (vide si aucun)
 */
function getStudentsByClass($class_id, $conn) {
    try {
        $sql = "SELECT s.id, s.full_name, s.email, s.phone
                FROM students s
                JOIN student_class sc ON s.id = sc.student_id
                WHERE sc.class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur getStudentsByClass: " . $e->getMessage());
        return [];
    }
}

/**
 * Compte le nombre d'étudiants dans une classe
 * @param int $class_id - ID de la classe
 * @param PDO $conn - Connexion à la base de données
 * @return int - Nombre d'étudiants (0 en cas d'erreur)
 */
function countStudentsInClass($class_id, $conn) {
    try {
        $sql = "SELECT COUNT(*) FROM student_class WHERE class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$class_id]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur countStudentsInClass: " . $e->getMessage());
        return 0;
    }
}