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
