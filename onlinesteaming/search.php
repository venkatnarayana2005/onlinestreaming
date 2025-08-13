<?php
require 'db.php'; // Database connection

$search = isset($_GET['query']) ? trim($_GET['query']) : '';

if (!$conn) {
    die("Database connection error.");
}

$suggestions = [];

if ($search !== '') {
    $sql = "
        SELECT title FROM videos WHERE title LIKE ?
        UNION
        SELECT title FROM cartoon WHERE title LIKE ?
        UNION
        SELECT title FROM gaming WHERE title LIKE ?
        UNION
        SELECT title FROM sports WHERE title LIKE ?
        UNION
        SELECT title FROM news WHERE title LIKE ?
    ";

    $stmt = $conn->prepare($sql);
    $searchTerm = "%$search%";
    $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['title'];
    }

    $stmt->close();
}

$conn->close();

echo json_encode($suggestions);
?>
