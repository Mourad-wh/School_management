<?php
session_start();


// Inclure la connexion PDO
include "../DB_connection.php"; // Ce fichier doit fournir $conn (instance PDO)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Professeur') {
    header("Location: ../../LOG/login.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];


$message = "";
$type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $c_new_pass = $_POST['c_new_pass'];

    if ($new_pass !== $c_new_pass) {
        $message = "Les mots de passe ne correspondent pas.";
        $type = "danger";
    } else {
        // Récupérer l'ancien mot de passe
        $stmt = $conn->prepare("SELECT password FROM teachers WHERE id = ?");
        $stmt->execute([$teacher_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($old_pass, $row['password'])) {
                // Hacher le nouveau mot de passe
                $new_hashed = password_hash($new_pass, PASSWORD_DEFAULT);

                // Mettre à jour le mot de passe
                $update = $conn->prepare("UPDATE teachers SET password = ? WHERE id = ?");
                if ($update->execute([$new_hashed, $teacher_id])) {
                    $message = "Mot de passe changé avec succès.";
                    $type = "success";
                } else {
                    $message = "Erreur lors de la mise à jour du mot de passe.";
                    $type = "danger";
                }
            } else {
                $message = "Ancien mot de passe incorrect.";
                $type = "danger";
            }
        } else {
            $message = "Enseignant introuvable.";
            $type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changement de mot de passe</title>
    <style>
        /* ===== BASE STYLES ===== */
        body {
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .main-content {
            margin-top: 120px; /* Adjust based on your navbar height */
            padding: 20px;
        }

        /* ===== FORM CONTAINER ===== */
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background-color: #04207b;
            color: white;
            padding: 25px;
            text-align: center;
            border-bottom: 4px solid rgba(255, 255, 255, 0.1);
        }

        .card-header h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 30px;
        }

        /* ===== FORM ELEMENTS ===== */
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #04207b;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            margin-bottom: 20px;
            background-color: #f4f7fc;
        }

        .form-control:focus {
            outline: none;
            border-color: #04207b;
            box-shadow: 0 0 0 2px rgba(152, 183, 245, 0.1);
            background-color: white;
        }

        /* ===== BUTTON ===== */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: #04207b;
            color: white;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary:hover {
            background-color: #030e63;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* ===== ALERT MESSAGES ===== */
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-size: 0.95rem;
            border-left: 4px solid transparent;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-content {
                margin-top: 100px;
                padding: 15px;
            }
            
            .container {
                max-width: 100%;
                border-radius: 10px;
            }
            
            .card-header {
                padding: 20px;
            }
            
            .card-body {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                margin-top: 80px;
            }
            
            .card-header h4 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
<?php include "inc/navbar.php"; ?>
<div class="main-content">
    <div class="container">
        <div class="card-header">
            <h4>Changer le mot de passe</h4>
        </div>
        <div class="card-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Ancien mot de passe</label>
                    <input type="password" name="old_pass" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="new_pass" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="c_new_pass" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary">Changer le mot de passe</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>