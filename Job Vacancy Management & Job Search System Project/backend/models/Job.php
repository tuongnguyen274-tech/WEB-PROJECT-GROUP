<?php
class Job {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Function to create a new job posting
    public function create($employer_id, $data) {
        try {
            // Start a transaction
            $this->conn->beginTransaction();

            // 1. Insert the main job details (Using normalized IDs)
            $query = "INSERT INTO job_vacancies 
                        (employer_id, title_id, category_id, employment_type_id, country_id, city_id, work_arrangement_id, salary_range_id, description)
                      VALUES 
                        (:employer_id, :title_id, :category_id, :employment_type_id, :country_id, :city_id, :work_arrangement_id, :salary_range_id, :description)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':employer_id' => $employer_id,
                ':title_id' => $data->title_id ?? null,
                ':category_id' => $data->category_id ?? null,
                ':employment_type_id' => $data->employment_type_id ?? null,
                ':country_id' => $data->country_id ?? null,
                ':city_id' => $data->city_id ?? null,
                ':work_arrangement_id' => $data->work_arrangement_id ?? null,
                ':salary_range_id' => $data->salary_range_id ?? null,
                ':description' => $data->description ?? ''
            ]);

            // Get the ID of the newly created job
            $job_id = $this->conn->lastInsertId();

            // 2. Insert the required skills (Many-to-Many relationship)
            if (!empty($data->skills) && is_array($data->skills)) {
                $skillQuery = "INSERT INTO job_skills (job_id, skill_id, min_proficiency_id) VALUES (:job_id, :skill_id, :min_proficiency_id)";
                $skillStmt = $this->conn->prepare($skillQuery);

                foreach ($data->skills as $skill) {
                    $skillStmt->execute([
                        ':job_id' => $job_id,
                        ':skill_id' => $skill->skill_id,
                        ':min_proficiency_id' => $skill->min_proficiency_id
                    ]);
                }
            }

            // If everything was successful, commit the changes to the database
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // If any error occurs, rollback the database to its previous state
            $this->conn->rollBack();
            return false;
        }
    }
    // Get all jobs created by a specific employer
    public function getJobsByEmployer($employer_id) {
        // We join with lookup tables to get readable names instead of just IDs
        $query = "SELECT j.id, j.description, j.is_active, j.created_at, 
                         t.title_name, c.category_name 
                  FROM job_vacancies j
                  LEFT JOIN job_titles t ON j.title_id = t.id
                  LEFT JOIN job_categories c ON j.category_id = c.id
                  WHERE j.employer_id = :employer_id
                  ORDER BY j.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':employer_id', $employer_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update an existing job (Security: Must match employer_id)
    public function update($job_id, $employer_id, $data) {
        // The WHERE clause ensures they can only update if they own the job!
        $query = "UPDATE job_vacancies 
                  SET description = :description, 
                      is_active = :is_active 
                  WHERE id = :job_id AND employer_id = :employer_id";
        
        $stmt = $this->conn->prepare($query);
        
        // We are only updating description and status here for simplicity, 
        // but you can add title_id, category_id, etc., just like in create()
        $stmt->execute([
            ':description' => $data->description,
            ':is_active' => $data->is_active ?? 1,
            ':job_id' => $job_id,
            ':employer_id' => $employer_id
        ]);

        // rowCount() returns how many rows were updated. If 0, the job either 
        // doesn't exist or belongs to someone else.
        return $stmt->rowCount() > 0; 
    }

    // Delete a job (Security: Must match employer_id)
    public function delete($job_id, $employer_id) {
        $query = "DELETE FROM job_vacancies WHERE id = :job_id AND employer_id = :employer_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':job_id' => $job_id,
            ':employer_id' => $employer_id
        ]);

        return $stmt->rowCount() > 0;
    }
}
?>