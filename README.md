# Simple Validator

## What is it? <hr/>
A PHP library for sanitizing and validating user inputs. This library is based on https://www.phptutorial.net/ tutorial
on how to make a custom input validator form scratch.

1) Installation
2) Supported input types
3) Usage

## Installation   <hr/>
PHP versions 7.1 up to PHP 8.1 are currently supported.

The PECL mbstring extension is required.

It is recommended to use composer to install the library.

```
composer require deskola/simple-validator
```
You can also use any other PSR-4 compliant autoloader.

If you do not use composer, ensure that you also load any dependencies that this project has, such as giggsey/locale.

## Supported input types <hr/>
Below is the list of data type which will be sanitized against. Any data type not in the list will be result into an error

1) string
2) int
3) email
4) float
5) url

## Supported Rules <hr/>
Below are the rule that can be passed against an input<br/>

| Rule  | Rule name  | Parameter  | Meaning  |   |
|---|---|---|---|---|
| required  | required  |  No  | The field is set and not empty  |   |
| alphanumeric  | alphanumeric  | No  |  The field only contains letters and numbers |   |
| email  | email  |  No |  The field is a valid email address |   |
| secure  | secure  | No  | The field must have between 8 and 64 characters and contain at least one number, one upper case letter, one lower case letter, and one special character. This rule is for the password field. example (!@#$%^&*+_)  |   |
| min: 3  | min  | An integer specifies the minimum length of the field  | The length of the field must be greater than or equal to min length, e.g., 3  |   |
| max: 255  | max  |  An integer specifies the maximum length of the field | The length of the field must be less than or equal to min length, e.g., 255  |   |
| same: another_field  | same  | The name of another field | The field value must be the same as the value of the another_field  |   |
| between: min, max  | between  | min and max are integers that specify the minimum and maximum length of the field  | The length of the field must be between min and max.  |   |
| url  | url  |  No | The field must have a valid url starting with (http/https://)  |   |
| iso:KE  | iso  | A string value of country ISO Name e.g. KE (for Kenya)  | This library also utilises giggsey library to validate phone number based on a country ISO Name  |   |
|   |   |   |   |   |

## Usage <hr/>

```php
require_once  'vendor/autoload.php';
$validation = new Deskola\SimpleValidator\InputFilter();

$data = [
    'name' => 'Doe',
    'email' => 'john@email.com',
    'phone' => '2547********'
];

$iso = "KE";
$fields = [
    'name' => "string| required | max: 3",
    'email' => 'email| required | email',
    'phone' => 'string| required | iso:KE'
];

$response = $validation->filter($data, $fields);

print_r($response);
```

### Success Output
```php
Array
(
)
```

### Error Output
```php
Array
(
    [name] => The name must have at most 3 characters
    [email] => The email is not a valid email address
    [phone] => The phone must be a valid phone number
)
```


