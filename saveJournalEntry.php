<?php
session_start();
require_once 'db_connect.php';

// Ensure necessary session data exists
if (!isset($_SESSION['user_id'], $_SESSION['journalEntry'])) {
    header('Location: homepage.html'); // Redirect if not logged in
    exit;
}

$data = $_SESSION['journalEntry']; // Journal entry data
$userId = $_SESSION['user_id']; // Current user ID
$strategyId = isset($_SESSION['strategy_id']) ? $_SESSION['strategy_id'] : null; // Current strategy ID

try {
    // Add debug logging
    error_log("===== SAVE JOURNAL ENTRY =====");
    error_log("USER ID: $userId");
    error_log("EMOTION ID: " . $data['emotionId']);
    error_log("INTENSITY: " . $data['emotionalIntensityRating']);
    error_log("STRATEGY ID FROM SESSION: " . ($strategyId ? $strategyId : "NULL"));
    
    // If no strategy ID in session, get the current one from database
    if (!$strategyId) {
        $sqlCurrent = "SELECT strategy_id FROM Assigned_Strategy 
                      WHERE user_id = :user_id AND is_current = 1 
                      ORDER BY assigned_start_date DESC LIMIT 1";
        $stmtCurrent = $db->prepare($sqlCurrent);
        $stmtCurrent->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCurrent->execute();
        $strategyId = $stmtCurrent->fetchColumn();
        
        if ($strategyId) {
            error_log("FOUND CURRENT STRATEGY ID IN DATABASE: $strategyId");
            $_SESSION['strategy_id'] = $strategyId;
        } else {
            error_log("NO CURRENT STRATEGY FOUND IN DATABASE");
        }
    }
    
    // Check for duplicate entries
    // Check for duplicate entries
   $sqlCheck = "SELECT COUNT(*) FROM Journal_Entry 
           WHERE user_id = :user_id 
           AND emotion_id = :emotion_id 
           AND journal_content = :journal_content
           AND DATE(journal_date) = CURRENT_DATE()";
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtCheck->bindParam(':emotion_id', $data['emotionId'], PDO::PARAM_INT);
    $stmtCheck->bindParam(':journal_content', $data['journalContent'], PDO::PARAM_STR);
    $stmtCheck->execute();
    $duplicateExists = $stmtCheck->fetchColumn() > 0;

    if ($duplicateExists) {
        error_log("DUPLICATE ENTRY DETECTED - REDIRECTING TO HOMEPAGE");
        unset($_SESSION['journalEntry']);
        echo "<script>
            if (window.localStorage) {
                localStorage.removeItem('homepageData');
                localStorage.setItem('reloadHomepage', 'true');
            }
            window.location.replace('homepage.html');
        </script>";
        exit();
    }
    $stmtCheck = $db->prepare($sqlCheck);
    $stmtCheck->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtCheck->bindParam(':emotion_id', $data['emotionId'], PDO::PARAM_INT);
    $stmtCheck->bindParam(':journal_content', $data['journalContent'], PDO::PARAM_STR);
    $stmtCheck->execute();
    $duplicateExists = $stmtCheck->fetchColumn() > 0;

    if ($duplicateExists) {
        error_log("DUPLICATE ENTRY DETECTED - REDIRECTING TO HOMEPAGE");
        unset($_SESSION['journalEntry']);
        echo "<script>
            if (window.localStorage) {
                localStorage.removeItem('homepageData');
                localStorage.setItem('reloadHomepage', 'true');
            }
            window.location.replace('homepage.html');
        </script>";
        exit();
    }

    // Get current entry count BEFORE inserting
    $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmtCount->execute();
    $currentEntryCount = $stmtCount->fetchColumn();
    
    // New count will be current count + 1
    $newEntryCount = $currentEntryCount + 1;
    
    error_log("CURRENT ENTRY COUNT: $currentEntryCount");
    error_log("NEW ENTRY COUNT WILL BE: $newEntryCount");
    
    // INSERT THE JOURNAL ENTRY
    error_log("INSERTING JOURNAL ENTRY WITH STRATEGY ID: $strategyId");
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
        error_log("JOURNAL ENTRY INSERTED SUCCESSFULLY");
        
        // Determine if this is a 5th entry (including the one we just inserted)
        $isFifthEntry = ($newEntryCount % 5 == 0 && $newEntryCount > 0);
        
        error_log("IS FIFTH ENTRY: " . ($isFifthEntry ? "YES" : "NO"));
        
        // Clear session data
        unset($_SESSION['journalEntry']);
        
        // Determine where to redirect
        $redirectPage = 'journalHome.php'; // Default
        
        // Handle 5th entry redirect for feedback
        if ($isFifthEntry) {
            error_log("FIFTH ENTRY - PREPARING FOR FEEDBACK");
            
            // Get assignment ID for feedback
            $sqlGetAssignment = "SELECT assignment_id FROM Assigned_Strategy 
                                WHERE user_id = :user_id AND is_current = 1 
                                ORDER BY assigned_start_date DESC LIMIT 1";
            $stmtGetAssignment = $db->prepare($sqlGetAssignment);
            $stmtGetAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtGetAssignment->execute();
            $assignmentId = $stmtGetAssignment->fetchColumn();
            
            if ($assignmentId) {
                error_log("SETTING UP FEEDBACK FOR ASSIGNMENT ID: $assignmentId");
                $_SESSION['current_assignment_id'] = $assignmentId;
                $_SESSION['needs_new_strategy'] = true;
                $redirectPage = 'journalStrategyFeedback.html';
            } else {
                error_log("NO CURRENT ASSIGNMENT FOUND FOR FEEDBACK");
            }
        }
        
        // Redirect to appropriate page
        error_log("REDIRECTING TO: $redirectPage");
        echo "<script>
            if (window.localStorage) {
                localStorage.removeItem('homepageData');
                localStorage.setItem('reloadHomepage', 'true');
            }
            console.log('Redirecting to: $redirectPage');
            window.location.replace('$redirectPage');
        </script>";
        exit();
    } else {
        error_log("FAILED TO INSERT JOURNAL ENTRY");
        echo "<script>
            window.location.replace('homepage.html');
        </script>";
        exit();
    }
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    error_log("DATABASE ERROR: $errorMessage");
    echo "<script>
        window.location.replace('homepage.html');
    </script>";
    exit();
}
?>
