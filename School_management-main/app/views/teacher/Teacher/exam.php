<?php
session_start();
include "../DB_connection.php";
include "data/teacher.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Professeur') {
    header("Location: ../../LOG/login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Get teacher data
    $teacher = getTeacherById($teacher_id, $conn);

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $date = $_POST['date'];
        $hour = $_POST['hour'];
        $subject_id = (int)$_POST['subject_id'];
        $class_id = (int)$_POST['class_id'];
        $exam_type = $_POST['exam_type'];

        // Check if teacher teaches this subject
        $check = $conn->prepare("SELECT * FROM teacher_subject WHERE subject_id = ? AND teacher_id = ?");
        $check->execute([$subject_id, $teacher_id]);

        if ($check->rowCount() > 0) {
            // Check for time conflict
            $conflictCheck = $conn->prepare("SELECT id FROM exam WHERE class_id = ? AND date = ? AND hour = ? LIMIT 1");
            $conflictCheck->execute([$class_id, $date, $hour]);
            
            if ($conflictCheck->rowCount() > 0) {
                $error = "Erreur : Cette classe a déjà un examen prévu à cette date et heure";
            } else {
                // Insert new exam with hour
                $stmt = $conn->prepare("
                    INSERT INTO exam (name, date, hour, subject_id, teacher_id, class_id, exam_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $date, $hour, $subject_id, $teacher_id, $class_id, $exam_type]);

                $success = "Examen créé avec succès !";
            }
        } else {
            $error = "Erreur : Sélection de matière invalide";
        }
    }

    // Get teacher's subjects
    $subjects = $conn->prepare("
        SELECT s.* FROM subjects s
        JOIN teacher_subject ts ON s.id = ts.subject_id
        WHERE ts.teacher_id = ?
    ");
    $subjects->execute([$teacher_id]);
    $allSubjects = $subjects->fetchAll(PDO::FETCH_ASSOC);

    // Get all classes
    $classes = $conn->query("SELECT id, name FROM classes ORDER BY name");
    $allClasses = $classes->fetchAll(PDO::FETCH_ASSOC);

    // Get teacher's exams
    $exams = $conn->prepare("
        SELECT e.*, s.name as subject_name, c.name as class_name 
        FROM exam e
        JOIN subjects s ON e.subject_id = s.id 
        JOIN classes c ON e.class_id = c.id
        WHERE e.teacher_id = ? 
        ORDER BY e.date DESC
    ");
    $exams->execute([$teacher_id]);
    $allExams = $exams->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des examens - <?= htmlspecialchars($teacher['full_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        /* Palette de bleus */
        --primary: #0066ff;
        --primary-dark: #0044cc;
        --primary-light: #4d94ff;
        --primary-ultralight: #e6f0ff;
        --secondary: #004080;
        --tertiary: #66b3ff;
        --accent: #00ccff;
        
        /* Neutres */
        --light: #ffffff;
        --dark: #1a1a2e;
        --gray: #6c757d;
        --light-gray: #f0f2f5;
        
        /* États */
        --success: #21d07e;
        --warning: #ffc107;
        --danger: #e63946;
        
        /* Interface */
        --border-radius: 10px;
        --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        --shadow-sm: 0 4px 12px rgba(0, 0, 0, 0.05);
        --transition: all 0.25s cubic-bezier(0.645, 0.045, 0.355, 1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Montserrat', sans-serif;
        background: linear-gradient(135deg, var(--primary-ultralight) 0%, var(--light) 100%);
        color: var(--dark);
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 2.5rem;
    }

    h1, h2, h3 {
        font-weight: 600;
    }

    h1 {
        font-size: 2.5rem;
        color: var(--primary-dark);
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
    }

    h1::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 4px;
        background: var(--accent);
        border-radius: 2px;
    }

    .container > p {
        color: var(--gray);
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    h2 {
        font-size: 1.75rem;
        color: var(--secondary);
        margin-bottom: 1.5rem;
        position: relative;
    }

    .card {
        background: var(--light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        padding: 2rem;
        margin-bottom: 2.5rem;
        transition: var(--transition);
        border-top: 5px solid var(--primary);
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 102, 255, 0.1);
    }

    .form-group {
        margin-bottom: 1.75rem;
    }

    label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 500;
        color: var(--secondary);
        font-size: 0.95rem;
    }

    input, select {
        width: 100%;
        padding: 0.9rem 1.2rem;
        border: 2px solid var(--light-gray);
        border-radius: var(--border-radius);
        font-size: 1rem;
        transition: var(--transition);
        font-family: 'Montserrat', sans-serif;
        color: var(--dark);
        background-color: var(--light);
    }

    input:focus, select:focus {
        outline: none;
        border-color: var(--tertiary);
        box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.15);
    }

    .btn {
        display: inline-block;
        background: linear-gradient(to right, var(--primary), var(--primary-dark));
        color: white;
        padding: 1rem 1.75rem;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 102, 255, 0.3);
        letter-spacing: 0.5px;
    }

    .btn:hover {
        background: linear-gradient(to right, var(--primary-dark), var(--secondary));
        transform: translateY(-3px);
        box-shadow: 0 7px 20px rgba(0, 102, 255, 0.4);
    }

    .btn-block {
        display: block;
        width: 100%;
    }

    .alert {
        padding: 1.25rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        box-shadow: var(--shadow-sm);
    }

    .alert i {
        margin-right: 1rem;
        font-size: 1.2rem;
    }

    .alert-success {
        background-color: rgba(33, 208, 126, 0.1);
        color: var(--success);
        border-left: 4px solid var(--success);
    }

    .alert-error {
        background-color: rgba(230, 57, 70, 0.1);
        color: var(--danger);
        border-left: 4px solid var(--danger);
    }

    .exam-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.75rem;
    }

    .exam-card {
        background: var(--light);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-sm);
        padding: 1.75rem;
        transition: var(--transition);
        border-left: 5px solid var(--primary);
        position: relative;
        overflow: hidden;
    }

    .exam-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, transparent 50%, rgba(0, 102, 255, 0.05) 50%);
        border-radius: 0 0 0 100px;
    }

    .exam-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 102, 255, 0.15);
    }

    .exam-card h3 {
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }

    .exam-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: var(--gray);
    }

    .exam-details {
        margin-top: 1.25rem;
    }

    .exam-details p {
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
    }

    .exam-details i {
        color: var(--primary);
        width: 20px;
        margin-right: 10px;
    }

    .badge {
        display: inline-block;
        padding: 0.35rem 0.75rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-primary {
        background-color: rgba(0, 102, 255, 0.1);
        color: var(--primary);
    }

    .badge-qcm, .badge-onsite {
        background-color: rgba(0, 204, 255, 0.1);
        color: var(--accent);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: var(--gray);
        background-color: rgba(255, 255, 255, 0.7);
        border-radius: var(--border-radius);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--tertiary);
        margin-bottom: 1.5rem;
        opacity: 0.6;
    }

    .empty-state p {
        font-size: 1.1rem;
        font-weight: 500;
    }

    /* Form layout pour écrans plus larges */
    @media (min-width: 768px) {
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 1.5rem;
        }
        
        .exam-list {
            grid-template-columns: 1fr;
        }
        
        h1 {
            font-size: 2rem;
        }
        
        h2 {
            font-size: 1.5rem;
        }
    }

    /* Fin des styles */

        .container.custom-margin-top { margin-top: 60px; }
</style>
</head>
<body>
        <div class="container custom-margin-top">
<?php include "inc/navbar.php"; ?>
<div class="container">
    <h1>Gestion des examens</h1>
    <p>Bienvenue, <?= htmlspecialchars($teacher['full_name']) ?></p>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Planifier un nouvel examen</h2>
        <form method="POST">
            <div class="form-group">
                <label for="name">Nom de l'examen</label>
                <input type="text" id="name" name="name" placeholder="Ex: Examen final de bases de données" required>
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>

            <div class="form-group">
                <label for="hour">Heure</label>
                <input type="time" id="hour" name="hour" required>
            </div>

            <div class="form-group">
                <label for="subject_id">Matière</label>
                <select id="subject_id" name="subject_id" required>
                    <option value="">Sélectionnez une matière</option>
                    <?php foreach ($allSubjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>">
                            <?= htmlspecialchars($subject['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="class_id">Classe</label>
                <select id="class_id" name="class_id" required>
                    <option value="">Sélectionnez une classe</option>
                    <?php foreach ($allClasses as $class): ?>
                        <option value="<?= $class['id'] ?>">
                            <?= htmlspecialchars($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="exam_type">Type d'examen</label>
                <select id="exam_type" name="exam_type" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="qcm">QCM</option>
                    <option value="onsite">Présentiel</option>
                </select>
            </div>

            <button type="submit" class="btn btn-block">Planifier l'examen</button>
        </form>
    </div>

    <div class="card">
        <h2>Mes examens planifiés</h2>
        
        <?php if (count($allExams) > 0): ?>
            <div class="exam-list">
                <?php foreach ($allExams as $exam): ?>
                    <div class="exam-card">
                        <div class="exam-meta">
                            <span class="badge badge-primary"><?= strtoupper($exam['exam_type']) ?></span>
                            <span><?= date('d/m/Y', strtotime($exam['date'])) ?></span>
                        </div>
                        <h3><?= htmlspecialchars($exam['name']) ?></h3>
                        
                        <div class="exam-details">
                            <p><i class="fas fa-book"></i> <?= htmlspecialchars($exam['subject_name']) ?></p>
                            <p><i class="fas fa-users"></i> <?= htmlspecialchars($exam['class_name']) ?></p>
                            <p><i class="fas fa-clock"></i> <?= date('H:i', strtotime($exam['hour'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="far fa-calendar-alt"></i>
                <p>Aucun examen planifié pour le moment</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Vérification côté client pour un feedback immédiat
    function checkConflictBeforeSubmit() {
        const date = document.querySelector('input[name="date"]').value;
        const hour = document.querySelector('input[name="hour"]').value;
        const classId = document.querySelector('select[name="class_id"]').value;
        const exams = <?= json_encode($allExams) ?>;
        
        const hasConflict = exams.some(exam => {
            return exam.class_id == classId && exam.date === date && exam.hour === hour;
        });
        
        if (hasConflict) {
            alert("Cette classe a déjà un examen prévu à cette date et heure !");
            return false;
        }
        return true;
    }

    // Attacher la fonction de vérification au formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!checkConflictBeforeSubmit()) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>