<?php
header("Content-Type: application/json");

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../models/Job.php';

// ENFORCE SECURITY: Only Admins can run this script[cite: 1]
requireRole('Admin');

if ($_SERVER['REQUEST_METHOD'] == 'DELETE' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $data = json_decode(file_get_contents("php://input"));
    $job_id = $data->job_id ?? null;

    if (!$job_id) {
        http_response_code(400);
        echo json_encode(["error" => "Job ID is required for deletion."]);
        exit();
    }

    // Admin deletion bypasses the employer_id check
    $query = "DELETE FROM job_vacancies WHERE id = :job_id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([':job_id' => $job_id])) {
        http_response_code(200);
        echo json_encode(["message" => "Admin Action: Inappropriate job posting removed successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete job posting."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Invalid request method."]);
}
?>