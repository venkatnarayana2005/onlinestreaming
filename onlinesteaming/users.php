<?php
// Step 1: Connect to the Database
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "stream"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 2: Handle Delete Request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM users WHERE id = $delete_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('User deleted successfully!'); window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $conn->error . "');</script>";
    }
}

// Step 3: Query the Database
$sql = "SELECT id, fullname, email, number, gender, created_at, profile_pic FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="userstyle.css">
    <title>Users List</title>
    
</head>
<body>

<h2>Users List</h2>

<?php
// Step 4: Display the Data
if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Gender</th>
                <th>Created At</th>
                <th>Profile Picture</th>
                <th>Action</th>
            </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row["id"]."</td>
                <td>".$row["fullname"]."</td>
                <td>".$row["email"]."</td>
                <td>".$row["number"]."</td>
                <td>".$row["gender"]."</td>
                <td>".$row["created_at"]."</td>
                <td><img src='".$row["profile_pic"]."' alt='Profile Picture' class='profile-pic'></td>
                <td>
                    <a href='users.php?delete_id=".$row["id"]."' class='delete-btn' onclick='return confirm(\"Are you sure you want to delete this user?\");'>Delete</a>
                </td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='no-results'>No users found.</p>";
}

// Close the connection
$conn->close();
?>

<!-- Dashboard Button -->
<button class="dashboard-btn" onclick="window.location.href='admin.php'">Dashboard</button>

</body>
</html>
