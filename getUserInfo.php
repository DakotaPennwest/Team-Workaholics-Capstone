<?php
session_start();
header('Content-Type: application/json');

$response = array('username' => '');

if (isset($_SESSION['username'])) {
    $response['username'] = $_SESSION['username'];
}

echo json_encode($response);
?>
