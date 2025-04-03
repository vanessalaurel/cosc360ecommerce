<?php
// Database connection
$userDB = new mysqli("localhost", "root", "root", "userInfo_db");

if ($userDB->connect_error) {
    die("Database connection failed: " . $userDB->connect_error);
}

// Get user ID from request
if (isset($_GET['id'])) {
    $userId = intval($_GET['id']);

    // Delete the user from the `users` table
    $deleteUserQuery = "DELETE FROM users WHERE id = ?";
    $stmt = $userDB->prepare($deleteUserQuery);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "User removed successfully.";
    } else {
        echo "Error removing user: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid user ID.";
}

$userDB->close();
?>
