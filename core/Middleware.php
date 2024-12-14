<?php

namespace Core;

class Middleware {
    public static function requireAuth() {
        if (!Auth::check()) {
            header('Location: ' . BASE_PATH . '/login');
            exit();
        }
    }
    
    public static function requireGuest() {
        if (Auth::check()) {
            header('Location: ' . BASE_PATH . '/');
            exit();
        }
    }
}