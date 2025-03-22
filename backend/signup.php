<?php
// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // Send 405 error if not POST
    echo "Error 405: Method Not Allowed. Use POST.";
    exit;
}

// Database connection (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

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

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Invalid email format.";
    exit;
}

// Check if form data is received correctly
if ($email && $password) {
    // Check if the email already exists in the database
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);  // "s" denotes the string type
    $stmt->execute();
    $stmt->store_result();

    // If email already exists, show an error
    if ($stmt->num_rows > 0) {
        echo "Error: Email is already registered.";
    } else {
        // Hash the password before storing it
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Use prepared statement to insert new user data into the database
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $password_hash); // "ss" denotes two string types
        
        if ($stmt->execute()) {
            echo "Registration successful! You can now log in.";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Close statement
    $stmt->close();
} else {
    echo "Error: Form data not received properly.";
}

// Close database connection
$conn->close();
?>
