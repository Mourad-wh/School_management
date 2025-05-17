<?php
// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "school_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection error");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}

$sql = "
    SELECT 
        s.id,
        s.full_name,
        s.email,
        s.phone,
        sc.name AS school_name,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS classes,
        ROUND(AVG(n.grade), 2) AS average_mark
    FROM 
        students s
    LEFT JOIN 
        school sc ON s.school_id = sc.id
    LEFT JOIN 
        student_class ct ON s.id = ct.student_id
    LEFT JOIN 
        classes c ON ct.class_id = c.id
    LEFT JOIN 
        notes n ON s.id = n.student_id
    GROUP BY 
        s.id, s.full_name, s.email, s.phone, sc.name
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Students Management</title>
</head>
<body>

    <!-- Navigation -->
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
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="students-section">
        <div class="students-header">
            <h1 style="color: aliceblue;">Students Management</h1>
        </div>
        
        <div class="students-container">
            <button class="add-student-btn" onclick="window.location.href='add_student.php';">
                <i class="fas fa-user-plus"></i> Ajouter un étudiant
            </button>
            
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ecole</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['classes'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['school_name']) ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../images/logo.png" alt="School Logo">
                <p>Donner du pouvoir aux futures générations grâce à une éducation de qualité</p>
            </div>
            <div class="footer-links">
                <h4>Liens rapides</h4>
                <a href="#">Home</a>
                <a href="#">About</a>
                <a href="#">Admissions</a>
                <a href="#">Contact</a>
            </div>
            <div class="footer-contact">
                <h4>Contacter Nous</h4>
                <p><i class="fas fa-map-marker-alt"></i> 123 School Street, City</p>
                <p><i class="fas fa-phone"></i> +123 456 7890</p>
                <p><i class="fas fa-envelope"></i> info@school.edu</p>
            </div>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        <div class="footer-copyright">
            <p>&copy; <?= date('Y') ?> School Name. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
