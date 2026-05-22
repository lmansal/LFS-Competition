# Plataforma Web LFS Competition

**Gestión y seguimiento de competiciones de esports en simuladores de carreras (Live for Speed)**

Plataforma web desarrollada como Proyecto de Fin de Curso del Ciclo Superior de Desarrollo de Aplicaciones Web. Permite la organización y seguimiento de campeonatos de Live for Speed, gestionando pilotos, equipos, carreras, inscripciones, resultados y sanciones.

---

## Características principales

- **Roles de usuario**: Usuario Anónimo, Piloto, Jefe de Equipo y Administrador
- Gestión completa de temporadas y carreras
- Sistema de inscripciones a carreras
- Publicación de resultados con tabla de clasificaciones
- Sistema de sanciones integrado
- Gestión de equipos y solicitudes de unión
- Roles dentro de los equipos (Piloto y Jefe de Equipo)
- Interfaz moderna con modo oscuro y claro (glassmorphism)
- Diseño totalmente responsive

---

## Tecnologías utilizadas

- **Backend**: PHP 8.2
- **Base de datos**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3 y JavaScript (vanilla)
- **Control de sesiones**: PHP Native Sessions
- **Seguridad**: PDO con prepared statements + protección XSS
- **Diseño**: CSS personalizado con glassmorphism y modo oscuro/claro

---

LFS-Competition/
├── admin/                      # Paneles de administración
│   ├── index.php
│   ├── carreras.php
│   ├── equipos.php
│   ├── resultados.php
│   ├── sanciones.php
│   └── usuarios.php
├── includes/                   # Archivos PHP reutilizables
│   ├── header.php
│   ├── footer.php
│   ├── functions.php
│   ├── config.php
│   └── auth.php
├── css/
│   └── estilos.css
├── js/
│   └── scripts.js
├── images/                     # Logos e imágenes
├── docs/                       # Documentación
├── index.php                   # Página principal
├── login.php
├── registro.php
├── calendario.php
├── clasificacion.php
├── inscripcion.php
├── perfil.php
├── equipo.php
└── logout.php

---

## Medidas de seguridad implementadas

- Uso de **PDO** con consultas preparadas para prevenir inyección SQL
- Escapado de salida con `htmlspecialchars()` para protección contra XSS
- Hashing de contraseñas con `password_hash()`
- Validación de roles y permisos en cada página sensible
- Uso de HTTPS en el servidor de producción

---

## Documentación

- [Proyecto completo (PDF)](docs/Proyecto-Fin-de-Curso-Luis-Jesus-Mantecon.pdf)
- [Diagrama de base de datos](https://dbdiagram.io/d/69b3f64d78c6c4bc7ad76cce)

---

## Autor

**Luis Jesús Mantecón Salvador**  
Proyecto de Fin de Curso - Ciclo Superior de Desarrollo de Aplicaciones Web (2026)
