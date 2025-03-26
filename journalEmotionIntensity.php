<?php
require_once 'db_connect.php';
session_start();
// Retrieve emotion data from the session. Use defaults if not set.
$emotionName  = $_SESSION['emotion_name'] ?? 'excited';
$emotionValue = $_SESSION['emotion_value'] ?? 'excited';
$emotionId    = $_SESSION['journalEntry']['emotionId'] ?? '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Journal Intensity</title>
  <link rel="stylesheet" href="styles/layoutwithnavbar.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/journalEmotionIntensity.css">
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
      <p class="navigation-bar-user-first-name" id="userFirstName">Jason</p>
    </div>
    <div class="navigation-bar-links-container">
      <a href="homepage.html" class="navigation-bar-link">
        <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Home</span>
      </a>
      <a href="journalHome.html" class="navigation-bar-link">
        <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text-selected">Journal</span>
      </a>
      <a href="#" class="navigation-bar-link">
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

  <!-- Top bar -->
  <div class="top-bar">
    <h1 class="page-title">Journal</h1>
    <a href="javascript:history.back()" class="back-arrow">
      <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
      <h2 class="go-back-text">Back</h2>
    </a>
    <img src="./images/progressBar/progressBarIntensity.svg" alt="Progress" class="progress-bar">
  </div>

  <!-- Main screen container -->
  <div class="main-screen-container">
    <div class="content-container">
      <!-- Chat Bubble with Question -->
      <div class="chat-bubble-container">
        <div class="chat-bubble-from" id="emotionIntensityQuestion">
          How <u><?php echo strtolower($emotionName); ?></u> do you feel?
        </div>
        <!-- Hidden inputs to hold emotion data from PHP -->
        <input type="hidden" id="passedEmotionName" value="<?php echo htmlspecialchars($emotionName); ?>">
        <input type="hidden" id="passedEmotionValue" value="<?php echo htmlspecialchars($emotionValue); ?>">
      </div>

      <!-- Intensity Slider / Form Section -->
      <div class="emotion-response-container">
        <div class="emotion-selection-form">
          <div class="left-content-container">
            <div class="left-content-container-top">
              <img src="./images/intensityBar/intensityBar3.svg" alt="selected intensity" class="selected-intensity-bar" id="selectedIntensityBar">
              <img src="./images/emotions/testEmoji.svg" alt="selected emotion emoji" class="selected-emotion-emoji" id="selectedEmotionEmoji">
            </div>
            <div class="left-content-container-middle">
              <div class="left-content-selected-emotion-container" id="selectedIntensity">Moderately</div>
            </div>
            <div class="left-content-container-bottom">
              <div class="slider-box">
                <div class="slider">
                  <div class="slider-progress"></div>
                  <div class="slider-stop" data-stop="1" style="left: 0%;"></div>
                  <div class="slider-stop" data-stop="2" style="left: 25%;"></div>
                  <div class="slider-stop" data-stop="3" style="left: 50%;"></div>
                  <div class="slider-stop" data-stop="4" style="left: 75%;"></div>
                  <div class="slider-stop" data-stop="5" style="left: 100%;"></div>
                  <div class="slider-handle"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="right-content-container">
            <div class="right-content-container-top">
              <div class="today-i-am-feeling-container">
                <h2>Today I am <br> feeling</h2>
              </div>
            </div>
            <div class="right-content-container-bottom">
              <div class="selected-emotion-name" id="selectedEmotionName">
                <?php echo htmlspecialchars($emotionName); ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Next Button Form for Intensity -->
      <div class="form-button-container">
        <form id="intensityForm" method="POST" action="saveIntensity.php">
          <input type="hidden" name="emotionName" id="finalEmotionName" value="<?php echo htmlspecialchars($emotionName); ?>">
          <input type="hidden" name="emotionValue" id="finalEmotionValue" value="<?php echo htmlspecialchars($emotionValue); ?>">
          <input type="hidden" name="intensityLevel" id="finalIntensityLevel">
          <input type="hidden" name="intensityLabel" id="finalIntensityLabel">
          <button class="form-button-next" type="submit">Next</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Front and Middle Waves -->
  <div class="waves-container">
    <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
    <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
  </div>

  <script src="scripts/journalIntensity.js"></script>
</body>
</html>
<script>
    // When the DOM content is fully loaded, this script fetches user information from the server.
    // It then updates the element with id 'userFirstName' to display the username.
    // If there is an error during the fetch operation, it logs the error to the console.
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