<?php
// Always start the session to check if the user exists
session_start();

// Function to enforce Role-Based Access Control
function requireRole($requiredRole) {
    // 1. Check if the user is logged in at all
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Security Error: You must be logged in to access this."]);
        exit(); // Stop execution immediately
    }

    // 2. Check if the user has the correct role
    if ($_SESSION['role'] !== $requiredRole) {
        http_response_code(403); // 403 Forbidden
        echo json_encode(["error" => "Security Error: You do not have permission. Requires role: " . $requiredRole]);
        exit(); // Stop execution immediately
    }
}
?>