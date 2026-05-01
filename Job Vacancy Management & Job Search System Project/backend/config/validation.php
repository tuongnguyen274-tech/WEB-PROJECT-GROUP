<?php
class Validator {
    // Sanitize input to prevent Cross-Site Scripting (XSS)
    public static function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Validate Job Vacancy Creation inputs
    public static function validateJobInput($data) {
        $errors = [];

        // Check required fields
        if (empty($data->title_id)) $errors[] = "Job title is required.";
        if (empty($data->category_id)) $errors[] = "Job category is required.";
        if (empty($data->country_id)) $errors[] = "Country location is required.";
        
        // Validate structured constraints
        if (isset($data->skills) && is_array($data->skills)) {
            if (count($data->skills) > 5) {
                $errors[] = "Maximum of 5 required skills allowed.";
            }
        } else {
            $errors[] = "At least one required skill must be provided.";
        }

        return $errors; // Returns an array of errors, empty if passed
    }
}
?>