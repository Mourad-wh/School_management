<?php
    include '../includes/db.php';

    $sql = "SELECT * FROM Attendance";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $attendance = [];

        while ($row = $result->fetch_assoc()) {
            $attendance[] = $row;
        }
        echo json_encode($attendance);
    }

    else {
        echo json_encode(['message'=> 'No Attendance records found.']);
    }
?>