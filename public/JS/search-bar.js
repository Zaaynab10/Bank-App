document.addEventListener('DOMContentLoaded', () => {
    const searchBar = document.querySelector('.search-bar input');
    const userListContainer = document.getElementById('userResults');
    const body = document.body;

    if (searchBar) {
        searchBar.addEventListener('input', async (e) => {
            const query = e.target.value.trim();

            try {
                const response = await fetch(`/admin/search?q=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des données.');
                }

                const users = await response.json();
                updateUserList(users);
            } catch (error) {
                console.error('Une erreur est survenue :', error);
                userListContainer.innerHTML = '<p class="error-message">Erreur lors de la recherche, veuillez réessayer.</p>';
            }
        });
    }

    function updateUserList(users) {
        userListContainer.innerHTML = ''; 

        if (users.length === 0) {
            userListContainer.innerHTML = '<p class="no-results">Aucun utilisateur trouvé.</p>';
            return;
        }

        users.forEach(user => {
            const userCard = document.createElement('div');
            userCard.classList.add('user-card');
            userCard.innerHTML = `
<a href="/admin/users/${user.id}/accounts" class="user-link">
                    <img src="${user.profilePicture || '/images/user.svg'}" alt="${user.name || 'Utilisateur'}" class="user-avatar">
                    <div class="user-info">
                        <h3>${user.name || 'Nom indisponible'}</h3>
                        <p>${user.email || 'Email indisponible'}</p>
                    </div>
                </a>
            `;
            userListContainer.appendChild(userCard);
        });

        if (body.classList.contains('dark-mode')) {
            userListContainer.classList.add('dark-mode');
        } else {
            userListContainer.classList.remove('dark-mode');
        }
    }
});
