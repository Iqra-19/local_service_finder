<?php
require_once __DIR__ . '/config/session.php';
if (isLoggedIn()) {
    redirectByRole();
} else {
    header('Location: pages/login.php');
    exit;
}
