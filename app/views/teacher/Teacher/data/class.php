<?php
// data/class.php
function getClassesByTeacherId($teacher_id, $conn) {
  $sql = "SELECT c.* 
          FROM classes c
          JOIN teacher_class sct ON c.id = sct.class_id
          WHERE sct.teacher_id = ?
          GROUP BY c.id";
  
  $stmt = $conn->prepare($sql);
  $stmt->execute([$teacher_id]);
  return $stmt->fetchAll() ?: 0;
}

function countStudentsInClass($class_id, $conn) {
  $sql = "SELECT COUNT(*) 
          FROM teacher_class
          WHERE class_id = ?";
  
  $stmt = $conn->prepare($sql);
  $stmt->execute([$class_id]);
  return $stmt->fetchColumn() ?: 0;
}
function getClassById($class_id, $conn) {
  $sql = "SELECT * FROM classes WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$class_id]);
  return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
function getStudentsByClass($class_id, $conn) {
  $sql = "SELECT s.* 
          FROM students s
          JOIN student_class sct ON s.id = sct.student_id
          WHERE sct.class_id = ?";
  
  $stmt = $conn->prepare($sql);
  $stmt->execute([$class_id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
function getAllClasses($conn) {
  $sql = "SELECT * FROM classes";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}