<?php
session_start();
require_once 'db_connect.php';
require_once 'updateAssignment.php';

// Ensure the necessary session data exists
if (!isset($_SESSION['user_id'], $_SESSION['journalEntry'], $_SESSION['strategy_id'])) {
    header('Location: journalHome.html');
    exit;
}

$data = $_SESSION['journalEntry'];
$userId = $_SESSION['user_id'];
$strategyId = $_SESSION['strategy_id'];

// Insert the new journal entry into Journal_Entry
$sqlInsert = "
    INSERT INTO Journal_Entry 
      (user_id, emotion_id, emotional_intensity_rating, strategy_id, journal_content, journal_date)
    VALUES (?, ?, ?, ?, ?, NOW())
";
$stmt = $db->prepare($sqlInsert);
$stmt->execute([
    $userId,
    $data['emotionId'],
    $data['emotionalIntensityRating'],
    $strategyId,
    $data['journalContent']
]);

// After insertion, call updateAssignmentCycle to check if this completes a cycle of 5 entries
$assignmentUpdated = updateAssignmentCycle($db, $userId, $strategyId);
error_log("saveJournalEntry.php - Assignment update result: " . ($assignmentUpdated ? "Updated" : "Not updated"));

// Redirect based on whether a cycle was completed:
// If updated (5 entries reached), send to feedback; otherwise, to the homepage.
if ($assignmentUpdated) {
    header('Location: journalStrategyFeedback.html');
} else {
    header('Location: strategiesCurrentStrategy.html');
}

exit;
?>
