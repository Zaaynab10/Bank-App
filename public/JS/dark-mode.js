document.addEventListener('DOMContentLoaded', () => {
    const themeIcon = document.getElementById('themeIcon');
    const video = document.querySelector('.background-video');
    const sideVideo = document.querySelector('.sidebar video');
    const footVideo = document.querySelector('.footer-video');
    const logos = document.querySelectorAll('.bank-logo');

    // Function to toggle themes
    function toggleTheme() {
        const isDarkMode = document.body.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', isDarkMode ? 'enabled' : 'disabled');

        // Change the theme icon
        if (themeIcon) {
            themeIcon.src = isDarkMode ? '/images/moon.svg' : '/images/sun.svg';
            themeIcon.style.filter = isDarkMode ? 'invert(100%)' : 'invert(0%)';
        }

        // Change all logos
        logos.forEach(logo => {
            logo.src = isDarkMode ? '/images/logo-sombre.png' : '/images/logo-clair.png';
        });

        // Change the main background video
        if (video) {
            const videoSrc = isDarkMode ? '/videos/dark-background.mp4' : '/videos/light-background.mp4';
            video.src = `${videoSrc}?v=${new Date().getTime()}`;
        }

        // Change the sidebar video
        if (sideVideo) {
            const sideVideoSrc = isDarkMode ? '/videos/dark-side.mp4' : '/videos/light-side.mp4';
            sideVideo.src = `${sideVideoSrc}?v=${new Date().getTime()}`;
        }

        // Change the footer video
        if (footVideo) {
            const footVideoSrc = isDarkMode ? '/videos/light-foot.mp4' : '/videos/dark-foot.mp4';
            footVideo.src = `${footVideoSrc}?v=${new Date().getTime()}`;
        }
    }

    // Apply saved dark mode state on page load
    const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
    document.body.classList.toggle('dark-mode', isDarkMode);

    // Set the initial theme icon
    if (themeIcon) {
        themeIcon.src = isDarkMode ? '/images/moon.svg' : '/images/sun.svg';
        themeIcon.style.filter = isDarkMode ? 'invert(100%)' : 'invert(0%)';
    }

    // Set the initial logos
    logos.forEach(logo => {
        logo.src = isDarkMode ? '/images/logo-sombre.png' : '/images/logo-clair.png';
    });

    // Set the initial background video
    if (video) {
        const videoSrc = isDarkMode ? '/videos/dark-background.mp4' : '/videos/light-background.mp4';
        video.src = `${videoSrc}?v=${new Date().getTime()}`;
    }

    // Set the initial sidebar video
    if (sideVideo) {
        const sideVideoSrc = isDarkMode ? '/videos/dark-side.mp4' : '/videos/light-side.mp4';
        sideVideo.src = `${sideVideoSrc}?v=${new Date().getTime()}`;
    }

    // Set the initial footer video
    if (footVideo) {
        const footVideoSrc = isDarkMode ? '/videos/light-foot.mp4' : '/videos/dark-foot.mp4';
        footVideo.src = `${footVideoSrc}?v=${new Date().getTime()}`;
    }

    // Attach event listener to the theme icon
    if (themeIcon) {
        themeIcon.addEventListener('click', toggleTheme);
    }
});
