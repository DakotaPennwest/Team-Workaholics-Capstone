<?php
function updateAssignmentCycle($db, $userId, $strategyId) {
    $sqlCount = "
        SELECT COUNT(*) FROM Journal_Entry
        WHERE user_id = ? AND strategy_id = ?
    ";
    $stmtCount = $db->prepare($sqlCount);
    $stmtCount->execute([$userId, $strategyId]);
    $totalEntries = $stmtCount->fetchColumn();
    
    // Check if the total number of entries is divisible by 5
    if ($totalEntries % 5 == 0 && $totalEntries > 0) {
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
