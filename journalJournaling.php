<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

// Retrieve dynamic values from session or set defaults
// These values should have been set previously (via saveEmotion.php and saveIntensity.php)
$dynamicEmotionId = $_SESSION['selected_emotion_id'] ?? '1'; 
$dynamicIntensity = $_SESSION['selected_emotional_intensity'] ?? '5';
$emotionName = $_SESSION['emotion_name'] ?? 'excited';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- Ensure the doctype is HTML5 to avoid Quirks Mode -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Journal</title>
    <link rel="stylesheet" href="styles/layoutwithnavbar.css">
    <link rel="stylesheet" href="styles/stylevariables.css">
    <link rel="stylesheet" href="styles/journalJournaling.css">
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
            <p class="navigation-bar-user-first-name" id="userFirstName"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
        <!-- Links Background -->
        <div class="navigation-bar-links-container">
            <a href="homePage.html" class="navigation-bar-link">
                <img src="./images/icons/homeIcon.svg" alt="Home Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Home</span>
            </a>
            <a href="journalJournaling.html" class="navigation-bar-link">
                <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text-selected">Journal</span>
            </a>
            <a href="strategies.html" class="navigation-bar-link">
                <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Strategies</span>
            </a>
            <a href="progress.html" class="navigation-bar-link">
                <img src="./images/icons/progressIcon.svg" alt="Progress Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Progress</span>
            </a>
            <a href="settings.html" class="navigation-bar-link">
                <img src="./images/icons/settingsIcon.svg" alt="Settings Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Settings</span>
            </a>
        </div>
    </div>

    <!-- Top Bar -->
    <div class="top-bar">
        <h1 class="page-title">Journal</h1>
        <a href="javascript:history.back()" class="back-arrow">
            <img src="./images/backarrow.svg" alt="Back Arrow" class="back-arrow-icon">
            <h2 class="go-back-text">Back</h2>
        </a>
        <img src="./images/progressBar/progressBarJournal.svg" alt="Progress" class="progress-bar">
    </div>

    <!-- Main Screen Container -->
    <div class="main-screen-container">
        <div class="content-container">
            <div class="chat-bubble-container">
                <div class="chat-bubble-from">
                    Feel free to click 
                    <img src="./images/icons/newPromptIcon.svg" alt="New Prompt" class="inline-new-prompt-icon">
                    for a new prompt!
                </div>
                <div class="prompt-message-container">
                    <img src="./images/icons/newPromptIcon.svg" alt="New Prompt" class="new-prompt-icon">
                    <!-- The prompt text will include the dynamic emotion name -->
                    <div class="chat-bubble-from">
                        <u id="Prompt">Why are you feeling <?php echo htmlspecialchars($emotionName); ?> today?</u>
                    </div>
                </div>
            </div>

            <!-- Journal Entry Form -->
            <form action="journaling.php" method="POST">
                <div class="journal-container">
                    <div class="wrapper">
                        <div class="inner-wrapper">
                            <h2>Write your journal below!</h2>
                            <textarea name="journalContent" id="journalContent" spellcheck="false" placeholder="Click here to begin typing..." required></textarea>
                            <!-- Dynamic hidden inputs using session data -->
                            <input type="hidden" name="emotionId" value="<?php echo htmlspecialchars($dynamicEmotionId); ?>">
                            <input type="hidden" name="emotionalIntensityRating" value="<?php echo htmlspecialchars($dynamicIntensity); ?>">
                        </div>
                    </div>
                </div>
                <div class="form-button-container">
                    <button type="submit" class="form-button-next">Next</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Waves -->
    <div class="waves-container">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

    <!-- Scripts for prompt generation and textarea resizing -->
    <script>
        // Array of prompts that include a placeholder [emotion]
        const prompts = [
            "Why are you feeling [emotion] today?",
            "Is there something that made you feel [emotion] today?",
            "How did you handle feeling [emotion] today?",
            "Write about a time when you felt [emotion] before.",
            "What can you do next time you feel [emotion] to help yourself feel better?"
        ];
        function setRandomPrompt() {
            const randomPrompt = prompts[Math.floor(Math.random() * prompts.length)];
            // Replace the placeholder with the actual emotion name from PHP (escaped)
            document.getElementById('Prompt').innerText = randomPrompt.replace(/\[emotion\]/g, "<?php echo addslashes($emotionName); ?>");
        }
        document.querySelectorAll('.new-prompt-icon, .inline-new-prompt-icon').forEach(item => {
            item.addEventListener('click', setRandomPrompt);
        });
    </script>
    <script>
        const textarea = document.querySelector("textarea");
        function adjustHeight() {
            textarea.style.height = "40px";
            let scHeight = textarea.scrollHeight;
            textarea.style.height = `${scHeight}px`;
            textarea.style.overflowY = scHeight >= 300 ? "auto" : "hidden";
        }
        textarea.addEventListener("keyup", adjustHeight);
        window.addEventListener("load", adjustHeight);
    </script>
    <script>
        // Update user info via AJAX (if needed)
        document.addEventListener("DOMContentLoaded", function() {
            fetch('getUserInfo.php')
                .then(response => response.json())
                .then(data => {
                    if (data.username) {
                        document.getElementById('userFirstName').textContent = data.username;
                    }
                })
                .catch(error => console.error('Error fetching user info:', error));
        });
    </script>
</body>
</html>
