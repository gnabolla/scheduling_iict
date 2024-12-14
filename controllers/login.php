<?php

use Core\Auth;
use Core\Middleware;

Middleware::requireGuest();

$config = require('config.php');
$db = new Database($config['database']);
$auth = new Auth($db);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        header('Location: ' . BASE_PATH . '/');
        exit();
    }
    
    $errors = $result['errors'];
}

$title = 'Login';
require "views/login.view.php";