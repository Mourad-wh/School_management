<?php
include "../DB_connection.php";
include "data/class.php";
include "data/subject.php";
include "data/score.php";

// Debug: Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Récupération de la classe
$class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT) ?? 0;

// Debug: Output the received class_id
error_log("Received class_id: " . $class_id);

$class = $class_id ? getClassById($class_id, $conn) : null;

// Debug: Output the class data
error_log("Class data: " . print_r($class, true));

if (!$class) {
    // More detailed error message
    die('<div class="alert alert-danger m-5">Classe introuvable (ID: '.$class_id.')</div>');
}

// Récupération des étudiants et des matières
$students = getStudentsByClass($class_id, $conn);
$subjects = getAllSubjects($conn);

// Préparation d'un tableau pour stocker toutes les notes existantes
$existingScores = [];
$scores = getAllScoresWithDetails($conn);

// Organisation des notes existantes par étudiant et par matière
foreach ($scores as $score) {
    $studentId = $score['student_id'];
    $subjectId = $score['subject_id'];
    
    if (!isset($existingScores[$studentId])) {
        $existingScores[$studentId] = [];
    }
    
    $existingScores[$studentId][$subjectId] = [
        'qcm' => $score['qcm'],
        'exam' => $score['grade'],
        'participation' => $score['participation']
    ];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = filter_input(INPUT_POST, 'subject_id', FILTER_VALIDATE_INT);
    
    if ($subject_id) {
        foreach ($_POST['grades'] as $student_id => $grades) {
            $qcm = filter_var($grades['qcm'], FILTER_VALIDATE_FLOAT, [
                'options' => ['min_range' => 0, 'max_range' => 20]
            ]);
            $exam = filter_var($grades['exam'], FILTER_VALIDATE_FLOAT, [
                'options' => ['min_range' => 0, 'max_range' => 20]
            ]);
            $participation = filter_var($grades['participation'], FILTER_VALIDATE_FLOAT, [
                'options' => ['min_range' => 0, 'max_range' => 20]
            ]);

            // Vérifier au moins une valeur valide
            if ($qcm !== false || $exam !== false || $participation !== false) {
                // Assigner des valeurs par défaut à NULL si non valides
                $qcm = $qcm === false ? NULL : $qcm;
                $exam = $exam === false ? NULL : $exam;
                $participation = $participation === false ? NULL : $participation;
                // Vérifier si une note existe déjà pour cet étudiant et cette matière
                $checkSql = "SELECT id FROM notes WHERE student_id = ? AND subject_id = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->execute([$student_id, $subject_id]);
                $existing = $checkStmt->fetch();
                
                if ($existing) {
                    // Mettre à jour la note existante
                    $sql = "UPDATE notes SET grade = ?, qcm = ?, participation = ? 
                            WHERE student_id = ? AND subject_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$exam, $qcm, $participation, $student_id, $subject_id]);
                } else {
                    // Insérer une nouvelle note
                    $sql = "INSERT INTO notes (student_id, subject_id, grade, qcm, participation)
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$student_id, $subject_id, $exam, $qcm, $participation]);
                }
            }
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?class_id=" . $class_id . "&success=1");
        exit();
    }
}

// Message de succès
$success = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT) === 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .grade-input { max-width: 80px; }
        .table-responsive { overflow-x: auto; }

        .grade-cell {
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<?php include "inc/navbar.php"; ?>
<div class="container mt-5">
    <h3 class="mb-4">
        <a href="classe.php" class="btn btn-secondary mb-3">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <i class="fas fa-edit"></i> 
        Notes - <?= htmlspecialchars($class['name'] ?? 'Classe inconnue') ?>
    </h3>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Succès!</strong> Les notes ont été enregistrées avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="" class="mt-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Gestion des notes</h4>
            </div>
            <div class="card-body">
                <!-- Sélectionner la matière -->
                <div class="mb-3">
                    <label for="subject_id" class="form-label">Matière</label>
                    <select id="subject_id" name="subject_id" class="form-select" required>
                        <option value="">Sélectionner une matière</option>
                        <?php foreach ($subjects as $subject) : ?>
                            <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Tableau combiné pour affichage et saisie -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom Étudiant</th>
                                <th>Matière</th>
                                <th>Note QCM</th>
                                <th>Note Examen</th>
                                <th>Note Participation</th>
                            </tr>
                        </thead>
                        <tbody id="gradesTableBody">
                            <?php foreach ($students as $student) : ?>
                                <tr class="student-row" data-student-id="<?= $student['id'] ?>">
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td id="subject-name-<?= $student['id'] ?>">Sélectionnez une matière</td>
                                    <td class="grade-cell">
                                        <input type="number" step="0.1" min="0" max="20" 
                                               name="grades[<?= $student['id'] ?>][qcm]" 
                                               id="qcm-input-<?= $student['id'] ?>"
                                               class="grade-input form-control">
                                    </td>
                                    <td class="grade-cell">
                                        <input type="number" step="0.1" min="0" max="20" 
                                               name="grades[<?= $student['id'] ?>][exam]" 
                                               id="exam-input-<?= $student['id'] ?>"
                                               class="grade-input form-control">
                                    </td>
                                    <td class="grade-cell">
                                        <input type="number" step="0.1" min="0" max="20" 
                                               name="grades[<?= $student['id'] ?>][participation]" 
                                               id="participation-input-<?= $student['id'] ?>"
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
                    <i class="fas fa-save"></i> Enregistrer les notes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Données des notes existantes pour JavaScript
const existingScores = <?= json_encode($existingScores) ?>;
const subjects = <?= json_encode($subjects) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('subject_id');
    
    // Fonction pour mettre à jour le tableau en fonction de la matière sélectionnée
    function updateTable() {
        const selectedSubjectId = subjectSelect.value;
        const selectedSubject = subjects.find(s => s.id == selectedSubjectId);
        const subjectName = selectedSubject ? selectedSubject.name : 'Sélectionnez une matière';
        
        // Parcourir tous les étudiants
        document.querySelectorAll('.student-row').forEach(row => {
            const studentId = row.dataset.studentId;
            
            // Mettre à jour le nom de la matière
            document.getElementById(`subject-name-${studentId}`).textContent = subjectName;
            
            // Récupérer les notes existantes si disponibles
            const hasExistingGrades = existingScores[studentId] && existingScores[studentId][selectedSubjectId];
            
            // Récupérer les champs de saisie
            const qcmInput = document.getElementById(`qcm-input-${studentId}`);
            const examInput = document.getElementById(`exam-input-${studentId}`);
            const participationInput = document.getElementById(`participation-input-${studentId}`);
            
            if (hasExistingGrades) {
                // Préremplir les champs de saisie avec les notes existantes
                qcmInput.value = existingScores[studentId][selectedSubjectId].qcm;
                examInput.value = existingScores[studentId][selectedSubjectId].exam;
                participationInput.value = existingScores[studentId][selectedSubjectId].participation;
            } else {
                // Réinitialiser les champs de saisie
                qcmInput.value = '';
                examInput.value = '';
                participationInput.value = '';
            }
        });
    }
    
    // Lier l'événement au changement de matière
    subjectSelect.addEventListener('change', updateTable);
    
    // Initialiser le tableau
    updateTable();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>