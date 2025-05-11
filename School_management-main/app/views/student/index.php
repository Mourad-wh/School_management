<?php
// Connexion à la base de données
$host = "127.0.0.1";
$dbname = "school_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérification de la session
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Etudiant') {
        header("Location: ../../LOG/login.php");
        exit;
    }

    $student_id = $_SESSION['student_id'];

    // Récupération des informations de l'étudiant
    $sql = "SELECT students.*, 
                   classes.name as class_name,
                   classes.grade as class_grade,
                   classes.id as class_id,
                   school.name as school_name
            FROM students
            JOIN student_class ON students.id = student_class.student_id
            JOIN classes ON student_class.class_id = classes.id
            JOIN school ON students.school_id = school.id
            WHERE students.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupération des examens à venir
    $exams = [];
    if ($student) {
        $examSql = "SELECT exam.*, subjects.name as subject_name 
                   FROM exam 
                   JOIN subjects ON exam.subject_id = subjects.id
                   WHERE class_id = ? 
                   AND date >= CURDATE()
                   ORDER BY date ASC";
        $examStmt = $pdo->prepare($examSql);
        $examStmt->execute([$student['class_id']]);
        $exams = $examStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Vérification des absences non justifiées
    $absenceAlert = false;
    $absenceId = null;
    if ($student) {
        $absenceSql = "SELECT id FROM absence
                      WHERE student_id = ? 
                      AND alert_sent = 1
                      AND justification_file IS NULL
                      LIMIT 1";
        $absenceStmt = $pdo->prepare($absenceSql);
        $absenceStmt->execute([$student_id]);
        $absenceAlert = $absenceStmt->fetch(PDO::FETCH_ASSOC);
        if ($absenceAlert) {
            $absenceId = $absenceAlert['id'];
        }
    }

    // Gestion de la soumission de justificatif
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_justification'])) {
        if (isset($_FILES['justification_file'])) {
            $fileContent = file_get_contents($_FILES['justification_file']['tmp_name']);
            
            $updateSql = "UPDATE absence 
                          SET justification_file = ?, 
                              is_validated = 0,
                              alert_sent = 0
                          WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$fileContent, $absenceId]);
            
            $absenceAlert = false;
            header("Refresh:0");
        }
    }

    // Récupération des moyennes
    $averages = [];
    if ($student) {
        $averageSql = "SELECT a.average, s.name as subject_name
                      FROM average a
                      JOIN subjects s ON a.subject_id = s.id
                      WHERE a.student_id = ?";
        $averageStmt = $pdo->prepare($averageSql);
        $averageStmt->execute([$student_id]);
        $averages = $averageStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Étudiant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --primary-light: #4895ef;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #ef233c;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f8fafc;
            color: var(--dark);
            line-height: 1.6;
            padding-top: 80px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: var(--transition);
            overflow: hidden;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 1.25rem;
            font-weight: 600;
            border-bottom: none;
            position: relative;
            overflow: hidden;
        }

        .card-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.2) 0%,
                rgba(255, 255, 255, 0) 60%
            );
            transform: rotate(30deg);
        }

        .card-body {
            padding: 1.5rem;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin: -60px auto 20px;
            display: block;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            position: relative;
            z-index: 2;
        }

        .profile-avatar i {
            font-size: 3rem;
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--primary-dark);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }

        .info-label i {
            margin-right: 8px;
            font-size: 1rem;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark);
            padding-left: 1.75rem;
        }

        .exam-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .exam-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .exam-date {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-pill {
            padding: 0.5em 1em;
            border-radius: 50px;
            font-weight: 600;
        }

        .alert-notification {
            border-left: 4px solid var(--danger);
            background-color: rgba(239, 35, 60, 0.1);
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .alert-notification i {
            margin-right: 12px;
            font-size: 1.5rem;
            color: var(--danger);
        }

        .average-display {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 1rem 0;
            text-align: center;
        }

        .progress-thin {
            height: 8px;
            border-radius: 4px;
        }

        .subject-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .subject-card:hover {
            transform: translateY(-3px);
        }

        .subject-header {
            background: linear-gradient(to right, var(--primary), var(--primary-light));
            color: white;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }

        .subject-body {
            background-color: white;
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            body {
                padding-top: 70px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <?php include "inc/navbar.php"; ?>

    <div class="container py-4">
        <?php if ($student): ?>
            <div class="row g-4">
                <!-- Profile Card -->
                <div class="col-lg-4 animate-fade">
                    <div class="glass-card">
                        <div class="card-header text-center">
                            <h5 class="mb-0">Mon Profil
                            </h5>
                        </div>
                        <div class="card-body text-center">

                            <h4 class="mb-3"><?= htmlspecialchars($student['full_name']) ?></h4>
                            
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-envelope"></i> Email
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($student['email']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-telephone"></i> Téléphone
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($student['phone'] ?? 'Non renseigné') ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-people"></i> Classe
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($student['class_name']) ?></div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="bi bi-building"></i> École
                                    </div>
                                    <div class="info-value"><?= htmlspecialchars($student['school_name']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Notifications -->
                    <div class="glass-card animate-fade delay-1 mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
                        </div>
                        <div class="card-body">
                          
                            <?php if (!empty($exams)): ?>
                                <div class="alert-notification" style="border-left-color: var(--warning); background-color: rgba(248, 150, 30, 0.1);">
                                    <i class="bi bi-info-circle" style="color: var(--warning);"></i>
                                    <div>
                                        <strong>Examen à venir</strong>
                                        <p>Vous avez un examen de <?= htmlspecialchars($exams[0]['subject_name']) ?> prévu le <?= date('d/m/Y', strtotime($exams[0]['date'])) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                 
                    
        <?php else: ?>
            <div class="alert alert-danger animate-fade">
                <i class="bi bi-exclamation-triangle"></i> Aucune information étudiante trouvée
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>