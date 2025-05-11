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
    
    // Récupération de l'ID de l'étudiant depuis la session
    $student_id = $_SESSION['student_id'];
    
    // Récupération des informations de l'étudiant
    $stmt_student = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt_student->execute([$student_id]);
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        die("Étudiant non trouvé");
    }
    
    // Récupération de la classe de l'étudiant
    $stmt_class = $pdo->prepare("SELECT c.* FROM classes c 
                                JOIN student_class sc ON c.id = sc.class_id 
                                WHERE sc.student_id = ?");
    $stmt_class->execute([$student_id]);
    $class = $stmt_class->fetch(PDO::FETCH_ASSOC);
    
    if (!$class) {
        die("Classe non trouvée pour cet étudiant");
    }
    
    // Récupération des matières de la classe de l'étudiant
    $stmt_subjects = $pdo->prepare("SELECT s.* FROM subjects s
                                   JOIN teacher_subject ts ON s.id = ts.subject_id
                                   JOIN teacher_class tc ON ts.teacher_id = tc.teacher_id
                                   WHERE tc.class_id = ?");
    $stmt_subjects->execute([$class['id']]);
    $subjects = $stmt_subjects->fetchAll(PDO::FETCH_ASSOC);
    
    // Tableau pour stocker les notes par matière
    $grades_by_subject = [];
    
    // Pour chaque matière, récupérer les notes de l'étudiant
    foreach ($subjects as $subject) {
        $sql = "SELECT 
                    n.id,
                    n.grade,
                    n.qcm,
                    n.participation,
                    n.class_id,
                    s.name AS subject_name,
                    s.id AS subject_id,
                    e.date AS exam_date,
                    e.name AS exam_name,
                    c.name AS class_name,
                    t.full_name AS teacher_name
                FROM notes n
                JOIN subjects s ON n.subject_id = s.id
                LEFT JOIN exam e ON n.exam_id = e.id
                LEFT JOIN classes c ON n.class_id = c.id
                LEFT JOIN teachers t ON e.teacher_id = t.id
                WHERE n.student_id = ? AND n.subject_id = ?
                ORDER BY e.date DESC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $subject['id']]);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Stocker les notes pour cette matière
        $grades_by_subject[$subject['id']] = [
            'subject_info' => $subject,
            'grades' => $grades
        ];
    }
    
    // Préparation pour le calcul de la moyenne générale comme moyenne des moyennes des matières
    $subject_averages = [];
    $global_average = 0;

} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction de calcul d'appréciation
function getAppreciation($grade) {
    if ($grade >= 16) return 'Excellent';
    elseif ($grade >= 14) return 'Très bien';
    elseif ($grade >= 12) return 'Bien';
    elseif ($grade >= 10) return 'Satisfaisant';
    else return 'À améliorer';
}

// Fonction qui retourne la couleur du badge selon la note
function getBadgeColor($grade) {
    if ($grade >= 16) return 'bg-success';
    elseif ($grade >= 14) return 'bg-primary';
    elseif ($grade >= 12) return 'bg-info';
    elseif ($grade >= 10) return 'bg-secondary';
    else return 'bg-warning';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de notes - <?= $student ? htmlspecialchars($student['full_name']) : 'Étudiant' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        .grade-card {
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .grade-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .subject-title {
            background-color: #e9ecef;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            margin-bottom: 0;
        }
        .subject-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .global-stats {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            border-radius: 10px;
            padding: 20px;
        }
        .stats-card {
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: white;
            margin-bottom: 15px;
        }
        .no-grades {
            padding: 20px;
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php 
        include "inc/navbar.php";
    ?>
    <div class="container py-4" style="margin-top: 70px;">
        <!-- Entête avec information de l'étudiant -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Relevé de notes</h1>
                <?php if ($student): ?>
                    <p class="lead"><?= htmlspecialchars($student['full_name']) ?> | <?= htmlspecialchars($student['email']) ?></p>
                    <?php if ($class): ?>
                        <p class="text-muted">Classe: <?= htmlspecialchars($class['name']) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php 
            // Calcul de la moyenne générale comme moyenne des moyennes des matières
            $total_average = 0;
            $subject_count = 0;
            
            // Calculer la moyenne de chaque matière et l'ajouter au total
            foreach ($grades_by_subject as $subject_id => $data) {
                if (!empty($data['grades'])) {
                    // Calcul de la moyenne par matière
                    $subject_sum = 0;
                    $entry_count = 0;
                    
                    foreach ($data['grades'] as $grade) {
                        $entry_sum = 0;
                        $entry_types = 0;
                        
                        if ($grade['grade'] !== null) {
                            $entry_sum += $grade['grade'];
                            $entry_types++;
                        }
                        
                        if ($grade['qcm'] !== null) {
                            $entry_sum += $grade['qcm'];
                            $entry_types++;
                        }
                        
                        if ($grade['participation'] !== null) {
                            $entry_sum += $grade['participation'];
                            $entry_types++;
                        }
                        
                        if ($entry_types > 0) {
                            $subject_sum += $entry_sum / $entry_types;
                            $entry_count++;
                        }
                    }
                    
                    if ($entry_count > 0) {
                        $subject_average = $subject_sum / $entry_count;
                        $total_average += $subject_average;
                        $subject_count++;
                    }
                }
            }
            
            // Calcul de la moyenne générale
            $global_average = $subject_count > 0 ? $total_average / $subject_count : 0;
            ?>
            
            <?php if ($subject_count > 0): ?>
                <div class="global-stats">
                    <h4 class="mb-0">Moyenne générale</h4>
                    <h2 class="display-4 mb-0"><?= number_format($global_average, 2) ?>/20</h2>
                    <span class="badge <?= getBadgeColor($global_average) ?>">
                        <?= getAppreciation($global_average) ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

    <!-- Affichage des matières et notes -->
    <div class="row">
        <?php if (empty($grades_by_subject)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune matière ou note n'est disponible pour le moment.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($grades_by_subject as $subject_id => $data): ?>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header subject-title">
                            <h3 class="mb-0">
                                <i class="bi bi-book subject-icon"></i>
                                <?= htmlspecialchars($data['subject_info']['name']) ?>
                            </h3>
                            <?php if (!empty($data['subject_info']['description'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($data['subject_info']['description']) ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body">
                            <?php if (empty($data['grades'])): ?>
                                <div class="no-grades">
                                    Aucune note disponible pour cette matière
                                </div>
                            <?php else: ?>
                                <?php 
                                // Calcul de la moyenne par matière en prenant la moyenne des 3 types de notes (examen, QCM, participation)
                                $subject_sum = 0;
                                $subject_count = 0;
                                
                                foreach ($data['grades'] as $grade) {
                                    $entry_sum = 0;
                                    $entry_count = 0;
                                    
                                    // On ajoute chaque type de note disponible (examen, QCM, participation)
                                    if ($grade['grade'] !== null) {
                                        $entry_sum += $grade['grade'];
                                        $entry_count++;
                                    }
                                    
                                    if ($grade['qcm'] !== null) {
                                        $entry_sum += $grade['qcm'];
                                        $entry_count++;
                                    }
                                    
                                    if ($grade['participation'] !== null) {
                                        $entry_sum += $grade['participation'];
                                        $entry_count++;
                                    }
                                    
                                    // On calcule la moyenne pour cette entrée si des notes sont disponibles
                                    if ($entry_count > 0) {
                                        $subject_sum += $entry_sum / $entry_count;
                                        $subject_count++;
                                    }
                                }
                                
                                // Calcul de la moyenne de la matière, évitant la division par zéro
                                $subject_average = $subject_count > 0 ? $subject_sum / $subject_count : 0;
                                ?>
                                
                                <div class="stats-card mb-3">
                                    <div class="d-flex justify-content-between">
                                        <h5>Moyenne de la matière</h5>
                                        <h5><?= number_format($subject_average, 2) ?>/20</h5>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar <?= getBadgeColor($subject_average) ?>" 
                                            role="progressbar" 
                                            style="width: <?= $subject_average*5 ?>%" 
                                            aria-valuenow="<?= $subject_average ?>" 
                                            aria-valuemin="0" 
                                            aria-valuemax="20">
                                        </div>
                                    </div>
                                    <div class="text-end mt-1">
                                        <span class="badge <?= getBadgeColor($subject_average) ?>">
                                            <?= getAppreciation($subject_average) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php foreach ($data['grades'] as $grade): ?>
                                    <div class="grade-card p-3">
                                        <?php if (!empty($grade['exam_name'])): ?>
                                            <h5><?= htmlspecialchars($grade['exam_name']) ?></h5>
                                        <?php else: ?>
                                            <h5>Évaluation</h5>
                                        <?php endif; ?>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>Note d'examen:</strong> 
                                                    <span class="badge <?= getBadgeColor($grade['grade']) ?>">
                                                        <?= number_format($grade['grade'], 2) ?>/20
                                                    </span>
                                                </div>
                                                <?php if ($grade['exam_date']): ?>
                                                    <div class="mb-2">
                                                        <strong>Date:</strong> 
                                                        <?= date('d/m/Y', strtotime($grade['exam_date'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($grade['teacher_name'])): ?>
                                                    <div class="mb-2">
                                                        <strong>Professeur:</strong> 
                                                        <?= htmlspecialchars($grade['teacher_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?php if ($grade['qcm'] !== null): ?>
                                                    <div class="mb-2">
                                                        <strong>QCM:</strong> <?= $grade['qcm'] ?>/20
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($grade['participation'] !== null): ?>
                                                    <div class="mb-2">
                                                        <strong>Participation:</strong> <?= $grade['participation'] ?>/20
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($grade['class_name'])): ?>
                                                    <div class="mb-2">
                                                        <strong>Classe:</strong> <?= htmlspecialchars($grade['class_name']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2">
                                            <strong>Appréciation:</strong> 
                                            <span class="badge <?= getBadgeColor($grade['grade']) ?>">
                                                <?= getAppreciation($grade['grade']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

</body>
</html>