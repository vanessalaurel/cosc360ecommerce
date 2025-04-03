<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start a session to access user information

// Check if user is logged in (you'll need to implement proper session handling in your login system)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: ../frontend/login.html");
    exit;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "productInfo_db";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $product_name = isset($_POST["product_name"]) ? trim($_POST["product_name"]) : "";
    $price = isset($_POST["price"]) ? floatval($_POST["price"]) : 0;
    $description = isset($_POST["description"]) ? trim($_POST["description"]) : "";
    $category = isset($_POST["category"]) ? trim($_POST["category"]) : "";
    $quantity = isset($_POST["quantity"]) ? intval($_POST["quantity"]) : 1;
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID

    // Validate data
    if (empty($product_name) || $price <= 0 || empty($description) || empty($category)) {
        echo "Error: All fields are required and price must be greater than 0.";
        exit;
    }

    // Handle file upload
    $image_path = "";
    if (isset($_FILES["product_image"]) && $_FILES["product_image"]["error"] == 0) {
        $target_dir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Generate unique filename
        $filename = time() . "_" . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $filename;
        
        // Move the uploaded file
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/" . $filename; // Store relative path in database
        } else {
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    } else {
        echo "Error: Product image is required.";
        exit;
    }

    // Insert product into database
    $stmt = $conn->prepare("INSERT INTO products (user_id, product_name, price, description, category, quantity, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdssis", $user_id, $product_name, $price, $description, $category, $quantity, $image_path);
    
    if ($stmt->execute()) {
        // Redirect to the Profilepage.html after successful listing
        header("Location: ../frontend/Profilepage.html");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>  