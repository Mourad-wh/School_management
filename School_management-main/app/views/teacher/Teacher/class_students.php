<?php
session_start();
include "../DB_connection.php";
include "data/class.php";
include "data/student.php"; // si tu utilises getStudentsByClass()

// ✅ Vérification de sécurité : est-ce un professeur connecté ?
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Professeur') {
    header("Location: ../../LOG/login.php");
    exit();
}

// ✅ Vérifie que le paramètre class_id est bien passé
if (!isset($_GET['class_id'])) {
    header("Location: teacher_classes.php");
    exit();
}

$class_id = $_GET['class_id'];
$teacher_id = $_SESSION['teacher_id'];

// ✅ Vérifier que le professeur a bien accès à cette classe
$class = getClassById($class_id, $conn);
$teacher_classes = getClassesByTeacherId($teacher_id, $conn);

$has_access = false;
foreach ($teacher_classes as $tc) {
    if ($tc['id'] == $class_id) {
        $has_access = true;
        break;
    }
}

if (!$has_access) {
    header("Location: teacher_classes.php?error=AccessDenied");
    exit();
}

$students = getStudentsByClass($class_id, $conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Élèves de la classe</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional specific styles for this page */
        .students-container {
            margin: 0 auto;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            background-color: white;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background-color: #f1f3f5;
            color: rgb(150, 20, 255);
        }
        
        .back-btn i {
            margin-right: 8px;
        }
        
        .page-title {
            color: white;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        
        .page-title i {
            margin-right: 15px;
        }
        
        .students-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .students-table thead {
            background-color: #f8f9fa;
        }
        
        .students-table th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
        }
        
        .students-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .students-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .no-students {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
<?php include "inc/navbar.php"; ?>

    <main class="students-section">
        <div class="students-header">
            <h1 class="page-title" style="color: #f8f9fa">
                <i class="fas fa-users-class"></i>
                Élèves de <?= htmlspecialchars($class['name']) ?>
            </h1>
        </div>
        <h1 class="page-title" style="color: #04207b">
            <i class="fas fa-users-class"></i>
            Élèves de <?= htmlspecialchars($class['name']) ?>
        </h1>
        <div class="students-container">
            <a href="classes.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour aux classes
            </a>
            
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Dernière connexion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['full_name']) ?></td>
                                <td><?= htmlspecialchars($student['email']) ?></td>
                                <td><?= htmlspecialchars($student['phone']) ?></td>
                                <td><?= $student['last_login'] ?? 'Jamais' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-students">
                                Aucun élève dans cette classe
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>