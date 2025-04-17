// JavaScript to handle the sliding navigation and icon animation

document.addEventListener('DOMContentLoaded', () => {
    const adminToggle = document.getElementById('adminToggle');
    const hiddenNav = document.querySelector('.hidden-nav');
    const navIcons = document.querySelectorAll('.hidden-nav .nav-icon');

    adminToggle.addEventListener('click', () => {
        // Toggle the nav-visible class to make the nav slide in/out
        hiddenNav.classList.toggle('nav-visible');

        // Trigger icon animation
        if (hiddenNav.classList.contains('nav-visible')) {
            navIcons.forEach((icon, index) => {
                setTimeout(() => {
                    icon.style.transform = 'scale(1)';
                    icon.style.opacity = '1';
                }, index * 100); // Staggered animation
            });
        } else {
            navIcons.forEach(icon => {
                icon.style.transform = 'scale(0)';
                icon.style.opacity = '0';
            });
        }
    });

    // Initialize icons to small size and invisible
    navIcons.forEach(icon => {
        //icon.style.transform = 'scale(0)';
        //icon.style.opacity = '0';
        icon.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
    });
});