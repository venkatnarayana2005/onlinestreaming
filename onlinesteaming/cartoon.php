<?php
session_start(); // Start session to check if user is logged in

// Database connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$database = "stream"; // Change to your actual database

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch videos from the database
$videos = [];
$sql = "SELECT * FROM cartoon ORDER BY uploaded_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}
$conn->close();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_email']);
$userEmail = $isLoggedIn ? $_SESSION['user_email'] : '';

// Function to limit video title length and add ellipsis if needed
function truncate_title($title, $maxLength = 20) {
    if (strlen($title) > $maxLength) {
        return substr($title, 0, $maxLength) . '...';
    }
    return $title;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Streaming App</title>
    <link rel="stylesheet" href="vstyle.css">
</head>
<body>

    <div id="navbar">
        <a href="home.php">Home</a>
        <a href="gaming.php">Gaming</a>
        <a href="news.php">News</a>
        <a href="sports.php">Sports</a>
        <a href="index.php">Music</a>
        <a href="cartoon.php">Kids</a>
        <a href="profile.php">My Library</a>
    </div>

    <h1>Video Streaming Application</h1>

    <h2>Enjoy your Cartoon</h2>
    <div id="videoList">
        <?php foreach ($videos as $video): ?>
            <div class="videoItem">
                <video controls>
                    <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <!-- Apply truncation to the title -->
                <h3><?= htmlspecialchars(truncate_title($video['title'], 20)) ?></h3>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Upload a New Video</h2>
    <form id="uploadForm" method="POST" enctype="multipart/form-data" action="uploadc.php">
        <input type="file" name="video" accept="video/*" required />
        <button type="submit">Upload Video</button>
    </form>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const videos = document.querySelectorAll("video");

        videos.forEach((video) => {
            video.addEventListener("play", function () {
                videos.forEach((v) => {
                    if (v !== video) {
                        v.pause();
                    }
                });
            });
        });
    });
</script>

</body>
</html>
