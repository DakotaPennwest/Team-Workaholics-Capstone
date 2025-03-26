<?php
session_start();
require_once 'db_connect.php';
require_once 'updateAssignment.php'; // This file contains the updateAssignmentCycle() function

// Ensure required session data is available
if (!isset($_SESSION['user_id'], $_SESSION['journalEntry']['emotionId'], $_SESSION['strategy_id'])) {
    header('Location: journalHome.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $journalContent = $_POST['journalContent'] ?? '';
    $emotionId = $_POST['emotionId'] ?? '';
    $emotionalIntensityRating = $_POST['emotionalIntensityRating'] ?? '';
    $userId = $_SESSION['user_id'];

    // Log for debugging
    error_log("journaling.php - Received emotionId: " . $emotionId);
    error_log("journaling.php - Received emotionalIntensityRating: " . $emotionalIntensityRating);

    // Save journal entry data to session (if needed later)
    $_SESSION['journalEntry'] = [
        'journalContent' => $journalContent,
        'emotionId' => $emotionId,
        'emotionalIntensityRating' => $emotionalIntensityRating
    ];

    // Query to count the current number of entries for this user and emotion
    $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = ? AND emotion_id = ?";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([$userId, $emotionId]);
    $entryCount = $stmtCount->fetchColumn();
    error_log("journaling.php - Journal entry count for emotion_id {$emotionId}: " . $entryCount);

    // If the count is 4, then this submission will be the 5th entry.
    if ($entryCount == 4) {
        // Update the assignment cycle (this function will update the Assigned_Strategy table)
        $updated = updateAssignmentCycle($db, $userId, $_SESSION['strategy_id']);
        error_log("Assignment update triggered: " . ($updated ? "Yes" : "No"));
        header('Location: journalAssignedStrategy.php');
    } else {
        header('Location: strategiesCurrentStrategy.html');
    }
    exit;
} else {
    echo "Error: Invalid request method.";
    exit;
}
?>
