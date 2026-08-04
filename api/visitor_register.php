<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight CORS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config.php';

$db = null;
if (isset($pdo)) {
    $db = $pdo;
} elseif (isset($conn)) {
    $db = $conn;
} elseif (isset($database) && is_object($database) && method_exists($database, 'getConnection')) {
    $db = $database->getConnection();
}

if (!$db) {
    try {
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbname = defined('DB_NAME') ? DB_NAME : 'attendance_db';
        $user = defined('DB_USER') ? DB_USER : 'root';
        $pass = defined('DB_PASS') ? DB_PASS : '';
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Database Connection Failed: " . $e->getMessage()]);
        exit();
    }
}

try {
    $rawInput = file_get_contents("php://input");
    $data = json_decode($rawInput);

    // Flexible fallback for any property casing from Blazor
    $nfcTagId = trim($data->NfcTagId ?? $data->nfctagid ?? $data->nfcTagId ?? '');
    $name = trim($data->Name ?? $data->name ?? '');
    $address = trim($data->Address ?? $data->address ?? '');
    $purpose = trim($data->PurposeOfVisit ?? $data->purposeOfVisit ?? $data->purpose ?? '');

    if (empty($nfcTagId) || empty($name)) {
        http_response_code(400);
        echo json_encode([
            "success" => false, 
            "message" => "NFC Tag ID and Full Name are required."
        ]);
        exit();
    }

    // Check if the card is already logged in without a TimeOut
    $stmt_check = $db->prepare("SELECT LogId FROM visitor_logs WHERE NfcTagId = :nfcTagId AND TimeOut IS NULL");
    $stmt_check->execute([':nfcTagId' => $nfcTagId]);
    if ($stmt_check->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "This NFC card is currently active with another visitor."]);
        exit();
    }

    $db->beginTransaction();

    // Step 1: Find existing visitor or insert into `visitor_details`
    $stmt_find = $db->prepare("SELECT VisitorId FROM visitor_details WHERE Name = :name LIMIT 1");
    $stmt_find->execute([':name' => $name]);
    $visitor = $stmt_find->fetch();

    if ($visitor) {
        $visitorId = $visitor['VisitorId'];
    } else {
        $stmt_insert_v = $db->prepare("INSERT INTO visitor_details (Name, Address) VALUES (:name, :address)");
        $stmt_insert_v->execute([':name' => $name, ':address' => $address]);
        $visitorId = $db->lastInsertId();
    }

    // Step 2: Insert log entry into `visitor_logs`
    $stmt_log = $db->prepare("INSERT INTO visitor_logs (VisitorId, NfcTagId, PurposeOfVisit, TimeIn, DateVisited) VALUES (:visitorId, :nfcTagId, :purpose, NOW(), CURDATE())");
    $stmt_log->execute([
        ':visitorId' => $visitorId,
        ':nfcTagId' => $nfcTagId,
        ':purpose' => $purpose
    ]);

    $db->commit();

    http_response_code(200);
    echo json_encode(["success" => true, "message" => "Visitor registered successfully!"]);

} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>