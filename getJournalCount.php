<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Ensure the necessary session data is set
if (!isset($_SESSION['user_id'], $_SESSION['strategy_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing session data']);
    exit;
}

$userId = $_SESSION['user_id'];
$strategyId = $_SESSION['strategy_id'];

// Query the database to count the journal entries for the current strategy
$sql = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = ? AND strategy_id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$userId, $strategyId]);
$count = $stmt->fetchColumn();

// Compute remaining entries needed to reach a cycle of 5
// For example, if threshold is 5 journal entries per cycle:
$target = 5;
$remaining = $target - ($count % $target);

// Optionally, if exactly a multiple of the target you might want to show 0 (or trigger feedback)
// Here, we assume if count is exactly a multiple (and > 0) then the cycle is complete.
if ($count > 0 && $count % $target == 0) {
    $remaining = 0;
}

echo json_encode([
    'success' => true,
    'count' => $count,
    'remaining' => $remaining
]);
?>
