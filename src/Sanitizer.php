<?php

namespace Deskola\SimpleValidator;

class Sanitizer
{
    /**
     * Filters for various Data types and some common input fields
     */
    const FILTERS = [
        'string' => FILTER_SANITIZE_STRING,
        'string[]' => [
            'filter' => FILTER_SANITIZE_STRING,
            'flags' => FILTER_REQUIRE_ARRAY,
        ],
        'email' => FILTER_SANITIZE_EMAIL,
        'int' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_SCALAR,
        ],
        'int[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_INT,
            'flags' => FILTER_REQUIRE_ARRAY,
        ],
        'float' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_FLAG_ALLOW_FRACTION,
        ],
        'float[]' => [
            'filter' => FILTER_SANITIZE_NUMBER_FLOAT,
            'flags' => FILTER_REQUIRE_ARRAY,
        ],
        'url' => FILTER_SANITIZE_URL,
    ];

    public function __construct(){}

    /**
     * This method trims input data based on the filter supplied
     * @param array $inputs
     * @param array $fields
     * @param int $default_filter
     * @param array $filters
     * @param bool $trim
     * @return array
     */
    public function sanitize(array $inputs, array $fields = [],int $default_filter = FILTER_SANITIZE_STRING, array $filters = self::FILTERS, bool $trim = true): array
    {
        if ($fields) {

            $options = array_map(function ($field) use ($filters) {
                return $filters[$field];
            }, $fields);
            $data = filter_var_array($inputs, $options);
        }else{
            $data = filter_var_array($inputs, $default_filter);
        }

        return $trim ? $this->array_trim($data) : $data;
    }

    /**
     * This method removes special characters from the input
     * it also does a recursive call for nested array inputs
     * @param array $items
     * @return array
     */
    private function array_trim(array $items): array {
        return array_map(
            function ($item) {
                if (is_string($item)) {
                    return trim($item);
                }elseif (is_array($item)) {
                    return $this->array_trim($item);
                }else{
                    return $item;
                }
            }, $items
        );
    }

}