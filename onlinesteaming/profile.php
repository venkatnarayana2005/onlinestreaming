<?php
session_start();
require 'db.php'; // Ensure database connection

// Get logged-in user email from session
$userEmail = $_SESSION['user_email']; 

// Ensure database connection exists
if (!$conn) {
    die("Database connection error.");
}

// Fetch user details, including profile picture
$userSql = "SELECT fullname, number, profile_pic FROM users WHERE email = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$userResult = $stmt->get_result();
$user_details = $userResult->fetch_assoc();

// Function to limit video title length and add ellipsis if needed
function truncate_title($title, $maxLength = 20) {
    if (strlen($title) > $maxLength) {
        return substr($title, 0, $maxLength) . '...';
    }
    return $title;
}

// Fetch videos uploaded by the user from multiple tables using UNION
$sql = "(SELECT 'videos' AS source_table, title, file_path, uploaded_at FROM videos WHERE uploaded_by = ?) 
        UNION 
        (SELECT 'news' AS source_table, title, file_path, uploaded_at FROM news WHERE uploaded_by = ?) 
        UNION 
        (SELECT 'sports' AS source_table, title, file_path, uploaded_at FROM sports WHERE uploaded_by = ?) 
        UNION 
        (SELECT 'gaming' AS source_table, title, file_path, uploaded_at FROM gaming WHERE uploaded_by = ?) 
        UNION 
        (SELECT 'cartoon' AS source_table, title, file_path, NOW() AS uploaded_at FROM cartoon WHERE uploaded_by = ?) 
        ORDER BY uploaded_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $userEmail, $userEmail, $userEmail, $userEmail, $userEmail);
$stmt->execute();
$result = $stmt->get_result();

$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="pstyle.css">
    
</head>
<body>

    <!-- Sidebar -->
<div class="sidebar">
    <br><br><br><br><br><br><br><br>
    <h2>User Profile</h2>

    <?php if ($user_details): ?>
        <!-- Profile Image with Pencil Icon for Editing -->
        <div class="profile-container">
            <a href="edit_profile.php" class="profile-link">
                <img src="<?= htmlspecialchars($user_details['profile_pic']) ?>" alt="Profile Image" class="profile-image">
                <span class="edit-icon">&#9998;</span> <!-- Unicode Pencil Icon -->
            </a>
        </div>
        <p><strong>Name:</strong> <?= htmlspecialchars($user_details['fullname']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($userEmail) ?></p>
        <p><strong>Phone Number:</strong> <?= htmlspecialchars($user_details['number']) ?></p>
        <a href="logout.php" class="logout-btn">Logout</a>
    <?php else: ?>
        <script>
            alert("User not found! Please try again or register.");
            window.location.href = "login.php"; // Redirect to login page
        </script>
    <?php endif; ?>
</div>



    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="home.php">Home</a>
        <a href="gaming.php">Gaming</a>
        <a href="news.php">News</a>
        <a href="sports.php">Sports</a>
        <a href="index.php">Music</a>
        <a href="cartoon.php">Kids</a>
        <a href="profile.php">My Library</a>
    </div>

    <!-- Content Section -->
    <div class="content">
        <h2>My Library</h2>
        <div id="videoList">
    <?php if (!empty($videos)): ?>
        <?php foreach ($videos as $video): ?>
            <div class="videoItem">
                <h3><?= htmlspecialchars(truncate_title($video['title'])) ?> (<?= htmlspecialchars($video['source_table']) ?>)</h3>
                <video controls>
                    <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <!-- Add a delete button -->
                <form method="POST" action="delete_video.php" onsubmit="return confirm('Are you sure you want to delete this video?');">
                    <input type="hidden" name="video_id" value="<?= $video['file_path']; ?>">
                    <input type="hidden" name="source_table" value="<?= $video['source_table']; ?>">
                    <button type="submit" class="delete-btn">
    <i class="fas fa-trash-alt"></i> Delete
</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No videos uploaded yet.</p>
    <?php endif; ?>
</div>

    </div>

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

<?php
$conn->close();
?>
