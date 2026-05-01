<?php
header("Content-Type: application/json");

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../models/Job.php';

// ENFORCE SECURITY: Only Employers can update jobs
requireRole('Employer');

if ($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $job = new Job($db);

    $data = json_decode(file_get_contents("php://input"));
    $job_id = $data->job_id ?? null;
    $employer_id = $_SESSION['user_id'];

    if (!$job_id) {
        http_response_code(400);
        echo json_encode(["error" => "Job ID is required."]);
        exit();
    }

    // Try to update. If it fails, it's likely a security block.
    if ($job->update($job_id, $employer_id, $data)) {
        http_response_code(200);
        echo json_encode(["message" => "Job updated successfully!"]);
    } else {
        http_response_code(403); // Forbidden
        echo json_encode(["error" => "Failed to update. This job does not exist or you do not have permission to edit it."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only PUT/POST requests are allowed."]);
}
?>