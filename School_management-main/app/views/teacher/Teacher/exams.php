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
    // Récupération des données du professeur
    $teacher = getTeacherById($teacher_id, $conn);

    // Traitement du formulaire d'ajout
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $date = $_POST['date'];
        $subject_id = (int)$_POST['subject_id'];

        // Vérification de l'appartenance de la matière via teacher_subject
        $check = $conn->prepare("
            SELECT * FROM teacher_subject 
            WHERE subject_id = :subject_id AND teacher_id = :teacher_id
        ");
        $check->execute([
            ':subject_id' => $subject_id,
            ':teacher_id' => $teacher_id
        ]);

        if ($check->rowCount() > 0) {
            // Insertion de l'examen
            $stmt = $conn->prepare("
                INSERT INTO exam (name, date, subject_id, teacher_id)
                VALUES (:name, :date, :subject_id, :teacher_id)
            ");
            $stmt->execute([
                ':name' => $name,
                ':date' => $date,
                ':subject_id' => $subject_id,
                ':teacher_id' => $teacher_id
            ]);
            $success = "Examen ajouté avec succès !";
        } else {
            $error = "Erreur : Sélection de matière invalide";
        }
    }

    // Récupération des matières associées via teacher_subject
    $subjects = $conn->prepare("
        SELECT s.* FROM subjects s
        INNER JOIN teacher_subject ts ON s.id = ts.subject_id
        WHERE ts.teacher_id = :teacher_id
    ");
    $subjects->execute([':teacher_id' => $teacher_id]);
    $allSubjects = $subjects->fetchAll(PDO::FETCH_ASSOC);

    // Récupération des examens existants
    $exams = $conn->prepare("
        SELECT exam.*, subjects.name as subject_name 
        FROM exam 
        JOIN subjects ON exam.subject_id = subjects.id 
        WHERE exam.teacher_id = :teacher_id 
        ORDER BY exam.date DESC
    ");
    $exams->execute([':teacher_id' => $teacher_id]);
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
    <title>Gestion des examens (Professeur #1)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables globales */
        :root {
            --primary-color: #04207b;
            --primary-light: #4a7eb5;
            --secondary-color: #f4f7fa;
            --accent-color: #ff6b6b;
            --text-color: #333;
            --text-light: #666;
            --gray-light: #f1f3f5;
            --gray-medium: #e9ecef;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --error-color: #dc3545;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }

        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            background-color: #f9fafc;
        }

        /* Container principal */
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Titre de la page */
        h2 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--gray-medium);
        }

        h3 {
            font-size: 1.3rem;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        p {
            margin-bottom: 0.5rem;
        }

        hr {
            border: none;
            border-top: 1px solid var(--border-color);
            margin: 2rem 0;
        }

        /* Alerte */
        .alert {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            border-left: 4px solid transparent;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: var(--success-color);
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: var(--error-color);
        }

        /* Formulaire */
        form {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        input[type="text"],
        input[type="date"],
        select {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-color);
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: var(--transition);
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus {
            border-color: var(--primary-light);
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(58, 110, 165, 0.25);
        }

        button[type="submit"] {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 4px;
            transition: var(--transition);
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-light);
            border-color: var(--primary-light);
        }

        /* Carte d'examen */
        .exam-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: var(--transition);
        }

        .exam-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        }

        .exam-card h3 {
            color: var(--primary-color);
        }

        .exam-card p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            form, .exam-card {
                padding: 1rem;
            }
            
            button[type="submit"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include "inc/navbar.php"; ?>
<div class="container">
    <h2>Planification des examens - Professeur #1</h2>

    <?php if (isset($success)): ?>
        <div class="alert success"><?= $success ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="name" placeholder="Nom de l'examen" required>
        </div>

        <div class="form-group">
            <input type="date" name="date" required>
        </div>

        <div class="form-group">
            <select name="subject_id" required>
                <option value="">Choisir la matière</option>
                <?php foreach ($allSubjects as $subject): ?>
                    <option value="<?= $subject['id'] ?>">
                        <?= htmlspecialchars($subject['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit">Enregistrer</button>
    </form>

    <hr>

    <h2>Examens planifiés</h2>

    <?php if (count($allExams) > 0): ?>
        <?php foreach ($allExams as $exam): ?>
            <div class="exam-card">
                <h3><?= htmlspecialchars($exam['name']) ?></h3>
                <p>Matière : <?= htmlspecialchars($exam['subject_name']) ?></p>
                <p>Date : <?= date('d/m/Y', strtotime($exam['date'])) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun examen planifié pour le moment.</p>
    <?php endif; ?>
</div>
</body>
</html>