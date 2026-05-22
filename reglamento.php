<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$titulo_pagina = 'Reglamento — LFS Competition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>📋 Reglamento</h1>
    <p>Normas oficiales de la competición</p>
</div>

<div class="container max-w-800 pb-40">
    <div class="card card-accent mb-3">
        <h2 class="text-accent mb-2">1. Normas Generales</h2>
        <ul class="list-clean flex flex-col gap-10 text-muted fs-14">
            <li>🔹 Todos los participantes deben tener una cuenta registrada en la plataforma.</li>
            <li>🔹 Es obligatorio estar inscrito en la carrera antes de la hora de inicio.</li>
            <li>🔹 El idioma oficial de la competición es el español.</li>
            <li>🔹 Se requiere un comportamiento respetuoso en todo momento.</li>
            <li>🔹 Las decisiones de los comisarios son inapelables.</li>
        </ul>
    </div>

    <div class="card card-accent mb-3">
        <h2 class="text-accent mb-2">2. Conducta en Pista</h2>
        <ul class="list-clean flex flex-col gap-10 text-muted fs-14">
            <li>🏁 No se permite bloqueo intencionado.</li>
            <li>🏁 Las maniobras de adelantamiento deben ser limpias y seguras.</li>
            <li>🏁 Si causas un accidente, debes esperar al piloto afectado.</li>
            <li>🏁 Está prohibido cortar la pista para ganar ventaja.</li>
            <li>🏁 Los pilotos rezagados deben facilitar el paso a los líderes.</li>
        </ul>
    </div>

    <div class="card card-accent mb-3">
        <h2 class="text-danger mb-2">3. Sistema de Sanciones</h2>
        <ul class="list-clean flex flex-col gap-10 text-muted fs-14">
            <li>⚠️ <strong>Aviso:</strong> Infracción leve, sin penalización de puntos.</li>
            <li>⚠️ <strong>Penalización de tiempo:</strong> +5s, +10s o +30s añadidos al tiempo final.</li>
            <li>⚠️ <strong>Penalización de posiciones:</strong> Pérdida de 3-5 posiciones en parrilla.</li>
            <li>⚠️ <strong>Descalificación (DSQ):</strong> Exclusión de la carrera sin puntos.</li>
            <li>⚠️ <strong>Suspensión:</strong> Inhabilitación temporal de 1 o más carreras.</li>
        </ul>
    </div>

    <div class="card card-accent">
        <h2 class="text-gold mb-2">4. Sistema de Puntuación</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Posición</th><th>Puntos</th></tr></thead>
                <tbody>
                    <tr><td class="rank-gold">🥇 1º</td><td><strong>25</strong></td></tr>
                    <tr><td class="rank-silver">🥈 2º</td><td><strong>18</strong></td></tr>
                    <tr><td class="rank-bronze">🥉 3º</td><td><strong>15</strong></td></tr>
                    <tr><td>4º</td><td>12</td></tr>
                    <tr><td>5º</td><td>10</td></tr>
                    <tr><td>6º</td><td>8</td></tr>
                    <tr><td>7º</td><td>6</td></tr>
                    <tr><td>8º</td><td>4</td></tr>
                    <tr><td>9º</td><td>2</td></tr>
                    <tr><td>10º</td><td>1</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
