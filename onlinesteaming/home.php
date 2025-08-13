<?php
session_start();
require 'db.php'; // Include database connection file

// Ensure the connection is valid
if (!$conn) {
    die("Database connection error.");
}

// Fetch videos based on search query
$videos = [];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL query to retrieve videos from all tables
$sql = "
    SELECT title, file_path FROM news WHERE title LIKE ?
    UNION
    SELECT title, file_path FROM cartoon WHERE title LIKE ?
    UNION
    SELECT title, file_path FROM gaming WHERE title LIKE ?
    UNION
    SELECT title, file_path FROM sports WHERE title LIKE ?
    UNION
    SELECT title, file_path FROM videos WHERE title LIKE ?
";

$stmt = $conn->prepare($sql);
$searchTerm = "%$search%";
$stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
} else {
    echo "Error fetching videos: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Streaming App</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        /* Top Navigation Bar */
        #topbar {
            width: 100%;
            background-color: #333;
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 200px;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        #search-form {
            display: flex;
        }
        #search-input {
            padding: 8px;
            width: 250px;
            border: none;
            border-radius: 5px;
            margin-right: 5px;
        }
        #search-button {
            padding: 8px 12px;
            background-color: #ff6600;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        /* Sidebar Styling */
        #sidebar {
            width: 200px;
            background-color: #333;
            color: white;
            position: fixed;
            height: 100vh;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            width: 100%;
            text-align: center;
        }
        #sidebar a:hover {
            background-color: #575757;
            border-radius: 5px;
        }
        /* Content Area */
        #content {
            margin-left: 200px;
            margin-top: 50px;
            flex-grow: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        /* Video Styling */
        .videoItem {
            width: 30%;
            height: 250px;
            text-align: center;
            background-color: white;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 2px 5px rgb(200, 200, 200);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Responsive design */
        @media (max-width: 1200px) {
            .videoItem {
                width: 45%;
            }
        }

        @media (max-width: 768px) {
            .videoItem {
                width: 100%;
            }
        }

        .videoItem h3 {
            color: black;
            font-size: 16px;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        video {
            width: 100%;
            height: 180px;
            border-radius: 10px;
            object-fit: cover;
        }
        #logo {
    width: 100px;
    height: 100px; /* Ensures it's a perfect circle */
    margin-bottom: 20px;
    border-radius: 50%; /* Makes it circular */
    object-fit: cover; /* Ensures the image fills the circle properly */
}

    </style>
</head>
<body>
    
    <!-- Sidebar Navigation -->
    <div id="sidebar">
        <img src="img/logo.jpeg" alt="Logo" id="logo">
        <a href="home.php">Home</a>
        <a href="gaming.php">Gaming</a>
        <a href="news.php">News</a>
        <a href="sports.php">Sports</a>
        <a href="index.php">Music</a>
        <a href="cartoon.php">Kids</a>
        <a href="profile.php">My Library</a>
    </div>

    <!-- Top Navigation Bar with Search Bar -->
    <div id="topbar">
        <form id="search-form" method="GET">
            <input type="text" id="search-input" name="search" placeholder="Search videos..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" id="search-button">Search</button>
        </form>
    </div>

    <!-- Video Content -->
    <div id="content">
        <?php if (!empty($videos)): ?>
            <?php foreach ($videos as $video): ?>
                <div class="videoItem">
                    <h3><?= htmlspecialchars($video['title']) ?></h3>
                    <video controls>
                        <source src="<?= htmlspecialchars($video['file_path']) ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: black;">No videos found for "<?= htmlspecialchars($search) ?>".</p>
        <?php endif; ?>
    </div>
</body>
</html>
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
