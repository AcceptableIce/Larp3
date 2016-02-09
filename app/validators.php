<?

Validator::extend('username', function($attribute, $value)
{
    return preg_match('/^[A-Za-z0-9!@#$%^&*\s]+$/u', $value);
});

?>