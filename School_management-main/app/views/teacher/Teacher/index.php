
<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'Professeur') {
    include "../DB_connection.php";
    include "data/teacher.php";
    include "data/class.php";
    
    $teacher_id = $_SESSION['teacher_id'];
    $teacher = getTeacherById($teacher_id, $conn);
    $subjects = getSubjectsByTeacherId($teacher_id, $conn);
    $classes = getClassesByTeacherId($teacher_id, $conn);

    // Ici tu peux continuer à afficher les infos du professeur ou charger la page
} else {
    header("Location: ../../LOG/login.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Professeur</title>
    <link rel="icon" type="image/png" href="/Learn-Bridge(final)/app/views/images/logo.png">
    <link rel="stylesheet" href="../../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .teacher-profile {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            display: flex;
            justify-content: center;
        }

        .profile-card {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            width: 360px;
            border: none;
            position: relative;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .profile-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(to right, #04207b, #4a7eb5);
        }
        .profile-header {
            padding: 2.5rem 1.5rem 1.5rem;
            position: relative;
            text-align: center;
        }

        .profile-avatar {
            position: relative;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #f4f7fa;
            margin: 0 auto 1.5rem;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 0 0 2px #dee2e6;
        }

        .profile-avatar i {
            font-size: 3.5rem;
            color: #04207b;
        }

        .profile-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #04207b;
            margin-bottom: 0.5rem;
        }

        .profile-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        .profile-info {
            list-style: none;
            padding: 0;
        }

        .profile-info-item {
            padding: 1rem 1.5rem;
            border-top: 1px solid #f1f3f5;
            display: flex;
            align-items: center;
        }

        .profile-info-item i {
            margin-right: 1rem;
            color: #04207b;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .info-label {
            font-weight: 600;
            width: 130px;
            color: #333;
        }

        .info-value {
            flex: 1;
            color: #666;
        }

        .subject-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .subject-badge {
            background-color: #f4f7fa;
            color: #3a6ea5;
            font-size: 0.85rem;
            padding: 0.3rem 0.7rem;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
        }

        .subject-badge i {
            margin-right: 0.4rem;
            font-size: 0.75rem;
        }

        .profile-actions {
            text-align: center;
            padding: 1.5rem;
        }

        .profile-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            background-color: #3a6ea5;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(58, 110, 165, 0.2);
        }

        body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f4f6fa;
    }

    /* Main section */
    .main-container {
      margin-top: 100px; /* Adjust depending on nav height */
      padding: 20px;
      display: flex;
      justify-content: center;
    }

    .profile-card {
      background-color: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 400px;
      text-align: center;
    }

    .profile-card h2 {
      color: #001f5c;
    }

    .profile-card p {
      margin: 10px 0;
      color: #444;
    }

    .profile-info {
      text-align: left;
      margin-top: 20px;
    }

    .profile-info div {
      margin-bottom: 15px;
    }

    .profile-info span {
      font-weight: bold;
      color: #001f5c;
    }

    .badge {
      display: inline-block;
      background-color: #e0edff;
      color: #0046ad;
      padding: 5px 10px;
      border-radius: 12px;
      font-size: 14px;
      margin-top: 5px;
    }


        .profile-btn:hover {
            background-color: #2c5682;
            transform: translateY(-2px);
        }

        .profile-btn i {
            margin-right: 0.5rem;
        }

        .profile-footer {
            background-color: #f4f7fa;
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
            border-top: 1px solid #f1f3f5;
        }

        .students-section, .logo-container{
            margin:auto;
        }

        @media (max-width: 768px) {
            .teacher-profile {
                padding: 1rem;
            }
            
            .profile-card {
                width: 100%;
                max-width: 340px;
            }
            
            .profile-title {
                font-size: 1.5rem;
            }
            
            .profile-info-item {
                padding: 0.8rem 1.2rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-label {
                width: 100%;
                margin-bottom: 0.3rem;
            }
            
            .info-value {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include "inc/navbar.php"; ?>

    <main class="students-section">
        <?php if ($teacher != 0): ?>
            <div class="teacher-profile">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="profile-title"><?= htmlspecialchars($teacher['full_name']) ?></h5>
                        <p class="profile-subtitle">Professeur</p>
                    </div>
                    <ul class="profile-info">
                        <li class="profile-info-item">
                            <i class="fas fa-id-card"></i>
                            <span class="info-label">Nom complet :</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['full_name']) ?></span>
                        </li>
                        <li class="profile-info-item">
                            <i class="fas fa-envelope"></i>
                            <span class="info-label">Email :</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['email']) ?></span>
                        </li>
                        <li class="profile-info-item">
                            <i class="fas fa-phone"></i>
                            <span class="info-label">Téléphone :</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['phone']) ?></span>
                        </li>
<li class="profile-info-item">
    <i class="fas fa-book"></i>
    <span class="info-label">Matières :</span>
    <div class="info-value subject-badges">
        <?php
        $subjects = getSubjectsByTeacherId($teacher['id'], $conn);
        if (!empty($subjects)) {
            foreach ($subjects as $subject) {
                echo '<span class="subject-badge"><i class="fas fa-circle"></i>' 
                     . htmlspecialchars($subject['name']) . '</span>';
            }
        } else {
            echo '<span class="text-muted">Aucune matière assignée</span>';
        }
        ?>
    </div>
</li>
                    </ul>
                    <div class="profile-actions">
                        <a href="classes.php" class="profile-btn">
                            <i class="fas fa-chalkboard"></i> Voir mes classes
                        </a>
                    </div>
                    <div class="profile-footer">
                        Dernière connexion : <?= date('d/m/Y H:i') ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>