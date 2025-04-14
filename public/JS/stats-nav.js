document.addEventListener('DOMContentLoaded', () => {
    const navButtons = document.querySelectorAll('.stats-icon');
    const detailsSections = document.querySelectorAll('.details-content');

    navButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Identifier la section cible
            const targetId = button.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);

            // Réinitialiser toutes les sections
            detailsSections.forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none'; // Masquer toutes les sections
            });

            // Afficher uniquement la section cible
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.style.display = 'block'; // Afficher la section cible
            }
        });
    });
});
