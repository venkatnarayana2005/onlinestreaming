<?php
session_start();
require 'db.php'; // Ensure database connection

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['user_email'];

// Check if the video_id is set
if (isset($_POST['video_id']) && isset($_POST['source_table'])) {
    $videoId = $_POST['video_id'];
    $sourceTable = $_POST['source_table'];

    // Make sure the source table is valid
    $validTables = ['videos', 'news', 'sports', 'gaming', 'cartoon'];
    if (in_array($sourceTable, $validTables)) {

        // Delete the video from the specified table
        $deleteSql = "DELETE FROM $sourceTable WHERE uploaded_by = ? AND file_path = ?";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bind_param("ss", $userEmail, $videoId);

        if ($stmt->execute()) {
            // Video deleted successfully
            echo "<script>alert('Video deleted successfully!'); window.location.href = 'profile.php';</script>";
        } else {
            // Error deleting video
            echo "<script>alert('Error deleting video. Please try again.'); window.location.href = 'profile.php';</script>";
        }
    } else {
        // Invalid source table
        echo "<script>alert('Invalid source table.'); window.location.href = 'profile.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href = 'profile.php';</script>";
}

$conn->close();
?>
