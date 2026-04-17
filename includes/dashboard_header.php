<?php
require_once __DIR__ . '/../config/session.php';
requireLogin();

// Ensure user data is set and accessible
$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['user_role'] ?? '';
$initials = strtoupper(substr($userName, 0, 1));
$flash = getFlash();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> - LocalService Finder</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <style>
      .table-clean th { border-top: none; text-transform: uppercase; font-size: 0.8rem; color: #6c757d; }
      .badge-pending { background-color: #ffc107; color: #000; }
      .badge-accepted { background-color: #0d6efd; color: #fff; }
      .badge-completed { background-color: #198754; color: #fff; }
      .badge-rejected { background-color: #dc3545; color: #fff; }
      .badge-cancelled { background-color: #6c757d; color: #fff; }
  </style>
<body>
<div class="dashboard-wrapper">
