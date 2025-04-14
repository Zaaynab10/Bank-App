// Add click animation for buttons
const buttons = document.querySelectorAll('.btn-green');

buttons.forEach((button) => {
    button.addEventListener('click', (event) => {
        const target = event.target;

        // Apply click animation
        target.style.transform = 'scale(0.9)';
        target.style.transition = 'transform 0.3s ease';

        // Reset to hover animation after the click
        setTimeout(() => {
            target.style.transform = 'scale(1.1)';
        }, 100); // Match the duration of the transition
    });
});
