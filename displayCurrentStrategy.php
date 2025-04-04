<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Query the latest active assignment for this user from the Assigned_Strategy table
$sql = "SELECT * FROM Assigned_Strategy WHERE user_id = ? AND is_current = 1 ORDER BY assigned_start_date DESC LIMIT 1";
$stmt = $db->prepare($sql);
$stmt->execute([$userId]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($assignment) {
    // Get the coping strategy details using the strategy_id from the assignment
    $strategyId = $assignment['strategy_id'];
    $sql2 = "SELECT strategy_id, strategy_name, strategy_descript, strategy_image FROM Coping_Strategy WHERE strategy_id = ?";
    $stmt2 = $db->prepare($sql2);
    $stmt2->execute([$strategyId]);
    $strategy = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($strategy) {
        echo json_encode(['success' => true, 'data' => $strategy]);
        exit;
    }
}

// If no active assignment exists, default to Deep Breathing (for example, strategy_id = 2)
$sqlDefault = "SELECT strategy_id, strategy_name, strategy_descript, strategy_image FROM Coping_Strategy WHERE strategy_id = 2";
$stmtDefault = $db->prepare($sqlDefault);
$stmtDefault->execute();
$defaultStrategy = $stmtDefault->fetch(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $defaultStrategy]);
exit;
?>
