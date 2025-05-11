<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'school_db';
$username = 'root'; // Remplacez par votre nom d'utilisateur MySQL
$password = ''; // Remplacez par votre mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupération des informations de l'élève (ID 1)
$student_id = 15;
$student_stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$student_stmt->execute([$student_id]);
$student = $student_stmt->fetch(PDO::FETCH_ASSOC);

// Récupération de toutes les matières
$subjects_stmt = $pdo->prepare("SELECT * FROM subjects");
$subjects_stmt->execute();
$all_subjects = $subjects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des notes et moyennes de l'élève
$notes_stmt = $pdo->prepare("
    SELECT n.*, s.name AS subject_name
    FROM notes n
    JOIN subjects s ON n.subject_id = s.id
    WHERE n.student_id = ?
");
$notes_stmt->execute([$student_id]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation des notes par matière pour l'affichage
$notes_by_subject = [];

// D'abord, initialiser le tableau avec toutes les matières
foreach ($all_subjects as $subject) {
    $subject_id = $subject['id'];
    $subject_name = $subject['name'];
    
    $notes_by_subject[$subject_id] = [
        'subject_name' => $subject_name,
        'notes' => [],
        'total' => 0,
        'count' => 0,
        'average' => 0,
        'has_notes' => false
    ];
}

// Ensuite, ajouter les notes aux matières correspondantes
foreach ($notes as $note) {
    $subject_id = $note['subject_id'];
    
    // Calcul de la moyenne pour chaque note (note + QCM + participation) / 3
    $grade = $note['grade'] ?? 0;
    $qcm = $note['qcm'] ?? 0;
    $participation = $note['participation'] ?? 0;
    
    // Calcul de la moyenne de ces 3 éléments
    $note_average = ($grade + $qcm + $participation) / 3;
    
    // Stockage de la note moyenne calculée
    $note['note_average'] = $note_average;
    
    $notes_by_subject[$subject_id]['notes'][] = $note;
    $notes_by_subject[$subject_id]['total'] += $note_average;
    $notes_by_subject[$subject_id]['count']++;
    $notes_by_subject[$subject_id]['has_notes'] = true;
}

// Calcul de la moyenne pour chaque matière
foreach ($notes_by_subject as $subject_id => &$subject_data) {
    $subject_data['average'] = $subject_data['count'] > 0 ? $subject_data['total'] / $subject_data['count'] : 0;
}

// Calcul de la moyenne générale (seulement pour les matières avec des notes)
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <title>Bulletin de Notes - <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
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
            background-color:rgba(0, 31, 116, 0.2);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 20px;
        }

        th {
            background-color: #001f74;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 12px;
        }

        td {
            background-color: #fff;
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        tr:nth-child(even) td {
            background-color: #f8f9fd;
        }

        .valid {
            color: #2ecc71;
            font-weight: bold;
        }

        .not-valid {
            color: #e74c3c;
            font-weight: bold;
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

        .subject-title {
            background-color: #001f74;
            color: white;
            padding: 8px;
            border-radius: 4px;
            margin-top: 15px;
            font-weight: bold;
        }
        .subject-average {
            background-color:rgb(30, 70, 181);
            padding: 5px;
            border-radius: 3px;
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
        }
    </style>
</head>
<body>
<?php include "inc/navbar.php"; ?>
    <div class="container">
        <h1>Bulletin de Notes</h1>
        
        <div class="student-info">
            <h2><?= htmlspecialchars($student['full_name']) ?></h2>
            <p>Email: <?= htmlspecialchars($student['email']) ?></p>
            <p>Téléphone: <?= htmlspecialchars($student['phone']) ?></p>
        </div>
        
        <h2>Notes par Matière</h2>
        
        <?php foreach ($notes_by_subject as $subject_id => $subject_data): ?>
            <div class="subject-title">
                <?= htmlspecialchars($subject_data['subject_name']) ?> - 
                <span class="subject-average">
                    Moyenne: <?= $subject_data['has_notes'] ? number_format($subject_data['average'], 2) : 'Note non encore saisie' ?>
                </span>
            </div>
            
            <?php if ($subject_data['has_notes']): ?>
            <table>
                <thead>
                    <tr>
                        <th>Note</th>
                        <th>QCM</th>
                        <th>Participation</th>
                        <th>Moyenne</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subject_data['notes'] as $note): ?>
                    <tr>
                        <td><?= number_format($note['grade'], 2) ?></td>
                        <td><?= number_format($note['qcm'] ?? 0, 2) ?></td>
                        <td><?= number_format($note['participation'] ?? 0, 2) ?></td>
                        <td><strong><?= number_format($note['note_average'], 2) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-notes">Note non encore saisie</div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <h2>Moyennes par Matière</h2>
        <table>
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Moyenne</th>
                    <th>Validation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes_by_subject as $subject_id => $subject_data): ?>
                <tr>
                    <td><?= htmlspecialchars($subject_data['subject_name']) ?></td>
                    <td><?= $subject_data['has_notes'] ? number_format($subject_data['average'], 2) : 'Note non encore saisie' ?></td>
                    <td class="<?= $subject_data['has_notes'] ? ($subject_data['average'] >= 12 ? 'valid' : 'not-valid') : '' ?>">
                        <?= $subject_data['has_notes'] ? ($subject_data['average'] >= 12 ? 'V' : 'NV') : 'N/A' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="general-average">
            Moyenne Générale: <?= number_format($general_average, 2) ?>
        </div>
    </div>
</body>
</html>