<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Set to local timezone
date_default_timezone_set('America/New_York'); 

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
    // Debug output
    error_log("Checking journal status for user: " . $userId);
    
    // Check if user has journaled today 
    $today = date('Y-m-d');
    error_log("Today's date for comparison: " . $today);
    
    $sqlJournal = "SELECT COUNT(*) FROM Journal_Entry 
                  WHERE user_id = :user_id 
                  AND DATE(journal_date) = :today";
    $stmtJournal = $db->prepare($sqlJournal);
    $stmtJournal->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtJournal->bindParam(':today', $today, PDO::PARAM_STR);
    $stmtJournal->execute();
    $journalCount = $stmtJournal->fetchColumn();
    
    error_log("Journal entry count for today: " . $journalCount);
    
    $response['journal_completed'] = ($journalCount > 0);
    
    // Get the most recent journal entry date for debugging
    $sqlLastEntry = "SELECT MAX(journal_date) FROM Journal_Entry WHERE user_id = :user_id";
    $stmtLastEntry = $db->prepare($sqlLastEntry);
    $stmtLastEntry->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtLastEntry->execute();
    $lastEntry = $stmtLastEntry->fetchColumn();
    
    error_log("Most recent journal entry date: " . $lastEntry);
    if ($lastEntry) {
        $lastEntryDate = date('Y-m-d', strtotime($lastEntry));
        error_log("Formatted last entry date: " . $lastEntryDate);
        $response['last_entry_date'] = $lastEntryDate;
        $response['today_date'] = $today;
        $response['dates_match'] = ($lastEntryDate == $today);
    }
    
    // Get current assigned strategy
    $sqlStrategy = "SELECT cs.strategy_name 
                   FROM Assigned_Strategy AS a
                   JOIN Coping_Strategy AS cs ON a.strategy_id = cs.strategy_id
                   WHERE a.user_id = :user_id AND a.is_current = 1
                   ORDER BY a.assigned_start_date DESC
                   LIMIT 1";
    $stmtStrategy = $db->prepare($sqlStrategy);
    $stmtStrategy->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtStrategy->execute();
    $strategy = $stmtStrategy->fetch(PDO::FETCH_ASSOC);
    
    if ($strategy) {
        $response['strategy_name'] = $strategy['strategy_name'];
    } else {
        $response['strategy_name'] = 'Not assigned';
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database error in getHomePageInfo.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
