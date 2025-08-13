<?php
session_start();
require 'db.php'; // Include your database connection file

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$userEmail = $_SESSION['user_email'];

// Ensure video_id and category are passed
if (isset($_POST['video_id']) && isset($_POST['category'])) {
    $videoId = $_POST['video_id'];
    $category = $_POST['category'];

    // Check if user has already liked the video
    $checkLikeSql = "SELECT * FROM user_likes WHERE user_email = ? AND video_id = ? AND category = ?";
    $stmt = $conn->prepare($checkLikeSql);
    $stmt->bind_param("sis", $userEmail, $videoId, $category);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User has liked the video, so unlike it
        $deleteLikeSql = "DELETE FROM user_likes WHERE user_email = ? AND video_id = ? AND category = ?";
        $stmt = $conn->prepare($deleteLikeSql);
        $stmt->bind_param("sis", $userEmail, $videoId, $category);
        $stmt->execute();

        // Decrease the like count in the corresponding category table
        $updateCountSql = "UPDATE $category SET likes_count = likes_count - 1 WHERE id = ?";
        $stmt = $conn->prepare($updateCountSql);
        $stmt->bind_param("i", $videoId);
        $stmt->execute();

        echo json_encode(["success" => "Video unliked", "action" => "unlike"]);
    } else {
        // User has not liked the video, so like it
        $insertLikeSql = "INSERT INTO user_likes (user_email, video_id, category) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertLikeSql);
        $stmt->bind_param("sis", $userEmail, $videoId, $category);
        $stmt->execute();

        // Increase the like count in the corresponding category table
        $updateCountSql = "UPDATE $category SET likes_count = likes_count + 1 WHERE id = ?";
        $stmt = $conn->prepare($updateCountSql);
        $stmt->bind_param("i", $videoId);
        $stmt->execute();

        echo json_encode(["success" => "Video liked", "action" => "like"]);
    }
} else {
    echo json_encode(["error" => "No video ID or category provided"]);
}

$conn->close();
?>
