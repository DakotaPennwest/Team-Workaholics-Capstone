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

// Try to get the emotion ID from the journal entry in session
if (isset($_SESSION['journalEntry']['emotionId']) && !empty($_SESSION['journalEntry']['emotionId'])) {
    $emotionId = $_SESSION['journalEntry']['emotionId'];
} else {
    // If emotion ID is not set, try to get the last assigned strategy AND assignment ID
    $sql = "SELECT asg.strategy_id, asg.assignment_id 
            FROM Assigned_Strategy asg
            WHERE asg.user_id = ? 
            ORDER BY asg.assigned_start_date DESC 
            LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId]);
    $lastAssignment = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch associative array

    if ($lastAssignment) {
        $lastStrategyId = $lastAssignment['strategy_id'];
        $assignmentId = $lastAssignment['assignment_id']; // Get assignment ID

        // Set strategy in session and fetch its details
        $_SESSION['strategy_id'] = $lastStrategyId;
        $sql2 = "SELECT strategy_name, strategy_image AS strategy_image_url, strategy_descript 
                 FROM Coping_Strategy 
                 WHERE strategy_id = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute([$lastStrategyId]);
        $strategyData = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($strategyData) {
            $strategyData['assignment_id'] = $assignmentId; // Add assignment ID
            $_SESSION['strategy_name'] = $strategyData['strategy_name'];
            $_SESSION['strategy_image_url'] = $strategyData['strategy_image_url'];
            echo json_encode(['success' => true, 'data' => $strategyData]);
            exit;
        }
    }

    // Default to Deep Breathing if no emotion or last assignment is found
    //  AND create a new assignment (you might need to adjust how this new assignment is tracked)
    $_SESSION['strategy_id'] = 2;
    $_SESSION['strategy_name'] = 'Deep Breathing';
    $_SESSION['strategy_image_url'] = 'images/strategySteps/deepBreathing.png';
    
    // For simplicity, assuming assignment ID 0 for default strategy.  
    $defaultStrategy = [
        'strategy_id' => 2,
        'strategy_name' => 'Deep Breathing',
        'strategy_image_url' => 'images/strategySteps/deepBreathing.png',
        'strategy_descript' => 'Deep breathing helps calm your body and mind.',
        'assignment_id' => 0  // Placeholder: Needs proper assignment tracking for defaults!
    ];
    echo json_encode(['success' => true, 'data' => $defaultStrategy]);
    exit;
}

// If an emotionId is available, query the coping strategy AND assignment ID for that emotion
$sql = "SELECT cs.strategy_id,
               cs.strategy_name,
               cs.strategy_image AS strategy_image_url,
               cs.strategy_descript,
               asg.assignment_id  -- Select assignment ID
        FROM Coping_Strategy AS cs
        JOIN Emotional_Strategy_Link AS esl ON cs.strategy_id = esl.strategy_id
        LEFT JOIN Assigned_Strategy AS asg ON cs.strategy_id = asg.strategy_id AND asg.user_id = :userId -- Assuming user_id link
        WHERE esl.emotion_id = :emotionId
        ORDER BY asg.assigned_start_date DESC  -- Get the most recent assignment, if any
        LIMIT 1";

$stmt = $db->prepare($sql);
$stmt->execute([':emotionId' => $emotionId, ':userId' => $userId]);
$strategy = $stmt->fetch(PDO::FETCH_ASSOC);

if ($strategy) {
    // Update session with the fetched strategy details
    $_SESSION['strategy_id'] = $strategy['strategy_id'];
    $_SESSION['strategy_name'] = $strategy['strategy_name'];
    $_SESSION['strategy_image_url'] = $strategy['strategy_image_url'];
    echo json_encode(['success' => true, 'data' => $strategy]);
} else {
    // If no strategy is found for the given emotion, default to Deep Breathing
    //  AND create a new assignment (you might need to adjust how this new assignment is tracked).
    $_SESSION['strategy_id'] = 2;
    $_SESSION['strategy_name'] = 'Deep Breathing';
    $_SESSION['strategy_image_url'] = 'images/strategySteps/deepBreathing.png';
    $defaultStrategy = [
        'strategy_id' => 2,
        'strategy_name' => 'Deep Breathing',
        'strategy_image_url' => 'images/strategySteps/deepBreathing.png',
        'strategy_descript' => 'Deep breathing helps calm your body and mind.',
        'assignment_id' => 0  // Placeholder: Needs proper assignment tracking for defaults
    ];
    error_log("No strategy found for emotion_id {$emotionId}. Defaulting to Deep Breathing.");
    echo json_encode(['success' => true, 'data' => $defaultStrategy]);
}
?>
