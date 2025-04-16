<?php
session_start();

// Ensure the user is signed in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = $_SESSION['user_id'];

// can probably replace with db connect file
$host = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "emotionalregulationapp";

$mysqli = new mysqli($host, $db_username, $db_password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// ------------------------------------------------------------------
// Get date filter parameters from GET and build a date clause.
// Use MySQL DATE() function to ignore any time portion.
// ------------------------------------------------------------------
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date'])
    ? $mysqli->real_escape_string($_GET['start_date'])
    : "";
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date'])
    ? $mysqli->real_escape_string($_GET['end_date'])
    : "";
$dateClause = "";
if ($start_date !== "") {
    $dateClause .= " AND DATE(journal_entry.journal_date) >= '$start_date'";
}
if ($end_date !== "") {
    $dateClause .= " AND DATE(journal_entry.journal_date) <= '$end_date'";
}

// set filter display text based on active filters
$filterFromDateText = "All Time";
$filterToDateText = "";
if ($start_date != "") {
    $filterFromDateText = "From: " . $start_date;
    if ($end_date != "") {
        $filterToDateText = "To: " . $end_date;
    }
} else if ($end_date != "") {
    $filterToDateText = "To: " . $end_date;
}

// ------------------------------------------------------------------
// Query: For each emotion, get emotion name, core category, times chosen and average intensity.
// ------------------------------------------------------------------
$query = "
    SELECT 
        e.emotion_name, 
        e.emotion_core_category, 
        COUNT(*) AS cnt, 
        AVG(journal_entry.emotional_intensity_rating) AS avg_intensity
    FROM journal_entry
    JOIN emotion e ON journal_entry.emotion_id = e.emotion_id
    WHERE journal_entry.user_id = $currentUserId $dateClause
    GROUP BY e.emotion_id
    ORDER BY cnt DESC
";
$result = $mysqli->query($query);

// ------------------------------------------------------------------
// Close the database connection (we'll use $result for display).
// ------------------------------------------------------------------
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Emotion Report - Full Report</title>
  <link rel="stylesheet" href="styles/layoutwithnavbar.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/progressEmotionReportFull.css">
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
          <p class="navigation-bar-user-first-name" id="userFirstName">Jason</p>
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
              <span class="navigation-bar-link-text">Journal</span>
          </a>
          <!-- Strategies Link -->
          <a href="#" class="navigation-bar-link">
              <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text">Strategies</span>
          </a>
          <!-- Progress Link -->
          <a href="#" class="navigation-bar-link">
              <img src="./images/icons/progressIcon.svg" alt="Progress Icon" class="navigation-bar-link-icon">
              <span class="navigation-bar-link-text-selected">Progress</span>
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
    <h1 class="page-title">Emotion Report - Full Report</h1>
    <!-- Go Back arrow -->
    <a href="javascript:history.back()" class="back-arrow">
        <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
        <h2 class="go-back-text">Back</h2>
    </a>
</div>
      
      <!-- Display current filter -->
      <div class="filter-button-container">
          <div class="filter-dates-text" id="filterFromDate"><?php echo $filterFromDateText; ?></div>
          <div class="filter-dates-text" id="filterToDate"><?php echo $filterToDateText; ?></div>
          <button class="filter-button" id="openFilterButton">Filter</button>
      </div>
  </div>
    
  <!-- Main screen container -->
<div class="main-screen-container">
    <!-- Container for the table-->
    <div class="content-container">
        <div class="table-container">
            <div class="download-icon" id="downloadIcon"> 
                <img src="./images/icons/downloadIcon.svg" alt="Download">
            </div>
            <div class="table-shading"></div>
            <table>
                <thead>
                    <tr>
                        <th>Emotion</th>
                        <th>Core Category</th>
                        <th>Times Chosen</th>
                        <th>Average Intensity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['emotion_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['emotion_core_category']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['cnt']) . "</td>";
                            echo "<td>" . htmlspecialchars(number_format($row['avg_intensity'], 1)) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No journal entries found for the selected date range.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Front and Middle Waves -->
<div class="waves-container">
    <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
    <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
</div>

<!-- download button functionality -->
<script src="scripts/progressEmotionReportFull.js"></script>

<script>
    // Update navigation bar with user info
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
