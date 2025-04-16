<?php
session_start(); // Start the session
header('Content-Type: application/json'); // Set content type to JSON

// Ensure that the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Check if the 'emotion' parameter (which will be the emotion_id) is provided in the URL
if (!isset($_GET['emotion'])) {
    echo json_encode(['error' => 'No emotion ID provided']);
    exit;
}

// Retrieve the emotion_id from the query string
$emotionId = $_GET['emotion'];

// Include database connection
require_once 'db_connect.php';

try {
    // Query to count the number of journal entries for the given user_id and emotion_id
    $sql = "SELECT COUNT(*) AS count 
            FROM Journal_Entry 
            WHERE user_id = ? AND emotion_id = ?";
    
    // Prepare and execute the query
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $emotionId]);

    // Fetch the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return the count as JSON response
    echo json_encode(['success' => true, 'count' => (int)$result['count']]);
} catch (PDOException $e) {
    // Handle any potential SQL errors gracefully
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>
