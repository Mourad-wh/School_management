<?php 
session_start();
include "../DB_connection.php";
include "data/class.php";
include "data/teacher.php";

// ✅ Vérification que l'utilisateur est bien un professeur connecté
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Professeur') {
    header("Location: ../../LOG/login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Récupérer les données
$teacher = getTeacherById($teacher_id, $conn);
$classes = getClassesByTeacherId($teacher_id, $conn);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Classes du Professeur</title>
    <link rel="stylesheet" href="../../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional specific styles for this page */
        .classes-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .class-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        .class-info h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .class-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .student-count {
            background-color: rgb(150, 20, 255);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .no-classes {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
<?php include "inc/navbar.php"; ?>

    <main class="students-section">
        <div class="students-header">
            <h1 style="color: aliceblue;">Mes Classes</h1>
        </div>
        
        <div class="classes-container">
            <?php if ($classes != 0) { ?>
                <?php foreach ($classes as $class) { ?>
                    <a href="class_students.php?class_id=<?= $class['id'] ?>" class="class-card">
                        <div class="class-info">
                            <h3><?= htmlspecialchars($class['name']) ?></h3>
                            <?php if(isset($class['grade'])) { ?>
                                <p>Niveau : <?= htmlspecialchars($class['grade']) ?></p>
                            <?php } ?>
                        </div>
                        <span class="student-count">
                            <?= countStudentsInClass($class['id'], $conn) ?> élèves
                        </span>
                    </a>
                <?php } ?>
            <?php } else { ?>
                <div class="no-classes">
                    <p>Aucune classe assignée pour le moment.</p>
                </div>
            <?php } ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>