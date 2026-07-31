<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Manila');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "attendance_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $nfcTagId = $data['nfcTagId'] ?? null;

    if (!$nfcTagId) {
        echo json_encode(["success" => false, "message" => "No NFC tag ID provided"]);
        exit();
    }

    // 1. Fetch User
    $userQuery = $conn->prepare("SELECT * FROM users WHERE NfcTagId = ?");
    $userQuery->bind_param("s", $nfcTagId);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Unregistered NFC Card!"]);
        exit();
    }

    $user = $userResult->fetch_assoc();
    $userId = $user['Id'];
    $fullName = trim(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? ''));
    $userRole = $user['Role'] ?? 'Student';
    $eduLevel = $user['EducationalLevel'] ?? 'College';

    // 2. Fetch Settings
    $settingQuery = $conn->prepare("SELECT OpeningTime, LateThresholdTime, ThresholdEnabled FROM campussettings WHERE EducationalLevel = ?");
    $settingQuery->bind_param("s", $eduLevel);
    $settingQuery->execute();
    $settingResult = $settingQuery->get_result();
    
    $openingTime = "07:00:00";
    $lateThreshold = "07:45:00";
    $thresholdEnabled = 1;

    if ($settingRow = $settingResult->fetch_assoc()) {
        $openingTime = $settingRow['OpeningTime'];
        $lateThreshold = $settingRow['LateThresholdTime'];
        $thresholdEnabled = (int)$settingRow['ThresholdEnabled'];
    }

    $today = date("Y-m-d");
    $fullNow = date("Y-m-d H:i:s");
    $currentTimeStr = date("H:i:s");

    // 3. Check latest log for today
    $logQuery = $conn->prepare("SELECT * FROM attendancelogs WHERE UserId = ? AND LogDate = ? ORDER BY Id DESC LIMIT 1");
    $logQuery->bind_param("is", $userId, $today);
    $logQuery->execute();
    $logResult = $logQuery->get_result();
    $lastLog = ($logResult && $logResult->num_rows > 0) ? $logResult->fetch_assoc() : null;

$nextAction = (!$lastLog || ($lastLog['ActionStatus'] ?? 'EXIT') === 'EXIT') ? 'ENTRY' : 'EXIT';

    if ($nextAction === 'ENTRY') {
        $punctuality = "";
        if (strtolower($userRole) === 'student' && $thresholdEnabled === 1) {
            if ($currentTimeStr < $openingTime) $punctuality = "EARLY";
            elseif ($currentTimeStr > $lateThreshold) $punctuality = "LATE";
            else $punctuality = "ON TIME";
        }
        $remarks = !empty($punctuality) ? ($punctuality === 'LATE' ? "Late" : "On Time") : "Present";

        $insert = $conn->prepare("INSERT INTO attendancelogs (UserId, NfcTagId, LogDate, TimeIn, TimeOut, Status, Remarks, ActionStatus, Punctuality) VALUES (?, ?, ?, ?, NULL, 'ON Campus', ?, 'ENTRY', ?)");
        $insert->bind_param("isssss", $userId, $nfcTagId, $today, $fullNow, $remarks, $punctuality);
        $insert->execute();

        $customMessage = "Welcome to Campus! Have a great day! 🎓";
    } else {
        // EXIT / TIME OUT
        $update = $conn->prepare("UPDATE attendancelogs SET TimeOut = ?, Status = 'OFF Campus', ActionStatus = 'EXIT' WHERE Id = ?");
        $update->bind_param("si", $fullNow, $lastLog['Id']);
        $update->execute();

        $customMessage = "Goodbye, Ingat sa pag-uwi! 👋";
    }

    echo json_encode([
        "success" => true,
        "userName" => $fullName,
        "role" => $userRole,
        "schoolId" => $user['SchoolId'] ?? '',
        "department" => !empty($user['Department']) ? $user['Department'] : ($user['Course'] ?? ''),
        "yearLevel" => $user['YearLevel'] ?? '',
        "actionStatus" => $nextAction,
        "punctuality" => $lastLog['Punctuality'] ?? ($punctuality ?? ''),
        "message" => $customMessage,
        "timestamp" => date("h:i A")
    ]);

    $conn->close();
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>