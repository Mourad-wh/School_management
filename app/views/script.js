// (pour basculer l'affichage du menu)Fonction toggleMenu – Pour afficher / cacher le menu de navigation (mobile)

function toggleMenu() {
    const navLinks = document.querySelector('.nav-links ul');
    navLinks.classList.toggle('show');
}






// surligner dynamiquement le lien de navigation actif en fonction  de la section actuellement visible à l'écran pendant le défilement de la page.//
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-links a');

    window.addEventListener('scroll', function() {
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;

            if (pageYOffset >= (sectionTop - 100)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
});
 

    