<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emotionName     = $_POST['emotionName'] ?? null;
    $emotionValue    = $_POST['emotionValue'] ?? null;
    $intensityLevel  = $_POST['intensityLevel'] ?? null;
    $intensityLabel  = $_POST['intensityLabel'] ?? null;

    if ($emotionName && $emotionValue && $intensityLevel !== null && $intensityLabel) {
        $_SESSION['emotion_name'] = $emotionName;
        $_SESSION['emotion_value'] = $emotionValue;
        $_SESSION['intensity_level'] = $intensityLevel;
        $_SESSION['intensity_label'] = $intensityLabel;
        
        // Adjust the intensity rating for a 1-5 scale (if intensityLevel is 0-indexed)
        $_SESSION['journalEntry']['emotionalIntensityRating'] = $intensityLevel + 1;
        $_SESSION['selected_emotional_intensity'] = $intensityLevel + 1;
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
