<?php

use Core\Auth;
use Core\Middleware;

Middleware::requireAuth();
Auth::logout();

header('Location: ' . BASE_PATH . '/login');
exit();