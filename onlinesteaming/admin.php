<?php
// Start the session
session_start();

// Redirect to login if the user is not an admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$dbname = 'stream';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all users
$stmt = $conn->prepare("SELECT id, fullname, profile_pic FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #333;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            margin-bottom: 15px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            text-align: center;
            width: 100%;
            transition: background 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .sidebar .logout {
            background-color: #dc3545;
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            text-transform: uppercase;
            transition: background 0.3s ease, transform 0.2s ease;
            width: 80%;
            text-align: center;
        }
        .sidebar .logout:hover {
            background-color: #c82333;
            transform: scale(1.05);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 20px;
        }

        /* User List Styles */
        #user-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .user-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 150px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .user-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 3px solid #4a90e2;
        }

        .user-card p {
            margin: 10px 0;
            font-size: 1.1rem;
            color: #4a90e2;
            font-weight: bold;
        }

        /* User Details Styles */
        .user-details {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            animation: fadeIn 0.5s ease;
        }

        .user-details h2 {
            color: #4a90e2;
            text-align: center;
            margin-bottom: 20px;
        }

        .user-details p {
            font-size: 1.1rem;
            margin: 10px 0;
        }

        .user-details img {
            display: block;
            margin: 20px auto;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 3px solid #4a90e2;
        }

        /* Hide details by default */
        .hidden {
            display: none;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar"><br><br><br>
        <img src="uploads/1.jpg" alt="Admin Profile" class="profile-pic">
        <h2>Admin Panel</h2>
        <a href="admin.php">Dashboard</a>
        <a href="users.php">Manage Users</a>
        <a href="uploaded_videos.php">Videos</a>
        <a href="addvideo.php">Add video</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Users</h1>
        <div id="user-list">
            <?php foreach ($users as $user): ?>
                <div class="user-card" onclick="fetchUserDetails(<?php echo $user['id']; ?>)">
                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="<?php echo htmlspecialchars($user['fullname']); ?>">
                    <p><?php echo htmlspecialchars($user['fullname']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="user-details" class="user-details hidden">
            <h2>User Details</h2>
            <img id="user-image" src="" alt="Profile Picture">
            <p><strong>Name:</strong> <span id="user-name"></span></p>
            <p><strong>Email:</strong> <span id="user-email"></span></p>
            <p><strong>Phone:</strong> <span id="user-phone"></span></p>
            <p><strong>Gender:</strong> <span id="user-gender"></span></p>
            
        </div>
    </div>

    <script>
        function fetchUserDetails(userId) {
            // Fetch user details using AJAX
            fetch(`get_user_details.php?id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data) {
                        // Update the user details section
                        document.getElementById("user-name").textContent = data.fullname;
                        document.getElementById("user-email").textContent = data.email;
                        document.getElementById("user-phone").textContent = data.number;
                        document.getElementById("user-gender").textContent = data.gender;
                        document.getElementById("user-image").src = data.profile_pic;

                        // Show the user details section
                        document.getElementById("user-details").classList.remove("hidden");
                    }
                })
                .catch(error => console.error("Error fetching user details:", error));
        }
    </script>
</body>
</html>