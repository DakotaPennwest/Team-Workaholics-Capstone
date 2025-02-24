<?php
$testUserPassword = 'password456'; // The test user password
$hashedTestUserPassword = password_hash($testUserPassword, PASSWORD_DEFAULT);
echo 'Test User Hashed Password: ' . $hashedTestUserPassword . '<br>';

$testParentPassword = 'password123'; // The parent user password
$hashedTestParentPassword = password_hash($testParentPassword, PASSWORD_DEFAULT);
echo 'Parent Hashed Password: ' . $hashedTestParentPassword;
?>
