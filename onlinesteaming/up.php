<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "stream";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video"])) {
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = "recorded_" . time() . ".mp4";
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFilePath)) {
        $isLive = isset($_POST['isLive']) ? (int)$_POST['isLive'] : 0; // Detect if it's live

        if ($isLive) {
            $stmt = $conn->prepare("INSERT INTO live_videos (title, file_path) VALUES (?, ?)");
        } else {
            $stmt = $conn->prepare("INSERT INTO videos (title, file_path) VALUES (?, ?)");
        }

        $stmt->bind_param("ss", $fileName, $targetFilePath);
        $stmt->execute();
        $stmt->close();

        echo "Video saved successfully!";
    } else {
        echo "Failed to save video.";
    }
}

$conn->close();
?>
