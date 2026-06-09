<?php

$conn = new mysqli(
    "sql303.infinityfree.com",
    "if0_42136640",
    "Fdj1PvxxJh",
    "if0_42136640_XXX"
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");