<?php
header("Content-Type: application/json");

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../models/Job.php';

// ENFORCE SECURITY: Only logged-in Employers can create jobs
requireRole('Employer');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $job = new Job($db);

    // Get the JSON data sent from the frontend
    $data = json_decode(file_get_contents("php://input"));

    // ASSIGNMENT CONSTRAINT: Maximum 5 required skills per job
    if (isset($data->skills) && count($data->skills) > 5) {
        http_response_code(400); // Bad Request
        echo json_encode(["error" => "Employers may select up to a maximum of 5 required skills."]);
        exit();
    }

    // Pass the Employer's ID directly from their secure session, NOT from the frontend
    $employer_id = $_SESSION['user_id'];

    if ($job->create($employer_id, $data)) {
        http_response_code(201); // Created
        echo json_encode(["message" => "Job vacancy created successfully!"]);
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(["error" => "Database error: Unable to create job vacancy."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed."]);
}
?>