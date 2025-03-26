<?php
function updateAssignmentCycle($db, $userId, $strategyId) {
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

    // If exactly 5 entries, mark the current assignment as completed.
    if ($strategyUsageCount == 5) {
        $sqlUpdate = "UPDATE Assigned_Strategy 
                      SET assignment_end_date = NOW(), is_current = 0 
                      WHERE user_id = ? AND strategy_id = ?";
        $stmtUpdate = $db->prepare($sqlUpdate);
        $stmtUpdate->execute([$userId, $strategyId]);
        return true;
    }
    return false;
}
?>
