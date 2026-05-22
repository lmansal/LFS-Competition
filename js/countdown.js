// ====================================
// Cuenta atrás para el próximo evento
// ====================================

function iniciarCountdown(targetISO, containerId) {
    var container = document.getElementById(containerId);
    if (!container || !targetISO) {
        return;
    }

    function parseTarget(input) {
        var str = String(input).trim();

        if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
            var parts = str.split('-');
            var y = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
            var d = parseInt(parts[2], 10);
            return new Date(y, m - 1, d, 23, 59, 59).getTime();
        }

        var match = str.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/);
        if (match) {
            var y = parseInt(match[1], 10);
            var mo = parseInt(match[2], 10);
            var d = parseInt(match[3], 10);
            var h = parseInt(match[4], 10);
            var mi = parseInt(match[5], 10);
            var sStr = match[6] ? match[6] : '0';
            var s = parseInt(sStr, 10);
            return new Date(y, mo - 1, d, h, mi, s).getTime();
        }

        return new Date(str).getTime();
    }

    var target = parseTarget(targetISO);
    if (isNaN(target)) {
        return;
    }

    function pad2(value) {
        var s = String(value);
        while (s.length < 2) {
            s = '0' + s;
        }
        return s;
    }

    function actualizar() {
        var ahora = new Date().getTime();
        var diff = target - ahora;

        if (diff <= 0) {
            container.innerHTML = '<div class="countdown-live">¡EN PISTA! 🟢</div>';
            return;
        }

        var dias = Math.floor(diff / (1000 * 60 * 60 * 24));
        var horas = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutos = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        var segundos = Math.floor((diff % (1000 * 60)) / 1000);

        var items = [
            { val: dias, label: 'Días' },
            { val: horas, label: 'Horas' },

            { val: minutos,  label: 'Min' },
            { val: segundos, label: 'Seg' },
        ];

        var html = '';
        for (var i = 0; i < items.length; i++) {
            html += '<div class="countdown-item">';
            html += '<div class="number">' + pad2(items[i].val) + '</div>';
            html += '<div class="label">' + items[i].label + '</div>';
            html += '</div>';
        }
        container.innerHTML = html;

        requestAnimationFrame(function () {
            setTimeout(actualizar, 1000);
        });
    }

    actualizar();
}
