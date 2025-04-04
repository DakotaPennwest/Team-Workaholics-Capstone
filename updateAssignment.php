<?php
function updateAssignmentCycle($db, $userId, $strategyId) {
    // Count the number of journal entries for this user and strategy.
    // The subquery limits the count to the latest 5 entries.
    $sqlCount = "
        SELECT COUNT(*) FROM (
            SELECT journal_entry_id
            FROM Journal_Entry
            WHERE user_id = ? AND strategy_id = ?
            ORDER BY journal_date DESC
            LIMIT 5
        ) AS last_entries
    ";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([$userId, $strategyId]);
    $strategyUsageCount = $stmtCount->fetchColumn();
    error_log("updateAssignmentCycle: strategyUsageCount = " . $strategyUsageCount);

    // If exactly 5 entries have been recorded (i.e. this cycle is complete)
    if ($strategyUsageCount == 5) {
        // Update the current assignment record: mark it as ended (set assignment_end_date) and no longer current.
        $sqlUpdate = "UPDATE Assigned_Strategy 
                      SET assignment_end_date = NOW(), is_current = 0 
                      WHERE user_id = ? AND strategy_id = ? AND is_current = 1";
        $stmtUpdate = $db->prepare($sqlUpdate);
        $stmtUpdate->execute([$userId, $strategyId]);
        return true;
    }
    return false;
}
?>
