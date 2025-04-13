<?php
session_start();
require_once 'db_connect.php';
require_once 'updateAssignment.php';

// Ensure required session data is present
if (!isset($_SESSION['user_id'], $_SESSION['strategy_id'])) {
    header('Location: strategiesHome.html');
    exit;
}

$userId = $_SESSION['user_id'];
$strategyId = $_SESSION['strategy_id'];

//Check if there's an active assignment before inserting a new one
// If not, insert a new assignment record to mark the start of this cycle
$sqlCheckCurrent = "SELECT COUNT(*) FROM Assigned_Strategy WHERE user_id = ? AND strategy_id = ? AND is_current = 1";
$stmtCheckCurrent = $db->prepare($sqlCheckCurrent);
$stmtCheckCurrent->execute([$userId, $strategyId]);
$hasCurrentAssignment = $stmtCheckCurrent->fetchColumn() > 0;

if (!$hasCurrentAssignment) {
    // Insert a new assignment record
    $sqlInsertAssignment = "
        INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current)
        VALUES (?, ?, NOW(), 1)
    ";
    $stmt = $db->prepare($sqlInsertAssignment);
    $stmt->execute([$userId, $strategyId]);
}

// Store the new assignment id in the session (if needed later)
$_SESSION['assignment_id'] = $db->lastInsertId();

// Now, check if the assignment cycle is complete.
// (Assuming updateAssignmentCycle() returns true when 5 entries have been reached)
$assignmentUpdated = updateAssignmentCycle($db, $userId, $strategyId);

// If the cycle is complete, redirect to feedback page.
if ($assignmentUpdated) {
    header('Location: journalStrategyFeedback.html');
    exit;
}

// Otherwise, fetch and display the current strategy details.
$sqlStrategy = "SELECT strategy_name, strategy_descript, strategy_image AS strategy_image_url 
                FROM Coping_Strategy 
                WHERE strategy_id = ?";
$stmtStrategy = $db->prepare($sqlStrategy);
$stmtStrategy->execute([$strategyId]);
$strategy = $stmtStrategy->fetch(PDO::FETCH_ASSOC);

// Fallback: if no strategy was found, use a default (Deep Breathing)
if (!$strategy) {
    $_SESSION['strategy_id'] = 2;
    $stmtStrategy->execute([2]);
    $strategy = $stmtStrategy->fetch(PDO::FETCH_ASSOC);
}
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
    <!-- Needs to be here to create layered effect with nav bar -->
	<div class="waves-container">
        <img src="./images/waveBack.svg" alt="Back Wave" class="wave back-wave">
	</div>

    <!-- Navigation Bar -->
    <div class="navigation-bar">

        <a href="index.php" id="navigation-bar-sign-out-icon" data-tooltip="Sign Out">
          <img src="./images/icons/signOutIcon.svg" alt="Sign Out Icon" class="navigation-bar-sign-out-icon" title="Sign Out">
        </a>

        <!-- Profile Picture Section -->
        <div class="navigation-bar-profile-picture-section">
          <img src="./images/profilePictures/Deer.webp" alt="Profile Picture" class="navigation-bar-profile-picture">
        </div>
    
        <!-- Profile Name Section -->
        <div class="navigation-bar-user-name-section">
          <p class="navigation-bar-user-first-name" id="userFirstName"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>

        <!-- Links Background -->
        <div class="navigation-bar-links-container">
            <!-- Home Link -->
            <a href="homepage.html" class="navigation-bar-link">
              <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text">Home</span>
            </a>
            <!-- Journal Link -->
            <a href="journalHome.html" class="navigation-bar-link">
              <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text-selected">Journal</span>
            </a>
            <!-- Strategies Link -->
            <a href="strategiesHome.html" class="navigation-bar-link">
              <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text">Strategies</span>
            </a>
            <!-- Progress Link -->
            <a href="#" class="navigation-bar-link">
              <img src="./images/icons/progressIcon.svg" alt="Progress Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text">Progress</span>
            </a>
            <!-- Settings Link -->
            <a href="#" class="navigation-bar-link">
              <img src="./images/icons/settingsIcon.svg" alt="Settings Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text">Settings</span>
            </a>
        </div>

    </div>


	<!-- Top bar -->
	<div class="top-bar">
        <!-- Title of current page -->
		<h1 class="page-title">Journal</h1>
        <!-- Go Back arrow -->
         <!-- Note: I can see this needed to be changed. Needs testing to ensure this method works -->
        <a href="javascript:history.back()" class="back-arrow">
            <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
            <h2 class="go-back-text">Back</h2>
        </a>

         <!-- Progress Bar -->
        <img src="./images/progressBar/progressBarFeedback.svg" alt="Progress" class="progress-bar">

	</div>
    
	
	<!-- Main screen container -->
	<div class="main-screen-container">

		<!-- Container for messages and feedback form -->
		<div class="content-container">

            <!-- Chat Bubbles that direct the user what to do -->
            <div class="chat-bubble-container">

              <div class="chat-bubble-from" id="assignedStrategyMessage">
                  Your assigned strategy is <u><?php echo htmlspecialchars($strategy['strategy_name']); ?></u>
              </div>


                <!-- Assigned Strategy Box -->
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

            </div>
            

            <!-- next button -->
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

  <script src="./scripts/journalFeedback.js"></script>
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
  </script>
</body>
</html>
