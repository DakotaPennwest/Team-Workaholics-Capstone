<?php
session_start();
require_once 'db_connect.php';

// This file should be included in your journal submission process
// to check if it's time to assign a new strategy

function checkJournalEntryCount($userId) {
    global $db;
    
    try {
        // Get the total count of journal entries for this user
        $sqlCount = "SELECT COUNT(*) FROM Journal_Entry WHERE user_id = :user_id";
        $stmtCount = $db->prepare($sqlCount);
        $stmtCount->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtCount->execute();
        $entryCount = $stmtCount->fetchColumn();
        
        // Store the entry count in session
        $_SESSION['entry_count'] = $entryCount;
        
        // Check if it's the first entry
        if ($entryCount == 1) {
            // For first entry, assign a strategy and redirect to strategy page
            assignNewStrategy($userId);
            return 'first_entry';
        }
        // Check if it's a multiple of 5 (5th, 10th, 15th, etc.)
        else if ($entryCount % 5 == 0) {
            // For 5th, 10th, etc. entries, flag that we need feedback first
            $_SESSION['needs_new_strategy'] = true;
            
            // Get the current strategy assignment for feedback
            $sqlGetAssignment = "SELECT a.assignment_id, s.strategy_name 
                               FROM Assigned_Strategy a
                               JOIN Coping_Strategy s ON a.strategy_id = s.strategy_id
                               WHERE a.user_id = :user_id AND a.is_current = 1
                               LIMIT 1";
            $stmtGetAssignment = $db->prepare($sqlGetAssignment);
            $stmtGetAssignment->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmtGetAssignment->execute();
            $assignmentData = $stmtGetAssignment->fetch(PDO::FETCH_ASSOC);
            
            if ($assignmentData) {
                $_SESSION['current_assignment_id'] = $assignmentData['assignment_id'];
                $_SESSION['current_strategy_name'] = $assignmentData['strategy_name'];
            }
            
            return 'need_feedback';
        }
        // Regular entry
        else {
            return 'regular_entry';
        }
    } catch (PDOException $e) {
        error_log("Error checking journal entry count: " . $e->getMessage());
        return 'error';
    }
}

function assignNewStrategy($userId) {
    global $db;
    
    try {
        // First, mark any current strategy as no longer current
        $sqlUpdateOld = "UPDATE Assigned_Strategy 
                       SET assignment_end_date = NOW(), is_current = 0 
                       WHERE user_id = :user_id AND is_current = 1";
        $stmtUpdateOld = $db->prepare($sqlUpdateOld);
        $stmtUpdateOld->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtUpdateOld->execute();
        
        // Then assign a new random strategy
        $sqlGetNew = "SELECT strategy_id FROM Coping_Strategy ORDER BY RAND() LIMIT 1";
        $stmtGetNew = $db->prepare($sqlGetNew);
        $stmtGetNew->execute();
        $newStrategyId = $stmtGetNew->fetchColumn();
        
        $sqlInsertNew = "INSERT INTO Assigned_Strategy (user_id, strategy_id, assigned_start_date, is_current) 
                       VALUES (:user_id, :strategy_id, NOW(), 1)";
        $stmtInsertNew = $db->prepare($sqlInsertNew);
        $stmtInsertNew->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtInsertNew->bindParam(':strategy_id', $newStrategyId, PDO::PARAM_INT);
        $stmtInsertNew->execute();
        
        error_log("New strategy (ID: $newStrategyId) assigned to user ID: $userId");
        return true;
    } catch (PDOException $e) {
        error_log("Error assigning new strategy: " . $e->getMessage());
        return false;
    }
}
?>
