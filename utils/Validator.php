<?php
/**
 * Validator Class
 * 
 * Handles form validation and data sanitization
 */
class Validator {
    private $errors = [];
    private $data = [];
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Get validation errors
     * 
     * @return array Validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Check if validation has errors
     * 
     * @return bool True if has errors, false otherwise
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * Sanitize input data
     * 
     * @param string $input Input data
     * @return string Sanitized data
     */
    public function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function required($field, $message = 'Ce champ est obligatoire') {
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate email field
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function email($field, $message = 'Email invalide') {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param int $length Minimum length
     * @param string $message Error message
     * @return Validator
     */
    public function minLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message ?? "Ce champ doit contenir au moins $length caractères";
        }
        
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param int $length Maximum length
     * @param string $message Error message
     * @return Validator
     */
    public function maxLength($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message ?? "Ce champ ne doit pas dépasser $length caractères";
        }
        
        return $this;
    }
    
    /**
     * Validate numeric field
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function numeric($field, $message = 'Ce champ doit être un nombre') {
        if (isset($this->data[$field]) && $this->data[$field] !== '' && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate date field
     * 
     * @param string $field Field name
     * @param string $format Date format
     * @param string $message Error message
     * @return Validator
     */
    public function date($field, $format = 'Y-m-d', $message = 'Date invalide') {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $date = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$date || $date->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate time field
     * 
     * @param string $field Field name
     * @param string $format Time format
     * @param string $message Error message
     * @return Validator
     */
    public function time($field, $format = 'H:i', $message = 'Heure invalide') {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            $time = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$time || $time->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate field matches another field
     * 
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Error message
     * @return Validator
     */
    public function matches($field, $matchField, $message = 'Les champs ne correspondent pas') {
        if (isset($this->data[$field]) && isset($this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Validate field is in a list of values
     * 
     * @param string $field Field name
     * @param array $values Allowed values
     * @param string $message Error message
     * @return Validator
     */
    public function inList($field, $values, $message = 'Valeur non autorisée') {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return Validator
     */
    public function phone($field, $message = 'Numéro de téléphone invalide') {
        if (isset($this->data[$field]) && $this->data[$field] !== '') {
            // Simple regex for phone validation (adjust as needed)
            if (!preg_match('/^[0-9+\-\s()]{8,20}$/', $this->data[$field])) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Custom validation with callback
     * 
     * @param string $field Field name
     * @param callable $callback Validation callback
     * @param string $message Error message
     * @return Validator
     */
    public function custom($field, $callback, $message = 'Validation échouée') {
        if (isset($this->data[$field])) {
            if (!$callback($this->data[$field])) {
                $this->errors[$field] = $message;
            }
        }
        
        return $this;
    }
    
    /**
     * Get sanitized data
     * 
     * @return array Sanitized data
     */
    public function getSanitizedData() {
        $sanitized = [];
        
        foreach ($this->data as $key => $value) {
            $sanitized[$key] = $this->sanitize($value);
        }
        
        return $sanitized;
    }
    
    /**
     * Get specific field value
     * 
     * @param string $field Field name
     * @param mixed $default Default value if field not set
     * @return mixed Field value or default
     */
    public function getValue($field, $default = '') {
        return isset($this->data[$field]) ? $this->data[$field] : $default;
    }
}
?>
