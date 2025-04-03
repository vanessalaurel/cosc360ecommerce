<?php

// Start a session to manage user login state
session_start();

// If the user is already logged in, redirect them to myproduct.html
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: ../frontend/Homepage.html");
    exit;
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Send 405 error if not POST
    echo "Error 405: Method Not Allowed. Use POST.";
    exit;
}

// Database connection (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "userInfo_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data and sanitize inputs
$email = isset($_POST["email"]) ? trim($_POST["email"]) : null;
$password = isset($_POST["password"]) ? trim($_POST["password"]) : null;

// Sanitize email and password to prevent XSS attacks
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

// Check if form data is received correctly
if ($email && $password) {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  // "s" denotes the string type
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username, $password_hash);
        $stmt->fetch();

        // Verify password using password_hash() and password_verify() functions
        if (password_verify($password, $password_hash)) {
            
            // Set session variables to maintain login state
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['logged_in'] = true; // Explicit login state

            // Redirect to myproduct.html after successful login
            header("Location: ../frontend/Homepage.html");
            exit;
        } else {
            echo "Error: Invalid email or password.";
        }
    } else {
        echo "Error: User not found.";
    }
    
    // Close statement and connection
    $stmt->close();
} else {
    echo "Error: Form data not received properly.";
}

// Close database connection
$conn->close();
?>
