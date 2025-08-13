<?php
// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stream";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// DELETE VIDEO LOGIC
if (isset($_GET['delete_id']) && isset($_GET['table'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure ID is an integer
    $table = $_GET['table']; // Get the table name from URL

    // Whitelist allowed table names to prevent SQL injection
    $allowed_tables = ['cartoon', 'gaming', 'news', 'sports', 'videos'];

    if (in_array($table, $allowed_tables)) {
        // Prepare DELETE query
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Video deleted successfully!'); window.location.href='uploaded_videos.php';</script>";
        } else {
            echo "<script>alert('Error deleting video.');</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Invalid table name.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Videos</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background-color: #121212; 
            color: white; 
            text-align: center; 
        }
        table { 
            width: 80%; 
            margin: auto; 
            border-collapse: collapse; 
            background: #1e1e1e; 
            color: white; 
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2); 
            border-radius: 8px;
            overflow: hidden;
        }
        th, td { 
            padding: 12px; 
            border: 1px solid #444; 
            text-align: center;
        }
        th { 
            background: #333; 
        }
        tr:nth-child(even) { 
            background: #2a2a2a; 
        }
        tr:hover {
            background: #3a3a3a;
            transition: 0.3s;
        }
        
        /* View Button */
        .view-btn {
            background: #007BFF;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            font-weight: bold;
        }
        .view-btn:hover {
            background: #0056b3;
            transform: scale(1.05);
        }

        /* Delete Button */
        .delete-btn {
            background: #ff4d4d;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            font-weight: bold;
        }
        .delete-btn:hover {
            background: #cc0000;
            transform: scale(1.05);
        }

        .dashboard-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .dashboard-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>All Uploaded Videos</h2>

<?php
// Fetch Videos from All Categories
$sql = "
    SELECT 'cartoon' AS category, id, title, uploaded_by, file_path, upload_date AS uploaded_at FROM cartoon
    UNION ALL
    SELECT 'gaming' AS category, id, title, uploaded_by, file_path, uploaded_at FROM gaming
    UNION ALL
    SELECT 'news' AS category, id, title, uploaded_by, file_path, uploaded_at FROM news
    UNION ALL
    SELECT 'sports' AS category, id, title, uploaded_by, file_path, uploaded_at FROM sports
    UNION ALL
    SELECT 'videos' AS category, id, title, uploaded_by, file_path, uploaded_at FROM videos
    ORDER BY uploaded_at DESC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Uploaded By</th>
                <th>Category</th>
                <th>File</th>
                <th>Uploaded At</th>
                <th>Action</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['uploaded_by']}</td>
                <td>{$row['category']}</td>
                <td><a href='{$row['file_path']}' target='_blank' class='view-btn'>View</a></td>
                <td>{$row['uploaded_at']}</td>
                <td>
                    <a href='uploaded_videos.php?delete_id={$row['id']}&table={$row['category']}' 
                       class='delete-btn' 
                       onclick='return confirm(\"Are you sure you want to delete this video?\");'>
                       Delete
                    </a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No videos found.</p>";
}

$conn->close();
?>

<br><br>
<!-- Dashboard Button -->
<button class="dashboard-btn" onclick="window.location.href='admin.php'">Dashboard</button>

</body>
</html>
