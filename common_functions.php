<?php
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function isValidPassword($password)
{
    $errors = [];
    // Check for at least one letter
    if (!preg_match('/[a-zA-Z]/', $password)) {
        $errors[] = "Password must contain at least one letter.";
    }

    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    // Check for at least one non-alphanumeric character
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    // Check for at least 8 characters long
    if (!preg_match('/^.{8,}$/', $password)) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    return $errors;
}
?>