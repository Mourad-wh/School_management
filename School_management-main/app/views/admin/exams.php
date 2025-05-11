<?php
// Connexion à la base de données
try {
    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion à la base de données");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Système temporairement indisponible. Veuillez réessayer plus tard.");
}

$sql = "
    SELECT 
        e.id,
        t.full_name AS teacher_name,
        c.name AS class_name,
        s.name AS subject_name,
        e.exam_type,
        e.date,
        e.hour
    FROM 
        exam e
    LEFT JOIN 
        teachers t ON e.teacher_id = t.id
    LEFT JOIN
        subjects s ON e.subject_id = s.id
    LEFT JOIN 
        classes c ON e.class_id = c.id
    ORDER BY 
        e.date, e.hour
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exams Management</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php" style="color: rgb(150, 20, 255)">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="students-section">
        <div class="students-header">
            <h1 style="color: aliceblue;">Exams Management</h1>
        </div>
        
        <div class="students-container">
            <table>
                <tr>
                    <th>Professeur</th>
                    <th>Classe</th>
                    <th>Matière</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Heure</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['teacher_name']) ?></td>
                    <td><?= htmlspecialchars($row['class_name']) ?></td>
                    <td><?= htmlspecialchars($row['subject_name']) ?></td>
                    <td><?= htmlspecialchars($row['exam_type']) ?></td>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td><?= date('H:i', strtotime($row['hour'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>
</body>
</html>