<?php
// Include the database connection file
require_once 'db_connect.php';

// Start the session
session_start();

// Get form data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($db) {
    // Check the Users table
    $sqlUser = "SELECT user_password AS password, 'user' AS role FROM Users WHERE user_username = :username";
    $statementUser = $db->prepare($sqlUser);
    $statementUser->bindParam(':username', $username);
    $statementUser->execute();
    $rowUser = $statementUser->fetch(PDO::FETCH_ASSOC);

    if ($rowUser) {
        if ($password === $rowUser['password']) {  // Compare plain text passwords
            // Login successful as user
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $rowUser['role'];
            header("Location: userJournalHomePage.php");
            exit();
        } else {
            header("Location: index.php?error=user_invalid_password");
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
        if ($password === $rowParent['password']) {  // Compare plain text passwords
            // Login successful as parent
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $rowParent['role'];
            header("Location: ParentJournalHomePage.php");
            exit();
        } else {
            header("Location: index.php?error=parent_invalid_password");
            exit();
        }
    }

    // Invalid username
    header("Location: index.php?error=invalid_username");
    exit();
} else {
    header("Location: index.php?error=db_connection_failed");
    exit();
}
?>
