<?php 
session_start();

if (isset($_POST['exam1']) &&
    isset($_POST['exam2']) &&
    isset($_POST['participation']) &&
    isset($_POST['student_id']) &&
    isset($_POST['subject_id']) &&
    isset($_POST['current_year']) &&
    isset($_POST['current_semester'])
) {
    
    include '../../DB_connection.php';

    // Récupération des données
    $scores = [
        'exam1' => $_POST['exam1'],
        'exam2' => $_POST['exam2'],
        'participation' => $_POST['participation']
    ];

    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $current_year = $_POST['current_year'];
    $current_semester = $_POST['current_semester'];
    $teacher_id = 1;

    // Validation des scores
    foreach ($scores as $key => $value) {
        if (!is_numeric($value) || $value < 0 || $value > 20) {
            $em = "Note invalide pour $key (doit être entre 0 et 20)";
            header("Location: ../student-grade.php?student_id=$student_id&error=$em");
            exit;
        }
    }

    // Formatage des données pour la BDD
    $data = implode(',', array_map(function($score) {
        return "$score 20";
    }, $scores));

    // Gestion update/insert
    if (isset($_POST['student_score_id'])) {
        $sql = "UPDATE student_score SET results=? 
                WHERE student_score_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$data, $_POST['student_score_id']]);
        $sm = "Mise à jour réussie !";
    } else {
        $sql = "INSERT INTO student_score 
                (semester, year, student_id, teacher_id, subject_id, results)
                VALUES (?,?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $current_semester,
            $current_year,
            $student_id,
            $teacher_id,
            $subject_id,
            $data
        ]);
        $sm = "Enregistrement réussi !";
    }

    header("Location: ../student-grade.php?student_id=$student_id&success=$sm");
    exit;

} else {
    $em = "Champs manquants";
    header("Location: ../classes.php?error=$em");
    exit;
}
?>