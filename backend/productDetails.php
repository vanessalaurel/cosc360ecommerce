<?php
$servername = "localhost"; // Change if needed
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "reviews_db";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review_text = $_POST['review_text'];

    // Handle image upload
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_path = "";
    if (!empty($_FILES["review_image"]["name"])) {
        $target_file = $target_dir . basename($_FILES["review_image"]["name"]);
        if (move_uploaded_file($_FILES["review_image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // Insert review into database
    $stmt = $conn->prepare("INSERT INTO reviews (image_path, review_text) VALUES (?, ?)");
    $stmt->bind_param("ss", $image_path, $review_text);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
