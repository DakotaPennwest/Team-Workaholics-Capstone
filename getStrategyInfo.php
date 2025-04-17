<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$response = ['success' => true];

try {
    // Get current assigned strategy
    $sql = "SELECT cs.strategy_name 
           FROM Assigned_Strategy AS a
           JOIN Coping_Strategy AS cs ON a.strategy_id = cs.strategy_id
           WHERE a.user_id = :user_id AND a.is_current = 1
           ORDER BY a.assigned_start_date DESC
           LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $strategy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($strategy) {
        $response['strategy_name'] = $strategy['strategy_name'];
    } else {
        $response['strategy_name'] = 'Not assigned';
    }
    
    // Count total journal entries for this user
    $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtCount->execute();
    $entryCount = $stmtCount->fetchColumn();
    
    // Calculate journals until next strategy (mod 5)
    $entriesUntilNext = 5 - ($entryCount % 5);
    if ($entriesUntilNext == 5) {
        $entriesUntilNext = 5; // If they just got a new strategy(changed from 0)
    }
    
    $response['total_entries'] = $entryCount;
    $response['entries_until_next'] = $entriesUntilNext;
    
	 
    // Debug log
    error_log("getStrategyInfo.php - Total entries: $entryCount, Entries until next: $entriesUntilNext");
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
