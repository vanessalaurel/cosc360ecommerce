<?php
// Start the session
session_start();

// Set the response header to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Return user information
echo json_encode([
    'success' => true,
    'user' => [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => isset($_SESSION['email']) ? $_SESSION['email'] : ''
    ]
]);
?>