<?php

namespace Deskola\SimpleValidator;

class Filter
{
    private $sanitizer;
    private $validator;

    public function __construct()
    {
        $this->sanitizer = new Sanitizer();
        $this->validator = new Validator();
    }

    /**
     * This method receive the input Data from user application the passes the data for
     * sanitization and filtration.
     * Returns empty array if no error is found or array of error messages for each field that fails the
     * validation.
     * @param array $data
     * @param array $fields
     * @param array $messages
     * @return array
     */
    public function filter(array $data, array $fields, array $messages = []): array
    {
        $sanitization_rules = [];
        $validation_rules = [];

        foreach ($fields as $field => $rules) {
            if (strpos($rules, '|')) {
                [$sanitization_rules[$field], $validation_rules[$field]] = explode('|', $rules, 2);
            } else {
                $sanitization_rules[$field] = $rules;
            }
        }
        $inputs = $this->sanitizer->sanitize($data, $sanitization_rules);
        return $this->validator->validate($inputs, $validation_rules, $messages);
    }


}