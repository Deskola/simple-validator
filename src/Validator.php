<?php

namespace Deskola\SimpleValidator;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class Validator
{
    /**
     * A constant or the rules passed against an input
     */
    const DEFAULT_VALIDATION_ERRORS = [
        'required' => 'Please enter the %s',
        'email' => 'The %s is not a valid email address',
        'min' => 'The %s must have at least %s characters',
        'max' => 'The %s must have at most %s characters',
        'between' => 'The %s must have between %d and %d characters',
        'same' => 'The %s must match with %s',
        'alphanumeric' => 'The %s should have only letters and numbers',
        'secure' => 'The %s must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter and one special character example (!@#$%^&*+_)',
        'unique' => 'The %s already exists',
        'number' => 'The %s must be numeric',
        'phone' => 'The %s must be a valid phone number',
        'url' => 'The %s must be a valid URL',
        'iso' => 'The %s must be a valid phone number',
        'minAge' => 'The %s must be a older than minimun age',
    ];

    public function __construct()
    {
    }

    /**
     * This method checks if the input passed is meets the provided rules
     * @param array $data
     * @param array $fields
     * @param array $messages
     * @return array
     */
    public function validate(array $data, array $fields, array $messages = []): array
    {
        // get the message rules
        $rule_messages = array_filter($messages, function ($message) {
            return is_string($message);
        });
        // overwrite the default message
        $validation_errors = array_merge(self::DEFAULT_VALIDATION_ERRORS, $rule_messages);

        $errors = [];

        foreach ($fields as $field => $option) {

            $rules = $this->splitInput($option, '|');

            foreach ($rules as $rule) {
                // get rule name params
                $params = [];
                // if the rule has parameters e.g., min: 1
                if (strpos($rule, ':')) {
                    [$rule_name, $param_str] = $this->splitInput($rule, ':');
                    $params = $this->splitInput($param_str, ',');
                } else {
                    $rule_name = trim($rule);
                }
                // by convention, the callback should be is_<rule> e.g.,is_required
                $fn = array($this, 'is_' . $rule_name);

                if (is_callable($fn, true, $actualFunc)) {
                    $pass = $actualFunc($data, $field, ...$params);
                    if (!$pass) {
                        // get the error message for a specific field and rule if exists
                        // otherwise get the error message from the $validation_errors
                        $errors[$field] = sprintf(
                            $messages[$field][$rule_name] ?? $validation_errors[$rule_name],
                            $field,
                            ...$params
                        );
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * This method accept a string and split it as the supplied delimiter
     * @param $str
     * @param $separator
     * @return array
     */
    private function splitInput($str, $separator): array
    {
        return array_map('trim', explode($separator, $str));
    }

    /**
     * Return true if a string is not empty
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_required(array $data, string $field): bool
    {
        if (!isset($data[$field])){
            return false;
        }
        
        $errors = [];
        if (is_array($data[$field]) && !empty($data[$field])){
            $errors = array_map(function($item){
                if (trim($item) !== '') {
                    return true;
                }
                return false;
            }, $data[$field]);
        } elseif(!is_array($data[$field]) && !empty($data[$field])) {
           return true;
        }
        
        return in_array(false, $errors) ? false : true;
    }

    /**
     * Return true if the value is a valid email
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_email(array $data, string $field): bool
    {
        if (empty($data[$field])) {
            return true;
        }

        return filter_var($data[$field], FILTER_VALIDATE_EMAIL);
    }

    /**
     * Return true if a string has at least min length
     * @param array $data
     * @param string $field
     * @param int $min
     * @return bool
     */
    private static function is_min(array $data, string $field, int $min): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return mb_strlen($data[$field]) >= $min;
    }

    /**
     * Return true if a string cannot exceed max length
     * @param array $data
     * @param string $field
     * @param int $max
     * @return bool
     */
    private static function is_max(array $data, string $field, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return mb_strlen($data[$field]) <= $max;
    }

    /**
     * Returns tru if input is between the min and max range
     * @param array $data
     * @param string $field
     * @param int $min
     * @param int $max
     * @return bool
     */
    private static function is_between(array $data, string $field, int $min, int $max): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        $len = mb_strlen($data[$field]);
        return $len >= $min && $len <= $max;
    }

    /**
     * Return true if a string equals the other
     * @param array $data
     * @param string $field
     * @param string $other
     * @return bool
     */
    private static function is_same(array $data, string $field, string $other): bool
    {
        if (isset($data[$field], $data[$other])) {
            return $data[$field] === $data[$other];
        }

        if (!isset($data[$field]) && !isset($data[$other])) {
            return true;
        }

        return false;
    }

    /**
     * Return true if a string is alphanumeric
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_alphanumeric(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return ctype_alnum($data[$field]);
    }

    /**
     * Return true if a password is secure
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_secure(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return false;
        }

        $pattern = "#.*^(?=.{8,64})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#";
        return preg_match($pattern, $data[$field]);
    }

    /**
     * Returns true if an input is numeric in nature (int, float, double)
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_number(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return is_numeric($data[$field]);
    }

    /**
     * Returns true is the number is a valid phone number
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_phone(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return filter_var($data[$field], FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Returns true if the input in a valid url (http(s)://example.com)
     * @param array $data
     * @param string $field
     * @return bool
     */
    private static function is_url(array $data, string $field): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        return filter_var($data[$field], FILTER_SANITIZE_URL);
    }

    /**
     * Returns true if the phone number is valid based on the country iso name provided
     * @param array $data
     * @param string $field
     * @param $iso
     * @return bool
     */
    private static function is_iso(array $data, string $field, $iso): bool
    {
        if (!isset($data[$field])) {
            return true;
        }

        $phoneUtil = PhoneNumberUtil::getInstance();

        try {
            $swissNumberProto = $phoneUtil->parse($data[$field], $iso);
            return $phoneUtil->isValidNumber($swissNumberProto);
        } catch (NumberParseException $e) {
            return false;
        }
    }

     /**
     * Return true if the date is grater than minimum age
     * @param array $data
     * @param string $field
     * @param $minAge
     * @return bool
     */    
    private static function is_minAge(array $data, string $field, $minAge)
    {
        if (!isset($data[$field])) {
            return true;
        }

        return date('Y-m-d') - $data[$field] >= $minAge ? true : false;
    }


}