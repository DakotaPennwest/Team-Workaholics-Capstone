<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $journalContent = $_POST['journalContent'];
    $emotionId = $_POST['emotionId'];
    $emotionalIntensityRating = $_POST['emotionalIntensityRating'];

    $_SESSION['journalEntry'] = [
        'journalContent' => $journalContent,
        'emotionId' => $emotionId,
        'emotionalIntensityRating' => $emotionalIntensityRating
    ];

    // Redirect to the next page (e.g., copingStrategies.html)
    header('Location: copingStrategies.html');
    exit();
}
?>
