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

// First, try to get the emotion ID from the journal entry in session
if (isset($_SESSION['journalEntry']['emotionId']) && !empty($_SESSION['journalEntry']['emotionId'])) {
    $emotionId = $_SESSION['journalEntry']['emotionId'];
} else {
    // If the user hasn't selected an emotion yet, try to get the last assigned strategy
    $sql = "SELECT strategy_id FROM Assigned_Strategy WHERE user_id = ? ORDER BY assigned_start_date DESC LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$userId]);
    $lastStrategyId = $stmt->fetchColumn();
    if ($lastStrategyId) {
        // Set the strategy in session and fetch its details
        $_SESSION['strategy_id'] = $lastStrategyId;
        $sql2 = "SELECT strategy_name, strategy_image AS strategy_image_url, strategy_descript FROM Coping_Strategy WHERE strategy_id = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute([$lastStrategyId]);
        $strategyData = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($strategyData) {
            $_SESSION['strategy_name'] = $strategyData['strategy_name'];
            $_SESSION['strategy_image_url'] = $strategyData['strategy_image_url'];
            echo json_encode(['success' => true, 'data' => $strategyData]);
            exit;
        }
    }
    // If no emotion or last assignment is found, default to Deep Breathing (strategy_id 2)
    $_SESSION['strategy_id'] = 2;
    $_SESSION['strategy_name'] = 'Deep Breathing';
    $_SESSION['strategy_image_url'] = 'images/strategySteps/deepBreathing.png';
    // Optionally, you might also want to set a default description:
    $defaultStrategy = [
        'strategy_id' => 2,
        'strategy_name' => 'Deep Breathing',
        'strategy_image_url' => 'images/strategySteps/deepBreathing.png',
        'strategy_descript' => 'Deep breathing helps calm your body and mind.'
    ];
    echo json_encode(['success' => true, 'data' => $defaultStrategy]);
    exit;
}

// If an emotionId is available, query the coping strategy for that emotion.
$sql = "
    SELECT cs.strategy_id,
           cs.strategy_name,
           cs.strategy_image AS strategy_image_url,
           cs.strategy_descript
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
    echo json_encode(['success' => true, 'data' => $strategy]);
} else {
    // If no strategy is found for the given emotion, default to Deep Breathing
    $_SESSION['strategy_id'] = 2;
    $_SESSION['strategy_name'] = 'Deep Breathing';
    $_SESSION['strategy_image_url'] = 'images/strategySteps/deepBreathing.png';
    $defaultStrategy = [
        'strategy_id' => 2,
        'strategy_name' => 'Deep Breathing',
        'strategy_image_url' => 'images/strategySteps/deepBreathing.png',
        'strategy_descript' => 'Deep breathing helps calm your body and mind.'
    ];
    echo json_encode(['success' => true, 'data' => $defaultStrategy]);
}
?>
