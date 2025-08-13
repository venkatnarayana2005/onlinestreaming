<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stream";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["video_file"])) {
    $uploaded_by = "admin"; // Default uploader name
    $table_name = $_POST['table_name'];
    
    // Validate table name to prevent SQL injection
    $valid_tables = ['cartoon', 'gaming', 'news', 'sports', 'videos'];
    if (!in_array($table_name, $valid_tables)) {
        die("Invalid table selected");
    }

    // File upload handling
    $target_dir = "uploads/";
    $original_filename = basename($_FILES["video_file"]["name"]);
    $target_file = $target_dir . $original_filename;
    $uploadOk = 1;
    $videoFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $video_title = pathinfo($original_filename, PATHINFO_FILENAME);
    
    // Check if file was actually uploaded
    if ($_FILES["video_file"]["error"] !== UPLOAD_ERR_OK) {
        die("Error uploading file: " . $_FILES["video_file"]["error"]);
    }
    
    // Check file size (50MB max)
    if ($_FILES["video_file"]["size"] > 50000000) {
        die("Sorry, your file is too large.");
    }
    
    // Allow certain file formats
    $allowed_formats = ["mp4", "avi", "mov", "wmv", "flv"];
    if (!in_array($videoFileType, $allowed_formats)) {
        die("Sorry, only MP4, AVI, MOV, WMV & FLV files are allowed.");
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
        // Use prepared statement to prevent SQL injection
        if ($table_name == 'news' || $table_name == 'sports') {
            // Tables with description field
            $stmt = $conn->prepare("INSERT INTO $table_name (title, file_path, uploaded_by, description) VALUES (?, ?, ?, '')");
        } else {
            // Tables without description field
            $stmt = $conn->prepare("INSERT INTO $table_name (title, file_path, uploaded_by) VALUES (?, ?, ?)");
        }
        
        $stmt->bind_param("sss", $video_title, $target_file, $uploaded_by);
        
        if ($stmt->execute()) {
            echo "<script>alert('Video uploaded successfully!'); window.location.href='admin.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

// Get all valid tables for the select option
$valid_tables = ['cartoon', 'gaming', 'news', 'sports', 'videos'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 20px;
        }
        .upload-form {
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 80%;
            max-width: 500px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #ffffff;
        }
        select {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #333;
            background-color: #2a2a2a;
            color: #e0e0e0;
        }
        .file-upload {
            margin-bottom: 20px;
        }
        .file-upload-label {
            display: block;
            padding: 15px;
            border: 2px dashed #444;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            background-color: #2a2a2a;
        }
        .file-upload-label:hover {
            border-color: #555;
        }
        #file-name-display {
            display: block;
            margin-top: 5px;
            font-size: 14px;
            color: #aaa;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .submit-btn:hover {
            background-color: #45a049;
        }
        .dashboard-btn {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            text-decoration: none;
        }
        .dashboard-btn:hover {
            background-color: #444;
        }
    </style>
</head>
<body>
    <div class="upload-form">
        <h2>Upload Video</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="table_name">Select Category</label>
                <select id="table_name" name="table_name" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($valid_tables as $table): ?>
                        <option value="<?php echo htmlspecialchars($table); ?>">
                            <?php echo htmlspecialchars(ucfirst($table)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group file-upload">
                <label for="video_file" class="file-upload-label">
                    Choose Video File
                    <span id="file-name-display">No file selected</span>
                </label>
                <input type="file" id="video_file" name="video_file" accept="video/*" required style="display: none;">
            </div>
            
            <button type="submit" class="submit-btn" name="submit">Upload Video</button>
        </form>
    </div>
    
    <a href="admin.php" class="dashboard-btn">Back to Dashboard</a>

    <script>
        // Display selected filename
        document.getElementById('video_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('file-name-display').textContent = fileName;
        });
    </script>
</body>
</html> 