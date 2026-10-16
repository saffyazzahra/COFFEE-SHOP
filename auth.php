<?php
// auth.php — include on the FIRST line of every protected page
session_start();

function is_admin() {
    //if user_id and role exist in session before comparing
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function require_admin() {
    if (!is_admin()) {
        http_response_code(403);
        die("403 - Access denied. This page is for admins only.");
    }
}
?>