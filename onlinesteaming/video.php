<?php
session_start();
require 'db.php'; // Include database connection file

// Ensure the connection is valid
if (!$conn) {
    die("Database connection error.");
}

// Check if the video ID is set in the URL
if (isset($_GET['id'])) {
    $videoId = (int) $_GET['id']; // Ensure the ID is an integer

    // SQL query to retrieve the video based on its ID
    $sql = "SELECT title, file_path FROM videos WHERE id = ? 
            UNION 
            SELECT title, file_path FROM cartoon WHERE id = ?
            UNION 
            SELECT title, file_path FROM gaming WHERE id = ?
            UNION 
            SELECT title, file_path FROM sports WHERE id = ?
            UNION 
            SELECT title, file_path FROM news WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $videoId, $videoId, $videoId, $videoId, $videoId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $video = $result->fetch_assoc();
    } else {
        echo "Video not found.";
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "No video ID provided.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($video['title']) ?></h1>
    <video controls>
        <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
</body>
</html>
