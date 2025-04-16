<?php
session_start();
require_once 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: journalHome.html');
    exit;
}

$userId = $_SESSION['user_id'];

// Get the CURRENT assigned strategy directly from the database in a single query
$sqlGetCurrent = "
    SELECT a.strategy_id, a.assignment_id, c.strategy_name, c.strategy_descript, c.strategy_image
    FROM Assigned_Strategy a
    JOIN Coping_Strategy c ON a.strategy_id = c.strategy_id
    WHERE a.user_id = ? AND a.is_current = 1
    ORDER BY a.assigned_start_date DESC
    LIMIT 1
";

$stmtGetCurrent = $db->prepare($sqlGetCurrent);
$stmtGetCurrent->execute([$userId]);
$strategy = $stmtGetCurrent->fetch(PDO::FETCH_ASSOC);

// If no current assignment exists, use a default
if (!$strategy) {
    // Get the default strategy
    $defaultStrategyId = 2; // Deep Breathing
    $sqlDefault = "SELECT strategy_id, strategy_name, strategy_descript, strategy_image FROM Coping_Strategy WHERE strategy_id = ?";
    $stmtDefault = $db->prepare($sqlDefault);
    $stmtDefault->execute([$defaultStrategyId]);
    $strategy = $stmtDefault->fetch(PDO::FETCH_ASSOC);
    
    // Create a new assignment for the default strategy
    $sqlInsertAssignment = "
        INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current)
        VALUES (?, ?, NOW(), 1)
    ";
    $stmtInsert = $db->prepare($sqlInsertAssignment);
    $stmtInsert->execute([$userId, $defaultStrategyId]);
    
    // Store the assigned strategy info in session
    $_SESSION['strategy_id'] = $defaultStrategyId;
    $_SESSION['assignment_id'] = $db->lastInsertId();
} else {
    // Store the found strategy info in session
    $_SESSION['strategy_id'] = $strategy['strategy_id'];
    $_SESSION['assignment_id'] = $strategy['assignment_id'];
}

// For consistency with expected fields
$strategy['strategy_image_url'] = $strategy['strategy_image'];

// IMPORTANT: Remove any script that might be changing the displayed values
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Journal - Assigned Strategy</title>
  <link rel="stylesheet" href="styles/layoutwithnavbar.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/journalAssignedStrategy.css">
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
      <img src="./images/icons/signOutIcon.svg" alt="Sign Out Icon" class="navigation-bar-sign-out-icon" title="Sign Out">
    </a>
    <div class="navigation-bar-profile-picture-section">
      <img src="./images/profilePictures/Deer.webp" alt="Profile Picture" class="navigation-bar-profile-picture">
    </div>
    <div class="navigation-bar-user-name-section">
      <p class="navigation-bar-user-first-name" id="userFirstName"></p>
    </div>
    <div class="navigation-bar-links-container">
      <a href="homepage.html" class="navigation-bar-link">
        <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text-selected">Home</span>
      </a>
      <a href="journalHome.php" class="navigation-bar-link">
        <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Journal</span>
      </a>
      <a href="strategiesHome.html" class="navigation-bar-link">
        <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Strategies</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="./images/icons/progressIcon.svg" alt="Progress Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Progress</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="./images/icons/settingsIcon.svg" alt="Settings Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Settings</span>
      </a>
    </div>
  </div>
  <!-- Top Bar -->
  <div class="top-bar">
    <h1 class="page-title">Journal</h1>
    <a href="javascript:history.back()" class="back-arrow">
      <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
      <h2 class="go-back-text">Back</h2>
    </a>
    <img src="./images/progressBar/progressBarFeedback.svg" alt="Progress" class="progress-bar">
  </div>
  <!-- Main Content -->
  <div class="main-screen-container">
    <div class="content-container">
      <!-- Chat Bubble -->
      <div class="chat-bubble-container">
        <div class="chat-bubble-from" id="assignedStrategyMessage">
          Your assigned strategy is <u><?php echo htmlspecialchars($strategy['strategy_name']); ?></u>
        </div>
      </div>
      <!-- Strategy Details -->
      <div class="assigned-strategy-box">
        <div class="left-content-container">
          <div class="strategy-info-box">
            <div class="strategy-info-strategy-name" id="strategyName">
              <?php echo htmlspecialchars($strategy['strategy_name']); ?>
            </div>
            <div class="strategy-info-strategy-description" id="strategyDescription">
              <?php echo htmlspecialchars($strategy['strategy_descript']); ?>
            </div>
          </div>
        </div>
        <div class="right-content-container">
          <img src="<?php echo htmlspecialchars($strategy['strategy_image_url']); ?>" alt="Assigned Strategy Image" class="strategy-Steps">
        </div>
      </div>
      <!-- Hidden Form to Proceed to Final Journal Submission -->
      <div class="form-button-container">
        <form id="finalJournalForm" method="POST" action="saveJournalEntry.php">
          <button class="form-button-next" type="submit">Finish today's journal!</button>
        </form>
      </div>
    </div>
  </div>
  <!-- Front and Middle Waves -->
  <div class="waves-container">
    <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
    <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
  </div>
  <script>
      document.addEventListener("DOMContentLoaded", function() {
          fetch('getUserInfo.php')
              .then(response => response.json())
              .then(data => {
                  if (data.username) {
                      document.getElementById('userFirstName').textContent = data.username;
                  }
              })
              .catch(error => {
                  console.error('Error fetching user info:', error);
              });
      });
    // Set flag to force reload of homepage on next visit
    document.addEventListener('DOMContentLoaded', function() {
        if (window.localStorage) {
            localStorage.setItem('reloadHomepage', 'true');
        }
    });
  </script>
</body>
</html>
