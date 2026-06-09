<?php

$isLocal = $_SERVER['SERVER_NAME'] === 'localhost';

if ($isLocal) {
    $conn = new mysqli("localhost", "root", "", "blog");
} else {
    $conn = new mysqli(
        "sql303.infinityfree.com",
        "if0_42136640",
        "Fdj1PvxxJh",
        "if0_42136640_blog"
    );
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}