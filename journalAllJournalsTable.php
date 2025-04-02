<?php
session_start();

// this seems to be having issues... Check if the user is signed in; if not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];

// db connect that can likely be replaced with our db connect file.
$host = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "emotionalregulationapp";

// Create connection
$mysqli = new mysqli($host, $db_username, $db_password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Query to retrieve journal entries for the currently signed in user.
$query = "
    SELECT 
        journal_entry.journal_date, 
        emotion.emotion_name, 
        journal_entry.emotional_intensity_rating, 
        coping_strategy.strategy_name
    FROM journal_entry
    JOIN emotion ON journal_entry.emotion_id = emotion.emotion_id
    JOIN coping_strategy ON journal_entry.strategy_id = coping_strategy.strategy_id
    WHERE journal_entry.user_id = $currentUserId
    ORDER BY journal_entry.journal_date ASC
";

$result = $mysqli->query($query);
$index = 1; // Used for the Journal Number column.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Journal</title>
    <link rel="stylesheet" href="styles/layoutwithnavbar.css">
    <link rel="stylesheet" href="styles/stylevariables.css">
    <link rel="stylesheet" href="styles/journalAllJournalsTable.css">
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
</head>
<body>

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
            <div class="table-and-filter-container">
                <div class="button-container">
                    <button class="filter-button">Filter</button>
                </div>
                <div class="table-container">
                    <div class="table-shading"></div>
                    <table>
                        <thead>
                            <tr>
                                <th>Journal Number</th>
                                <th>Date</th>
                                <th>Emotion</th>
                                <th>Intensity</th>
                                <th>Strategy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $index++ . "</td>";
                                    echo "<td>" . htmlspecialchars($row['journal_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['emotion_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['emotional_intensity_rating']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['strategy_name']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>No journal entries found.</td></tr>";
                            }
                            $mysqli->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Front and Middle Waves -->
    <div class="waves-container">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

    <script>
    // Fetch user information for the navigation bar.
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
