<?php
session_start();

// ensure the user is signed in.
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

// --------------------------
// Build dynamic WHERE clause based on filter inputs
// --------------------------
$whereClauses = array();
$whereClauses[] = "journal_entry.user_id = $currentUserId";

// Check for start date filter
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = $mysqli->real_escape_string($_GET['start_date']);
    $whereClauses[] = "journal_entry.journal_date >= '$start_date'";
} else {
    $start_date = "";
}

// Check for end date filter
if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = $mysqli->real_escape_string($_GET['end_date']);
    $whereClauses[] = "journal_entry.journal_date <= '$end_date'";
} else {
    $end_date = "";
}

// Check for emotion filters
if (isset($_GET['emotions']) && !empty($_GET['emotions'])) {
    $emotions = array_map('intval', $_GET['emotions']);
    $emotions_list = implode(',', $emotions);
    $whereClauses[] = "journal_entry.emotion_id IN ($emotions_list)";
    $selectedEmotions = $emotions;
} else {
    $selectedEmotions = array();
}

// Combine all WHERE conditions.
$whereSQL = implode(" AND ", $whereClauses);

// query to retrieve journal entries for the signed-in user with filtering.
$sql = "
    SELECT 
        journal_entry.journal_entry_id,
        journal_entry.journal_date, 
        emotion.emotion_name, 
        journal_entry.emotional_intensity_rating, 
        coping_strategy.strategy_name
    FROM journal_entry
    JOIN emotion ON journal_entry.emotion_id = emotion.emotion_id
    JOIN coping_strategy ON journal_entry.strategy_id = coping_strategy.strategy_id
    WHERE $whereSQL
    ORDER BY journal_entry.journal_date ASC
";

$result = $mysqli->query($sql);
$index = 1; // used for the Journal Number column.

// --------------------------
// Retrieve all available emotions (for the filter form)
// --------------------------
$emotionQuery = "SELECT emotion_id, emotion_name FROM emotion ORDER BY emotion_name ASC";
$emotionResult = $mysqli->query($emotionQuery);
$allEmotions = array();
if ($emotionResult) {
    while ($row = $emotionResult->fetch_assoc()) {
         $allEmotions[] = $row;
    }
}
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
                <span class="navigation-bar-link-text-selected">Journal</span>
            </a>
            <!-- Strategies Link -->
            <a href="#" class="navigation-bar-link">
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
	</div>
    
	<!-- Main screen container -->
	<div class="main-screen-container">
		<!-- Container for the table-->
		<div class="content-container">
            <div class="table-and-filter-container">
                <div class="button-container">
                    <!-- Go to other pages-->
                    <div id="pagination" class="pagination-container">
                        <button id="prevBtn" class="pagination-button">&lt;</button>
                        <span id="pageInfo" class="page-info"></span>
                        <button id="nextBtn" class="pagination-button">&gt;</button>
                    </div>
                    <button class="filter-button" id="openFilterButton">Filter</button>
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
                                // fetch journal info from database, the journal number functions as a clickable cell.
                                echo "<tr>";
                                echo "<td onclick=\"window.location.href='journalviewjournal.php?id=" . $row['journal_entry_id'] . "&index=" . $index . "'\">" . $index . "</td>";
                                echo "<td>" . htmlspecialchars(date("m/d/Y", strtotime($row['journal_date']))) . "</td>";
                                echo "<td>" . htmlspecialchars($row['emotion_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['emotional_intensity_rating']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['strategy_name']) . "</td>";
                                echo "</tr>";
                                $index++;
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

    <!-- ============================= -->
    <!--  START OF FILTER MODAL SECTION  -->
    <!-- ============================= -->

    <!-- Filter Modal Container -->
    <div class="filter-modal-container" id="filterModal">
        <div class="filter-modal-outer-box">
            <!-- Wrap the modal content in a form so that the filter values are submitted -->
            <!-- Added id="filterForm" for validation -->
            <form method="GET" action="journalAllJournalsTable.php" id="filterForm">
                <div class="filter-modal-content">
                    <h2>Filter</h2>
                
                    <!-- Date filter container -->
                    <div class="date-filter-container">
                        <h3 class="date-filter-title">Filter by Date</h3>
                        <div class="date-filter-section">
                        <label for="startDate">Start Date:</label>
                        <input type="date" id="startDate" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                
                        <label for="endDate">End Date:</label>
                        <input type="date" id="endDate" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                    </div>
                
                    <!-- Emotions filter section -->
                    <div class="emotions-filter-section">
                        <h3>Filter by Emotions</h3>
                
                        <!-- Grid container for emotions -->
                        <div class="emotions-grid">
                            <?php foreach ($allEmotions as $emotion): ?>
                            <div class="emotion-item">
                                <input type="checkbox" id="emotion_<?php echo $emotion['emotion_id']; ?>" name="emotions[]" value="<?php echo $emotion['emotion_id']; ?>" <?php echo (in_array($emotion['emotion_id'], $selectedEmotions)) ? "checked" : ""; ?>>
                                <label for="emotion_<?php echo $emotion['emotion_id']; ?>"><?php echo htmlspecialchars($emotion['emotion_name']); ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- End of .emotions-grid -->
                    </div>
                    <!-- End of .emotions-filter-section -->
                
                    <!-- Buttons -->
                    <div class="filter-buttons">
                        <button type="submit" id="applyFilterButton">Apply/Close</button>
                        <button type="button" id="cancelFilterButton">Cancel</button>
                        <button type="button" id="clearFiltersButton">Clear Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- ============================= -->
    <!--  END OF FILTER MODAL SECTION  -->
    <!-- ============================= -->
    
  

    <!-- Front and Middle Waves -->
	<div class="waves-container">
		<img src="./images/waveMiddle.svg" alt="Middle Wave" class="wave middle-wave">
		<img src="./images/waveFront.svg" alt="Front Wave" class="wave front-wave">
	</div>

    <script>
        // Grab references to elements
        const openFilterBtn = document.getElementById('openFilterButton');
        const filterModal = document.getElementById('filterModal');
        const cancelFilterButton = document.getElementById('cancelFilterButton');
        const clearFiltersButton = document.getElementById('clearFiltersButton');

        // Pagination variables
        const rowsPerPage = 10;
        let currentPage = 1;
        const table = document.querySelector(".table-container table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        let totalPages = Math.ceil(rows.length / rowsPerPage);
        
        // Pagination control elements
        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        const pageInfo = document.getElementById("pageInfo");
            
        // open the modal when "Filter" is clicked
        openFilterBtn.addEventListener('click', () => {
          filterModal.style.display = 'block';
        });
      
        // close the modal on "Cancel"
        cancelFilterButton.addEventListener('click', () => {
          filterModal.style.display = 'none';
        });

        clearFiltersButton.addEventListener('click', () => {
            // clear all date inputs within the filter modal
            document.querySelectorAll('.filter-modal-content input[type="date"]').forEach(input => {
                input.value = "";
            });
            // uncheck all checkboxes within the filter
            document.querySelectorAll('.filter-modal-content input[type="checkbox"]').forEach(input => {
                input.checked = false;
            });
        });

        // Function to show a page of rows
        function showPage(page) {
            if (rows.length === 0) {
                pageInfo.textContent = "No entries found";
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                return;
            }
            
            rows.forEach(row => row.style.display = "none");
            
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            rows.slice(start, end).forEach(row => row.style.display = "");
            
            pageInfo.textContent = "Page " + page + " of " + totalPages;
            prevBtn.disabled = (page === 1);
            nextBtn.disabled = (page === totalPages);
        }
        
        prevBtn.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        });
        
        nextBtn.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        });
        
        showPage(currentPage);

        // Prevent form submission if end date is before start date.
        const filterForm = document.getElementById('filterForm');
        filterForm.addEventListener('submit', function(event) {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            if(startDate && endDate && new Date(endDate) < new Date(startDate)) {
                alert("End date cannot be before start date.");
                event.preventDefault();
            }
        });

        // Update navigation bar with user info.
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
