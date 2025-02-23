<?php
// Start the session
session_start();

// Debugging: Log session values
echo 'Session username: ' . $_SESSION['username'] . '<br>';
echo 'Session role: ' . $_SESSION['role'] . '<br>';

// Check if the user is logged in and has the correct role
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

// Check user role and set welcome message and links
if ($_SESSION['role'] === 'parent') {
    $welcomeMessage = "Welcome Parent";
    $links = [
        'Parent Dashboard' => 'parent_dashboard.php',
        'Manage Users' => 'manage_users.php',
        'View Feedback' => 'view_feedback.php'
    ];
} elseif ($_SESSION['role'] === 'child' || $_SESSION['role'] === 'solo') {
    $welcomeMessage = "Welcome User";
    $links = [
        'Journal Entries' => 'journal_entries.php',
        'Assigned Strategies' => 'assigned_strategies.php',
        'Provide Feedback' => 'provide_feedback.php'
    ];
} else {
    $welcomeMessage = "Welcome Guest";
    $links = [
        'Login' => 'index.php'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $welcomeMessage; ?></title>
  <link rel="stylesheet" type="text/css" href="styles/welcome.css">
</head>
<body>
  <div class="welcome-container">
    <h1><?php echo $welcomeMessage; ?>, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>You have reached the Journaling Home Page.</p>
    <!-- Role-specific content and links -->
    <ul>
      <?php foreach ($links as $linkText => $linkUrl): ?>
        <li><a href="<?php echo $linkUrl; ?>"><?php echo $linkText; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <a href="logout.php">Logout</a>
  </div>
</body>
</html>
