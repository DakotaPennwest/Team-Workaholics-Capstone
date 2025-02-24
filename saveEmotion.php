<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emotion = $_POST['emotion'] ?? null;

    if ($emotion) {
        $_SESSION['selected_emotion'] = $emotion;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No emotion provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
