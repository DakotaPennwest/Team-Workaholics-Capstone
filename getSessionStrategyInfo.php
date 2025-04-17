<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';
error_log("===== STARTING GET SESSION STRATEGY INFO =====");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("ERROR: User not logged in");
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
error_log("USER ID: $userId");

// Check if we have current assignment info in the session
if (isset($_SESSION['current_assignment_id'])) {
    $assignmentId = $_SESSION['current_assignment_id'];
    error_log("FOUND ASSIGNMENT ID IN SESSION: $assignmentId");
    
    try {
        // Get the strategy name associated with this assignment
        $sql = "SELECT a.assignment_id, cs.strategy_name 
               FROM Assigned_Strategy a
               JOIN Coping_Strategy cs ON a.strategy_id = cs.strategy_id
               WHERE a.assignment_id = :assignment_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            error_log("FOUND ASSIGNMENT DATA: " . json_encode($result));
            echo json_encode([
                'success' => true,
                'assignment_id' => $result['assignment_id'],
                'strategy_name' => $result['strategy_name']
            ]);
            exit;
        } else {
            error_log("ASSIGNMENT ID $assignmentId NOT FOUND IN DATABASE");
        }
    } catch (PDOException $e) {
        $errorMessage = $e->getMessage();
        error_log("DATABASE ERROR: $errorMessage");
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $errorMessage
        ]);
        exit;
    }
}

// If not in session or not found, try to get the current strategy
try {
    error_log("LOOKING FOR CURRENT STRATEGY IN DATABASE");
    $sql = "SELECT a.assignment_id, cs.strategy_name 
           FROM Assigned_Strategy a
           JOIN Coping_Strategy cs ON a.strategy_id = cs.strategy_id
           WHERE a.user_id = :user_id AND a.is_current = 1
           ORDER BY a.assigned_start_date DESC
           LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        error_log("FOUND CURRENT STRATEGY: " . json_encode($result));
        
        // Store the assignment ID in session
        $_SESSION['current_assignment_id'] = $result['assignment_id'];
        error_log("STORED ASSIGNMENT ID IN SESSION: " . $result['assignment_id']);
        
        echo json_encode([
            'success' => true,
            'assignment_id' => $result['assignment_id'],
            'strategy_name' => $result['strategy_name']
        ]);
    } else {
        error_log("NO CURRENT STRATEGY FOUND - CHECKING FOR ANY STRATEGY");
        
        // Try to find any strategy assignment for this user
        $sqlAny = "SELECT a.assignment_id, a.strategy_id, cs.strategy_name 
                 FROM Assigned_Strategy a
                 JOIN Coping_Strategy cs ON a.strategy_id = cs.strategy_id
                 WHERE a.user_id = :user_id
                 ORDER BY a.assigned_start_date DESC
                 LIMIT 1";
        $stmtAny = $db->prepare($sqlAny);
        $stmtAny->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmtAny->execute();
        $anyResult = $stmtAny->fetch(PDO::FETCH_ASSOC);
        
        if ($anyResult) {
            error_log("FOUND PAST STRATEGY: " . json_encode($anyResult));
            
            // Mark it as current
            $sqlUpdate = "UPDATE Assigned_Strategy 
                        SET is_current = 1 
                        WHERE assignment_id = :assignment_id";
            $stmtUpdate = $db->prepare($sqlUpdate);
            $stmtUpdate->bindParam(':assignment_id', $anyResult['assignment_id'], PDO::PARAM_INT);
            $result = $stmtUpdate->execute();
            
            if ($result) {
                error_log("MARKED ASSIGNMENT " . $anyResult['assignment_id'] . " AS CURRENT");
            } else {
                error_log("FAILED TO MARK ASSIGNMENT AS CURRENT: " . print_r($stmtUpdate->errorInfo(), true));
            }
            
            // Store in session
            $_SESSION['current_assignment_id'] = $anyResult['assignment_id'];
            
            echo json_encode([
                'success' => true,
                'assignment_id' => $anyResult['assignment_id'],
                'strategy_name' => $anyResult['strategy_name']
            ]);
        } else {
            error_log("NO STRATEGY FOUND AT ALL");
            echo json_encode([
                'success' => false,
                'message' => 'No current strategy assigned'
            ]);
        }
    }
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    error_log("DATABASE ERROR: $errorMessage");
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $errorMessage
    ]);
}
?>
