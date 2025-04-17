<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // EXTENSIVE DEBUGGING
    error_log("===== STARTING SUBMIT FEEDBACK =====");
    
    // Validate that the user is logged in
    if (!isset($_SESSION['user_id'])) {
        error_log("ERROR: User not logged in");
        echo "User not logged in.";
        exit;
    }
    
    $userId = $_SESSION['user_id'];
    error_log("USER ID: $userId");
    
    // Check if POST variables are set
    if (!isset($_POST['selectedEmotionValue']) || !isset($_POST['assignmentId'])) {
        error_log("ERROR: Missing POST data - selectedEmotionValue or assignmentId");
        echo "Feedback or Assignment ID not provided.";
        exit;
    }
    
    $assignmentId = $_POST['assignmentId'];
    $feedbackValue = $_POST['selectedEmotionValue'];
    $comments = isset($_POST['selectedEmotionName']) ? $_POST['selectedEmotionName'] : '';
    
    error_log("ASSIGNMENT ID: $assignmentId");
    error_log("FEEDBACK VALUE: $feedbackValue");
    error_log("COMMENTS: $comments");
    
    // Ensure assignment ID is numeric
    if (!is_numeric($assignmentId)) {
        error_log("ERROR: Invalid assignment ID (not numeric): $assignmentId");
        echo "Invalid Assignment ID.";
        exit;
    }
    
    try {
        // Check if the assignment exists
        $sqlCheckAssignment = "SELECT a.strategy_id, cs.strategy_name 
                             FROM Assigned_Strategy a
                             JOIN Coping_Strategy cs ON a.strategy_id = cs.strategy_id
                             WHERE a.assignment_id = :assignment_id";
        $stmtCheckAssignment = $db->prepare($sqlCheckAssignment);
        $stmtCheckAssignment->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmtCheckAssignment->execute();
        $assignmentData = $stmtCheckAssignment->fetch(PDO::FETCH_ASSOC);
        
        if (!$assignmentData) {
            error_log("ERROR: Assignment ID $assignmentId not found!");
            echo "Assignment not found.";
            exit;
        }
        
        error_log("FOUND ASSIGNMENT - STRATEGY: " . $assignmentData['strategy_name'] . " (ID: " . $assignmentData['strategy_id'] . ")");
        
        // Convert feedback value to boolean (helpful = 1, unhelpful = 0)
        $isHelpful = ($feedbackValue === 'helpful') ? 1 : 0;
        error_log("IS HELPFUL: " . ($isHelpful ? "YES" : "NO"));
        
        // 1. Save feedback
        $sqlFeedback = "INSERT INTO Strategy_Feedback (assignment_id, is_helpful, comments) 
                       VALUES (:assignment_id, :is_helpful, :comments)";
        $stmtFeedback = $db->prepare($sqlFeedback);
        $stmtFeedback->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmtFeedback->bindParam(':is_helpful', $isHelpful, PDO::PARAM_BOOL);
        $stmtFeedback->bindParam(':comments', $comments, PDO::PARAM_STR);
        $stmtFeedback->execute();
        
        if ($stmtFeedback->rowCount() > 0) {
            error_log("FEEDBACK SAVED SUCCESSFULLY");
        } else {
            error_log("WARNING: Feedback insert returned 0 rows affected");
        }
        
        // Check if we need a new strategy
        if (isset($_SESSION['needs_new_strategy']) && $_SESSION['needs_new_strategy']) {
            error_log("NEEDS NEW STRATEGY FLAG DETECTED");
            unset($_SESSION['needs_new_strategy']);
            
            // End the current strategy assignment
            $sqlEnd = "UPDATE Assigned_Strategy 
                     SET assignment_end_date = NOW(), is_current = 0 
                     WHERE assignment_id = :assignment_id";
            $stmtEnd = $db->prepare($sqlEnd);
            $stmtEnd->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
            $result = $stmtEnd->execute();
            error_log("END CURRENT ASSIGNMENT RESULT: " . ($result ? "SUCCESS" : "FAILURE") . " (Rows: " . $stmtEnd->rowCount() . ")");
            
            // Reset ALL existing assignments
            $sqlReset = "UPDATE Assigned_Strategy 
                       SET is_current = 0, assignment_end_date = NOW() 
                       WHERE user_id = :user_id";
            $stmtReset = $db->prepare($sqlReset);
            $stmtReset->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $result = $stmtReset->execute();
            error_log("RESET ALL ASSIGNMENTS RESULT: " . ($result ? "SUCCESS" : "FAILURE") . " (Rows: " . $stmtReset->rowCount() . ")");
            
            // Get user's latest emotion
            $sqlGetEmotion = "SELECT e.emotion_id, e.emotion_name 
                            FROM Journal_Entry j
                            JOIN Emotion e ON j.emotion_id = e.emotion_id
                            WHERE j.user_id = :user_id 
                            ORDER BY j.journal_date DESC 
                            LIMIT 1";
            $stmtGetEmotion = $db->prepare($sqlGetEmotion);
            $stmtGetEmotion->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtGetEmotion->execute();
            $emotionData = $stmtGetEmotion->fetch(PDO::FETCH_ASSOC);
            
            if ($emotionData) {
                $emotionId = $emotionData['emotion_id'];
                $emotionName = $emotionData['emotion_name'];
                error_log("FOUND LATEST EMOTION: $emotionName (ID: $emotionId)");
            } else {
                $emotionId = 1; // Default
                error_log("NO EMOTION FOUND - USING DEFAULT ID: 1");
            }
            
            // Get a strategy for this emotion
            $sqlGetStrategy = "SELECT esl.strategy_id, cs.strategy_name
                              FROM Emotional_Strategy_Link esl
                              JOIN Coping_Strategy cs ON esl.strategy_id = cs.strategy_id
                              WHERE esl.emotion_id = :emotion_id
                              ORDER BY RAND() LIMIT 1";
            $stmtGetStrategy = $db->prepare($sqlGetStrategy);
            $stmtGetStrategy->bindParam(':emotion_id', $emotionId, PDO::PARAM_INT);
            $stmtGetStrategy->execute();
            $strategyData = $stmtGetStrategy->fetch(PDO::FETCH_ASSOC);
            
            if ($strategyData) {
                $newStrategyId = $strategyData['strategy_id'];
                $strategyName = $strategyData['strategy_name'];
                error_log("FOUND MATCHING STRATEGY: $strategyName (ID: $newStrategyId)");
            } else {
                $newStrategyId = 2; // Default
                error_log("NO MATCHING STRATEGY - USING DEFAULT ID: 2 (Deep Breathing)");
                
                // Get default strategy name
                $sqlGetName = "SELECT strategy_name FROM Coping_Strategy WHERE strategy_id = 2";
                $stmtGetName = $db->prepare($sqlGetName);
                $stmtGetName->execute();
                $strategyName = $stmtGetName->fetchColumn();
            }
            
            // Insert new strategy assignment
            $sqlInsertNew = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current) 
                           VALUES (:user_id, :strategy_id, NOW(), 1)";
            $stmtInsertNew = $db->prepare($sqlInsertNew);
            $stmtInsertNew->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtInsertNew->bindParam(':strategy_id', $newStrategyId, PDO::PARAM_INT);
            $result = $stmtInsertNew->execute();
            
            if ($result) {
                $newAssignmentId = $db->lastInsertId();
                error_log("NEW ASSIGNMENT CREATED - ID: $newAssignmentId, STRATEGY: $strategyName (ID: $newStrategyId)");
                
                // Update session
                $_SESSION['strategy_id'] = $newStrategyId;
                $_SESSION['assignment_id'] = $newAssignmentId;
                
                // Redirect to see the new strategy
                error_log("REDIRECTING TO STRATEGIES CURRENT STRATEGY PAGE");
                echo "<script>
                    if (window.localStorage) {
                        localStorage.removeItem('homepageData');
                        localStorage.setItem('reloadHomepage', 'true');
                    }
                    console.log('Redirecting to see new strategy: $strategyName');
                    window.location.replace('strategiesCurrentStrategy.html');
                </script>";
                exit();
            } else {
                $errorInfo = print_r($stmtInsertNew->errorInfo(), true);
                error_log("FAILED TO CREATE NEW ASSIGNMENT: $errorInfo");
            }
        } else {
            error_log("NO NEW STRATEGY NEEDED - REDIRECTING TO HOMEPAGE");
            // Redirect to homepage
            echo "<script>
                if (window.localStorage) {
                    localStorage.removeItem('homepageData');
                    localStorage.setItem('reloadHomepage', 'true');
                }
                window.location.replace('homepage.html');
            </script>";
            exit();
        }
    } catch (PDOException $e) {
        $errorMessage = $e->getMessage();
        error_log("DATABASE ERROR: $errorMessage");
        echo "Error submitting feedback: $errorMessage";
    }
} else {
    error_log("NOT A POST REQUEST - REDIRECTING TO FEEDBACK PAGE");
    header("Location: journalStrategyFeedback.html");
    exit();
}
?>
