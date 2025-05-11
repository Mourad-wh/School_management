<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "school_db", 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to get all classes with student counts
function getClassesWithStudents($conn) {
    $sql = "SELECT c.id, c.name, COUNT(sc.student_id) as student_count 
            FROM classes c
            LEFT JOIN student_class sc ON sc.class_id = c.id
            GROUP BY c.id";
    $result = $conn->query($sql);
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    return $classes;
}

// Function to get students by class ID with proper JOIN
function getStudentsByClass($conn, $class_id) {
    $sql = "SELECT s.id, s.full_name 
            FROM students s
            JOIN student_class sc ON sc.student_id = s.id
            WHERE sc.class_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    return $students;
}

// Get all classes
$classes = getClassesWithStudents($conn);

// Get selected class and its students if ID is provided
$selected_class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
$selected_class = null;
$students = [];

if ($selected_class_id) {
    // Find the selected class details
    foreach ($classes as $class) {
        if ($class['id'] == $selected_class_id) {
            $selected_class = $class;
            break;
        }
    }
    
    // Get students only if class exists
    if ($selected_class) {
        $students = getStudentsByClass($conn, $selected_class_id);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Classes Management</title>
    <style>
        /* Additional styles specific to this page */
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav>
        <div class="logo-container">
            <img src="../images/logo.png" alt="School Logo">
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="classes.php" style="color: rgb(150, 20, 255)">Classes</a></li>
                <li><a href="teacher.php">Profs</a></li>
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="classes-section">
        <div class="classes-header">
            <h1 style="color: aliceblue">Classes Management</h1>
        </div>
        
        <div class="classes-container">
            <div class="classes-grid">
                <?php foreach ($classes as $class): ?>
                    <a href="classes.php?class_id=<?php echo $class['id']; ?>" class="class-card <?php echo $selected_class_id == $class['id'] ? 'active' : ''; ?>">
                        <div class="class-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="class-name"><?php echo htmlspecialchars($class['name']); ?></div>
                        <div class="class-meta">
                            Class ID: <?php echo $class['id']; ?>
                        </div>
                        <div class="student-count">
                            <i class="fas fa-users"></i>
                            <?php echo $class['student_count']; ?> Students
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($selected_class_id): ?>
                <div class="students-container">
                    <div class="students-header">
                        <h2 class="students-title">
                            Students in <?php echo htmlspecialchars($classes[array_search($selected_class_id, array_column($classes, 'id'))]['name']); ?>
                        </h2>
                        <a href="classes.php" class="back-btn">
                            <i class="fas fa-arrow-left"></i> Back to all classes
                        </a>
                    </div>
                    
                    <?php if (!empty($students)): ?>
                        <div class="students-list">
                            <?php foreach ($students as $student): ?>
                                <div class="student-card">
                                    <div class="student-avatar">
                                        <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                    </div>
                                    <div class="student-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-students">No students found in this class</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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