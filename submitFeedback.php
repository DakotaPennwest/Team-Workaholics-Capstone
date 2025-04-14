<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate that the user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        exit; // Stop execution if user is not logged in
    }

    // Check if POST variables are set
    if (!isset($_POST['selectedEmotionValue']) || !isset($_POST['assignmentId'])) {
        echo "Feedback or Assignment ID not provided.";
        exit; // Stop execution if inputs are missing
    }

    $assignmentId = $_POST['assignmentId'];
    $feedbackValue = $_POST['selectedEmotionValue'];

    // Ensure assignment ID is numeric
    if (!is_numeric($assignmentId)) {
        echo "Invalid Assignment ID.";
        exit; // Stop execution for invalid input
    }

    // Set boolean value for feedback (true for helpful, false for unhelpful)
    $isHelpful = ($feedbackValue === 'helpful') ? true : false; 

    // Prepare SQL query to insert feedback
    $sql = "INSERT INTO Strategy_Feedback (assignment_id, is_helpful) VALUES (:assignment_id, :is_helpful)";
    
    try {
        $stmt = $db->prepare($sql);
        // Bind parameters
        $stmt->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmt->bindParam(':is_helpful', $isHelpful, PDO::PARAM_BOOL);
        
        // Execute the statement
        $stmt->execute();

        // Feedback submitted successfully, redirect the user
        header("Location: strategiesCurrentStrategy.html");
        exit();
    } catch (PDOException $e) {
        // Catch any errors
        $error_message = $e->getMessage();
        echo "Error submitting feedback: " . $error_message;
    }
} else {
    // If not a POST request, redirect to the feedback form page
    header("Location: journalStrategyFeedback.html");
    exit();
}
?>
