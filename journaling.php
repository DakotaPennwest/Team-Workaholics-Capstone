<?php
session_start();
require_once 'db_connect.php';

// Ensure required session data is available
if (!isset($_SESSION['user_id'])) {
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
    
    // Get strategy ID from session or from database
    $strategyId = isset($_SESSION['strategy_id']) ? $_SESSION['strategy_id'] : null;
    
    if (!$strategyId) {
        // If no strategy in session, try to get current one from database
        error_log("NO STRATEGY ID IN SESSION - CHECKING DATABASE");
        
        $sqlCurrent = "SELECT strategy_id FROM Assigned_Strategy 
                      WHERE user_id = :user_id AND is_current = 1 
                      ORDER BY assigned_start_date DESC LIMIT 1";
        $stmtCurrent = $db->prepare($sqlCurrent);
        $stmtCurrent->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCurrent->execute();
        $strategyId = $stmtCurrent->fetchColumn();
        
        if ($strategyId) {
            error_log("FOUND CURRENT STRATEGY ID IN DATABASE: $strategyId");
            $_SESSION['strategy_id'] = $strategyId;
        } else {
            // Default to Deep Breathing if no strategy assigned
            $strategyId = 2; // Default to Deep Breathing
            error_log("NO STRATEGY FOUND - USING DEFAULT ID: 2 (Deep Breathing)");
        }
    } else {
        error_log("STRATEGY ID FROM SESSION: $strategyId");
    }
    
    // Save journal entry data to session for use in saveJournalEntry.php
    $_SESSION['journalEntry'] = [
        'journalContent' => $journalContent,
        'emotionId' => $emotionId,
        'emotionalIntensityRating' => $emotionalIntensityRating
    ];
    
    // Redirect to journalAssignedStrategy.php which will show the strategy
    // and then redirect to saveJournalEntry.php
    error_log("REDIRECTING TO journalAssignedStrategy.php");
    header('Location: journalAssignedStrategy.php');
    exit;
} else {
    // Handle error if this script is not accessed via POST
    echo "Error: Invalid request method.";
    exit;
}
?>
