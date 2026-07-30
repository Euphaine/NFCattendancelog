<?php
// api/dashboard_stats.php
require_once 'config.php';

try {
    // Get total registered users
    $userStmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $userStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get total logs for today
    $today = date('Y-m-d');
    $logStmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendancelogs WHERE DATE(TimeIn) = :today");
    $logStmt->execute([':today' => $today]);
    $totalLogsToday = $logStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "success" => true,
        "totalUsers" => (int)$totalUsers,
        "totalLogsToday" => (int)$totalLogsToday
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>