<?php

// this is for testing inputs before inserting to database
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// validate password before entering database
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

// this is for the image uploads
function uploadImage($fileKey)
{
    // Set the target directory to 'images/tattoo_uploads/'
    $target_dir = "images/tattoo_uploads/";

    // Create the directory if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Ensure the file exists and there's no error
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return "No file uploaded or an error occurred!";
    }

    // Retrieve file details
    $originalFileName = htmlspecialchars(basename($_FILES[$fileKey]["name"]));
    $imageFileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $baseFileName = pathinfo($originalFileName, PATHINFO_FILENAME);

    // Generate the target file name
    $target_file = $target_dir . $originalFileName;

    // If the file already exists, add a unique ID
    while (file_exists($target_file)) {
        $uniqueId = uniqid('', true);
        $target_file = $target_dir . $baseFileName . '_' . $uniqueId . '.' . $imageFileType;
    }

    // Validate the file type
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        return "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
    }

    // Check file size (limit: 500KB)
    if ($_FILES[$fileKey]["size"] > 500000) { // 500KB max size
        return "File is too large!";
    }

    // Validate if the file is an actual image
    $check = getimagesize($_FILES[$fileKey]["tmp_name"]);
    if ($check === false) {
        return "File is not an image.";
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES[$fileKey]["tmp_name"], $target_file)) {
        return $target_file;
    } else {
        return "Sorry, there was an error uploading your file.";
    }
}