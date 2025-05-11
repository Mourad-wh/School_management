<?php
// Database connection with enhanced error handling
try {
  $conn = new mysqli("localhost", "root", "", "school_db", 3306);
  if ($conn->connect_error) {
      throw new Exception("Database connection failed: " . $conn->connect_error);
  }
  $conn->set_charset("utf8mb4");
} catch (Exception $e) {
  error_log($e->getMessage());
  die("System error. Please try again later.");
}

// Dashboard statistics functions
function getTotalStudents($conn) {
    $sql = "SELECT COUNT(*) as total FROM students";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getTotalTeachers($conn) {
    $sql = "SELECT COUNT(*) as total FROM teachers";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getActiveClasses($conn) {
    $sql = "SELECT COUNT(DISTINCT class_id) as total FROM student_class";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getTodaysAbsences($conn) {
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) as total FROM absence WHERE date = '$today'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function getActivityIcon($type) {
    $icons = [
        'student_added' => 'fa-user-plus',
        'teacher_added' => 'fa-chalkboard-teacher',
        'attendance' => 'fa-calendar-check',
        'exam' => 'fa-clipboard-list',
        'system' => 'fa-cog'
    ];
    return $icons[$type] ?? 'fa-bell';
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
    <title>Admin Dashboard</title>
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
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <main class="dashboard-section">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1 style="color: aliceblue;">Admin Dashboard</h1>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card students">
                <div class="stat-title">Total Students</div>
                <div class="stat-value"><?php echo getTotalStudents($conn); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 12% from last year
                </div>
            </div>
            
            <div class="stat-card teachers">
                <div class="stat-title">Total Teachers</div>
                <div class="stat-value"><?php echo getTotalTeachers($conn); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> 5% from last year
                </div>
            </div>
            
            <div class="stat-card classes">
                <div class="stat-title">Active Classes</div>
                <div class="stat-value"><?php echo getActiveClasses($conn); ?></div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> 2% from last term
                </div>
            </div>
            
            <div class="stat-card absences">
                <div class="stat-title">Today's Absences</div>
                <div class="stat-value"><?php echo getTodaysAbsences($conn); ?></div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-down"></i> 8% from yesterday
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-container">
            
            <div class="chart-card">
                <div class="chart-header">
                    <h2 class="chart-title">Recent Activity</h2>
                </div>
                <div class="activity-card">
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-content">
                            <div>New student registered</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div class="activity-content">
                            <div>Teacher assignment updated</div>
                            <div class="activity-time">5 hours ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div class="activity-content">
                            <div>Absence marked for student</div>
                            <div class="activity-time">Yesterday</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="../images/logo.png" alt="LearnBridge Marrakech">
                <p>LearnBridge, votre passerelle vers l'excellence en ingénierie à Marrakech.</p>
            </div>
            <div class="footer-links">
                <a href="#top">Accueil</a>
                <a href="#presentation">L'École</a>
                <a href="#formations">Formations</a>
                <a href="#recherche">Recherche</a>
                <a href="admission.html">Admission</a>
                <a href="temoignages.html">Témoignages</a>
                <a href="faq.html">FAQ</a>


        </div>
        <div class="footer-social">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <div class="footer-contact">
                <h3><i class="fas fa-building"></i> CONTACT</h3>

                <p><strong>LearnBridge</strong><br>
                   BP 7540 Avenue Abdelkrim Khattabi<br>
                   Guéliz - Marrakech<br></p>
                   <p><i class="fas fa-phone"></i> (+212) 06 00 00 00 00</p>
                   <p><i class="fas fa-envelope"></i> learnbridge@uca.ac.ma</p>
            </div>
        </div>
        </div>
        <div class="footer-copyright">
            <p>© 2025 LearnBridge - Tous droits réservés</p>
        </div>
    </footer>

</body>
</html>