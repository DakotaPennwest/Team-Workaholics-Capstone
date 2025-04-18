<?php
session_start();

// Ensure the user is signed in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$currentUserId = (int)$_SESSION['user_id'];

// Database connection
$host        = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "emotionalregulationapp";

$mysqli = new mysqli($host, $db_username, $db_password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Get date filter parameters (unused on full page but retained for internal use if needed)
$start_date = isset($_GET['start_date']) && $_GET['start_date'] !== ''
    ? $mysqli->real_escape_string($_GET['start_date'])
    : "";
$end_date = isset($_GET['end_date']) && $_GET['end_date'] !== ''
    ? $mysqli->real_escape_string($_GET['end_date'])
    : "";
// Build date clause
$dateClause = "";
if ($start_date !== "") {
    $dateClause .= " AND DATE(sf.feedback_date) >= '$start_date'";
}
if ($end_date !== "") {
    $dateClause .= " AND DATE(sf.feedback_date) <= '$end_date'";
}

// Query: For each strategy, get assigned count, helpful count,
// unhelpful count, and helpfulness percentage.
$query = "
    SELECT
      cs.strategy_name,
      COUNT(*)                          AS assigned_count,
      SUM(sf.is_helpful)                AS helpful_count,
      (COUNT(*) - SUM(sf.is_helpful))   AS unhelpful_count,
      (SUM(sf.is_helpful)/COUNT(*)*100) AS pct_helpful
    FROM strategy_feedback sf
    JOIN assigned_strategy a
      ON sf.assignment_id = a.assignment_id
    JOIN coping_strategy cs
      ON a.strategy_id = cs.strategy_id
    WHERE a.user_id = {$currentUserId}
      {$dateClause}
    GROUP BY cs.strategy_id
    ORDER BY pct_helpful DESC, assigned_count DESC
";
$result = $mysqli->query($query);
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Strategy Report - Full Report</title>
  <link rel="stylesheet" href="styles/layoutwithnavbar.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/progressStrategyReportFull.css">
  <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body>

  <!-- Back Wave -->
  <div class="waves-container">
    <img src="./images/waveBack.svg" alt="Back Wave" class="wave back-wave">
  </div>

  <!-- Navigation Bar -->
  <div class="navigation-bar">
    <a href="index.php" id="navigation-bar-sign-out-icon" data-tooltip="Sign Out">
      <img src="./images/icons/signOutIcon.svg" alt="Sign Out Icon" class="navigation-bar-sign-out-icon">
    </a>
    <div class="navigation-bar-profile-picture-section">
      <img src="./images/profilePictures/Deer.webp" alt="Profile Picture" class="navigation-bar-profile-picture">
    </div>
    <div class="navigation-bar-user-name-section">
      <p class="navigation-bar-user-first-name" id="userFirstName">Jason</p>
    </div>
    <div class="navigation-bar-links-container">
      <a href="homepage.html" class="navigation-bar-link">
        <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Home</span>
      </a>
      <a href="journalHome.html" class="navigation-bar-link">
        <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Journal</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Strategies</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="./images/icons/progressIcon.svg" alt="Progress Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text-selected">Progress</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="./images/icons/settingsIcon.svg" alt="Settings Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Settings</span>
      </a>
    </div>
  </div>

  <!-- Top bar -->
  <div class="top-bar">
    <h1 class="page-title">Strategy Report - Full Report</h1>
    <a href="javascript:history.back()" class="back-arrow">
      <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
      <h2 class="go-back-text">Back</h2>
    </a>
  </div>

  <!-- Main screen container -->
  <div class="main-screen-container">
    <div class="content-container">
      <div class="table-container">
        <div class="download-icon" id="downloadIcon">
          <img src="./images/icons/downloadIcon.svg" alt="Download">
        </div>
        <div class="table-shading"></div>
        <table>
          <thead>
            <tr>
              <th>Strategy</th>
              <th>Times Assigned</th>
              <th>Times Rated Helpful</th>
              <th>Times Rated Unhelpful</th>
              <th>Helpfulness</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['strategy_name']) ?></td>
                  <td><?= (int)$row['assigned_count'] ?></td>
                  <td><?= (int)$row['helpful_count'] ?></td>
                  <td><?= (int)$row['unhelpful_count'] ?></td>
                  <td><?= round($row['pct_helpful']) ?>%</td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">No feedback found for the selected date range.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Waves -->
  <div class="waves-container">
    <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
    <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
  </div>

  <script>
    // Navbar user info fetch
    document.addEventListener("DOMContentLoaded", () => {
      fetch('getUserInfo.php')
        .then(res => res.json())
        .then(data => {
          if (data.username) {
            document.getElementById('userFirstName').textContent = data.username;
          }
        })
        .catch(console.error);
    });
  </script>
</body>
</html>
