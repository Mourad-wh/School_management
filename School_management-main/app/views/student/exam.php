<?php
// Connexion à la base de données
$host = "127.0.0.1";
$dbname = "school_db";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Etudiant') {
        header("Location: ../../LOG/login.php");
        exit;
    }

    $student_id = $_SESSION['student_id'];

// Requête pour récupérer les informations de l'étudiant
$student_query = "SELECT full_name FROM students WHERE id = ?";
$student_stmt = $db->prepare($student_query);
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

// Handle case when student not found
if (!$student) {
    die("Étudiant non trouvé");
}

// Requête pour récupérer les examens de l'étudiant - FIXED THE JOIN CONDITION
$query = "
    SELECT e.*, s.name as subject_name, t.full_name as teacher_name, 
           cls.name as class_name, sess.start_time, sess.end_time
    FROM exam e
    JOIN subjects s ON e.subject_id = s.id
    JOIN teachers t ON e.teacher_id = t.id
    JOIN student_class sc ON sc.student_id = ?
    JOIN classes cls ON sc.class_id = cls.id
    LEFT JOIN session sess ON sess.exam_id = e.id
    WHERE e.class_id = cls.id
    ORDER BY e.date ASC
";

$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <title>Mes Examens</title>
    <style>
        :root {
            --primary-color: #1e88e5;
            --primary-dark: #1565c0;
            --primary-light: #bbdefb;
            --secondary-color: #0d47a1;
            --accent-color: #29b6f6;
            --text-primary: #37474f;
            --text-secondary: #546e7a;
            --text-light: #78909c;
            --background-color: #f5f9ff;
            --card-color: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(7, 41, 103, 0.1);
            --hover-shadow: 0 8px 30px rgba(7, 41, 103, 0.2);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }
        
        .page-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 2.5rem;
            letter-spacing: 0.5px;
        }
        
        .page-header p {
            margin: 10px 0 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .student-info {
            background-color: var(--card-color);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .student-info .student-details {
            display: flex;
            align-items: center;
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: var(--primary-dark);
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .student-info h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .student-info p {
            margin: 5px 0 0;
            color: var(--text-secondary);
        }
        
        .student-info .student-id {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .exams-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .exams-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        
        .exam-card {
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
        }
        
        .exam-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--hover-shadow);
        }
        
        .exam-card-header {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 15px 20px;
            position: relative;
        }
        
        .exam-card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .exam-card-header .date-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            backdrop-filter: blur(5px);
        }
        
        .exam-card-body {
            padding: 20px;
        }
        
        .exam-detail {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .exam-detail:last-child {
            margin-bottom: 0;
        }
        
        .exam-detail i {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary-dark);
            border-radius: 50%;
            margin-right: 15px;
            font-size: 0.8rem;
        }
        
        .exam-detail-label {
            color: var(--text-light);
            width: 80px;
            font-size: 0.85rem;
        }
        
        .exam-detail-value {
            font-weight: 500;
            color: var(--text-primary);
            flex-grow: 1;
        }
        
        .no-exams {
            text-align: center;
            padding: 60px 0;
            background-color: var(--card-color);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .no-exams i {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 20px;
            display: block;
        }
        
        .no-exams p {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .exams-grid {
                grid-template-columns: 1fr;
            }
            
            .student-info {
                flex-direction: column;
                text-align: center;
            }
            
            .student-info .student-details {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .student-avatar {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <?php include "inc/navbar.php"; ?>
    
    <div class="container" style="margin-top: 90px;">
        <div class="page-header">
            <h1>Mes Examens à Venir</h1>
            <p>Consultez le calendrier de vos prochains examens</p>
        </div>
        
        <div class="student-info">
            <div class="student-details">
                <div class="student-avatar">
                    <?= strtoupper(substr($student['full_name'] ?? 'E', 0, 1)) ?>
                </div>
                <div>
                    <h2><?= htmlspecialchars($student['full_name']) ?></h2>
                    <p>Étudiant</p>
                </div>
            </div>
            <div class="student-id">
                <i class="fas fa-id-card"></i> ID: <?= $student_id ?>
            </div>
        </div>
        
        <div class="exams-section">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i> Calendrier des examens
            </h2>
            
            <?php if (empty($exams)): ?>
                <div class="no-exams">
                    <i class="fas fa-calendar-times"></i>
                    <p>Aucun examen prévu pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="exams-grid">
                    <?php foreach ($exams as $exam): ?>
                        <div class="exam-card">
                            <div class="exam-card-header">
                                <h3><?= htmlspecialchars($exam['name']) ?></h3>
                                <div class="date-badge">
                                    <i class="fas fa-calendar-day"></i>
                                    <?= date('d/m/Y', strtotime($exam['date'])) ?>
                                </div>
                            </div>
                            <div class="exam-card-body">
                                <div class="exam-detail">
                                    <i class="fas fa-book"></i>
                                    <span class="exam-detail-label">Matière:</span>
                                    <span class="exam-detail-value"><?= htmlspecialchars($exam['subject_name']) ?></span>
                                </div>
                                
                                <?php if ($exam['start_time']): ?>
                                <div class="exam-detail">
                                    <i class="fas fa-clock"></i>
                                    <span class="exam-detail-label">Horaire:</span>
                                    <span class="exam-detail-value">
                                        <?= date('H:i', strtotime($exam['start_time'])) ?> - 
                                        <?= date('H:i', strtotime($exam['end_time'])) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="exam-detail">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <span class="exam-detail-label">Professeur:</span>
                                    <span class="exam-detail-value"><?= htmlspecialchars($exam['teacher_name']) ?></span>
                                </div>
                                
                                <div class="exam-detail">
                                    <i class="fas fa-users"></i>
                                    <span class="exam-detail-label">Classe:</span>
                                    <span class="exam-detail-value"><?= htmlspecialchars($exam['class_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>