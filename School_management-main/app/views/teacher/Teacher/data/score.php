<?php  

// Récupère toutes les notes
function getAllScores($conn){
   $sql = "SELECT * FROM notes";
   $stmt = $conn->prepare($sql);
   $stmt->execute();

   if ($stmt->rowCount() >= 1) {
     return $stmt->fetchAll();
   } else {
     return 0;
   }
}

// Récupère les notes d'un étudiant pour une matière et un enseignant
function getScoreById($student_id, $teacher_id, $subject_id, $conn){
   $sql = "SELECT * FROM notes
           WHERE student_id = ? AND subject_id = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$student_id, $subject_id]);

   if ($stmt->rowCount() == 1) {
     return $stmt->fetch();
   } else {
     return 0;
   }
}

function getAllScoresWithDetails($conn) {
  $sql = "SELECT s.id AS student_id, s.full_name AS student_name, sub.id AS subject_id, sub.name AS subject_name, n.qcm, n.grade, n.participation
          FROM students s
          JOIN notes n ON s.id = n.student_id
          JOIN subjects sub ON n.subject_id = sub.id";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassGrades($class_id, $conn) {
  $sql = "SELECT * FROM notes WHERE class_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$class_id]);
  $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Retourne les résultats sous forme d'un tableau associatif structuré
  $formatted_grades = [];
  foreach ($grades as $grade) {
      $formatted_grades[$grade['student_id']][$grade['subject_id']] = [
          'qcm' => $grade['qcm'],
          'grade' => $grade['grade'],
          'participation' => $grade['participation']
      ];
  }

  return $formatted_grades;
}
