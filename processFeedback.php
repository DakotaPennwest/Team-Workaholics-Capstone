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
    $selectedEmotionName = $_POST['selectedEmotionName'] ?? ''; // Capture feedback comments( if that functionality is later added)
    $selectedEmotionValue = $_POST['selectedEmotionValue'] ?? ''; // Capture helpful/unhelpful

    // Convert feedback to boolean for easier processing
    $isHelpful = ($selectedEmotionValue === 'helpful') ? true : false; 

    // Get the current assigned strategy for the user
    $stmt = $conn->prepare("SELECT asg.assignment_id 
                            FROM Assigned_Strategy asg 
                            WHERE asg.user_id = ? AND asg.is_current = TRUE 
                            ORDER BY asg.assigned_start_date DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $assignment_id = $row['assignment_id'];

        // Insert feedback into Strategy_Feedback table
        $stmt = $conn->prepare("INSERT INTO Strategy_Feedback (assignment_id, is_helpful, comments) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $assignment_id, $isHelpful, $selectedEmotionName);
        
        if ($stmt->execute()) {
            // Feedback saved successfully
            header("Location: journalHome.html");
            exit();
        } else {
            // Error saving feedback
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "No current assigned strategy found for the user.";
    }
} else {
    // If not a POST request, redirect to the form page
    header("Location: journalStrategyFeedback.html");
    exit();
}
?>
