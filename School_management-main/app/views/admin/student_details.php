<?php
// Connexion à la base de données en PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=school_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion à la base de données : " . $e->getMessage());
}

// Validation des entrées
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

if (!$student_id || !$class_id) {
    die("ID étudiant ou ID classe invalide.");
}

// Récupération des informations de l'étudiant
$student_stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Étudiant non trouvé");
}

// Récupération des informations de l'étudiant
$student_stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Étudiant non trouvé");
}

// Récupération de la classe de l'étudiant
$class_stmt = $pdo->prepare("SELECT c.* FROM classes c 
                            JOIN student_class sc ON c.id = sc.class_id 
                            WHERE sc.student_id = ?");
$class_stmt->execute([$student_id]);
$class = $class_stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des matières de la classe de l'étudiant
$subjects_stmt = $pdo->prepare("SELECT s.* FROM subjects s
                               JOIN teacher_subject ts ON s.id = ts.subject_id
                               JOIN teacher_class tc ON ts.teacher_id = tc.teacher_id
                               WHERE tc.class_id = ?");
$subjects_stmt->execute([$class['id']]);
$subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des notes de l'étudiant avec les informations des examens
$notes_stmt = $pdo->prepare("
    SELECT 
        n.id,
        n.grade,
        n.qcm,
        n.participation,
        n.subject_id,
        s.name AS subject_name,
        e.name AS exam_name,
        e.date AS exam_date,
        t.full_name AS teacher_name
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    LEFT JOIN exam e ON n.exam_id = e.id
    LEFT JOIN teachers t ON e.teacher_id = t.id
    WHERE n.student_id = ?
    ORDER BY s.name, e.date DESC
");
$notes_stmt->execute([$student_id]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation des notes par matière
$notes_by_subject = [];

foreach ($subjects as $subject) {
    $notes_by_subject[$subject['id']] = [
        'subject_name' => $subject['name'],
        'description' => $subject['description'],
        'notes' => [],
        'total' => 0,
        'count' => 0,
        'average' => 0,
        'has_notes' => false
    ];
}

foreach ($notes as $note) {
    $subject_id = $note['subject_id'];
    
    // Calcul de la moyenne pour cette note (moyenne des 3 composantes)
    $grade = $note['grade'] ?? 0;
    $qcm = $note['qcm'] ?? 0;
    $participation = $note['participation'] ?? 0;
    
    $note_average = ($grade + $qcm + $participation) / 3;
    $note['note_average'] = $note_average;
    
    $notes_by_subject[$subject_id]['notes'][] = $note;
    $notes_by_subject[$subject_id]['total'] += $note_average;
    $notes_by_subject[$subject_id]['count']++;
    $notes_by_subject[$subject_id]['has_notes'] = true;
}

// Calcul des moyennes par matière
foreach ($notes_by_subject as $subject_id => &$subject_data) {
    if ($subject_data['has_notes']) {
        $subject_data['average'] = $subject_data['total'] / $subject_data['count'];
    }
}

// Calcul de la moyenne générale
$general_total = 0;
$general_count = 0;

foreach ($notes_by_subject as $subject_data) {
    if ($subject_data['has_notes']) {
        $general_total += $subject_data['average'];
        $general_count++;
    }
}

$general_average = $general_count > 0 ? $general_total / $general_count : 0;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Marks for <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: white;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: #f5f8fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 100px auto 40px;
            background-color: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        h1, h2 {
            color: #102b72;
            font-weight: 600;
        }

        .student-info {
            background-color: rgba(0, 31, 116, 0.2);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .subject-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .subject-header {
            background-color: #001f74;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
        }

        .subject-body {
            padding: 15px;
        }

        .note-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }

        .note-item:last-child {
            border-bottom: none;
        }

        .general-average {
            background-color: #001f74;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            margin-top: 30px;
        }

        .valid {
            color: #2ecc71;
            font-weight: bold;
        }

        .not-valid {
            color: #e74c3c;
            font-weight: bold;
        }

        .no-notes {
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            margin-bottom: 15px;
            font-style: italic;
            color: #6c757d;
            text-align: center;
        }

        .average-display {
            font-size: 1.1em;
            font-weight: bold;
            color: #001f74;
        }

        .exam-info {
            font-size: 0.9em;
            color: #6c757d;
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
                <li><a href="students.php">Etudiants</a></li>
                <li><a href="admin_absences.php">L'absence</a></li>
                <li><a href="exams.php">Exams</a></li>
                <li><a href="notes.php" style="color: rgb(150, 20, 255)">Notes</a></li>
                <li><a href="logout.php" class="login-btn">Déconnexion<i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Bulletin de Notes</h1>
        
        <div class="student-info">
            <h2><?= htmlspecialchars($student['full_name']) ?></h2>
            <p>Email: <?= htmlspecialchars($student['email']) ?></p>
            <?php if ($class): ?>
                <p>Classe: <?= htmlspecialchars($class['name']) ?></p>
            <?php endif; ?>
        </div>
        
        <h2>Détail des notes par matière</h2>
        
        <?php foreach ($notes_by_subject as $subject_id => $subject_data): ?>
            <div class="subject-card">
                <div class="subject-header">
                    <?= htmlspecialchars($subject_data['subject_name']) ?>
                    <span class="float-end average-display">
                        Moyenne: <?= $subject_data['has_notes'] ? number_format($subject_data['average'], 2) : 'N/A' ?>
                    </span>
                </div>
                
                <div class="subject-body">
                    <?php if (!empty($subject_data['description'])): ?>
                        <p><?= htmlspecialchars($subject_data['description']) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($subject_data['has_notes']): ?>
                        <?php foreach ($subject_data['notes'] as $note): ?>
                            <div class="note-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Note d'examen:</strong> <?= number_format($note['grade'], 2) ?>/20<br>
                                        <strong>QCM:</strong> <?= number_format($note['qcm'] ?? 0, 2) ?>/20<br>
                                        <strong>Participation:</strong> <?= number_format($note['participation'] ?? 0, 2) ?>/20
                                    </div>
                                    <div class="col-md-6">
                                        <div class="exam-info">
                                            <?php if (!empty($note['exam_name'])): ?>
                                                <strong>Examen:</strong> <?= htmlspecialchars($note['exam_name']) ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($note['exam_date'])): ?>
                                                <strong>Date:</strong> <?= date('d/m/Y', strtotime($note['exam_date'])) ?><br>
                                            <?php endif; ?>
                                            <?php if (!empty($note['teacher_name'])): ?>
                                                <strong>Professeur:</strong> <?= htmlspecialchars($note['teacher_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <strong>Moyenne:</strong> 
                                            <span class="badge <?= $note['note_average'] >= 12 ? 'bg-success' : 'bg-warning' ?>">
                                                <?= number_format($note['note_average'], 2) ?>/20
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-notes">Aucune note disponible pour cette matière</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="general-average">
            Moyenne Générale: <?= number_format($general_average, 2) ?>/20
            <span class="badge <?= $general_average >= 12 ? 'bg-success' : 'bg-warning' ?>">
                <?= $general_average >= 12 ? 'Validé' : 'Non validé' ?>
            </span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
