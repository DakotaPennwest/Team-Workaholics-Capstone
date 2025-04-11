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
    // Retrieve POST data
    $journalContent = $_POST['journalContent'] ?? '';
    $emotionId = $_POST['emotionId'] ?? '';
    $emotionalIntensityRating = $_POST['emotionalIntensityRating'] ?? '';
    $userId = $_SESSION['user_id'];

    // Log for debugging
    error_log("journaling.php - Received emotionId: " . $emotionId);
    error_log("journaling.php - Received emotionalIntensityRating: " . $emotionalIntensityRating);

    // Save journal entry data to session
    $_SESSION['journalEntry'] = [
        'journalContent' => $journalContent,
        'emotionId' => $emotionId,
        'emotionalIntensityRating' => $emotionalIntensityRating
    ];

    // Count the current number of journal entries for this user and emotion
    $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = ? AND emotion_id = ?";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([$userId, $emotionId]);
    $entryCount = $stmtCount->fetchColumn();
    error_log("journaling.php - Journal entry count for emotion_id {$emotionId}: " . $entryCount);

    // Regardless of the journal entry count, always update the assignment cycle if necessary
    if ($entryCount >= 5) {
        $updated = updateAssignmentCycle($db, $userId, $_SESSION['strategy_id']);
        error_log("Current journal entry count: " . $entryCount);
        error_log("Assignment update triggered: " . ($updated ? "Yes" : "No"));
    }

    // Always redirect to journalAssignedStrategy.php after processing
    header('Location: journalAssignedStrategy.php');
    exit;
} else {
    echo "Error: Invalid request method.";
    exit;
}
?>
