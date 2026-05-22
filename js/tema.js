// =======================================
// Cambiar entre modo oscuro y modo claro
// =======================================

function toggleTheme() {
    var body = document.body;
    var btn = document.getElementById('theme-btn');
    var isLight = body.getAttribute('data-theme') === 'light';

    if (isLight) {
        body.removeAttribute('data-theme');
        localStorage.setItem('theme', 'dark');
        if (btn) {
            btn.textContent = '☀️';
        }
    } else {
        body.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        if (btn) {
            btn.textContent = '🌙';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var saved = localStorage.getItem('theme');
    var btn = document.getElementById('theme-btn');

    if (saved === 'light') {
        document.body.setAttribute('data-theme', 'light');
        if (btn){
            btn.textContent = '🌙';
        }
    } else {
        if (btn){
            btn.textContent = '☀️';
        }
    }
});
