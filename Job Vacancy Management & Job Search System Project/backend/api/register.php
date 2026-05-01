<?php
header("Content-Type: application/json");

// Include database and model files
require_once '../config/database.php';
require_once '../models/User.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Connect to the database
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Get the JSON data sent from the frontend
    $data = json_decode(file_get_contents("php://input"));
    
    $username = $data->username ?? '';
    $password = $data->password ?? '';
    
    // In our database schema, role_id 1 is 'Employer'. 
    // We will default to 1 so people registering are automatically Employers.
    $role_id = $data->role_id ?? 1; 

    // Basic validation
    if(empty($username) || empty($password)) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Username and password are required."]);
        exit();
    }

    // Attempt to register the user
    try {
        if($user->register($username, $password, $role_id)) {
            http_response_code(201); // 201 Created
            echo json_encode(["message" => "User registered successfully! You can now log in."]);
        } else {
            http_response_code(500); // Server Error
            echo json_encode(["error" => "Registration failed."]);
        }
    } catch (PDOException $e) {
        // If the username already exists, it will throw a database duplicate error
        http_response_code(409); // Conflict
        echo json_encode(["error" => "Registration failed. Username may already exist."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed."]);
}
?>