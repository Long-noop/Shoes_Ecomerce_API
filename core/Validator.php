<?php
class Validator {
    private $errors = [];
    private $data = [];

    public function validate($data, $rules) {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            
            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $rule) {
        $value = $this->data[$field] ?? null;

        if (strpos($rule, ':') !== false) {
            [$ruleName, $ruleValue] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "$field is required");
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "$field must be a valid email");
                }
                break;

            case 'min':
                if (strlen($value) < $ruleValue) {
                    $this->addError($field, "$field must be at least $ruleValue characters");
                }
                break;

            case 'max':
                if (strlen($value) > $ruleValue) {
                    $this->addError($field, "$field must not exceed $ruleValue characters");
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, "$field must be numeric");
                }
                break;

            case 'alpha':
                if ($value && !ctype_alpha($value)) {
                    $this->addError($field, "$field must contain only letters");
                }
                break;

            case 'alphanumeric':
                if ($value && !ctype_alnum($value)) {
                    $this->addError($field, "$field must be alphanumeric");
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "$field must be a valid URL");
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if ($value && !in_array($value, $allowedValues)) {
                    $this->addError($field, "$field must be one of: " . implode(', ', $allowedValues));
                }
                break;
        }
    }

    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }
}
?>