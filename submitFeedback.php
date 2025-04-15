<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate that the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        exit;
    }
    
    // Check if POST variables are set
    if (!isset($_POST['selectedEmotionValue']) || !isset($_POST['assignmentId'])) {
        echo "Feedback or Assignment ID not provided.";
        exit;
    }
    
    $assignmentId = $_POST['assignmentId'];
    $feedbackValue = $_POST['selectedEmotionValue'];
    $comments = isset($_POST['selectedEmotionName']) ? $_POST['selectedEmotionName'] : '';
    
    // Ensure assignment ID is numeric
    if (!is_numeric($assignmentId)) {
        echo "Invalid Assignment ID.";
        exit;
    }
    
    // Set boolean value for feedback
    $isHelpful = ($feedbackValue === 'helpful') ? true : false; 
    
    try {
        // 1. Save feedback
        $sqlFeedback = "INSERT INTO Strategy_Feedback (assignment_id, is_helpful, comments) 
                       VALUES (:assignment_id, :is_helpful, :comments)";
        $stmtFeedback = $db->prepare($sqlFeedback);
        $stmtFeedback->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmtFeedback->bindParam(':is_helpful', $isHelpful, PDO::PARAM_BOOL);
        $stmtFeedback->bindParam(':comments', $comments, PDO::PARAM_STR);
        $stmtFeedback->execute();
        
        // 2. End the current strategy assignment
        $userId = $_SESSION['user_id'];
        $sqlEnd = "UPDATE Assigned_Strategy 
                  SET assignment_end_date = NOW(), is_current = 0 
                  WHERE assignment_id = :assignment_id";
        $stmtEnd = $db->prepare($sqlEnd);
        $stmtEnd->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmtEnd->execute();
        
        // 3. Assign a new random strategy
        $sqlGetStrategy = "SELECT strategy_id FROM Coping_Strategy ORDER BY RAND() LIMIT 1";
        $stmtGetStrategy = $db->prepare($sqlGetStrategy);
        $stmtGetStrategy->execute();
        $newStrategyId = $stmtGetStrategy->fetchColumn();
        
        $sqlNewAssignment = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current) 
                            VALUES (:user_id, :strategy_id, NOW(), 1)";
        $stmtNewAssignment = $db->prepare($sqlNewAssignment);
        $stmtNewAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtNewAssignment->bindParam(':strategy_id', $newStrategyId, PDO::PARAM_INT);
        $stmtNewAssignment->execute();
        
        // 4. Redirect to see the new strategy
        header("Location: strategiesCurrentStrategy.html");
        exit();
        
    } catch (PDOException $e) {
        error_log("Error in submitFeedback.php: " . $e->getMessage());
        echo "There was a problem processing your feedback. Please try again.";
    }
} else {
    // If not a POST request, redirect to the feedback form page
    header("Location: journalStrategyFeedback.html");
    exit();
}
?>
