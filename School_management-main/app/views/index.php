<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> LearnBridge </title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Section Header avec Navbar -->
    <section class="header" id="top">
        <nav>
            <a href="#top"><img src="images/logo.png" alt="LearnBridge Marrakech"></a>
            <div class="nav-links">
                <ul>
                    <li><a href="#top" class="active">Accueil</a></li>
                    <li><a href="#presentation">L'École</a></li>
                    <li><a href="#formations">Formations</a></li>  
                    <li><a href="#recherche">Recherche</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li class="dropdown">
                        <a href="javascript:void(0)" class="dropbtn">Pages</i></a>
                        <div class="dropdown-content">
                            <a href="admission.html">Admission</a>
                            <a href="temoignages.html">Témoignages</a>
                            <a href="faq.html">FAQ</a>
                        </div>
                    </li>
                    
                    <li><a href="LOG/login.php" class="login-btn">Connexion</a></li>
                </ul>
            </div>
            <div class="mobile-menu" onclick="toggleMenu()">☰</div>
        </nav>
       
        <div class="text-box">
            <h1>Bienvenue sur le portail officiel de l’Université LearnBridge!</h1>
            <p>Formation d'excellence en ingénierie depuis 1950</p>
            <div class="stats">
                <span>5 filières d'ingénieur</span>
                <span>12 laboratoires</span>
                <span>95% de réussite</span>
            </div>

        <p >L’Université LearnBridge forme les leaders de demain dans les domaines des sciences, technologies et ingénierie.
         Nos programmes allient rigueur académique, innovation et ouverture sur le monde professionnel.</p>

        </div>
    </section>

    <!-- Section Présentation -->
    <section class="ensa-presentation" id="presentation">
        <div class="ensa-header">
            
            <p class="subtitle">LearnBridge : Excellence Académique et Innovation en Ingénierie</p>
        </div>

        <div class="ensa-container">
            <div class="ensa-history">
                <h2><i class="fas fa-landmark"></i> Notre Histoire</h2>
                <p>LearnBridge est une université d’ingénierie privée fondée en 1950 à Marrakech.
                Elle forme des ingénieurs compétents grâce à une pédagogie innovante, des projets concrets et des partenariats avec le monde industriel. LearnBridge allie excellence académique, innovation et ouverture sur l’avenir.</p>
                <div class="mission-box">
                    <h3>Notre Mission</h3>
                    <p>Chez LearnBridge, nous croyons que l'éducation doit être accessible, flexible et orientée vers la pratique.Notre objectif est de réduire le fossé entre l'apprentissage académique et les besoins du monde professionnel.</p>
                </div>
            </div>

            <div class="ensa-image">
                <img src="images/learnbridgebat.png" alt="Bâtiment de LearnBridge">
                <div class="image-caption">Le campus moderne de LearnBridge</div>
            </div>
        </div>
    </section>

    <!-- Section Formations -->
    <section class="formation-section" id="formations">
        <h2><i class="fas fa-graduation-cap"></i> Notre Offre de Formation</h2>
        <p>Un parcours d'ingénieur sur cinq ans avec des spécialisations couvrant divers secteurs stratégiques :</p>
        
        <div class="formation-grid">
            <div class="cycle-card">
                <h3>Cycle Préparatoire Intégré</h3>
                <p>2 ans - Enseignements Généraux et Techniques (E.G.T)</p>
                <div class="cycle-details">
                    <p>Formation pluridisciplinaire en sciences fondamentales (mathématiques, physique), informatique et sciences de l'ingénieur, complétée par des modules de communication et langues. Le programme intègre des projets pratiques, visites d'entreprises et stages industriels.</p>
                </div>
            </div>
            
            <div class="cycle-card">
                <h3>Cycle Ingénieur</h3>
                <p>3 ans - Spécialisation dans l'une des filières :</p>
                <ul>
                    <li>Génie Cyber-Défense et Systèmes de Télécommunications Embarqués</li>
                    <li>Génie Industriel et Logistique</li>
                    <li>Génie Informatique</li>
                    <li>Réseaux, Systèmes & Services Programmables</li>
                    <li>Systèmes Electroniques Embarqués et Commande des Systèmes</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Section Recherche -->
    <section class="innovation-section" id="recherche">
        <div class="innovation-content">
            <h2><i class="fas fa-lightbulb"></i> Innovation & Recherche</h2>
            <p>LearnBridge dispose d'un espace dédié à l'innovation et d'un Fab Lab, offrant aux étudiants des opportunités concrètes de recherche et développement technologique.</p>
            
            <div class="research-domains">
                <h3>Domaines de recherche :</h3>
                <div class="domains-grid">
                    <div class="domain-item">
                        <i class="fas fa-square-root-alt"></i>
                        <span>Mathématiques Appliquées</span>
                    </div>
                    <div class="domain-item">
                        <i class="fas fa-city"></i>
                        <span>Villes Intelligentes</span>
                    </div>
                    <div class="domain-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Cybersécurité</span>
                    </div>
                    <div class="domain-item">
                        <i class="fas fa-satellite-dish"></i>
                        <span>Télécommunications</span>
                    </div>
                    <div class="domain-item">
                        <i class="fas fa-solar-panel"></i>
                        <span>Énergie Renouvelable</span>
                    </div>
                    <div class="domain-item">
                        <i class="fas fa-robot"></i>
                        <span>Intelligence Artificielle</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="innovation-image">
            <img src="images/recherche.jpg" alt="Laboratoire de recherche ENSA">
            <div class="lab-caption">Laboratoire de recherche high-tech</div>
        </div>
    </section>
    <!-- Section Why -->
    <section class="why-us-section">
        <div class="why-us-title">
        <h2><i class="fas fa-check-circle"></i>Pourquoi nous choisir?</h2>
        </div>
        <div class="why-us-container">
          <div class="why-us-box active">
            <i class="fas fa-graduation-cap"></i>
            <h5>Excellence académique</h5>
            <p>LearnBridge propose un programme rigoureux, combinant théorie et pratique, dirigé par un corps professoral qualifié.</p>
          </div>
          <div class="why-us-box">
            <i class="fas fa-user-friends"></i>
            <h5>Approche centrée </h5>
            <p>Nous offrons un environnement personnalisé avec un soutien adapté pour accompagner chaque étudiant dans son développement./p>
          </div>
          <div class="why-us-box">
            <i class="fas fa-globe-americas"></i>
            <h5>Environnement internationnal</h5>
            <p>LearnBridge attire des étudiants du monde entier, favorisant l’échange culturel et les collaborations internationales.</p>
          </div>
          <div class="why-us-box">
            <i class="fas fa-handshake"></i>
            <h5>Partenariats stratégiques</h5>
            <p>Grâce à nos partenariats avec des entreprises de renommée mondiale, nos étudiants accèdent à des stages et projets réels qui préparent à un avenir prometteur.</p>
          </div>
        </div>
      </section>
      

    <!-- Section Partenaires -->
    <section class="partners-section" id="partenaires">
        <h2><i class="fas fa-handshake"></i> Partenariats</h2>
        <p>LearnBridge entretient des relations solides avec le tissu industriel national et international.</p>
        <div class="partners-logos">
            <img src="images/cadi_ayyad.png" alt="Université Cadi Ayyad">
            <img src="images/LOGO-APEBI-COULEUR.jpg" alt="Apebi">
            <img src="images/Stellantis-Logo.jpg" alt="Stellantis">
            <img src="images/mp.png" alt="MenaraPrefa">
            <img src="images/ocp.png" alt="OCP Group">
            <img src="images/inwi.png" alt="Inwi">
            <img src="images/Renault.png" alt="Renault">
            <img src="images/cyber.png" alt="Cyber4D">
            <img src="images/Deloitte.png" alt="Deloitte">
        </div>
    </section>


    <!-- Section Contact -->
    <section class="contact-section" id="contact">
        <div class="contact-container">
            <h2><i class="fas fa-envelope"></i> Envoyer un message</h2>
            
            <div class="legal-notice">
                <p>
                    Conformément à la loi 09-08 relative à la protection des personnes physiques à l'égard du traitement des données à caractère personnel, 
                    vous bénéficiez d'un droit d'accès, de rectification et d'opposition aux données vous concernant. 
                    Ce traitement a été autorisé par la Commission Nationale de contrôle de la protection des Données à Caractère Personnel (CNDP) 
                    sous la référence D-W-175/2025. Ce traitement a été autorisé par la CNDP sous le n° : <strong>D-W-175/2025</strong>.
                </p>
            </div>

            <div class="contact-grid">
                <!-- Colonne de contact -->
                <div class="contact-info">
                    <!-- Information de contact -->
                    <div class="contact-block">
                        <h3><i class="fas fa-building"></i> CONTACT</h3>
                        <p>LearnBridge</p>
                        <p>BP 7540 Avenue Abdelkrim Khattabi</p>
                        <p>Guéliz - Marrakech</p>
                        <p><i class="fas fa-phone"></i> (+212) 06 00 00 00 00</p>
                        <p><i class="fas fa-envelope"></i> learnbridge@uca.ac.ma</p>
                    </div>

                    <!-- Réseaux sociaux -->
                    <div class="contact-block">
                        <h3><i class="fas fa-share-alt"></i> RÉSEAUX SOCIAUX</h3>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i> </a>
                            <a href="#"><i class="fab fa-twitter"></i> </a>
                            <a href="#"><i class="fab fa-linkedin-in"></i> </a>
                            <a href="#"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de contact -->
                <div class="contact-form">
                    <form>
                        <div class="form-group">
                            <input type="text" id="prenom" placeholder="Prénom" required>
                        </div>
                        <div class="form-group">
                            <input type="text" id="nom" placeholder="Nom" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                            <textarea id="message" placeholder="Votre message" rows="5" required></textarea>
                        </div>

                        <div class="form-checkbox">
                            <input type="checkbox" id="conditions" required>
                            <label for="conditions">
                                J'ai lu et j'accepte les conditions générales d'utilisation, notamment la mention relative à la protection des données personnelles.
                            </label>
                        </div>

                        <button type="submit" class="submit-btn">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Colonne droite : Carte -->
        <div class="contact-map">
            <h3><i class="fas fa-map-marker-alt"></i> Localisation</h3>
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.5964136288663!2d-8.018106584799595!3d31.63429238133953!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xdafe82c6c5e9f8b%3A0xfbd9f6f8e1b1c6e6!2sENSA%20Marrakech!5e0!3m2!1sfr!2sma!4v1623077106592!5m2!1sfr!2sma" 
                width="100%" 
                height="300" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </section>


  


    <!-- Footer -->
     
    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/logo.png" alt="LearnBridge Marrakech">
                <p>LearnBridge, votre passerelle vers l'excellence en ingénierie à Marrakech.</p>
            </div>
            <div class="footer-links">
                <a href="#top">Accueil</a>
                <a href="#presentation">L'École</a>
                <a href="#formations">Formations</a>
                <a href="#recherche">Recherche</a>
                <a href="admission.html">Admission</a>
                <a href="temoignages.html">Témoignages</a>
                <a href="faq.html">FAQ</a>


        </div>
        <div class="footer-social">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <div class="footer-contact">
                <h3><i class="fas fa-building"></i> CONTACT</h3>

                <p><strong>LearnBridge</strong><br>
                   BP 7540 Avenue Abdelkrim Khattabi<br>
                   Guéliz - Marrakech<br></p>
                   <p><i class="fas fa-phone"></i> (+212) 06 00 00 00 00</p>
                   <p><i class="fas fa-envelope"></i> learnbridge@uca.ac.ma</p>
            </div>
        </div>
        </div>
        <div class="footer-copyright">
            <p>© 2025 LearnBridge - Tous droits réservés</p>
        </div>
    </footer>


<script src="script.js"></script>  
</body>
</html>