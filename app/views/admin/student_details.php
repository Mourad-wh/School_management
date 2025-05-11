<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) {
    die("Database connection failed");
}

// Validate inputs
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if (!$student_id || !$class_id) {
    die("Invalid student or class ID.");
}

// Get student name
$student_result = $conn->query("SELECT full_name FROM students WHERE id = $student_id");
$student = $student_result->fetch_assoc();

// Get subject marks for this student
$marks_query = $conn->query("
    SELECT sub.name AS subject_name, a.average
    FROM average a
    JOIN subjects sub ON a.subject_id = sub.id
    WHERE a.student_id = $student_id AND a.average IS NOT NULL
");

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Marks for <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 2rem;
        }

        h2 {
            text-align: center;
        }

        table {
            margin: 2rem auto;
            border-collapse: collapse;
            width: 70%;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 1rem;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .back-link {
            text-align: center;
            display: block;
            margin-top: 2rem;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php">Classes</a></li>
                <li><a href="teacher.php">Profs</a></li>
                <li><a href="students.php" style="color: rgb(150, 20, 255)">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 90px;">
        <h2>Marks for <?= htmlspecialchars($student['full_name']) ?></h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>Mark</th>
            </tr>
            <?php while ($row = $marks_query->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= $row['average'] ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <a href="javascript:history.back()" class="back-link">← Back to Results</a>
    </div>

</body>
</html>
