<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$sql = "
    SELECT 
        t.id,
        t.full_name,
        GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') AS subjects,
        GROUP_CONCAT(DISTINCT c.name ORDER BY c.name SEPARATOR ', ') AS classes
    FROM 
        teachers t
    LEFT JOIN 
        teacher_subject ts ON t.id = ts.teacher_id
    LEFT JOIN 
        subjects s ON ts.subject_id = s.id
    LEFT JOIN 
        teacher_class ct ON t.id = ct.teacher_id
    LEFT JOIN 
        classes c ON ct.class_id = c.id
    GROUP BY 
        t.id, t.full_name
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
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2&family=Fredoka+One&display=swap" rel="stylesheet">
    <title>Teachers</title>
</head>
<body>

    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php">Classes</a></li>
                <li><a href="teacher.php" style="color: rgb(150, 20, 255)">Profs</a></li>
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="teachers-section">
        <div class="teachers-header">
            <h1>Gestion des profs</h1>
        </div>
        
        <div class="teachers-container">
            <button class="add-teacher-btn" onclick="window.location.href='add_teacher.php';">
                <i class="fas fa-user-plus"></i> Ajouter un nouveau prof
            </button>
            
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Classes</th>
                    <th>Modules</th>
                    <th>Demandes</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['classes'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['subjects'] ?? 'N/A') ?></td>
                    <td>
                        <?php if (!empty($row['request_file'])): ?>
                            <a href="?file=<?= urlencode($row['request_file']) ?>" class="download-link">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        <?php else: ?>
                            <em>Pas de demande</em>
                        <?php endif; ?>
                    </td>
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