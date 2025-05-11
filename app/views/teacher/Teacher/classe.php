<?php 
$teacher_id = 1; // À remplacer par l'ID dynamique du professeur connecté

include "../DB_connection.php";
include "data/class.php";
include "data/teacher.php";

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
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional specific styles for this page */
        .classes-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .class-list {
            list-style: none;
            padding: 0;
        }
        
        .class-item {
            background: white;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .class-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            background-color: #f8f9fa;
        }
        
        .class-info h3 {
            color: #333;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }
        
        .class-info p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .student-count {
            background-color: rgb(150, 20, 255);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .no-classes {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .no-classes p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .page-title {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
<?php include "inc/navbar.php"; ?>

    <main class="students-section">
        <div class="students-header">
            <h1 class="page-title">Mes Classes</h1>
        </div>
        
        <div class="classes-container">
            <?php if ($classes != 0) { ?>
                <ul class="class-list">
                    <?php foreach ($classes as $class) { ?>
                        <a href="student-grade.php?class_id=<?= $class['id'] ?>" class="class-item">
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
                </ul>
            <?php } else { ?>
                <div class="no-classes">
                    <p>Aucune classe assignée pour le moment.</p>
                </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>