<?php
    include '../includes/db.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $courseId = $_POST['courseId'];
        $examDate = $_POST['examDate'];
        $examType = $_POST['examType'];
        
        $sql = "INSERT INTO Exams (courseID, ExamDate, ExamType) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param('iss', $courseId, $examDate, $examType);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Exam scheduled successfully', 'examId'=> $stmt->insert_id]);
        }

        else {
            echo json_encode(['error' => 'Failed to schedule exam !!!']);
        }
    }
?>