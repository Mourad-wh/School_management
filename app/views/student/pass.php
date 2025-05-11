<?php 
// Connexion à la base de données
$host = "127.0.0.1";
$dbname = "school_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ID élève fixé à 1
    $student_id = 1;

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

    try {
        // Vérification ancien mot de passe
        $stmt = $pdo->prepare("SELECT password FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || !password_verify($old_pass, $student['password'])) {
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <style>
        .form-w { max-width: 400px; width: 90%; }
        #gBtn { min-width: 80px; }
    </style>
</head>
<body>
<?php include "inc/navbar.php"; ?>
    <div class="d-flex justify-content-center align-items-center min-vh-100">
        <form method="post" class="shadow p-4 form-w bg-light">
            <h3 class="text-center mb-4">Changer le mot de passe</h3>
            
            <?php if (isset($_GET['perror'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['perror']) ?></div>
            <?php endif; ?>
            
            <?php if (isset($_GET['psuccess'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['psuccess']) ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Ancien mot de passe</label>
                <input type="password" 
                       class="form-control"
                       name="old_pass"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <div class="input-group">
                    <input type="text" 
                           class="form-control"
                           name="new_pass"
                           id="passInput"
                           required>
                    <button type="button" 
                            class="btn btn-outline-secondary"
                            id="gBtn">
                            Générer
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirmer le mot de passe</label>
                <input type="text" 
                       class="form-control"
                       name="c_new_pass"
                       id="passInput2"
                       required>
            </div>

            <button type="submit" 
                    class="btn btn-primary w-100">
                    Modifier
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generatePassword(length = 12) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@#$%^&*';
            let password = '';
            for (let i = 0; i < length; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return password;
        }

        document.getElementById('gBtn').addEventListener('click', function(e) {
            e.preventDefault();
            const newPass = generatePassword();
            document.getElementById('passInput').value = newPass;
            document.getElementById('passInput2').value = newPass;
        });
    </script>
</body>
</html>