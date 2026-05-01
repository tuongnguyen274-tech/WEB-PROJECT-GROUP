<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Secure Login Method
    public function login($username, $password) {
        // Prepare query to fetch user and their role name safely
        $query = "SELECT u.id, u.password_hash, r.role_name 
                  FROM users u
                  JOIN roles r ON u.role_id = r.id
                  WHERE u.username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify the password against the encrypted hash in the database
            if(password_verify($password, $row['password_hash'])) {
                return [
                    "id" => $row['id'],
                    "role" => $row['role_name']
                ];
            }
        }
        return false; // Login failed
    }

    // Secure Registration Method
    public function register($username, $password, $role_id) {
        $query = "INSERT INTO users (username, password_hash, role_id) VALUES (:username, :password_hash, :role_id)";
        $stmt = $this->conn->prepare($query);

        //Hash the password before saving to the database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password_hash", $hashed_password);
        $stmt->bindParam(":role_id", $role_id);

        return $stmt->execute();
    }
}
?>