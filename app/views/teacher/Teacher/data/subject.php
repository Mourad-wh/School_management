<?php 

// Get Subjects by Teacher ID
function getSubjectsByTeacherId($teacher_id, $conn) {
  $sql = "SELECT s.* 
          FROM subjects s
          JOIN teacher_subject ts ON s.id = ts.subject_id
          WHERE ts.teacher_id = ?";
  
  $stmt = $conn->prepare($sql);
  $stmt->execute([$teacher_id]);

  if ($stmt->rowCount() > 0) {
      return $stmt->fetchAll();
  } else {
      return 0;
  }
}

function getAllSubjects($conn) {
  $sql = "SELECT * FROM subjects";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
 ?>