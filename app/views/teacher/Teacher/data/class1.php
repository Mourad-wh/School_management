<?php
function getClassById($id, $conn) {
    try {
        $sql = "SELECT * FROM classes WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Erreur getClassById: " . $e->getMessage());
        return null;
    }
}

function getStudentsByClass($class_id, $conn) {
    try {
        $sql = "SELECT s.* 
                FROM students s
                JOIN student_class_teacher sct ON s.id = sct.student_id
                WHERE sct.class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$class_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erreur getStudentsByClass: " . $e->getMessage());
        return [];
    }
}

function countStudentsInClass($class_id, $conn) {
    try {
        $sql = "SELECT COUNT(*) FROM student_class_teacher WHERE class_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$class_id]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erreur countStudentsInClass: " . $e->getMessage());
        return 0;
    }
}