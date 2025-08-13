<?php
session_start(); // Ensure session is started before using session variables

// Database connection
$servername = "localhost";
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$database = "stream"; // Ensure this matches your DB name

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Login logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Email or phone number
    $password = $_POST['password'];

    if ($username === "admin@gmail.com" && $password === "ADMIN") {
        $_SESSION['admin_logged_in'] = true; // Corrected session variable
        $_SESSION['admin_email'] = $username; // Store admin email
        echo "<script>alert('Admin Login Successful!'); window.location.href='admin.php';</script>";
        exit();
    }
    

    // Query to check user
    $sql = "SELECT id, fullname, email, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $fullname, $email, $hashedPassword);
        $stmt->fetch();

        // Verify Password
        if (password_verify($password, $hashedPassword)) {
            // Store user info in session
            $_SESSION['user_id'] = $id;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['user_email'] = $email; // Store actual email instead of username input

            echo "<script>alert('Login successful!'); window.location.href='home.php';</script>";
            exit();
        } else {
            echo "<script>alert('Incorrect password!');</script>";
        }
    } else {
        echo "<script>alert('No account found with this email/phone number!');</script>";
    }

    $stmt->close();
}
$conn->close();
?>


<?php
$image_path = 'img/img1.jpeg';
if (!file_exists($image_path)) {
    // Use a fallback image or show an error message
    $image_path = 'img/loginbg.jpg';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - Naturals</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style>
        * { padding: 0; margin: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            background-image: url('<?php echo $image_path; ?>');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center center;
            height: 100vh;
        }
        form {
            width: 500px;
            background-color: rgba(241, 239, 239, 0.13);
            position: absolute;
            transform: translate(-50%, -50%);
            top: 50%;
            left: 50%;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 40px rgba(8, 7, 16, 0.6);
            padding: 50px 35px;
            text-align: center;
        }
        h3 {
            font-size: 30px;
            font-weight: 500;
            color: black;
        }
        input {
            display: block;
            height: 50px;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.07);
            border-radius: 3px;
            padding: 0 10px;
            margin-top: 8px;
            font-size: 14px;
            border: none;
            color: black;
        }
        input::placeholder { color: black; }
        button {
            margin-top: 20px;
            width: 100%;
            background-color: #0aa5e3;
            color: #fff;
            padding: 15px 0;
            font-size: 18px;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #0894c2;
        }
        .signup-link {
            margin-top: 15px;
            font-size: 14px;
        }
        .signup-link a {
            color: rgb(15, 15, 15);
            text-decoration: none;
            font-weight: 600;
        }
        .admin-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #0aa5e3;
            color: #eaeff3;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .admin-button:hover { background-color: #0c92cd; }
    </style>
</head>
<body>
    <form method="POST">
        <a href="registration.php" class="admin-button">Sign Up</a>
        <h3>User Login</h3>

        <input type="text" name="username" placeholder="Email or Phone" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>

    </form>
</body>
</html>
