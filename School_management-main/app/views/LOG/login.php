<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="main">
        <section class="sign-in">
            <div class="container">
                <div class="signin-content display-flex">
                    <div class="signin-image">
                        <figure><img src="img/signin.jpg" alt="Image de connexion"></figure>
                    </div>
                    <?php if (isset($_GET['error'])): ?>
                    <div class="alert" role="alert">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="signin-form">
                        <div class="logo-container">
                            <img src="img/logo.png" alt="Logo" class="logo-image">
                        </div>

                        <h2 class="form-title">Connexion</h2>

                        <form method="POST" class="register-form" id="login-form" action="req/login.php">
                            <div class="form-group">
                                <label for="username"></label>
                                <input  type="text" name="username" id="username" placeholder="Nom d'utilisateur" required>
                            </div>

                            <div class="form-group">
                                <label for="password"></label>
                                <input type="password" name="password" id="password" placeholder="Mot de passe" required>
                            </div>

                            <div class="form-group">
                                <label for="role"></label>
                                <div class="select-box">
                                    <select name="role" id="role" required>
                                        <option value="">Sélectionner un type d'utilisateur</option>
                                        <option value="1">Admin</option>
                                        <option value="2">Professeur</option>
                                        <option value="3">Étudiant</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group form-button">
                                <input type="submit" name="signin" id="signin" class="form-submit" value="Se Connecter">
                            </div>

                            <div class="form-group">
                                <a href="../index.php" class="home-link">Retour à l'accueil</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
