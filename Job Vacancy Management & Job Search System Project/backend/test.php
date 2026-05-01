<?php
// Include the database configuration file
require_once 'config/database.php';

// Instantiate the Database class
$database = new Database();
$db = $database->getConnection();

if($db) {
    echo json_encode(["message" => "Backend is successfully connected to MySQL!"]);
}
?>