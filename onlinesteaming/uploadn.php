<?php
session_start(); // Start session to access logged-in user data

$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$database = "stream"; // Change to your actual database

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "<script>alert('You must be logged in to upload videos.'); window.location.href='login.php';</script>";
    exit();
}

// Get the logged-in user's email
$user_email = $_SESSION['user_email'];

// File upload logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video"])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $videoName = basename($_FILES["video"]["name"]);
    $targetFilePath = $uploadDir . time() . "_" . $videoName; // Rename file to avoid overwriting

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFilePath)) {
        // Insert video details into the 'cartoon' table instead of 'videos'
        $stmt = $conn->prepare("INSERT INTO news (title, file_path, uploaded_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $videoName, $targetFilePath, $user_email);
        if ($stmt->execute()) {
            echo "<script>alert('Video uploaded successfully!'); window.location.href='news.php';</script>";
        } else {
            echo "<script>alert('Database error!');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('File upload failed!'); window.location.href='news.php';</script>";
    }
}
$conn->close();
?>
