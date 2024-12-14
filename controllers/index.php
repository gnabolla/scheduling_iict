<?php
// controllers/index.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/login');
    exit();
}

$config = require('config.php');
$db = new Database($config['database']);

$title = "Dashboard";
$view = "views/index.view.php";
require "views/layout.view.php";
?>
