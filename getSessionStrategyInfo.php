<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

require_once 'db_connect.php';
$userId = $_SESSION['user_id'];

// Check if we have current assignment info in the session
if (isset($_SESSION['current_assignment_id'])) {
    $assignmentId = $_SESSION['current_assignment_id'];
    
    try {
        // Get the strategy name associated with this assignment
        $sql = "SELECT a.assignment_id, s.strategy_name 
               FROM Assigned_Strategy a
               JOIN Coping_Strategy s ON a.strategy_id = s.strategy_id
               WHERE a.assignment_id = :assignment_id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':assignment_id', $assignmentId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'assignment_id' => $result['assignment_id'],
                'strategy_name' => $result['strategy_name']
            ]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// If not in session or not found, try to get the current strategy
try {
    $sql = "SELECT a.assignment_id, s.strategy_name 
           FROM Assigned_Strategy a
           JOIN Coping_Strategy s ON a.strategy_id = s.strategy_id
           WHERE a.user_id = :user_id AND a.is_current = 1
           LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'assignment_id' => $result['assignment_id'],
            'strategy_name' => $result['strategy_name']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No current strategy assigned'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
