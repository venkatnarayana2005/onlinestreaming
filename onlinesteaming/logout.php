<?php
session_start();  // Start the session

// Destroy all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the homepage or login page
header("Location: login.php");  // You can change the redirect URL to the desired page
exit();
?>
