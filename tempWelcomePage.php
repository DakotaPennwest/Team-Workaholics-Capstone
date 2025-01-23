<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Check the user role
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome</title>
  <link rel="stylesheet" type="text/css" href="styles/welcome.css">
</head>
<body>
  <div class="welcome-container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>You have successfully logged in.</p>
    
    <?php if ($role == 'user'): ?>
      <h2>User Portal</h2>
      <p>Welcome to the journaling home page! Here are some user-specific features and links:</p>
      <ul>
        <li><a href="user_dashboard.php">User Dashboard</a></li>
        <li><a href="view_emotions.php">View Emotions</a></li>
        <li><a href="journal_entries.php">Journal Entries</a></li>
      </ul>
    <?php elseif ($role == 'parent'): ?>
      <h2>Parent Portal</h2>
      <p>Welcome to the parent portal homepage! Here are some parent-specific features and links:</p>
      <ul>
        <li><a href="parent_dashboard.php">Parent Dashboard</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
        <li><a href="view_feedback.php">View Feedback</a></li>
      </ul>
    <?php endif; ?>
    
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>
