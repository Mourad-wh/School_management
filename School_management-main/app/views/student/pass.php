<?php 
// Connexion à la base de données
$host = "127.0.0.1";
$dbname = "school_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Etudiant') {
        header("Location: ../../LOG/login.php");
        exit;
    }

    $student_id = $_SESSION['student_id'];

    // Récupérer les infos de l'étudiant pour l'affichage
    $stmt = $pdo->prepare("SELECT full_name, email FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérification si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_pass'] ?? '';
    $new_pass = $_POST['new_pass'] ?? '';
    $c_new_pass = $_POST['c_new_pass'] ?? '';

    // Validation des mots de passe
    if (empty($old_pass)) {
        header("Location: change-password.php?perror=Ancien mot de passe requis");
        exit;
    }
    
    if ($new_pass !== $c_new_pass) {
        header("Location: change-password.php?perror=Les mots de passe ne correspondent pas");
        exit;
    }

    if (strlen($new_pass) < 8) {
        header("Location: change-password.php?perror=Le mot de passe doit contenir au moins 8 caractères");
        exit;
    }

    try {
        // Vérification ancien mot de passe
        $stmt = $pdo->prepare("SELECT password FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student_pass = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student_pass || !password_verify($old_pass, $student_pass['password'])) {
            header("Location: change-password.php?perror=Ancien mot de passe incorrect");
            exit;
        }

        // Mise à jour du mot de passe
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE students SET password = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $student_id]);

        header("Location: change-password.php?psuccess=Mot de passe mis à jour avec succès");
        exit;

    } catch(PDOException $e) {
        header("Location: change-password.php?perror=Erreur de base de données");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Changement de mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        body {
            background-color: #f5f7ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .password-container {
            max-width: 500px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }
        
        .password-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .password-body {
            padding: 30px;
            background-color: white;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(72, 149, 239, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-generate {
            background-color: var(--light-color);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-generate:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 5px;
            background-color: #e9ecef;
        }
        
        .strength-bar {
            height: 100%;
            border-radius: 5px;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .student-info {
            background-color: rgba(255,255,255,0.2);
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: inline-block;
        }
        
        @media (max-width: 576px) {
            .password-container {
                margin: 20px;
                width: calc(100% - 40px);
            }
            
            .password-header, .password-body {
                padding: 20px;
            }
        }
        .container.custom-margin-top { margin-top: 100px; }
    </style>
</head>
<body>
      <div class="container custom-margin-top">
    <?php include "inc/navbar.php"; ?>
    
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="password-container">
            <div class="password-header">
                <h3><i class="bi bi-shield-lock"></i> Changement de mot de passe</h3>
                <div class="student-info">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($student['full_name']) ?>
                </div>
            </div>
            
            <div class="password-body">
                <?php if (isset($_GET['perror'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_GET['perror']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['psuccess'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_GET['psuccess']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-4">
                        <label class="form-label">Ancien mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control"
                                   name="old_pass"
                                   id="oldPass"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleOldPass">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control"
                                   name="new_pass"
                                   id="newPass"
                                   required
                                   oninput="checkPasswordStrength(this.value)">
                            <button class="btn btn-generate" type="button" id="generateBtn">
                                <i class="bi bi-key"></i> Générer
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <small class="text-muted">Minimum 8 caractères avec majuscules, minuscules et chiffres</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control"
                                   name="c_new_pass"
                                   id="confirmPass"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPass">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="passMatch" class="mt-1 small"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-check-circle"></i> Modifier le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fonction pour générer un mot de passe sécurisé
        function generatePassword(length = 12) {
            const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            const lowercase = 'abcdefghijklmnopqrstuvwxyz';
            const numbers = '0123456789';
            const symbols = '@#$%^&*';
            
            let password = '';
            // On s'assure d'avoir au moins un caractère de chaque type
            password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
            password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
            password += numbers.charAt(Math.floor(Math.random() * numbers.length));
            password += symbols.charAt(Math.floor(Math.random() * symbols.length));
            
            // On complète avec des caractères aléatoires
            const allChars = uppercase + lowercase + numbers + symbols;
            for (let i = password.length; i < length; i++) {
                password += allChars.charAt(Math.floor(Math.random() * allChars.length));
            }
            
            // On mélange le mot de passe pour plus de sécurité
            return password.split('').sort(() => 0.5 - Math.random()).join('');
        }

        // Fonction pour évaluer la force du mot de passe
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            let strength = 0;
            
            // Longueur minimale
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Contient des minuscules
            if (/[a-z]/.test(password)) strength += 1;
            
            // Contient des majuscules
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Contient des chiffres
            if (/[0-9]/.test(password)) strength += 1;
            
            // Contient des caractères spéciaux
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Mise à jour de la barre de force
            const width = (strength / 6) * 100;
            strengthBar.style.width = width + '%';
            
            // Couleur en fonction de la force
            if (strength <= 2) {
                strengthBar.style.backgroundColor = '#dc3545'; // Rouge
            } else if (strength <= 4) {
                strengthBar.style.backgroundColor = '#fd7e14'; // Orange
            } else {
                strengthBar.style.backgroundColor = '#28a745'; // Vert
            }
            
            // Vérification de la correspondance des mots de passe
            checkPasswordMatch();
        }

        // Fonction pour vérifier si les mots de passe correspondent
        function checkPasswordMatch() {
            const newPass = document.getElementById('newPass').value;
            const confirmPass = document.getElementById('confirmPass').value;
            const matchDiv = document.getElementById('passMatch');
            
            if (newPass && confirmPass) {
                if (newPass === confirmPass) {
                    matchDiv.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Les mots de passe correspondent</span>';
                } else {
                    matchDiv.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle-fill"></i> Les mots de passe ne correspondent pas</span>';
                }
            } else {
                matchDiv.innerHTML = '';
            }
        }

        // Événements
        document.getElementById('generateBtn').addEventListener('click', function() {
            const newPass = generatePassword();
            document.getElementById('newPass').value = newPass;
            document.getElementById('confirmPass').value = newPass;
            checkPasswordStrength(newPass);
        });

        document.getElementById('toggleOldPass').addEventListener('click', function() {
            const passInput = document.getElementById('oldPass');
            const icon = this.querySelector('i');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        document.getElementById('toggleNewPass').addEventListener('click', function() {
            const passInput = document.getElementById('confirmPass');
            const icon = this.querySelector('i');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Vérification en temps réel
        document.getElementById('newPass').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        document.getElementById('confirmPass').addEventListener('input', checkPasswordMatch);
    </script>
</body>
</html>