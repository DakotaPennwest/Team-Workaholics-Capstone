<?php
session_start();
require_once 'db_connect.php';
require_once 'updateAssignment.php';

// Ensure necessary session data exists
if (!isset($_SESSION['user_id'], $_SESSION['journalEntry'], $_SESSION['strategy_id'])) {
    header('Location: journalHome.html');
    exit;
}

$data = $_SESSION['journalEntry'];
$userId = $_SESSION['user_id'];
$strategyId = $_SESSION['strategy_id'];

// Check to prevent duplicate entries in the journal_entry table
$sqlCheck = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = ? AND emotion_id = ? AND strategy_id = ?";
$stmtCheck = $db->prepare($sqlCheck);
$stmtCheck->execute([$userId, $data['emotionId'], $strategyId]);
$entryExists = $stmtCheck->fetchColumn();

if ($entryExists) {
    // If the entry already exists, redirect as if it's a successful entry
    // Clear the session data to prevent re-submission if needed.
    unset($_SESSION['journalEntry']);
    unset($_SESSION['strategy_id']);

    // Redirect to the success page directly
    header('Location: strategiesCurrentStrategy.html');
    exit(); // Exit to prevent further execution
}

// Insert the new journal entry if no duplicate is found
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

if ($stmt->rowCount() > 0) {
    // Clear the session data after a successful insertion
    unset($_SESSION['journalEntry']);
    unset($_SESSION['strategy_id']);
}

// Update the assignment cycle and check if it's the fifth entry
$isFifthEntry = updateAssignmentCycle($db, $userId, $strategyId);
error_log("saveJournalEntry.php - Is fifth entry: " . ($isFifthEntry ? "Yes" : "No"));

// Redirect based on whether it's the fifth entry
if ($isFifthEntry) {
    header('Location: journalStrategyFeedback.html');
} else {
    header('Location: strategiesCurrentStrategy.html');
}

exit;
?>
