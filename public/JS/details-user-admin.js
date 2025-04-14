document.addEventListener('DOMContentLoaded', () => {
    const collapsibleHeaders = document.querySelectorAll('.collapsible-header');
    const detailContainer = document.getElementById('detailContainer');
    const dynamicContent = document.getElementById('dynamicContent');
    const detailTitle = document.getElementById('detailTitle');

    collapsibleHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const dataType = header.dataset.type;
            const title = header.dataset.title;

            // Met à jour le titre
            detailTitle.textContent = title;

            // Génère le contenu en fonction du type
            dynamicContent.innerHTML = dataType === 'accounts'
                ? '<p>Chargement des comptes bancaires...</p>'
                : '<p>Chargement des transactions...</p>';

            // Affiche le conteneur
            detailContainer.classList.add('active');
        });
    });

    // Ajout d’un événement pour fermer le conteneur (par exemple, clic à l'extérieur)
    document.addEventListener('click', (e) => {
        if (e.target.closest('.detail-container') === null && !e.target.classList.contains('collapsible-header')) {
            detailContainer.classList.remove('active');
        }
    });
});
