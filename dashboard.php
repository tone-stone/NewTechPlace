<?php
/**
 * Dashboard - Página principal
 * Página protegida que requiere autenticación
 */

session_start();

// Verificar si el usuario está autenticado
if (empty($_SESSION['usuario_id']) || empty($_SESSION['usuario_nombre'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
require_once 'db.php';

$db = new Database();
$usuario_actual = [
    'id' => $_SESSION['usuario_id'],
    'nombre' => $_SESSION['usuario_nombre'],
    'email' => $_SESSION['usuario_email'] ?? 'usuario@techplace.com',
    'inicial' => strtoupper($_SESSION['usuario_nombre'][0])
];

$db->cerrar();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TechPlace | Dashboard</title>
    <link rel="icon" href="assets/img/logos/Techplace logos-05.png" type="image/png">
    
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Orbitron Font -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap" rel="stylesheet">
    <!-- Animate CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            background: #09090f !important;
            color: #fff;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        /* Colores temáticos */
        :root {
            --primary: #c084fc;
            --primary-dark: #a21caf;
            --primary-light: #d8b4fe;
            --secondary: #9333ea;
            --bg-dark: #09090f;
            --bg-card: #1a1a2e;
            --bg-card-hover: #252542;
            --text-primary: #fff;
            --text-secondary: #b0b0c0;
            --border-color: #2d2d4a;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #09090f;
        }

        ::-webkit-scrollbar-thumb {
            background: #c084fc;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a21caf;
        }

        /* === SIDEBAR === */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            border-right: 1px solid var(--border-color);
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.closed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #c084fc, #a21caf);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-nav {
            padding: 2rem 0;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            position: relative;
        }

        .nav-item:hover,
        .nav-item.active {
            color: var(--primary);
            background: rgba(192, 132, 252, 0.1);
            border-left-color: var(--primary);
        }

        .nav-item i {
            width: 24px;
            text-align: center;
        }

        .nav-section-title {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 1px;
        }

        /* === MAIN CONTENT === */
        .main-content {
            margin-left: 280px;
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            background: var(--bg-dark);
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* === TOPBAR === */
        .topbar {
            background: rgba(16, 16, 32, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex: 1;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--primary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .menu-toggle:hover {
            transform: scale(1.1);
        }

        .search-box {
            flex: 1;
            max-width: 400px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            color: var(--text-primary);
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .search-box input::placeholder {
            color: var(--text-secondary);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 15px rgba(192, 132, 252, 0.2);
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            pointer-events: none;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #c084fc, #a21caf);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1rem;
        }

        .logout-btn {
            background: rgba(192, 132, 252, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: var(--primary);
            color: #000;
        }

        /* === CONTENT AREA === */
        .content-area {
            padding: 2rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #c084fc, #e879f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        /* === ESTADÍSTICAS === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .stat-card:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(192, 132, 252, 0.1) 0%, rgba(162, 28, 175, 0.05) 100%);
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(192, 132, 252, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #c084fc, #a21caf);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }

        .stat-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(192, 132, 252, 0.2);
            border-top: 3px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* === FILTROS === */
        .filters-section {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 600;
        }

        .filter-select {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            color: var(--text-primary);
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(192, 132, 252, 0.2);
        }

        .filter-select option {
            background: #1a1a2e;
            color: var(--text-primary);
        }

        .btn-reset {
            background: rgba(192, 132, 252, 0.1);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: auto;
        }

        .btn-reset:hover {
            background: var(--primary);
            color: #000;
        }

        /* === USUARIOS CARDS === */
        .users-section {
            margin-top: 2rem;
        }

        .users-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .users-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .btn-add-user {
            background: linear-gradient(135deg, #c084fc, #a21caf);
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-add-user:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(192, 132, 252, 0.3);
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .user-card {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f0f23 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .user-card:nth-child(1) { animation-delay: 0.05s; }
        .user-card:nth-child(2) { animation-delay: 0.1s; }
        .user-card:nth-child(3) { animation-delay: 0.15s; }
        .user-card:nth-child(4) { animation-delay: 0.2s; }
        .user-card:nth-child(5) { animation-delay: 0.25s; }
        .user-card:nth-child(6) { animation-delay: 0.3s; }

        .user-card:hover {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(192, 132, 252, 0.1) 0%, rgba(162, 28, 175, 0.05) 100%);
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(192, 132, 252, 0.2);
        }

        .user-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #c084fc, #a21caf, transparent);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .user-card:hover::before {
            transform: translateX(0);
        }

        .user-card-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .user-avatar-large {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: linear-gradient(135deg, #c084fc, #a21caf);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .user-status {
            font-size: 0.75rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            display: inline-block;
        }

        .status-badge.inactive {
            background: #ef4444;
        }

        .user-card-body {
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 0;
            margin-bottom: 1rem;
        }

        .user-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .user-detail:last-child {
            margin-bottom: 0;
        }

        .user-detail i {
            width: 16px;
            color: var(--primary);
            flex-shrink: 0;
        }

        .user-detail-text {
            color: var(--text-secondary);
            word-break: break-word;
        }

        .user-card-footer {
            display: flex;
            gap: 0.75rem;
        }

        .btn-action {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.05);
            color: var(--primary);
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            background: rgba(192, 132, 252, 0.1);
            border-color: var(--primary);
            transform: scale(1.05);
        }

        .btn-action.danger {
            color: #ef4444;
            border-color: #ef4444;
        }

        .btn-action.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }

        /* === EMPTY STATE === */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        /* === ANIMACIONES === */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* === TOAST NOTIFICATION === */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #1a1a2e;
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.3s ease-out;
            z-index: 2000;
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast.success {
            border-left-color: #10b981;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                margin-left: 240px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                max-width: 280px;
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .content-area {
                padding: 1rem;
            }

            .topbar {
                padding: 1rem;
                gap: 1rem;
            }

            .topbar-left {
                gap: 1rem;
            }

            .search-box {
                max-width: 100%;
            }

            .topbar-right {
                gap: 1rem;
            }

            .btn-reset {
                margin-left: 0;
            }

            .filters-section {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex: 1;
            }

            .filter-select {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .users-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .user-card-body {
                padding: 0.75rem 0;
            }

            .toast {
                right: 1rem;
                left: 1rem;
            }
        }

        @media (max-width: 480px) {
            .topbar {
                flex-direction: column;
                align-items: stretch;
            }

            .topbar-left {
                flex-direction: column;
            }

            .search-box {
                max-width: 100%;
            }

            .topbar-right {
                justify-content: flex-end;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .user-card-footer {
                flex-direction: column;
            }

            .btn-action {
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="./assets/img/logos/Techplace logos-02.webp" alt="TechPlace Logo" >
            </div>
        </div>

        <div class="sidebar-nav">
            <div class="nav-section-title">Menú Principal</div>
            <div class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </div>

            <div class="nav-section-title">Otros</div>
            <div class="nav-item">
                <i class="fas fa-file-alt"></i>
                <span>Reportes</span>
            </div>
            <div class="nav-item">
                <i class="fas fa-life-ring"></i>
                <span>Soporte</span>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content" id="mainContent">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar usuario...">
                </div>
            </div>

            <div class="topbar-right">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo $usuario_actual['inicial']; ?></div>
                    <span><?php echo $usuario_actual['nombre']; ?></span>
                </div>
                <button class="logout-btn" id="logoutBtn">
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </button>
            </div>
        </div>

        <!-- CONTENT AREA -->
        <div class="content-area">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <h1 class="page-title">Dashboard de Usuarios</h1>
                <p class="page-subtitle">Gestión completa de usuarios del sistema</p>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="totalUsers">0</div>
                    <div class="stat-label">Total de Usuarios</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value" id="activeUsers">0</div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-value" id="inactiveUsers">0</div>
                    <div class="stat-label">Usuarios Inactivos</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value" id="thisMonthUsers">0</div>
                    <div class="stat-label">Este Mes</div>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="filters-section">
                <div class="filter-group">
                    <label class="filter-label">Estado:</label>
                    <select class="filter-select" id="filterStatus">
                        <option value="">Todos</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Ordenar por:</label>
                    <select class="filter-select" id="filterSort">
                        <option value="fecha">Más Reciente</option>
                        <option value="nombre">Nombre A-Z</option>
                        <option value="nombre-desc">Nombre Z-A</option>
                    </select>
                </div>

                <button class="btn-reset" id="btnResetFilters">
                    <i class="fas fa-redo"></i>
                    Reiniciar Filtros
                </button>
            </div>

            <!-- USUARIOS CARDS -->
            <div class="users-section">
                <div class="users-header">
                    <h2 class="users-title">Usuarios del Sistema</h2>
                    <button class="btn-add-user" id="btnAddUser">
                        <i class="fas fa-plus"></i>
                        Agregar Usuario
                    </button>
                </div>

                <div class="users-grid" id="usersGrid">
                    <div class="loading">
                        <div class="loading-spinner"></div>
                        <p>Cargando usuarios...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT -->
    <script>
        // === VARIABLES GLOBALES ===
        let usuariosOriginales = [];
        let usuariosFiltrados = [];
        const API_URL = 'api_dashboard.php';

        // === FUNCIONES DE UTILIDAD ===
        function mostrarToast(mensaje, tipo = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${tipo}`;
            toast.textContent = mensaje;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        async function hacerPeticion(url, opciones = {}) {
            try {
                const respuesta = await fetch(url, {
                    ...opciones,
                    headers: {
                        'Content-Type': 'application/json',
                        ...opciones.headers
                    }
                });

                if (!respuesta.ok) {
                    const error = await respuesta.json();
                    throw new Error(error.error || `Error ${respuesta.status}`);
                }

                return await respuesta.json();
            } catch (error) {
                console.error('Error en petición:', error);
                mostrarToast(error.message, 'error');
                throw error;
            }
        }

        // === FUNCIONES DE DATOS ===
        async function cargarEstadisticas() {
            try {
                const resultado = await hacerPeticion(`${API_URL}?accion=obtener_estadisticas`);
                
                if (resultado.success) {
                    const stats = resultado.data;
                    document.getElementById('totalUsers').textContent = stats.total;
                    document.getElementById('activeUsers').textContent = stats.activos;
                    document.getElementById('inactiveUsers').textContent = stats.inactivos;
                    document.getElementById('thisMonthUsers').textContent = stats.este_mes;
                }
            } catch (error) {
                console.error('Error cargando estadísticas:', error);
            }
        }

        async function cargarUsuarios() {
            try {
                const resultado = await hacerPeticion(`${API_URL}?accion=obtener_usuarios`);
                
                if (resultado.success) {
                    usuariosOriginales = resultado.data;
                    usuariosFiltrados = [...usuariosOriginales];
                    renderizarUsuarios();
                }
            } catch (error) {
                console.error('Error cargando usuarios:', error);
                document.getElementById('usersGrid').innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Error al cargar usuarios</p>
                    </div>
                `;
            }
        }

        function renderizarUsuarios(usuarios = usuariosFiltrados) {
            const grid = document.getElementById('usersGrid');

            if (usuarios.length === 0) {
                grid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <i class="fas fa-inbox"></i>
                        <p>No hay usuarios para mostrar</p>
                        <p style="font-size: 0.75rem;">Intenta cambiar los filtros</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = usuarios.map((usuario, index) => `
                <div class="user-card" style="animation-delay: ${index * 0.05}s">
                    <div class="user-card-header">
                        <div class="user-avatar-large">${usuario.nombre.charAt(0).toUpperCase()}</div>
                        <div class="user-info">
                            <div class="user-name">${usuario.nombre}</div>
                            <div class="user-status">
                                <span class="status-badge ${usuario.estado === 'inactivo' ? 'inactive' : ''}"></span>
                                ${usuario.estado === 'activo' ? 'Activo' : 'Inactivo'}
                            </div>
                        </div>
                    </div>

                    <div class="user-card-body">
                        <div class="user-detail">
                            <i class="fas fa-envelope"></i>
                            <span class="user-detail-text">${usuario.email}</span>
                        </div>
                        <div class="user-detail">
                            <i class="fas fa-phone"></i>
                            <span class="user-detail-text">${usuario.telefono || 'No especificado'}</span>
                        </div>
                    </div>

                    <div class="user-card-footer">
                        <button class="btn-action" onclick="editarUsuario(${usuario.id})">
                            <i class="fas fa-edit"></i>
                            Editar
                        </button>
                        <button class="btn-action danger" onclick="eliminarUsuario(${usuario.id})">
                            <i class="fas fa-trash"></i>
                            Eliminar
                        </button>
                    </div>
                </div>
            `).join("");
        }

        function filtrarUsuarios() {
            const busqueda = document.getElementById('searchInput').value.toLowerCase();
            const estado = document.getElementById('filterStatus').value;
            const orden = document.getElementById('filterSort').value;

            usuariosFiltrados = usuariosOriginales.filter(usuario => {
                const coincideBusqueda = usuario.nombre.toLowerCase().includes(busqueda) ||
                                        usuario.email.toLowerCase().includes(busqueda) ||
                                        (usuario.telefono && usuario.telefono.includes(busqueda));
                const coincideEstado = !estado || usuario.estado === estado;
                return coincideBusqueda && coincideEstado;
            });

            // Aplicar ordenamiento
            if (orden === 'nombre') {
                usuariosFiltrados.sort((a, b) => a.nombre.localeCompare(b.nombre));
            } else if (orden === 'nombre-desc') {
                usuariosFiltrados.sort((a, b) => b.nombre.localeCompare(a.nombre));
            }

            renderizarUsuarios();
        }

        async function editarUsuario(id) {
            alert(`Función de edición para usuario #${id} - Se implementará en la siguiente fase`);
        }

        async function eliminarUsuario(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                return;
            }

            try {
                const resultado = await hacerPeticion(`${API_URL}?accion=eliminar_usuario&id=${id}`, {
                    method: 'DELETE'
                });

                if (resultado.success) {
                    mostrarToast('Usuario eliminado exitosamente', 'success');
                    await cargarEstadisticas();
                    await cargarUsuarios();
                }
            } catch (error) {
                console.error('Error eliminando usuario:', error);
            }
        }

        // === EVENT LISTENERS ===
        document.getElementById('menuToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('closed');
            mainContent.classList.toggle('expanded');
        });

        document.getElementById('searchInput').addEventListener('input', filtrarUsuarios);
        document.getElementById('filterStatus').addEventListener('change', filtrarUsuarios);
        document.getElementById('filterSort').addEventListener('change', filtrarUsuarios);

        document.getElementById('btnResetFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterSort').value = 'fecha';
            filtrarUsuarios();
        });

        document.getElementById('btnAddUser').addEventListener('click', function() {
            alert('Función para agregar usuario - Se implementará en la siguiente fase');
        });

        document.getElementById('logoutBtn').addEventListener('click', function() {
            if (confirm('¿Deseas cerrar sesión?')) {
                window.location.href = 'logout.php';
            }
        });

        // === INICIALIZACIÓN ===
        window.addEventListener('load', async function() {
            await cargarEstadisticas();
            await cargarUsuarios();
        });
    </script>
</body>
</html>
<?php
$db->cerrar();
?>
