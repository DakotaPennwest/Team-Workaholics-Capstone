<?php
session_start();
require_once 'db_connect.php';
require_once 'updateAssignment.php'; // To update assignment cycle if needed

// Ensure necessary session data exists
if (!isset($_SESSION['user_id'], $_SESSION['journalEntry'])) {
    header('Location: journalHome.html'); // Redirect if not logged in
    exit;
}

$data = $_SESSION['journalEntry']; // Journal entry data
$userId = $_SESSION['user_id']; // Current user ID
$strategyId = isset($_SESSION['strategy_id']) ? $_SESSION['strategy_id'] : null; // Current strategy ID

try {
    // Check for duplicate entries in journal_entry table
    $sqlCheck = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id AND emotion_id = :emotion_id AND journal_content = :journal_content";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtCheck->bindParam(':emotion_id', $data['emotionId'], PDO::PARAM_INT);
    $stmtCheck->bindParam(':journal_content', $data['journalContent'], PDO::PARAM_STR);
    $stmtCheck->execute();
    $duplicateExists = $stmtCheck->fetchColumn() > 0;

    if ($duplicateExists) {
        // Clear the session data if duplicate exists
        unset($_SESSION['journalEntry']);
        unset($_SESSION['strategy_id']);
        
        echo "<script>
            // Clear any cached homepage data
            if (window.localStorage) {
                localStorage.removeItem('homepageData');
                localStorage.setItem('reloadHomepage', 'true');
            }
            window.location.href = 'journalHome.php';
        </script>";
        exit();
    }

    // Insert the new journal entry if no duplicates exist
    $sqlInsert = "
        INSERT INTO Journal_Entry 
          (user_id, emotion_id, emotional_intensity_rating, strategy_id, journal_content, journal_date)
        VALUES (:user_id, :emotion_id, :emotional_intensity_rating, :strategy_id, :journal_content, NOW())
    ";
    $stmtInsert = $db->prepare($sqlInsert);
    $stmtInsert->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':emotion_id', $data['emotionId'], PDO::PARAM_INT);
    $stmtInsert->bindParam(':emotional_intensity_rating', $data['emotionalIntensityRating'], PDO::PARAM_INT);
    $stmtInsert->bindParam(':strategy_id', $strategyId, PDO::PARAM_INT);
    $stmtInsert->bindParam(':journal_content', $data['journalContent'], PDO::PARAM_STR);
    $stmtInsert->execute();

    if ($stmtInsert->rowCount() > 0) {
        // Get the current count of journal entries for this user
        $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id";
        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCount->execute();
        $entryCount = $stmtCount->fetchColumn(); // Total entries for this user
        
        // Store the entry count in session for use in feedback page
        $_SESSION['entry_count'] = $entryCount;
        
        // Clear session data after successful insertion
        unset($_SESSION['journalEntry']);
        
        // Determine redirect based on entry count
        $redirectPage = 'journalHome.php'; // Default redirect
        
        // Check if it's the first entry
        if ($entryCount == 1) {
            // For first entry, assign a strategy
            $sqlGetStrategy = "SELECT strategy_id FROM Coping_Strategy ORDER BY RAND() LIMIT 1";
            $stmtGetStrategy = $db->prepare($sqlGetStrategy);
            $stmtGetStrategy->execute();
            $newStrategyId = $stmtGetStrategy->fetchColumn();
            
            // Insert the assignment
            $sqlInsertAssignment = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current)
                                   VALUES (:user_id, :strategy_id, NOW(), 1)";
            $stmtInsertAssignment = $db->prepare($sqlInsertAssignment);
            $stmtInsertAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtInsertAssignment->bindParam(':strategy_id', $newStrategyId, PDO::PARAM_INT);
            $stmtInsertAssignment->execute();
            
            // Redirect to show the new strategy
            $redirectPage = 'strategiesCurrentStrategy.html';
        }
        // Check if it's a multiple of 5 (5th, 10th, 15th, etc.)
        else if ($entryCount % 5 == 0) {
            // Get current strategy assignment ID for feedback
            $sqlGetAssignment = "SELECT assignment_id FROM Assigned_Strategy 
                                WHERE user_id = :user_id AND is_current = 1";
            $stmtGetAssignment = $db->prepare($sqlGetAssignment);
            $stmtGetAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtGetAssignment->execute();
            $assignmentId = $stmtGetAssignment->fetchColumn();
            
            if ($assignmentId) {
                // Store assignment ID in session for feedback
                $_SESSION['current_assignment_id'] = $assignmentId;
                $_SESSION['needs_new_strategy'] = true;
                
                // Redirect to feedback page
                $redirectPage = 'journalStrategyFeedback.html';
            } else {
                // If no current assignment found, assign a new strategy and redirect
                $redirectPage = 'strategiesCurrentStrategy.html';
            }
        }
        
        // Use JavaScript to handle the redirect with localStorage updates
        echo "<script>
            // Clear any cached homepage data
            if (window.localStorage) {
                localStorage.removeItem('homepageData');
                localStorage.setItem('reloadHomepage', 'true');
            }
            
            // Continue with redirect
            window.location.href = '$redirectPage';
        </script>";
        exit();
    } else {
        // Failed to insert entry
        error_log("Failed to insert journal entry for user $userId");
        echo "<script>
            window.location.href = 'journalHome.php';
        </script>";
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error in saveJournalEntry.php: " . $e->getMessage());
    echo "<script>
        window.location.href = 'journalHome.php';
    </script>";
    exit();
}
?>
