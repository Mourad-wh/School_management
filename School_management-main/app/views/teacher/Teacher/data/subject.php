<?php 


/**
 * Récupère les matières enseignées par un professeur pour une classe spécifique
 * @param PDO $conn - Connexion à la base de données
 * @param int $teacher_id - ID du professeur
 * @param int $class_id - ID de la classe
 * @return array - Liste des matières (vide si aucune)
 */
function getTeacherSubjectsForClass($conn, $teacher_id, $class_id) {
    try {
        $sql = "SELECT DISTINCT s.id, s.name, s.description 
                FROM subjects s
                JOIN teacher_subject ts ON s.id = ts.subject_id
                JOIN teacher_class tc ON tc.teacher_id = ts.teacher_id
                WHERE ts.teacher_id = ? AND tc.class_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$teacher_id, $class_id]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur dans getTeacherSubjectsForClass: " . $e->getMessage());
        return [];
    }
}
 ?>