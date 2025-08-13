<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Default for XAMPP
$password = ""; // Default password (empty)
$dbname = "stream"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmpassword = mysqli_real_escape_string($conn, $_POST['confirmpassword']);
    $number = mysqli_real_escape_string($conn, $_POST['number']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    // Check if profile picture is uploaded
    if (empty($_FILES['profile']['name'])) {
        echo "<script>alert('Profile picture is required!'); window.location.href='registration.php';</script>";
        exit();
    }

    // Handle profile picture upload
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $profilePic = $targetDir . basename($_FILES["profile"]["name"]);
    
    if (!move_uploaded_file($_FILES["profile"]["tmp_name"], $profilePic)) {
        echo "<script>alert('Error uploading the profile picture.'); window.location.href='registration.php';</script>";
        exit();
    }

    // Password validation
    if ($password !== $confirmpassword) {
        echo "<script>alert('Passwords do not match!'); window.location.href='registration.php';</script>";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (fullname, email, password, number, gender, profile_pic, created_at) 
            VALUES ('$fullname', '$email', '$hashedPassword', '$number', '$gender', '$profilePic', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration Successful! Redirecting to login...'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registration - Naturals</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="regstyle.css">
    
    <!-- Add this style just for the background image -->
    <style>
        body {
            background-image: url('img/loginbg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
    </style>
</head>
<body>
    <form action="registration.php" method="POST" enctype="multipart/form-data">
        <a href="login.php" class="admin-button">Login</a>
        <h3>Sign Up</h3>
        
        <!-- Profile Picture Upload -->
        <div class="profile-pic-container">
            <img src="default.jpg" id="profileImage" class="profile-pic" onclick="document.getElementById('file-input').click();">
            <input type="file" id="file-input" name="profile" accept="image/*" onchange="previewImage(event)" required>
            <i class="fas fa-camera upload-icon" onclick="document.getElementById('file-input').click();"></i>
        </div>

        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirmpassword" placeholder="Confirm Password" required>
        <input type="number" name="number" placeholder="Phone Number" required>
        
        <select name="gender" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        
        <button type="submit">Sign Up</button>
    </form>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('profileImage').src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>
</html>