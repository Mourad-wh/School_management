<?php

// All Students 
function getAllStudents($conn){
   $sql = "SELECT * FROM students";
   $stmt = $conn->prepare($sql);
   $stmt->execute();

   if ($stmt->rowCount() >= 1) {
     $students = $stmt->fetchAll();
     return $students;
   } else {
    return 0;
   }
}

// Get Student By Id 
function getStudentById($id, $conn){
   $sql = "SELECT * FROM students
           WHERE id=?"; // Changé student_id -> id
   $stmt = $conn->prepare($sql);
   $stmt->execute([$id]);

   if ($stmt->rowCount() == 1) {
     $student = $stmt->fetch();
     return $student;
   } else {
    return 0;
   }
}

// Vérifie l'unicité de l'email
function emailIsUnique($email, $conn, $id=0){
   $sql = "SELECT email, id FROM students
           WHERE email=?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email]);
   
   if ($id == 0) {
     return $stmt->rowCount() === 0;
   } else {
     if ($stmt->rowCount() > 0) {
       $student = $stmt->fetch();
       return $student['id'] == $id;
     }
     return true;
   }
}

// Vérification du mot de passe
function studentPasswordVerify($password, $conn, $id){
   $sql = "SELECT * FROM students
           WHERE id=?"; // Changé student_id -> id
   $stmt = $conn->prepare($sql);
   $stmt->execute([$id]);

   if ($stmt->rowCount() == 1) {
     $student = $stmt->fetch();
     return password_verify($password, $student['password']);
   }
   return false;
}
