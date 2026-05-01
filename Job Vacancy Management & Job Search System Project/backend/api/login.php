<?php
// Start the session to track the logged-in user
session_start();
header("Content-Type: application/json");

require_once '../config/database.php';
require_once '../models/User.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Get the JSON data sent from the frontend
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username ?? '';
    $password = $data->password ?? '';

    $loggedInUser = $user->login($username, $password);

    if ($loggedInUser) {
        // Store user ID and Role in the server-side session
        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['role'] = $loggedInUser['role'];
        
        echo json_encode(["message" => "Login successful", "role" => $loggedInUser['role']]);
    } else {
        http_response_code(401); // 401 Unauthorized
        echo json_encode(["error" => "Invalid username or password"]);
    }
}
?>