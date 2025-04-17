<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Set JavaScript variable based on session data
$isJournalComplete = isset($_SESSION['journalEntry']['journal_entry_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Journal</title>
    <link rel="stylesheet" href="styles/layoutwithnavbar.css">
    <link rel="stylesheet" href="styles/stylevariables.css">
    <link rel="stylesheet" href="styles/journalHome.css">
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
        
        <!-- Profile Picture Section -->
        <div class="navigation-bar-profile-picture-section">
            <img src="./images/profilePictures/Deer.webp" alt="Profile Picture" class="navigation-bar-profile-picture">
        </div>
    
        <!-- Profile Name Section -->
        <div class="navigation-bar-user-name-section">
            <p class="navigation-bar-user-first-name" id="userFirstName"></p>
        </div>

        <!-- Links Background -->
        <div class="navigation-bar-links-container">
            <!-- Home Link -->
            <a href="homepage.html" class="navigation-bar-link">
                <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Home</span>
            </a>
            <!-- Journal Link -->
            <a href="journalHome.php" class="navigation-bar-link">
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
        <!-- Title of Current Page-->
        <h1 class="page-title">Journal</h1>
    </div>
    
    <!-- Main screen container -->
    <div class="main-screen-container">
        <!-- Container for boxes -->
        <div class="content-container">
          
            <div class="home-page-boxes-container">
                <!-- Journal Box -->
                <a href="journalEmotionSelection.html" class="home-page-box home-page-journal-box">
                    <div class="home-page-box-status" id="journalStatus">
                      <?php
                        // Update journal status based on completion
                        if ($isJournalComplete) {
                            echo "Good job! You've completed your journal today!";
                        } else {
                            echo "You haven't done your journal today.";
                        }
                        ?>
                    </div>
                    <div class="home-page-box-text home-page-journal-box-text" id="journalDirection">
                        Begin Journal!
                    </div>
                </a>
                <!-- All Journals Box -->
                <a href="#" class="home-page-box home-page-all-journal-box">
                    <div class="home-page-box-status" id="currentUserAllJournals">
                        All your journals
                    </div>
                    <div class="home-page-box-text home-page-all-journal-box-text">
                        Look at Journals
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Front and Middle Waves -->
    <div class="waves-container">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

<script>
//script to dynamically update the user's journaling status
    document.addEventListener("DOMContentLoaded", function() {
        // Fetch user info
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
        
        // Fetch journal status with cache-busting
        fetch('getHomePageInfo.php?_=' + new Date().getTime())
            .then(response => response.json())
            .then(data => {
                console.log("Received journal home data:", data); // Add debugging
                
                if (data.success) {
                    // Update journal status
                    const journalStatus = document.getElementById('journalStatus');
                    const journalDirection = document.getElementById('journalDirection');
                    
                    if (data.journal_completed) {
                        journalStatus.textContent = "Good job! You've completed your journal today!";
                        journalDirection.textContent = "View Today's Journal";
                    } else {
                        journalStatus.textContent = "You haven't done your journal today.";
                        journalDirection.textContent = "Begin Journal!";
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching homepage info:', error);
            });
    });
</script>
  
</body>
</html>
