<?php
session_start();
require "config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = (int) $_GET['id'];

/*
Delete posts first
*/
$conn->query("DELETE FROM posts WHERE user_id = $user_id");

/*
Delete user
*/
$conn->query("DELETE FROM users WHERE id = $user_id");

/*
Logout
*/
session_destroy();

header("Location: auth/login.php");
exit;