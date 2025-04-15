<?php
session_start();
require_once 'db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for user_id in the session
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        exit;
    }
    
    $user_id = $_SESSION['user_id']; // Get the user ID from the session
    $selectedEmotionName = $_POST['selectedEmotionName'] ?? ''; // Capture feedback comments
    $selectedEmotionValue = $_POST['selectedEmotionValue'] ?? ''; // Capture helpful/unhelpful
    $assignmentId = $_POST['assignmentId'] ?? null; // Get assignment ID if provided
    
    // Convert feedback to boolean for easier processing
    $isHelpful = ($selectedEmotionValue === 'helpful') ? 1 : 0; 
    
    // Use the provided assignment ID if available, otherwise find the current one
    if (!$assignmentId) {
        $stmtFind = $conn->prepare("SELECT assignment_id 
                                FROM Assigned_Strategy 
                                WHERE user_id = ? AND is_current = 1 
                                ORDER BY assigned_start_date DESC LIMIT 1");
        $stmtFind->bind_param("i", $user_id);
        $stmtFind->execute();
        $resultFind = $stmtFind->get_result();
        
        if ($row = $resultFind->fetch_assoc()) {
            $assignmentId = $row['assignment_id'];
        } else {
            echo "No current assigned strategy found for the user.";
            exit;
        }
    }
    
    // Insert feedback into Strategy_Feedback table
    $stmtFeedback = $conn->prepare("INSERT INTO Strategy_Feedback (assignment_id, is_helpful, comments) VALUES (?, ?, ?)");
    $stmtFeedback->bind_param("iis", $assignmentId, $isHelpful, $selectedEmotionName);
    
    if ($stmtFeedback->execute()) {
        // Feedback saved successfully
        
        // If this is a 5th entry feedback (check session flag)
        if (isset($_SESSION['needs_new_strategy']) && $_SESSION['needs_new_strategy']) {
            // Clear the flag
            unset($_SESSION['needs_new_strategy']);
            
            // 1. End the current strategy assignment
            $stmtEnd = $conn->prepare("UPDATE Assigned_Strategy 
                                     SET assignment_end_date = NOW(), is_current = 0 
                                     WHERE assignment_id = ?");
            $stmtEnd->bind_param("i", $assignmentId);
            $stmtEnd->execute();
            
            // 2. Select a new random strategy
            $stmtRandom = $conn->prepare("SELECT strategy_id FROM Coping_Strategy ORDER BY RAND() LIMIT 1");
            $stmtRandom->execute();
            $resultRandom = $stmtRandom->get_result();
            $rowRandom = $resultRandom->fetch_assoc();
            $newStrategyId = $rowRandom['strategy_id'];
            
            // 3. Create a new assignment
            $stmtNew = $conn->prepare("INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current) 
                                     VALUES (?, ?, NOW(), 1)");
            $stmtNew->bind_param("ii", $user_id, $newStrategyId);
            $stmtNew->execute();
            
            // Set JavaScript redirect with localStorage update
            echo "<script>
                // Clear any cached homepage data
                if (window.localStorage) {
                    localStorage.removeItem('homepageData');
                    localStorage.setItem('reloadHomepage', 'true');
                }
                
                // Redirect to see the new strategy
                window.location.href = 'strategiesCurrentStrategy.html';
            </script>";
            exit();
        } else {
            // Regular feedback (not 5th entry)
            echo "<script>
                // Clear any cached homepage data
                if (window.localStorage) {
                    localStorage.removeItem('homepageData');
                    localStorage.setItem('reloadHomepage', 'true');
                }
                
                // Redirect to journal home
                window.location.href = 'journalHome.html';
            </script>";
            exit();
        }
    } else {
        // Error saving feedback
        echo "Error: " . $stmtFeedback->error;
    }
} else {
    // If not a POST request, redirect to the form page
    header("Location: journalStrategyFeedback.html");
    exit();
}
?>
