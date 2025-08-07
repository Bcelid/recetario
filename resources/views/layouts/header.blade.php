<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Portlogistics</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="{{ asset('css/adminlte.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list"></i>
          </a>
        </li>
        <li class="nav-item d-none d-md-block">
          <a href="/portlogistics/dashboard" class="nav-link">
            <i class="bi bi-house-door-fill"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle fs-4"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li class="dropdown-item-text">
          <strong><?= $_SESSION['username']; ?></strong>
        </li>
        <!-- Más opciones del menú -->
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/portlogistics/logout">Cerrar sesión</a></li>
      </ul>

      </ul>
    </div>
  </nav>
