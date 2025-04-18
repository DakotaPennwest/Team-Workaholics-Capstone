<?php
session_start();

// Ensure the user is logged in (check for user_id)
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get emotion name from the session
if (!isset($_SESSION['emotion_name'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No emotion name in session']); // More specific error
    exit;
}
$emotionName = $_SESSION['emotion_name']; // Use emotion_name from session

include_once 'db_connect.php';

// Query to count the number of times the given emotion has been inserted for the logged-in user
try {
    // Correctly query the journal_entry table
    $sql = "SELECT COUNT(*) AS count FROM Journal_Entry WHERE user_id = ? AND emotion_id = (SELECT emotion_id FROM Emotion WHERE emotion_name = ?)"; // Use emotion_id in both tables
    $stmt = $db->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $emotionName]); // Use emotionName from session (capitalized)
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['count' => $result['count']]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    exit;
}
?>
