<?php
// uploadg.php - Video Upload Handler
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "<script>alert('You must be logged in to upload videos.'); window.location.href='login.php';</script>";
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stream";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set higher limits for video uploads
ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('max_execution_time', '600');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video"])) {
    // Check for upload errors
    if ($_FILES["video"]["error"] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            1 => 'File exceeds maximum upload size',
            2 => 'File exceeds form MAX_FILE_SIZE',
            3 => 'File only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing temporary folder',
            7 => 'Failed to write to disk',
            8 => 'PHP extension stopped the upload'
        ];
        $errorMsg = $uploadErrors[$_FILES["video"]["error"]] ?? 'Unknown upload error';
        die("<script>alert('Upload failed: $errorMsg'); window.history.back();</script>");
    }

    // Validate file type
    $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    $fileType = $_FILES["video"]["type"];
    
    if (!in_array($fileType, $allowedTypes)) {
        die("<script>alert('Only MP4, WebM, and OGG videos are allowed.'); window.history.back();</script>");
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $originalName = basename($_FILES["video"]["name"]);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $newFilename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '', $originalName);
    $targetPath = $uploadDir . $newFilename;

    // Move uploaded file
    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetPath)) {
        // Insert into database
        $title = pathinfo($originalName, PATHINFO_FILENAME); // Use filename as title
        $uploadedBy = $_SESSION['user_email'];
        $filePath = $targetPath;
        
        $stmt = $conn->prepare("INSERT INTO gaming (title, file_path, uploaded_by) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $filePath, $uploadedBy);
        
        if ($stmt->execute()) {
            echo "<script>alert('Video uploaded successfully!'); window.location.href='gaming.php';</script>";
        } else {
            // Delete the uploaded file if DB insert fails
            unlink($targetPath);
            echo "<script>alert('Database error: Failed to save video details.'); window.history.back();</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Failed to move uploaded file.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='gaming.php';</script>";
}

$conn->close();
?>