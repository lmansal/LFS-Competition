// =====================
// JavaScript principal
// =====================

// --- Pestañas (tabs) ---
function showTab(tabName) {
    var contents = document.querySelectorAll('.tab-content');
    for (var i = 0; i < contents.length; i++) {
        contents[i].classList.remove('active');
    }

    var buttons = document.querySelectorAll('.tab-btn');
    for (var j = 0; j < buttons.length; j++) {
        buttons[j].classList.remove('active');
    }

    var panel = document.getElementById('tab-' + tabName);
    var btn = document.querySelector('[data-tab="' + tabName + '"]');

    if (panel) {
        panel.classList.add('active');
    }

    if (btn) {
        btn.classList.add('active');
    }
}

// --- Ventanas emergentes (modales) ---
function openModal(id) {
    var modal = document.getElementById(id);
    if (!modal) {
        return;
    }
    modal.style.display = 'flex';
    requestAnimationFrame(function () {
        modal.classList.add('show');
    });
    document.body.style.overflow = 'hidden'; // evita el scroll de la página
}

function closeModal(id) {
    var modal = document.getElementById(id);
    if (!modal) {
        return;
    }
    modal.classList.remove('show');
    setTimeout(function () {
        modal.style.display = 'none';
    }, 300); // espera 300ms a que se complete la animación
    document.body.style.overflow = 'auto'; // permite el scroll de la página
}

// --- Cerrar modales al hacer clic fuera de ellos ---
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        setTimeout(function () {
            e.target.style.display = 'none';
        }, 300); // espera 300ms a que se complete la animación
        document.body.style.overflow = 'auto'; // permite el scroll de la página
    }
});

// --- Confirmación para acciones "peligrosas" (borrar, etc.) ---
function confirmar(msg) {
    return confirm(msg || '¿Estás seguro de esta acción?');
}

document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.getElementById('nav-toggle');
    var collapse = document.getElementById('nav-collapse');
    if (!toggleBtn || !collapse) {
        return;
    }

    function setOpen(isOpen) {
        collapse.classList.toggle('is-open', isOpen);
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    toggleBtn.addEventListener('click', function () {
        setOpen(!collapse.classList.contains('is-open'));
    });

    collapse.addEventListener('click', function (e) {
        var target = e.target;
        if (target && target.tagName === 'A') {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            setOpen(false);
        }
    });

    var mql = window.matchMedia('(min-width: 1025px)');
    var onMqlChange = function (e) {
        if (e.matches) {
            setOpen(false);
        }
    };
    if (typeof mql.addEventListener === 'function') {
        mql.addEventListener('change', onMqlChange);
    } else if (typeof mql.addListener === 'function') {
        mql.addListener(onMqlChange);
    }
});
