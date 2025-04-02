<?php
session_start();

// ensure the user is signed in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ensure the required GET parameters are present.
if (!isset($_GET['id']) || !isset($_GET['index'])) {
    die("Journal entry not specified.");
}

$journalId = intval($_GET['id']);
$journalIndex = intval($_GET['index']);

$host = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "emotionalregulationapp";

$mysqli = new mysqli($host, $db_username, $db_password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// query to retrieve the specific journal entry.
$query = "
    SELECT 
        journal_entry.journal_date, 
        journal_entry.journal_content,
        emotion.emotion_name,
        coping_strategy.strategy_name,
        journal_entry.emotional_intensity_rating
    FROM journal_entry
    JOIN emotion ON journal_entry.emotion_id = emotion.emotion_id
    JOIN coping_strategy ON journal_entry.strategy_id = coping_strategy.strategy_id
    WHERE journal_entry.journal_entry_id = $journalId
";

$result = $mysqli->query($query);
if ($result->num_rows > 0) {
    $journal = $result->fetch_assoc();
} else {
    die("Journal entry not found.");
}
$mysqli->close();

// retrieve username from session or default to "Unknown User"
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Unknown User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Journal View</title>
    <link rel="stylesheet" href="styles/layoutwithnavbar.css">
    <link rel="stylesheet" href="styles/stylevariables.css">
    <link rel="stylesheet" href="styles/journalViewJournal.css">
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
            <p class="navigation-bar-user-first-name" id="userFirstName"><?php echo htmlspecialchars($username); ?></p>
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
    </div>

    <!-- Main Screen Container -->
    <div class="main-screen-container">
        <div class="content-container">
            <div class="view-journal-box">
                <div class="download-icon" id="downloadIcon">
                    <img src="./images/icons/downloadIcon.svg" alt="Download">
                </div>
                
                <div class="left-content-container">
                    <div class="left-content-inner-container-shading"></div>
                    <div class="left-content-inner-container">
                        <div class="left-content-inner-container-top">
                            <div class="left-content-inner-container-top-text">Chosen Emotion</div>
                            <div class="chosen-emotion-image-container">
                                <!-- These images can be updated dynamically if needed -->
                                <img src="./images/intensityBar/intensityBar3.svg" alt="selected intensity" class="selected-intensity-bar" id="selectedIntensityBar">
                                <img src="./images/emotions/testEmoji.svg" alt="selected emotion emoji" class="selected-emotion-emoji" id="selectedEmotionEmoji">
                            </div>
                            <div class="left-content-inner-container-bottom-text" id="journalEmotion">
                                <?php echo htmlspecialchars($journal['emotion_name']); ?>
                            </div>
                        </div>
                        <div class="left-content-inner-container-bottom">
                            <div class="left-content-inner-container-top-text">Assigned Strategy</div>
                            <div class="strategy-image-container">
                                <img src="./images/icons/strategyIconBigShaded.svg" alt="assigned strategy" class="assigned-strategy-image">
                            </div>
                            <div class="left-content-inner-container-bottom-text" id="journalAssignedStrategy">
                                <?php echo htmlspecialchars($journal['strategy_name']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="right-content-container">
                    <div class="journal-info-container">
                        <p id="journalAuthorName"><?php echo htmlspecialchars($username); ?></p>
                        <p id="journalNumber">Journal <?php echo htmlspecialchars($journalIndex); ?></p>
                        <p id="journalDate"><?php echo htmlspecialchars($journal['journal_date']); ?></p>
                    </div>

                    <div class="journal-prompt" id="journalPrompt">
                        Please explain why you felt this way today.
                    </div>

                    <div class="journal-content" id="journalContent">
                        <?php echo nl2br(htmlspecialchars($journal['journal_content'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Front and Middle Waves -->
    <div class="waves-container">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

    <script src="scripts/journalViewJournal.js"></script>
    <script>
    // Update the navigation bar with user info.
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
