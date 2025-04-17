<?php
session_start();
require_once 'db_connect.php';

// Ensure required session data is available
if (!isset($_SESSION['user_id'])) {
    error_log("USER NOT LOGGED IN - REDIRECTING TO JOURNAL HOME");
    header('Location: journalHome.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $journalContent = $_POST['journalContent'] ?? '';
    $emotionId = $_POST['emotionId'] ?? '';
    $emotionalIntensityRating = $_POST['emotionalIntensityRating'] ?? '';
    $userId = $_SESSION['user_id'];
    
    // Enhanced logging for debugging
    error_log("===== JOURNALING.PHP =====");
    error_log("USER ID: $userId");
    error_log("EMOTION ID: $emotionId");
    error_log("INTENSITY RATING: $emotionalIntensityRating");
    
    // Get the current strategy ID from the database directly
    // This prevents duplicate assignments
    $sqlGetCurrentStrategy = "SELECT strategy_id FROM Assigned_Strategy 
                             WHERE user_id = :user_id AND is_current = 1
                             ORDER BY assigned_start_date DESC LIMIT 1";
    $stmtGetCurrentStrategy = $db->prepare($sqlGetCurrentStrategy);
    $stmtGetCurrentStrategy->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtGetCurrentStrategy->execute();
    $strategyId = $stmtGetCurrentStrategy->fetchColumn();
    
    if (!$strategyId) {
        // If user doesn't have a current strategy, get one based on emotion
        error_log("NO CURRENT STRATEGY FOUND - SELECTING ONE BASED ON EMOTION");
        
        // Get a strategy for this emotion
        $sqlGetStrategy = "SELECT esl.strategy_id 
                          FROM Emotional_Strategy_Link esl
                          WHERE esl.emotion_id = :emotion_id
                          ORDER BY RAND() LIMIT 1";
        $stmtGetStrategy = $db->prepare($sqlGetStrategy);
        $stmtGetStrategy->bindParam(':emotion_id', $emotionId, PDO::PARAM_INT);
        $stmtGetStrategy->execute();
        $strategyId = $stmtGetStrategy->fetchColumn();
        
        if (!$strategyId) {
            // Default to Deep Breathing (ID 2) if no matching strategy
            $strategyId = 2;
            error_log("NO MATCHING STRATEGY - USING DEFAULT ID: 2");
        }
        
        error_log("SELECTED STRATEGY ID: $strategyId");
        
        // Clear any existing current strategies
        $sqlClearCurrent = "UPDATE Assigned_Strategy SET is_current = 0 
                           WHERE user_id = :user_id";
        $stmtClearCurrent = $db->prepare($sqlClearCurrent);
        $stmtClearCurrent->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtClearCurrent->execute();
        
        // Create new assignment
        $sqlNewAssignment = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current)
                           VALUES (:user_id, :strategy_id, NOW(), 1)";
        $stmtNewAssignment = $db->prepare($sqlNewAssignment);
        $stmtNewAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtNewAssignment->bindParam(':strategy_id', $strategyId, PDO::PARAM_INT);
        $stmtNewAssignment->execute();
        
        $assignmentId = $db->lastInsertId();
        error_log("CREATED NEW ASSIGNMENT ID: $assignmentId");
    }
    
    // Store the strategy ID in session
    $_SESSION['strategy_id'] = $strategyId;
    
    // Save journal entry data to session
    $_SESSION['journalEntry'] = [
        'journalContent' => $journalContent,
        'emotionId' => $emotionId,
        'emotionalIntensityRating' => $emotionalIntensityRating
    ];
    
    // Redirect to journal assigned strategy page
    error_log("REDIRECTING TO JOURNAL ASSIGNED STRATEGY");
    header('Location: journalAssignedStrategy.php');
    exit;
} else {
    // Handle error if this script is not accessed via POST
    echo "Error: Invalid request method.";
    exit;
}
?>
