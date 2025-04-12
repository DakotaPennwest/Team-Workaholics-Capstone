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

     // Proceed with the assignment cycle update if the count is at threshold
    if ($entryCount >= 5) {
        $updated = updateAssignmentCycle($db, $userId, $_SESSION['strategy_id']);
        error_log("Assignment update triggered: " . ($updated ? "Yes" : "No"));
    }

    // Insert the journal entry into the database.
    $sqlInsert = "INSERT INTO Journal_Entry (user_id, emotion_id, strategy_id, journal_content, emotional_intensity_rating) VALUES (?, ?, ?, ?, ?)";
    $stmtInsert = $db->prepare($sqlInsert);
    $stmtInsert->execute([$userId, $emotionId, $_SESSION['strategy_id'], $journalContent, $emotionalIntensityRating]);

    // Always redirect to journalAssignedStrategy.php after processing
    header('Location: journalAssignedStrategy.php');
    exit;
} else {
    // Handle error if this script is not accessed via POST
    echo "Error: Invalid request method.";
    exit;
}
?>
