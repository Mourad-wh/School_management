<?php  

function getTeacherById($teacher_id, $conn) {
    if (!$conn) {
        error_log("Erreur: Pas de connexion DB");
        return false;
    }

    try {
        $sql = "SELECT * FROM teachers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erreur préparation requête: " . implode(" ", $conn->errorInfo()));
            return false;
        }
        
        $stmt->execute([$teacher_id]);
        
        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Retourne un tableau associatif
        }
        
        error_log("Aucun enseignant trouvé avec l'ID: " . $teacher_id);
        return false;
    } catch (PDOException $e) {
        error_log("Erreur getTeacherById: " . $e->getMessage());
        return false;
    }
}
/**
 * Récupère l'emploi du temps des examens d'un professeur
 * @param int $teacher_id - ID du professeur
 * @param PDO $conn - Connexion à la base de données
 * @return array|int - Liste des examens ou 0 si aucun
 */
function getTeacherSchedule($teacher_id, $conn) {
    $sql = "SELECT e.id, e.name, e.date, e.hour, e.exam_type, 
                   s.name as subject_name, c.name as class_name
            FROM exam e
            JOIN subjects s ON e.subject_id = s.id
            JOIN classes c ON e.class_id = c.id
            WHERE e.teacher_id = ?
            ORDER BY e.date, e.hour";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$teacher_id]);
    
    return ($stmt->rowCount() > 0) ? $stmt->fetchAll() : 0;
}