<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

// Check that the emotion ID is set in the session
if (!isset($_SESSION['user_id']) || empty($_SESSION['journalEntry']['emotionId'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing emotion data in session'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$emotionId = $_SESSION['journalEntry']['emotionId'];
error_log("getAssignedStrategy.php: User ID = {$userId}, Emotion ID = {$emotionId}");

// Query the coping strategy based on the emotion
$sql = "
    SELECT cs.strategy_id,
           cs.strategy_name,
           cs.strategy_image AS strategy_image_url
    FROM Coping_Strategy AS cs
    JOIN Emotional_Strategy_Link AS esl ON cs.strategy_id = esl.strategy_id
    WHERE esl.emotion_id = :emotionId
    LIMIT 1
";
$stmt = $db->prepare($sql);
$stmt->execute([':emotionId' => $emotionId]);
$strategy = $stmt->fetch(PDO::FETCH_ASSOC);

if ($strategy) {
    // Update session with the fetched strategy details
    $_SESSION['strategy_id'] = $strategy['strategy_id'];
    $_SESSION['strategy_name'] = $strategy['strategy_name'];
    $_SESSION['strategy_image_url'] = $strategy['strategy_image_url'];
    error_log("getAssignedStrategy.php: Found strategy: " . print_r($strategy, true));
    echo json_encode([
        'success' => true,
        'data' => $strategy
    ]);
} else {
    // Default to "Deep Breathing" (strategy_id = 2) if no strategy found
    $_SESSION['strategy_id'] = 2;
    $_SESSION['strategy_name'] = 'Deep Breathing';
    $_SESSION['strategy_image_url'] = 'images/strategySteps/deepBreathing.png';
    $defaultStrategy = [
        'strategy_id' => 2,
        'strategy_name' => 'Deep Breathing',
        'strategy_image_url' => 'images/strategySteps/deepBreathing.png'
    ];
    error_log("getAssignedStrategy.php: No strategy found for emotion_id {$emotionId}. Defaulting to Deep Breathing.");
    echo json_encode([
        'success' => true,
        'data' => $defaultStrategy
    ]);
}
?>
