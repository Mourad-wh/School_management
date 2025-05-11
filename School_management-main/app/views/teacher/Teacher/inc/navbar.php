<style>
/* ===== HEADER & NAVBAR ===== */
.header {
  height: 800px;
  width: 100%;
  background-image: linear-gradient(rgba(4,9,30,0.7), rgba(4,9,30,0.7)), url(images/banner.jpeg);
  background-position: center;
  background-size: cover;
  position: relative;
}

nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: rgba(252, 252, 252, 0.9);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    width: 100%;
    height: 80px;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

/* Effet de rétrécissement au scroll */
nav.scrolled {
  height: 70px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.logo-container {
  display: flex;
  align-items: center;
  height: 100%;
}

nav img {
  width: 180px;
  height: auto;
  transition: transform 0.3s;
}

nav img:hover {
  transform: scale(1.05);
}

.nav-links {
  flex: 1;
  text-align: right;
}

.nav-links ul {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin: 0;
  padding: 0;
}

.nav-links ul li {
  list-style: none;
  padding: 8px 15px;
  position: relative;
}

.nav-links ul li::after {
  content: '';
  width: 0%;
  height: 2px;
  background-color: #04207b;
  display: block;
  margin: auto;
  transition: width 0.3s;
  position: absolute;
  bottom: 0;
  left: 0;
}

.nav-links ul li:hover::after {
  width: 100%;
}

.nav-links ul li a {
  text-decoration: none;
  color: #030e63;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: block;
  padding: 5px 0;
}

.nav-links ul li a:hover {
  color: #04207b;
}

.nav-links ul li a.active {
  color: #04207b;
  position: relative;
}

.nav-links ul li a.active::after {
  content: '';
  width: 100%;
  height: 2px;
  background-color: #04207b;
  display: block;
  position: absolute;
  bottom: 0;
  left: 0;
}

.login-btn {
  background: #04207b;
  color: white !important;
  padding: 8px 20px;
  border-radius: 20px;
  margin-left: 15px;
  transition: all 0.3s;
  border: none;
  font-weight: 600;
}

.login-btn:hover {
  background: #030e63;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Menu mobile */
.mobile-menu {
  display: none;
  font-size: 28px;
  cursor: pointer;
  color: #030e63;
  margin-left: 20px;
}

/* Responsive */
@media (max-width: 992px) {
  .nav-links ul {
    display: none;
    position: absolute;
    top: 80px;
    right: 0;
    width: 250px;
    background-color: white;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    border-radius: 0 0 8px 8px;
    flex-direction: column;
    padding: 20px 0;
  }
  
  .nav-links ul.show {
    display: flex;
  }
  
  .nav-links ul li {
    padding: 12px 25px;
  }
  
  .nav-links ul li a {
    color: #333;
  }
  
  .mobile-menu {
    display: block;
  }
  
  .login-btn {
    margin-left: 0;
    margin-top: 15px;
    width: calc(100% - 50px);
  }
}

@media (max-width: 768px) {
  nav {
    padding: 15px 4%;
  }
  
  nav img {
    width: 150px;
  }
}

/* Animation pour le menu déroulant */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.nav-links ul.show {
  animation: fadeIn 0.3s ease forwards;
}
</style>

<nav>
    <div class="logo-container">
        <img src="..\..\LOG\img\logo.png" alt="School Logo">
    </div>
    <div class="nav-links">
        <ul>
            <li><a href="index.php">Tableau de bord</a></li>
            <li><a href="classes.php">Classes</a></li>
            <li><a href="classe.php">Gestion Des Notes</a></li>
            <li><a href="exam.php">Gestions Des Examens</a></li>
            <li><a href="pass.php">Changer le mot de passe</a></li>
            <li><a href="logout.php" class="login-btn">Déconnexion <i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </div>
</nav>