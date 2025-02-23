<?php
// Include the database connection file
require_once 'db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start();

// Set response header for JSON
header('Content-Type: application/json');

// Get form data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$response = array('success' => false, 'error' => '');

// Check the Users table
$sqlUser = "SELECT user_password AS password, 'user' AS role FROM Users WHERE user_username = :username";
$statementUser = $db->prepare($sqlUser);
$statementUser->bindParam(':username', $username);
$statementUser->execute();
$rowUser = $statementUser->fetch(PDO::FETCH_ASSOC);

if ($rowUser) {
    if (password_verify($password, $rowUser['password'])) {
        // Login successful as user
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $rowUser['role'];

        $response['success'] = true;
        $response['redirect'] = 'homepage.html';
        echo json_encode($response);
        exit();
    } else {
        $response['error'] = 'user_invalid_password';
        echo json_encode($response);
        exit();
    }
}

// Check the Parents table
$sqlParent = "SELECT parent_password AS password, 'parent' AS role FROM Parents WHERE parent_username = :username";
$statementParent = $db->prepare($sqlParent);
$statementParent->bindParam(':username', $username);
$statementParent->execute();
$rowParent = $statementParent->fetch(PDO::FETCH_ASSOC);

if ($rowParent) {
    if (password_verify($password, $rowParent['password'])) {
        // Login successful as parent
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $rowParent['role'];

        $response['success'] = true;
        $response['redirect'] = 'homepage.html';
        echo json_encode($response);
        exit();
    } else {
        $response['error'] = 'parent_invalid_password';
        echo json_encode($response);
        exit();
    }
}

// Invalid username
$response['error'] = 'invalid_username';
echo json_encode($response);
exit();
?>
