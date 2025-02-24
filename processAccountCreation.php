<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    session_start();
    $response = array('success' => false, 'message' => '', 'redirect' => '');

    // Include the database connection file
    require 'db_connect.php';

    // Get form data
    $accountType = $_POST['accountType'];
    $firstName = $_POST['firstName'];
    $email = $_POST['email'] ?? null; // Allow email to be optional
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $parentId = $_POST['parentId'] ?? null; // Ensure parentId is retrieved

    // Debugging: Log received data
    error_log("Received data: accountType=$accountType, firstName=$firstName, email=$email, username=$username, password=$password, confirmPassword=$confirmPassword, parentId=$parentId");

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && $email !== null) {
        $response['message'] = "Invalid email format!";
    } elseif ($password !== $confirmPassword) {
        $response['message'] = "Passwords do not match!";
    } else {
        // Check if the username or email already exists
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM (SELECT parent_username AS username, parent_email AS email FROM Parents UNION SELECT user_username AS username, user_email AS email FROM Users) AS combined WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $response['message'] = "Username or email already exists! Please choose a different username or email.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                switch ($accountType) {
                    case "parent":
                        try {
                            $stmt = $db->prepare("INSERT INTO Parents (parent_fname, parent_username, parent_password, parent_email) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$firstName, $username, $hashedPassword, $email]);
                            $parentId = $db->lastInsertId();
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = 'parent';
                            $response['success'] = true;
                            $response['message'] = "Parent account created successfully!";
                            $response['redirect'] = "accountcreationchilduser.html?parent_id=" . $parentId;
                        } catch (PDOException $e) {
                            $response['message'] = "Error: " . $e->getMessage();
                        }
                        break;

                    case "child":
                        if ($parentId) {
                            try {
                                $stmt = $db->prepare("INSERT INTO Users (parent_user_id, user_fname, user_dob, user_email, user_username, user_password) VALUES (?, ?, ?, ?, ?, ?)");
                                $stmt->execute([$parentId, $firstName, $_POST['birthday'], $email, $username, $hashedPassword]);
                                $_SESSION['username'] = $username;
                                $_SESSION['role'] = 'child';
                                $response['success'] = true;
                                $response['message'] = "Child account created successfully!";
                                $response['redirect'] = "homepage.html";
                            } catch (PDOException $e) {
                                $response['message'] = "Error: " . $e->getMessage();
                            }
                        } else {
                            $response['message'] = "Parent ID is missing!";
                        }
                        break;

                    case "solo":
                        try {
                            $stmt = $db->prepare("INSERT INTO Users (user_fname, user_dob, user_email, user_username, user_password) VALUES (?, ?, ?, ?, ?)");
                            $stmt->execute([$firstName, $_POST['birthday'], $email, $username, $hashedPassword]);
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = 'solo';
                            $response['success'] = true;
                            $response['message'] = "Solo user account created successfully!";
                            $response['redirect'] = "homepage.html";
                        } catch (PDOException $e) {
                            $response['message'] = "Error: " . $e->getMessage();
                        }
                        break;

                    default:
                        $response['message'] = "Invalid account type!";
                        break;
                }
            }
        } catch (PDOException $e) {
            $response['message'] = "Error: " . $e->getMessage();
        }
    }

    echo json_encode($response);
}
?>
