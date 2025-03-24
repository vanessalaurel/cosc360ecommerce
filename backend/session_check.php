<?php
session_start();

// Check if the user is logged in
$response = array("logged_in" => false);

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $response["logged_in"] = true;
    $response["username"] = $_SESSION['username']; // Optional
    $response["email"] = $_SESSION['email']; // Optional
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
