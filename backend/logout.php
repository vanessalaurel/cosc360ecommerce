<?php
session_start();
session_destroy(); // Destroy session
header("Location: ../frontend/login.html"); // Redirect to login page
exit;
?>
