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

// ----------------------------------------------------------------
// Get date filter parameters (if provided) and build a date clause.
// ----------------------------------------------------------------
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date'])
    ? $mysqli->real_escape_string($_GET['start_date'])
    : "";
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date'])
    ? $mysqli->real_escape_string($_GET['end_date'])
    : "";
$dateClause = "";
if ($start_date != "") {
    $dateClause .= " AND journal_entry.journal_date >= '$start_date'";
}
if ($end_date != "") {
    $dateClause .= " AND journal_entry.journal_date <= '$end_date'";
}

// Query: Most commonly picked emotion for the user (limited to date filter if applied).
$emotionQuery = "
    SELECT e.emotion_name, e.emotion_id, e.emotion_core_category, COUNT(*) AS cnt
    FROM journal_entry
    JOIN emotion e ON journal_entry.emotion_id = e.emotion_id
    WHERE journal_entry.user_id = $currentUserId $dateClause
    GROUP BY e.emotion_id
    ORDER BY cnt DESC
    LIMIT 1
";
$emotionResult = $mysqli->query($emotionQuery);
$mostCommonEmotion = $emotionResult->fetch_assoc();

// prevent null errors
if ($mostCommonEmotion === null) {
    $mostCommonEmotion = [
        'emotion_name'          => null,
        'emotion_id'            => null,
        'emotion_core_category' => null,
        'cnt'                   => 0
    ];
}


// Query: Total number of journal entries for the user (within date filter).
$totalQuery = "SELECT COUNT(*) AS total FROM journal_entry WHERE user_id = $currentUserId $dateClause";
$totalResult = $mysqli->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalEntries = (int)$totalRow['total'];

// calculate the percentage of times the most common emotion was chosen.
$percentChosen = ($totalEntries > 0)
    ? round(($mostCommonEmotion['cnt'] / $totalEntries) * 100)
    : 0;


// Query: Most commonly chosen core category for the user (within date filter).
$coreQuery = "
    SELECT e.emotion_core_category, COUNT(*) AS cnt
    FROM journal_entry
    JOIN emotion e ON journal_entry.emotion_id = e.emotion_id
    WHERE journal_entry.user_id = $currentUserId $dateClause
    GROUP BY e.emotion_core_category
    ORDER BY cnt DESC
    LIMIT 1
";
$coreResult = $mysqli->query($coreQuery);
$coreRow = $coreResult->fetch_assoc();

// prevent null errors
if ($coreRow === null) {
    $coreRow = [
        'emotion_core_category' => null,
        'cnt'                   => 0
    ];
}

$mostCommonCoreCategory = $coreRow['emotion_core_category'];


// Determine Emoji Filenames
// For the most commonly picked emotion, assume the emoji filename is the lower-case version 
// of the emotion name with spaces removed, ending in ".svg".
$mostPickedEmotionName = $mostCommonEmotion['emotion_name'] ?? 'None';
$mostPickedEmotionEmoji = isset($mostCommonEmotion['emotion_name'])
    ? strtolower(str_replace(' ', '', $mostPickedEmotionName)) . '.svg'
    : 'noEmotionSelected.svg';

// For the most common core category, use a mapping array (update as needed).
$coreCategoryEmojis = array(
    "Happiness" => "happy.svg",
    "Sadness"   => "sad.svg",
    "Anger"     => "angry.svg",
    "Fear"      => "scared.svg",
    "Surprise"  => "surprised.svg",
    "Disgust"   => "disgusted.svg",
    "Neutral"   => "neutral.svg"
);
$mostCommonCoreCategory = $mostCommonCoreCategory ?? 'None';
$mostCommonCoreCategoryEmoji = isset($coreCategoryEmojis[$mostCommonCoreCategory])
    ? $coreCategoryEmojis[$mostCommonCoreCategory]
    : "noEmotionSelected.svg";

// Set filter display text based on active filters
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

// Close the database connection.
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Emotion Report</title>
    <link rel="stylesheet" href="styles/layoutwithnavbar.css">
    <link rel="stylesheet" href="styles/stylevariables.css">
    <link rel="stylesheet" href="styles/progressEmotionReportSimple.css">
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
            <a href="journalHome.php" class="navigation-bar-link">
                <img src="./images/icons/journalIcon.svg" alt="Journal Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Journal</span>
            </a>
            <!-- Strategies Link -->
            <a href="strategiesHome.html" class="navigation-bar-link">
                <img src="./images/icons/strategyIcon.svg" alt="Strategies Icon" class="navigation-bar-link-icon">
                <span class="navigation-bar-link-text">Strategies</span>
            </a>
            <!-- Progress Link -->
            <a href="progressHome.html" class="navigation-bar-link">
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
        <h1 class="page-title">Emotion Report</h1>
        <!-- Go Back arrow -->
        <a href="javascript:history.back()" class="back-arrow">
            <img src="./images/backarrow.svg" alt="back arrow" class="back-arrow-icon">
            <h2 class="go-back-text">Back</h2>
        </a>

        <div class="filter-button-container">
            <div class="filter-dates-text" id="filterFromDate"><?php echo $filterFromDateText; ?></div>
            <div class="filter-dates-text" id="filterToDate"><?php echo $filterToDateText; ?></div>
            <button class="filter-button" id="openFilterButton">Filter</button>
        </div>
    </div>

    <!-- Main screen container -->
    <div class="main-screen-container">
        <!-- Container for everything on main screen -->
        <div class="content-container">

            <div class="boxes-container">

                <!-- Report Box for Most Common Core Category-->
                <div class="report-box">

                    <div class="left-content-container">

                        <div class="left-content-inner-container-shading"> </div>

                        <!-- What holds the top and bottom colored boxes -->
                        <div class="left-content-inner-container">

                            <div class="left-content-inner-container-top">

                                <div class="left-content-inner-container-top-text">Chosen</div>

                                <div class="left-content-inner-container-middle-text" id="mostCommonCoreCategoryPercentChosen"><?php echo $percentChosen; ?>%</div>

                                <div class="left-content-inner-container-bottom-text">of the Time</div>

                            </div>

                            <div class="left-content-inner-container-bottom">

                                <div class="left-content-inner-container-top-text">Most Commonly <br> Picked Emotion</div>

                                <div class="emotion-emoji-container">
                                    <img src="./images/emotions/<?php echo $mostPickedEmotionEmoji; ?>" alt="Most Commonly Picked Emotion" id="mostPickedEmotionEmoji" class="emotion-emoji">
                                    <div class="emotion-emoji-name" id="mostPickedEmotionName"><?php echo htmlspecialchars($mostPickedEmotionName); ?></div>
                                </div>

                                <div class="left-content-inner-container-bottom-text" id="mostCommonEmotionTimesPicked">
                                    Picked <?php echo isset($mostCommonEmotion['cnt']) ? $mostCommonEmotion['cnt'] : '0'; ?> Times
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="right-content-container">

                        <div class="right-content-container-top-text">Most Common <br> Core Category</div>

                        <div class="emotion-emoji-big-container">
                            <img src="./images/emotions/<?php echo $mostCommonCoreCategoryEmoji; ?>" alt="Most Common Emotion Category" id="mostCommonCoreCategoryEmoji" class="emotion-emoji-big">
                        </div>

                        <div class="right-content-container-bottom-text" id="mostCommonCoreCategory"><?php echo htmlspecialchars($mostCommonCoreCategory); ?></div>

                    </div>

                </div>

                <!-- The box for "Most Intense Emotion" is currently commented out. -->

            </div>

            <div class="button-container">
                <button class="go-to-full-report-button" id="goToFullReportButton">Go to Full Report</button>
            </div>

        </div>
    </div>

    <!-- ============================= -->
    <!-- START OF DATE FILTER MODAL SECTION -->
    <!-- ============================= -->

    <!-- Filter Modal Container -->
    <div class="filter-modal-container" id="filterModal">
        <div class="filter-modal-outer-box">
            <div class="filter-modal-content">
                <h2>Filter</h2>

                <h3 class="date-filter-title">Filter by Date</h3>
                <!-- Default Date Options -->
                <div class="default-date-options">
                    <button class="default-option" id="optionLastMonth">Last Month</button>
                    <button class="default-option" id="optionLast3Months">Last 3 Months</button>
                    <button class="default-option" id="optionLast6Months">Last 6 Months</button>
                    <button class="default-option" id="optionLastYear">Last Year</button>
                </div>

                <!-- Date Filter Container -->
                <div class="date-filter-container">
                    <div class="date-filter-section">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" value="<?php echo $start_date; ?>">
                        
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" value="<?php echo $end_date; ?>">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="filter-buttons">
                    <button id="applyFilterButton">Apply/Close</button>
                    <button id="cancelFilterButton">Cancel</button>
                    <button id="clearFiltersButton">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================= -->
    <!--  END OF DATE FILTER MODAL SECTION  -->
    <!-- ============================= -->

    <!-- Front and Middle Waves -->
    <div class="waves-container">
        <img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
        <img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
    </div>

    <script>
        const currentFile = "progressEmotionReportSimple";
    </script>
    <script src="scripts/helpButton.js"></script>

    <script>
        // grab modal elements
        const openFilterBtn = document.getElementById('openFilterButton');
        const filterModal = document.getElementById('filterModal');
        const applyFilterButton = document.getElementById('applyFilterButton');
        const cancelFilterButton = document.getElementById('cancelFilterButton');
        const clearFiltersButton = document.getElementById('clearFiltersButton');
        const goToFullReportButton = document.getElementById('goToFullReportButton');
        
        // date input fields
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        
        // default date option buttons
        const optionLastMonth = document.getElementById('optionLastMonth');
        const optionLast3Months = document.getElementById('optionLast3Months');
        const optionLast6Months = document.getElementById('optionLast6Months');
        const optionLastYear = document.getElementById('optionLastYear');
        
        // elements to update with the selected dates
        const filterFromDateText = document.getElementById('filterFromDate');
        const filterToDateText = document.getElementById('filterToDate');
        
        // current filter values
        let currentStartDate = "<?php echo $start_date; ?>";
        let currentEndDate = "<?php echo $end_date; ?>";
        
        // Utility function: format a Date object as YYYY-MM-DD (for input[type="date"] value)
        function formatDate(date) {
          const year = date.getFullYear();
          const month = ('0' + (date.getMonth() + 1)).slice(-2);
          const day = ('0' + date.getDate()).slice(-2);
          return `${year}-${month}-${day}`;
        }
        
        // default option event listeners
        optionLastMonth.addEventListener('click', () => {
          const today = new Date();
          const startDate = new Date(today);
          startDate.setMonth(startDate.getMonth() - 1);
          startDateInput.value = formatDate(startDate);
          endDateInput.value = formatDate(today);
        });
        
        optionLast3Months.addEventListener('click', () => {
          const today = new Date();
          const startDate = new Date(today);
          startDate.setMonth(startDate.getMonth() - 3);
          startDateInput.value = formatDate(startDate);
          endDateInput.value = formatDate(today);
        });
        
        optionLast6Months.addEventListener('click', () => {
          const today = new Date();
          const startDate = new Date(today);
          startDate.setMonth(startDate.getMonth() - 6);
          startDateInput.value = formatDate(startDate);
          endDateInput.value = formatDate(today);
        });
        
        optionLastYear.addEventListener('click', () => {
          const today = new Date();
          const startDate = new Date(today);
          startDate.setFullYear(startDate.getFullYear() - 1);
          startDateInput.value = formatDate(startDate);
          endDateInput.value = formatDate(today);
        });
        
        // open the modal when the Filter button is clicked
        openFilterBtn.addEventListener('click', () => {
          filterModal.style.display = 'block';
        });
        
        // "apply/Close" button: update the text elements and close the modal
        applyFilterButton.addEventListener('click', () => {
          const startDateVal = startDateInput.value;
          const endDateVal = endDateInput.value;
          
          // validate dates
          if (startDateVal && endDateVal && new Date(endDateVal) < new Date(startDateVal)) {
            alert("End date cannot be before start date.");
            return;
          }
          
          // close the modal
          filterModal.style.display = 'none';
          
          // reload the page with the date filter values as GET parameters
          let urlParams = new URLSearchParams(window.location.search);
          if (startDateVal) {
              urlParams.set('start_date', startDateVal);
          } else {
              urlParams.delete('start_date');
          }
          if (endDateVal) {
              urlParams.set('end_date', endDateVal);
          } else {
              urlParams.delete('end_date');
          }
          window.location.search = urlParams.toString();
        });
        
        // "Cancel" button: close the modal without applying filters
        cancelFilterButton.addEventListener('click', () => {
          // restore previous values
          startDateInput.value = currentStartDate;
          endDateInput.value = currentEndDate;
          filterModal.style.display = 'none';
        });
        
        // "Clear Filters" button: clear the date fields and reload page without date filters
        clearFiltersButton.addEventListener('click', () => {
          startDateInput.value = "";
          endDateInput.value = "";
          window.location.search = "";
        });
        
        // full Report button: navigate to the full report with current filters
        goToFullReportButton.addEventListener('click', () => {
          // construct URL with current filter parameters
          let url = "progressEmotionReportFull.php";
          let urlParams = new URLSearchParams(window.location.search);
          if (urlParams.toString()) {
            url += "?" + urlParams.toString();
          }
          window.location.href = url;
        });

        // update navigation bar with user info.
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
