<?php
// Connexion directe à la base de données
$host = "127.0.0.1";
$dbname = "school_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Connexion directe à l'élève ID 1
    $student_id = 18;

    // Get student info
    $sql = "SELECT students.*,
                   classes.name as class_name,
                   classes.grade as class_grade,
                   classes.id as class_id
            FROM students
            JOIN student_class ON students.id = student_class.student_id
            JOIN classes ON student_class.class_id = classes.id
            WHERE students.id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get upcoming exams for the student's class
    $exams = [];
    if ($student) {
        $examSql = "SELECT * FROM exam 
                   WHERE class_id = ? 
                   AND date >= CURDATE()
                   ORDER BY date ASC";
        $examStmt = $pdo->prepare($examSql);
        $examStmt->execute([$student['class_id']]);
        $exams = $examStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check for absence alerts
    $absenceAlert = false;
    $absenceId = null;
    if ($student) {
        $absenceSql = "SELECT id FROM absence
                      WHERE student_id = ? 
                      AND alert_sent = 1
                      AND justification_file IS NULL";
        $absenceStmt = $pdo->prepare($absenceSql);
        $absenceStmt->execute([$student_id]);
        $absenceAlert = $absenceStmt->fetch(PDO::FETCH_ASSOC);
        if ($absenceAlert) {
            $absenceId = $absenceAlert['id'];
        }
    }

    // Handle justification submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_justification']) && $absenceId) {
        if (isset($_FILES['justification_file']) && $_FILES['justification_file']['error'] === UPLOAD_ERR_OK) {
            $fileContent = file_get_contents($_FILES['justification_file']['tmp_name']);
            $fileType = $_FILES['justification_file']['type'];
            
            $updateSql = "UPDATE absences 
                          SET justification = ?, 
                              justification_type = ?,
                              alert_sent = 0
                          WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$fileContent, $fileType, $absenceId]);
            
            // Refresh the absence alert status
            $absenceAlert = false;
        }
    }

} catch(PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
} catch(Exception $e) {
    die("Erreur générale : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Étudiant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #0d47a1;
            --primary-dark: #082b60;
            --primary-light: #d6e5ff;
            --accent-color: #0078d7;
            --text-color: #333333;
            --light-text: #ffffff;
            --background-color: #f5f7fa;
            --card-background: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
            --spacing-unit: 16px;
            --transition: all 0.3s ease;
            --warning-color: #ff9800;
            --danger-color: #f44336;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 90px;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: calc(var(--spacing-unit) * 2);
            display: flex;
            flex-wrap: wrap;
            gap: var(--spacing-unit);
        }

        .profile-section {
            flex: 1;
            min-width: 300px;
        }

        .notifications-section {
            flex: 1;
            min-width: 300px;
        }

        .profile-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-color);
            margin-bottom: var(--spacing-unit);
            transition: var(--transition);
            animation: fadeIn 0.6s ease-out forwards;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .notification-card {
            background-color: var(--card-background);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px var(--shadow-color);
            margin-bottom: var(--spacing-unit);
            transition: var(--transition);
        }

        .card-header {
            background-color: var(--primary-color);
            color: var(--light-text);
            padding: var(--spacing-unit);
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--accent-color) 0%, transparent 50%);
            opacity: 0.1;
        }

        .card-title {
            text-align: center;
            margin-bottom: 0;
            font-size: 1.5rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: calc(var(--spacing-unit) * 1.5);
        }

        .profile-list {
            display: grid;
            grid-template-columns: 1fr 2fr;
            row-gap: var(--spacing-unit);
            column-gap: var(--spacing-unit);
        }

        .profile-label {
            font-weight: 600;
            color: var(--primary-dark);
            position: relative;
            padding-left: 12px;
        }

        .profile-label::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 14px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .profile-value {
            color: var(--text-color);
        }

        .alert {
            padding: var(--spacing-unit);
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: var(--spacing-unit);
            border-left: 4px solid;
        }

        .alert-warning {
            background-color: #fff3e0;
            color: #e65100;
            border-left-color: var(--warning-color);
        }

        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border-left-color: var(--danger-color);
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left-color: var(--primary-color);
        }

        .exam-list {
            list-style: none;
            padding: 0;
        }

        .exam-item {
            padding: calc(var(--spacing-unit) / 2);
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .exam-item:last-child {
            border-bottom: none;
        }

        .exam-subject {
            font-weight: 600;
        }

        .exam-date {
            color: var(--primary-dark);
        }

        .justification-form {
            margin-top: var(--spacing-unit);
        }

        .form-group {
            margin-bottom: calc(var(--spacing-unit) / 2);
        }

        .form-label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: var(--primary-dark);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            animation: pulse 1.5s infinite;
        }

        .notification-header {
            position: relative;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .profile-list {
                grid-template-columns: 1fr;
                row-gap: calc(var(--spacing-unit) / 2);
            }

            .profile-label {
                color: var(--primary-color);
                margin-bottom: 4px;
            }

            .profile-value {
                padding-left: 12px;
                border-left: 2px solid var(--primary-light);
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>
    <?php include "inc/navbar.php"; ?>

    <div class="container" style="margin-top: 20px;">
        <?php if ($student): ?>
            <div class="profile-section">
                <div class="profile-card">
                    <div class="card-header">
                        <h3 class="card-title">Profil de l'étudiant</h3>
                    </div>
                    <div class="card-body">
                        <dl class="profile-list">
                            <dt class="profile-label">Nom complet:</dt>
                            <dd class="profile-value"><?= htmlspecialchars($student['full_name']) ?></dd>

                            <dt class="profile-label">Email:</dt>
                            <dd class="profile-value"><?= htmlspecialchars($student['email']) ?></dd>

                            <dt class="profile-label">Téléphone:</dt>
                            <dd class="profile-value"><?= htmlspecialchars($student['phone']) ?></dd>

                            <dt class="profile-label">Classe:</dt>
                            <dd class="profile-value">
                                <?= htmlspecialchars($student['class_name']) ?>
                                (Niveau <?= htmlspecialchars($student['class_grade']) ?>)
                            </dd>

                            <dt class="profile-label">École ID:</dt>
                            <dd class="profile-value"><?= htmlspecialchars($student['school_id']) ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="notifications-section">
                <!-- Absence Notification -->
                <?php if ($absenceAlert): ?>
                    <div class="notification-card">
                        <div class="card-header">
                            <div class="notification-header">
                                <h3 class="card-title">Alerte d'absence</h3>
                                <span class="notification-badge">!</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger">
                                Vous avez une absence non justifiée. Veuillez soumettre un justificatif.
                            </div>
                            <form class="justification-form" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="form-label" for="justification_file">Fichier justificatif:</label>
                                    <input type="file" id="justification_file" name="justification_file" required>
                                </div>
                                <button type="submit" name="submit_justification" class="btn">Soumettre</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upcoming Exams -->
                <?php if (!empty($exams)): ?>
                    <div class="notification-card">
                        <div class="card-header">
                            <h3 class="card-title">Examens à venir</h3>
                        </div>
                        <div class="card-body">
                            <ul class="exam-list">
                                <?php foreach ($exams as $exam): ?>
                                    <li class="exam-item">
                                        <span class="exam-subject"><?= htmlspecialchars($exam['subject']) ?></span>
                                        <span class="exam-date">
                                            <?= date('d/m/Y', strtotime($exam['exam_date'])) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="notification-card">
                        <div class="card-header">
                            <h3 class="card-title">Examens</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                Aucun examen à venir pour le moment.
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                Aucun étudiant trouvé avec cet ID
            </div>
        <?php endif; ?>
    </div>
</body>
</html>