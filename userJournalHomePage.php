<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: index.php"); // Redirect to login page if not logged in or not a user
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome User</title>
  <link rel="stylesheet" type="text/css" href="styles/welcome.css">
</head>
<body>
  <div class="welcome-container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>You have successfully logged in as a user.</p>
    <!-- User-specific content and links -->
    <ul>
      <li><a href="user_dashboard.php">User Dashboard</a></li>
      <li><a href="view_emotions.php">View Emotions</a></li>
      <li><a href="journal_entries.php">Journal Entries</a></li>
    </ul>
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>
