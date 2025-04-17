<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Check that the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
error_log("===== GET ASSIGNED STRATEGY =====");
error_log("USER ID: $userId");

// First, check if the user has any current assigned strategy
$sqlCheckCurrent = "SELECT a.strategy_id, a.assignment_id, cs.strategy_name, cs.strategy_image AS strategy_image_url, cs.strategy_descript
                  FROM Assigned_Strategy a
                  JOIN Coping_Strategy cs ON a.strategy_id = cs.strategy_id
                  WHERE a.user_id = :user_id AND a.is_current = 1
                  ORDER BY a.assigned_start_date DESC
                  LIMIT 1";
$stmtCheckCurrent = $db->prepare($sqlCheckCurrent);
$stmtCheckCurrent->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmtCheckCurrent->execute();
$currentStrategy = $stmtCheckCurrent->fetch(PDO::FETCH_ASSOC);

// If user has a current strategy, return it
if ($currentStrategy) {
    error_log("FOUND CURRENT STRATEGY: " . $currentStrategy['strategy_name']);
    
    // Update session with the current strategy information
    $_SESSION['strategy_id'] = $currentStrategy['strategy_id'];
    $_SESSION['strategy_name'] = $currentStrategy['strategy_name'];
    $_SESSION['strategy_image_url'] = $currentStrategy['strategy_image_url'];
    
    echo json_encode(['success' => true, 'data' => $currentStrategy]);
    exit;
}

// If there's no current strategy, check if we need to assign one based on the latest emotion
if (isset($_SESSION['journalEntry']['emotionId']) && !empty($_SESSION['journalEntry']['emotionId'])) {
    $emotionId = $_SESSION['journalEntry']['emotionId'];
    error_log("FOUND EMOTION ID IN SESSION: $emotionId");
    
    // Get a strategy for this emotion
    $sqlGetStrategy = "SELECT cs.strategy_id, cs.strategy_name, cs.strategy_image AS strategy_image_url, cs.strategy_descript
                      FROM Emotional_Strategy_Link esl
                      JOIN Coping_Strategy cs ON esl.strategy_id = cs.strategy_id
                      WHERE esl.emotion_id = :emotion_id
                      ORDER BY RAND()
                      LIMIT 1";
    $stmtGetStrategy = $db->prepare($sqlGetStrategy);
    $stmtGetStrategy->bindParam(':emotion_id', $emotionId, PDO::PARAM_INT);
    $stmtGetStrategy->execute();
    $strategy = $stmtGetStrategy->fetch(PDO::FETCH_ASSOC);
    
    if ($strategy) {
        error_log("FOUND STRATEGY FOR EMOTION: " . $strategy['strategy_name']);
        
        // Check journal entries count to decide if we should assign this strategy
        $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id";
        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCount->execute();
        $entryCount = $stmtCount->fetchColumn();
        
        error_log("USER HAS $entryCount JOURNAL ENTRIES");
        
        // Only assign on first entry (subsequent assignments handled by saveJournalEntry.php)
        if ($entryCount == 0) {
            error_log("FIRST ENTRY - ASSIGNING STRATEGY");
            
            // Create new assignment
            $sqlInsert = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current)
                        VALUES (:user_id, :strategy_id, NOW(), 1)";
            $stmtInsert = $db->prepare($sqlInsert);
            $stmtInsert->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtInsert->bindParam(':strategy_id', $strategy['strategy_id'], PDO::PARAM_INT);
            $stmtInsert->execute();
            
            $assignmentId = $db->lastInsertId();
            error_log("CREATED ASSIGNMENT ID: $assignmentId");
            $strategy['assignment_id'] = $assignmentId;
            
            // Update session
            $_SESSION['strategy_id'] = $strategy['strategy_id'];
            $_SESSION['strategy_name'] = $strategy['strategy_name'];
            $_SESSION['strategy_image_url'] = $strategy['strategy_image_url'];
            
            echo json_encode(['success' => true, 'data' => $strategy]);
            exit;
        }
    }
}

// If we reach here, the user has no assigned strategy and we shouldn't auto-assign one
error_log("NO STRATEGY ASSIGNED AND NOT ASSIGNING ONE");

// Return "Not assigned" status
$notAssignedResponse = [
    'strategy_id' => null,
    'strategy_name' => 'No strategy assigned',
    'strategy_image_url' => '',
    'strategy_descript' => 'Complete your first journal entry to get a coping strategy!',
    'assignment_id' => null
];

echo json_encode(['success' => true, 'data' => $notAssignedResponse]);
exit;
?>
