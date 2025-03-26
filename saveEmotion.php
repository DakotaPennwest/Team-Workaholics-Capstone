<?php
require_once 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emotionId    = $_POST['emotionId'] ?? null;
    $emotionName  = $_POST['selectedEmotionName'] ?? null;
    $emotionValue = $_POST['selectedEmotionValue'] ?? null;

    if ($emotionId && isset($_SESSION['user_id'])) {
        $_SESSION['journalEntry']['emotionId'] = $emotionId;
        $_SESSION['selected_emotion_id'] = $emotionId;
        $_SESSION['emotion_name'] = $emotionName;
        $_SESSION['emotion_value'] = $emotionValue;

        // Set default strategy (Deep Breathing, strategy_id = 2) if none is set
        if (!isset($_SESSION['strategy_id']) || empty($_SESSION['strategy_id'])) {
            $_SESSION['strategy_id'] = 2;
        }

        header('Location: journalEmotionIntensity.php');
        exit();
    }
    echo json_encode(['success' => false, 'message' => 'Missing emotion data']);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}
?>
