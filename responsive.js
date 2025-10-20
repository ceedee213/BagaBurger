document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('.nav-toggle');
    const body = document.body;

    navToggle.addEventListener('click', () => {
        body.classList.toggle('nav-open');
    });
});