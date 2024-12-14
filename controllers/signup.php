<?php

use Core\Auth;
use Core\Middleware;

Middleware::requireGuest();

$config = require('config.php');
$db = new Database($config['database']);
$auth = new Auth($db);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->signup([
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm' => isset($_POST['confirm'])
    ]);
    
    if ($result['success']) {
        header('Location: ' . BASE_PATH . '/login');
        exit();
    }
    
    $errors = $result['errors'];
}

$title = 'Sign Up';
require "views/signup.view.php";