<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'parent') {
    header("Location: index.php"); // Redirect to login page if not logged in or not a parent
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome Parent</title>
  <link rel="stylesheet" type="text/css" href="styles/welcome.css">
</head>
<body>
  <div class="welcome-container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>You have successfully logged in as a parent.</p>
    <!-- Parent-specific content and links -->
    <ul>
      <li><a href="parent_dashboard.php">Parent Dashboard</a></li>
      <li><a href="manage_users.php">Manage Users</a></li>
      <li><a href="view_feedback.php">View Feedback</a></li>
    </ul>
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>
