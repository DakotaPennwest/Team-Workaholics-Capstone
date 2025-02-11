<?php
session_start();
header('Content-Type: application/json');

$response = array('username' => '', 'role' => '', 'journals' => array());

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $response['username'] = $username;
    $response['role'] = $_SESSION['role'];

    // Fetch user's journal entries from the database
    require 'db_connect.php'; // Include your database connection file
    try {
        $stmt = $db->prepare("SELECT journal_content, journal_date, emotional_intensity_rating FROM Journal_Entry WHERE user_id = (SELECT user_id FROM Users WHERE user_username = ?)");
        $stmt->execute([$username]);
        $response['journals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>
