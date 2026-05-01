<?php
header("Content-Type: application/json");

require_once '../config/database.php';
require_once '../config/security.php';
require_once '../models/Job.php';

// ENFORCE SECURITY: Only Employers can view their dashboard
requireRole('Employer');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $database = new Database();
    $db = $database->getConnection();
    $job = new Job($db);

    $employer_id = $_SESSION['user_id'];
    
    // Fetch jobs
    $myJobs = $job->getJobsByEmployer($employer_id);
    
    http_response_code(200);
    echo json_encode(["jobs" => $myJobs]);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Only GET requests are allowed."]);
}
?>