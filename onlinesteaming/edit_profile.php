<?php
session_start();
require 'db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$userEmail = $_SESSION['user_email'];

// Fetch user details including ID
$userSql = "SELECT id, fullname, number, profile_pic, email, password FROM users WHERE email = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$userResult = $stmt->get_result();
$user_details = $userResult->fetch_assoc();

// Set default profile picture path
$default_profile_pic = "default.jpg";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $number = $_POST['number'];
    $newEmail = $_POST['email'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $userId = $user_details['id'];

    // Initialize target_file with current profile picture
    $target_file = !empty($user_details['profile_pic']) ? $user_details['profile_pic'] : $default_profile_pic;

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        // File upload settings
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        
        // Generate unique filename
        $file_ext = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
        $file_name = 'profile_' . $userId . '_' . time() . '.' . $file_ext;
        $target_file = $target_dir . $file_name;
        
        // Validate image file
        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check !== false) {
            // Check file size (max 2MB)
            if ($_FILES["profile_pic"]["size"] <= 2000000) {
                // Allow certain file formats
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($file_ext, $allowed_types)) {
                    // Try to upload file
                    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                        // Delete old profile picture if it exists and isn't default
                        if (!empty($user_details['profile_pic']) && 
                            $user_details['profile_pic'] != $default_profile_pic && 
                            file_exists($user_details['profile_pic'])) {
                            unlink($user_details['profile_pic']);
                        }
                    } else {
                        echo "<script>alert('Error uploading file.');</script>";
                        $target_file = $user_details['profile_pic'] ?? $default_profile_pic;
                    }
                } else {
                    echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
                    $target_file = $user_details['profile_pic'] ?? $default_profile_pic;
                }
            } else {
                echo "<script>alert('File size too large (max 2MB).');</script>";
                $target_file = $user_details['profile_pic'] ?? $default_profile_pic;
            }
        } else {
            echo "<script>alert('File is not an image.');</script>";
            $target_file = $user_details['profile_pic'] ?? $default_profile_pic;
        }
    }

    // Prepare update parameters
    $params = [];
    $types = "";
    $fields = [];
    
    // Always update these fields
    $fields[] = "fullname = ?";
    $params[] = $fullname;
    $types .= "s";
    
    $fields[] = "number = ?";
    $params[] = $number;
    $types .= "s";
    
    $fields[] = "email = ?";
    $params[] = $newEmail;
    $types .= "s";
    
    $fields[] = "profile_pic = ?";
    $params[] = $target_file;
    $types .= "s";

    // Handle password change if all password fields are filled
    if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        if (password_verify($currentPassword, $user_details['password'])) {
            if ($newPassword === $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $fields[] = "password = ?";
                $params[] = $hashedPassword;
                $types .= "s";
            } else {
                echo "<script>alert('New passwords do not match.');</script>";
            }
        } else {
            echo "<script>alert('Current password is incorrect.');</script>";
        }
    }

    // Build and execute the update query
    $updateSql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $userId;
    $types .= "i";
    
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $_SESSION['user_email'] = $newEmail; // Update session email if changed
        echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('<?= htmlspecialchars($user_details['profile_pic'] ?: $default_profile_pic) ?>') no-repeat center center fixed;
            background-size: cover;
            padding: 50px;
            transition: background-image 0.5s ease-in-out;
        }

        /* Transparent Form Container */
        .container {
            width: 500px;
            background: rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin: auto;
            text-align: left;
            backdrop-filter: blur(10px);
        }

        /* Profile Image */
        .profile-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #0aa5e3;
            cursor: pointer;
        }

        /* Form Fields */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 150px;
            font-weight: bold;
            font-size: 16px;
            color: black;
        }

        .form-group input {
            flex: 1;
            padding: 10px;
            border: 1px solid rgba(15, 14, 14, 0.5);
            background: rgba(18, 17, 17, 0.3);
            color: black;
            border-radius: 5px;
            outline: none;
            font-size: 16px;
        }

        /* Placeholder Styling */
        .form-group input::placeholder {
            color: rgba(18, 18, 18, 0.7);
        }

        /* Hidden file input */
        #profile_pic {
            display: none;
        }

        /* Submit Button */
        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background-color: rgba(40, 167, 69, 0.8);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: 0.3s ease-in-out;
        }

        button:hover {
            background-color: rgba(33, 136, 56, 0.9);
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <!-- Profile Picture Section -->
    <div class="profile-container">
        <label for="profile_pic">
            <img id="profilePreview" src="<?= htmlspecialchars($user_details['profile_pic'] ?: $default_profile_pic) ?>" alt="Profile Image" class="profile-image">
        </label>
        <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewImage(event)">
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="fullname">Full Name:</label>
            <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($user_details['fullname']) ?>" required>
        </div>

        <div class="form-group">
            <label for="number">Phone Number:</label>
            <input type="text" name="number" id="number" value="<?= htmlspecialchars($user_details['number']) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_details['email']) ?>" required>
        </div>

        <h3>Change Password</h3>

        <div class="form-group">
            <label for="current_password">Current Password:</label>
            <input type="password" name="current_password" id="current_password" placeholder="Enter current password">
        </div>

        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" placeholder="Enter new password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password">
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            document.getElementById('profilePreview').src = reader.result;
            document.body.style.backgroundImage = `url('${reader.result}')`;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</body>
</html>