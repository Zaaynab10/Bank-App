document.addEventListener('DOMContentLoaded', () => {
    const statsBoxes = document.querySelectorAll('.stats-box');
    const contentBoxes = document.querySelectorAll('.content-box');

    statsBoxes.forEach(box => {
        box.addEventListener('click', () => {
            // Cache toutes les sections
            contentBoxes.forEach(content => content.classList.remove('active'));

            // Affiche la section correspondant à la boîte cliquée
            const targetId = box.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);

            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });
});
