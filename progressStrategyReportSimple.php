<?php
session_start();

// ensure the user is signed in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$currentUserId = (int)$_SESSION['user_id'];

// can probably replace with db connect file
$host        = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "emotionalregulationapp";

$mysqli = new mysqli($host, $db_username, $db_password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// get date filter parameters
$start_date = isset($_GET['start_date']) && $_GET['start_date'] !== ''
    ? $mysqli->real_escape_string($_GET['start_date'])
    : "";
$end_date = isset($_GET['end_date']) && $_GET['end_date'] !== ''
    ? $mysqli->real_escape_string($_GET['end_date'])
    : "";

// build date clause
$dateClause = "";
if ($start_date !== "") {
    $dateClause .= " AND DATE(sf.feedback_date) >= '$start_date'";
}
if ($end_date !== "") {
    $dateClause .= " AND DATE(sf.feedback_date) <= '$end_date'";
}

// Query: most helpful strategy (highest helpful ratio, tie‑broken by helpful count)
$strategyQuery = "
    SELECT
      cs.strategy_name,
      COUNT(*)                          AS assigned_count,
      SUM(sf.is_helpful)                AS helpful_count,
      (SUM(sf.is_helpful)/COUNT(*)*100) AS pct_helpful
    FROM strategy_feedback sf
    JOIN assigned_strategy asg ON sf.assignment_id = asg.assignment_id
    JOIN coping_strategy cs    ON asg.strategy_id = cs.strategy_id
    WHERE asg.user_id = $currentUserId
      $dateClause
    GROUP BY cs.strategy_id
    ORDER BY pct_helpful DESC, helpful_count DESC
    LIMIT 1
";
$result = $mysqli->query($strategyQuery);
$best   = $result->fetch_assoc();

// defaults for no data found
if (!$best) {
    $best = [
        'pct_helpful'    => 0,
        'assigned_count' => 0,
        'strategy_name'  => ''
    ];
}

// decide which image to show: use strategyIconBigShaded if there is data,
// otherwise fall back to noEmotionSelected.svg
$strategyCount = (int)$best['assigned_count'];
$strategyName  = $best['strategy_name'];
if ($strategyCount > 0 && $strategyName !== '') {
    $displayImage = "icons/strategyIconBigShaded.svg";
} else {
    $displayImage = "emotions/noEmotionSelected.svg";
}

// set filter display text
$filterFromDateText = "All Time";
$filterToDateText   = "";
if ($start_date !== "") {
    $filterFromDateText = "From: $start_date";
    if ($end_date !== "") {
        $filterToDateText = "To: $end_date";
    }
} elseif ($end_date !== "") {
    $filterToDateText = "To: $end_date";
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>Strategy Report</title>
  <link rel="stylesheet" href="styles/layoutwithnavbar.css">
  <link rel="stylesheet" href="styles/stylevariables.css">
  <link rel="stylesheet" href="styles/progressStrategyReportSimple.css">
  <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
</head>
<body>

  <!-- Back Wave -->
  <div class="waves-container">
    <img src="images/waveBack.svg" alt="Back Wave" class="wave back-wave">
  </div>

  <!-- Navigation Bar -->
  <div class="navigation-bar">
    <a href="index.php" id="navigation-bar-sign-out-icon" data-tooltip="Sign Out">
      <img src="images/icons/signOutIcon.svg" alt="Sign Out Icon" class="navigation-bar-sign-out-icon">
    </a>
    <div class="navigation-bar-profile-picture-section">
      <img src="images/profilePictures/Deer.webp" alt="Profile Picture" class="navigation-bar-profile-picture">
    </div>
    <div class="navigation-bar-user-name-section">
      <p class="navigation-bar-user-first-name" id="userFirstName">User</p>
    </div>
    <div class="navigation-bar-links-container">
      <a href="homepage.html" class="navigation-bar-link">
        <img src="images/icons/homeIcon.svg" alt="Home" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Home</span>
      </a>
      <a href="journalHome.html" class="navigation-bar-link">
        <img src="images/icons/journalIcon.svg" alt="Journal" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Journal</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="images/icons/strategyIcon.svg" alt="Strategies" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Strategies</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="images/icons/progressIcon.svg" alt="Progress" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text-selected">Progress</span>
      </a>
      <a href="#" class="navigation-bar-link">
        <img src="images/icons/settingsIcon.svg" alt="Settings" class="navigation-bar-link-icon">
        <span class="navigation-bar-link-text">Settings</span>
      </a>
    </div>
  </div>

  <!-- Top bar -->
  <div class="top-bar">
    <h1 class="page-title">Strategy Report</h1>
    <a href="javascript:history.back()" class="back-arrow">
      <img src="images/backarrow.svg" alt="Back" class="back-arrow-icon">
      <h2 class="go-back-text">Back</h2>
    </a>
    <div class="filter-button-container">
      <div class="filter-dates-text" id="filterFromDate"><?php echo $filterFromDateText; ?></div>
      <div class="filter-dates-text" id="filterToDate"><?php echo $filterToDateText; ?></div>
      <button id="openFilterButton" class="filter-button">Filter</button>
    </div>
  </div>

  <!-- Main screen container -->
  <div class="main-screen-container">
    <div class="content-container">

      <div class="boxes-container">

        <!-- Most Helpful Strategy -->
        <div class="report-box">

          <div class="left-content-container">
            <div class="left-content-inner-container-shading"></div>
            <div class="left-content-inner-container">

              <div class="left-content-inner-container-top">
                <div class="left-content-inner-container-top-text">Rated</div>
                <div class="left-content-inner-container-middle-text" id="mostHelpfulStrategyRating">
                  <?php echo round($best['pct_helpful']); ?>%
                </div>
                <div class="left-content-inner-container-bottom-text">Helpful</div>
              </div>

              <div class="left-content-inner-container-bottom">
                <div class="left-content-inner-container-top-text">Assigned</div>
                <div class="left-content-inner-container-middle-text" id="mostHelpfulStrategyNumberAssigned">
                  <?php echo (int)$best['assigned_count']; ?>
                </div>
                <div class="left-content-inner-container-bottom-text">Times</div>
              </div>

            </div>
          </div>

          <div class="right-content-container">
            <div class="right-content-container-top-text">Most Helpful Strategy</div>
            <div class="strategy-image-container">
              <img
                src="images/<?php echo $displayImage; ?>"
                alt="<?php echo htmlspecialchars($strategyName ?: 'No Strategy'); ?>"
                class="assigned-strategy-image"
              >
            </div>
            <div class="right-content-container-bottom-text" id="mostHelpfulStrategy">
              <?php echo htmlspecialchars($strategyName); ?>
            </div>
          </div>

        </div>
      </div>

      <div class="button-container">
        <button id="goToFullReportButton" class="go-to-full-report-button">
          Go to Full Report
        </button>
      </div>

    </div>
  </div>

  <!-- Filter Modal -->
  <div id="filterModal" class="filter-modal-container">
    <div class="filter-modal-outer-box">
      <div class="filter-modal-content">
        <h2>Filter</h2>
        <h3 class="date-filter-title">Filter by Date</h3>
        <div class="default-date-options">
          <button id="optionLastMonth"    class="default-option">Last Month</button>
          <button id="optionLast3Months" class="default-option">Last 3 Months</button>
          <button id="optionLast6Months" class="default-option">Last 6 Months</button>
          <button id="optionLastYear"    class="default-option">Last Year</button>
        </div>
        <div class="date-filter-container">
          <div class="date-filter-section">
            <label for="startDate">Start Date:</label>
            <input type="date" id="startDate" value="<?php echo $start_date; ?>">
            <label for="endDate">End Date:</label>
            <input type="date" id="endDate"   value="<?php echo $end_date; ?>">
          </div>
        </div>
        <div class="filter-buttons">
          <button id="applyFilterButton">Apply/Close</button>
          <button id="cancelFilterButton">Cancel</button>
          <button id="clearFiltersButton">Clear Filters</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Front & Middle Waves -->
  <div class="waves-container">
    <img src="images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
    <img src="images/waveFront.svg"  alt="Front Wave"  class="wave front-wave">
  </div>

  <script>
    // Nav-bar user name
    document.addEventListener("DOMContentLoaded", () => {
      fetch('getUserInfo.php')
        .then(r => r.json())
        .then(d => {
          if (d.username) {
            document.getElementById('userFirstName').textContent = d.username;
          }
        })
        .catch(console.error);
    });

    // Full Report button
    document.getElementById('goToFullReportButton')
      .addEventListener('click', () => {
        let url    = "progressStrategyReportFull.php";
        let params = new URLSearchParams(window.location.search);
        if (params.toString()) url += "?" + params.toString();
        window.location.href = url;
      });

    // Filter modal logic
    const openFilterBtn      = document.getElementById('openFilterButton');
    const filterModal        = document.getElementById('filterModal');
    const applyFilterButton  = document.getElementById('applyFilterButton');
    const cancelFilterButton = document.getElementById('cancelFilterButton');
    const clearFiltersButton = document.getElementById('clearFiltersButton');
    const startDateInput     = document.getElementById('startDate');
    const endDateInput       = document.getElementById('endDate');
    const optionLastMonth    = document.getElementById('optionLastMonth');
    const optionLast3Months  = document.getElementById('optionLast3Months');
    const optionLast6Months  = document.getElementById('optionLast6Months');
    const optionLastYear     = document.getElementById('optionLastYear');

    let currentStartDate = "<?php echo $start_date; ?>";
    let currentEndDate   = "<?php echo $end_date; ?>";

    openFilterBtn.addEventListener('click', () => {
      filterModal.style.display = 'block';
    });

    function formatDate(d) {
      const y = d.getFullYear(),
            m = ('0'+(d.getMonth()+1)).slice(-2),
            da = ('0'+d.getDate()).slice(-2);
      return `${y}-${m}-${da}`;
    }

    optionLastMonth.addEventListener('click', () => {
      const t = new Date(), s = new Date(t);
      s.setMonth(s.getMonth() - 1);
      startDateInput.value = formatDate(s);
      endDateInput.value   = formatDate(t);
    });
    optionLast3Months.addEventListener('click', () => {
      const t = new Date(), s = new Date(t);
      s.setMonth(s.getMonth() - 3);
      startDateInput.value = formatDate(s);
      endDateInput.value   = formatDate(t);
    });
    optionLast6Months.addEventListener('click', () => {
      const t = new Date(), s = new Date(t);
      s.setMonth(s.getMonth() - 6);
      startDateInput.value = formatDate(s);
      endDateInput.value   = formatDate(t);
    });
    optionLastYear.addEventListener('click', () => {
      const t = new Date(), s = new Date(t);
      s.setFullYear(s.getFullYear() - 1);
      startDateInput.value = formatDate(s);
      endDateInput.value   = formatDate(t);
    });

    applyFilterButton.addEventListener('click', () => {
      const sv = startDateInput.value,
            ev = endDateInput.value;
      if (sv && ev && new Date(ev) < new Date(sv)) {
        alert("End date cannot be before start date.");
        return;
      }
      filterModal.style.display = 'none';
      const params = new URLSearchParams(window.location.search);
      if (sv) params.set('start_date', sv); else params.delete('start_date');
      if (ev) params.set('end_date', ev);   else params.delete('end_date');
      window.location.search = params.toString();
    });

    cancelFilterButton.addEventListener('click', () => {
      startDateInput.value = currentStartDate;
      endDateInput.value   = currentEndDate;
      filterModal.style.display = 'none';
    });

    clearFiltersButton.addEventListener('click', () => {
      startDateInput.value = "";
      endDateInput.value   = "";
      window.location.search = "";
    });
  </script>

</body>
</html>
