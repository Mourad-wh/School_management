<?php
include "../DB_connection.php";
include "data/class.php";
include "data/subject.php";
include "data/score.php";

// Démarrer la session et récupérer l'ID du professeur
session_start();
$teacher_id = $_SESSION['teacher_id'] ?? 0;

// Vérifier la connexion
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Récupérer l'ID de la classe
$class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT) ?? 0;
$class = $class_id ? getClassById($class_id, $conn) : null;

// Vérifier que le professeur est bien assigné à cette classe
if (!$class || !isTeacherAssignedToClass($teacher_id, $class_id, $conn)) {
    die('<div class="alert alert-danger m-5">Accès non autorisé à cette classe</div>');
}

// Récupérer les données
$students = getStudentsByClass($class_id, $conn);
$subjects = getTeacherSubjectsForClass($conn, $teacher_id, $class_id);

// Préparer les notes existantes
$existingScores = [];
$scores = getAllScoresWithDetails($conn);
foreach ($scores as $score) {
    $existingScores[$score['student_id']][$score['subject_id']] = [
        'qcm' => $score['qcm'],
        'exam' => $score['grade'],
        'participation' => $score['participation']
    ];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
    
    // Vérifier que la matière appartient bien au professeur
    $subjectValid = false;
    foreach ($subjects as $subj) {
        if ($subj['id'] == $subject_id) {
            $subjectValid = true;
            break;
        }
    }
    
    if ($subjectValid) {
        foreach ($_POST['grades'] as $student_id => $grades) {
            // Validation des notes
            $qcm = validateGrade($grades['qcm']);
            $exam = validateGrade($grades['exam']);
            $participation = validateGrade($grades['participation']);

            if ($qcm !== false || $exam !== false || $participation !== false) {
                updateOrInsertGrade($conn, $student_id, $subject_id, $exam, $qcm, $participation);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?class_id=" . $class_id . "&success=1");
        exit();
    }
}

// Fonction helper pour valider une note
function validateGrade($grade) {
    if ($grade === '' || $grade === null) {
        return null;
    }
    $validated = filter_var($grade, FILTER_VALIDATE_FLOAT, [
        'options' => ['min_range' => 0, 'max_range' => 20]
    ]);
    return $validated !== false ? $validated : null;
}


// Fonction helper pour mettre à jour ou insérer une note
function updateOrInsertGrade($conn, $student_id, $subject_id, $exam, $qcm, $participation) {
    $checkSql = "SELECT id FROM notes WHERE student_id = ? AND subject_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$student_id, $subject_id]);

    if ($checkStmt->fetch()) {
        // UPDATE
        $sql = "UPDATE notes SET grade = ?, qcm = ?, participation = ? 
                WHERE student_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $exam !== null ? $exam : null,
            $qcm !== null ? $qcm : null,
            $participation !== null ? $participation : null,
            $student_id,
            $subject_id
        ]);
    } else {
        // INSERT
        $sql = "INSERT INTO notes (student_id, subject_id, grade, qcm, participation)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $student_id,
            $subject_id,
            $exam !== null ? $exam : null,
            $qcm !== null ? $qcm : null,
            $participation !== null ? $participation : null
        ]);
    }
}




// Affichage
$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT) === 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Notes - <?= htmlspecialchars($class['name'] ?? 'Classe') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .grade-input { max-width: 80px; }
        .table-responsive { overflow-x: auto; }
        .grade-cell { text-align: center; vertical-align: middle; }
        .container.custom-margin-top { margin-top: 100px; }
    </style>
</head>
<body>
    <div class="container custom-margin-top">
        <?php include "inc/navbar.php"; ?>
        
        <div class="container mt-5">
            <h3 class="mb-4">
                <a href="classe.php" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <i class="fas fa-edit"></i> 
                Notes - <?= htmlspecialchars($class['name']) ?>
            </h3>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Succès!</strong> Les notes ont été enregistrées.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($subjects)): ?>
                <div class="alert alert-warning">
                    Vous n'enseignez aucune matière dans cette classe.
                </div>
            <?php else: ?>
                <form method="post" action="" class="mt-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h4 class="m-0">Gestion des notes</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="subject_id" class="form-label">Matière</label>
                                <select id="subject_id" name="subject_id" class="form-select" required>
                                    <option value="">Sélectionner une matière</option>
                                    <?php foreach ($subjects as $subject) : ?>
                                        <option value="<?= $subject['id'] ?>">
                                            <?= htmlspecialchars($subject['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Étudiant</th>
                                            <th>QCM</th>
                                            <th>Examen</th>
                                            <th>Participation</th>
                                        </tr>
                                    </thead>
                                    <tbody id="gradesTableBody">
                                        <?php foreach ($students as $student) : ?>
                                            <tr class="student-row" data-student-id="<?= $student['id'] ?>">
                                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                                <td class="grade-cell">
                                                    <input type="number" step="0.1" min="0" max="20" 
                                                           name="grades[<?= $student['id'] ?>][qcm]" 
                                                           class="grade-input form-control">
                                                </td>
                                                <td class="grade-cell">
                                                    <input type="number" step="0.1" min="0" max="20" 
                                                           name="grades[<?= $student['id'] ?>][exam]" 
                                                           class="grade-input form-control">
                                                </td>
                                                <td class="grade-cell">
                                                    <input type="number" step="0.1" min="0" max="20" 
                                                           name="grades[<?= $student['id'] ?>][participation]" 
                                                           class="grade-input form-control">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    const existingScores = <?= json_encode($existingScores) ?>;
    
    document.getElementById('subject_id').addEventListener('change', function() {
        const subjectId = this.value;
        document.querySelectorAll('.student-row').forEach(row => {
            const studentId = row.dataset.studentId;
            const inputs = row.querySelectorAll('input');
            
            if (existingScores[studentId] && existingScores[studentId][subjectId]) {
                const scores = existingScores[studentId][subjectId];
                inputs[0].value = scores.qcm || '';
                inputs[1].value = scores.exam || '';
                inputs[2].value = scores.participation || '';
            } else {
                inputs.forEach(input => input.value = '');
            }
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
