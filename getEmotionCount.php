<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Check if the 'emotion' parameter is provided in the URL
if (!isset($_GET['emotion'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No emotion provided']);
    exit;
}

$emotion = $_GET['emotion'];

// Include your existing database connection
include_once 'db_connect.php';

// Query to count the number of times the given emotion has been inserted for the logged-in user
try {
    // Adjust the table and column names as necessary
    $sql = "SELECT COUNT(*) AS count FROM user_emotions WHERE user_id = :user_id AND emotion = :emotion";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'],
        ':emotion' => $emotion,
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['count' => $result['count']]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>
