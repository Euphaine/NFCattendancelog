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
    $nfcTagId = $data['nfcTagId'] ?? $data['NfcTagId'] ?? $data['nfctagid'] ?? null;

    if (!$nfcTagId) {
        echo json_encode(["success" => false, "message" => "No NFC tag ID provided"]);
        exit();
    }

    $today = date("Y-m-d");
    $fullNow = date("Y-m-d H:i:s");
    $currentTimeStr = date("H:i:s");

    // ==========================================
    // 1. CHECK USERS TABLE (Students / Staff)
    // ==========================================
    $userQuery = $conn->prepare("SELECT * FROM users WHERE NfcTagId = ?");
    $userQuery->bind_param("s", $nfcTagId);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $userId = $user['Id'];
        $fullName = trim(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? ''));
        $userRole = $user['Role'] ?? 'Student';
        $eduLevel = $user['EducationalLevel'] ?? 'College';

        // Fetch Settings
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

        // Check latest log for today
        $logQuery = $conn->prepare("SELECT * FROM attendancelogs WHERE UserId = ? AND LogDate = ? ORDER BY Id DESC LIMIT 1");
        $logQuery->bind_param("is", $userId, $today);
        $logQuery->execute();
        $logResult = $logQuery->get_result();
        $lastLog = ($logResult && $logResult->num_rows > 0) ? $logResult->fetch_assoc() : null;

        $nextAction = (!$lastLog || ($lastLog['ActionStatus'] ?? 'EXIT') === 'EXIT') ? 'ENTRY' : 'EXIT';
        $punctuality = "";

        if ($nextAction === 'ENTRY') {
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
            $update = $conn->prepare("UPDATE attendancelogs SET TimeOut = ?, Status = 'OFF Campus', ActionStatus = 'EXIT' WHERE Id = ?");
            $update->bind_param("si", $fullNow, $lastLog['Id']);
            $update->execute();

            $customMessage = "Goodbye, Ingat sa pag-uwi! 👋";
            $punctuality = $lastLog['Punctuality'] ?? '';
        }

        echo json_encode([
            "success" => true,
            "userName" => $fullName,
            "role" => $userRole,
            "schoolId" => $user['SchoolId'] ?? '',
            "department" => !empty($user['Department']) ? $user['Department'] : ($user['Course'] ?? ''),
            "yearLevel" => $user['YearLevel'] ?? '',
            "actionStatus" => $nextAction,
            "punctuality" => $punctuality,
            "message" => $customMessage,
            "timestamp" => date("h:i A")
        ]);
        exit();
    }

    // ==========================================
    // 2. CHECK VISITOR TABLES IF NOT A REGULAR USER
    // ==========================================
    $visitorStmt = $conn->prepare("SELECT vl.LogId, vl.VisitorId, vl.NfcTagId, vl.PurposeOfVisit, vl.TimeIn, vl.TimeOut, vl.DateVisited, vd.Name, vd.Address FROM visitor_logs vl JOIN visitor_details vd ON vl.VisitorId = vd.VisitorId WHERE vl.NfcTagId = ? ORDER BY vl.LogId DESC LIMIT 1");
    $visitorStmt->bind_param("s", $nfcTagId);
    $visitorStmt->execute();
    $visitorResult = $visitorStmt->get_result();

    if ($visitorResult->num_rows > 0) {
        $visitorLog = $visitorResult->fetch_assoc();
        $logId = $visitorLog['LogId'];
        $visitorName = $visitorLog['Name'];
        $purpose = $visitorLog['PurposeOfVisit'] ?? 'Official Visit';

        // Check if the latest log entry for this visitor is currently timed in (TimeOut is NULL)
        $isCurrentlyIn = empty($visitorLog['TimeOut']);

        if ($isCurrentlyIn) {
            // Action: TIMEOUT / EXIT
            $updateVisitor = $conn->prepare("UPDATE visitor_logs SET TimeOut = ? WHERE LogId = ?");
            $updateVisitor->bind_param("si", $fullNow, $logId);
            $updateVisitor->execute();

            echo json_encode([
                "success" => true,
                "userName" => $visitorName,
                "role" => "Visitor",
                "schoolId" => "VISITOR",
                "department" => $purpose,
                "yearLevel" => "",
                "actionStatus" => "EXIT",
                "punctuality" => "OFF CAMPUS",
                "message" => "Goodbye! Thank you for visiting the campus. 👋",
                "timestamp" => date("h:i A")
            ]);
        } else {
            // Action: Create a new ENTRY log row for this returning visitor card
            $insertVisitorLog = $conn->prepare("INSERT INTO visitor_logs (VisitorId, NfcTagId, PurposeOfVisit, TimeIn, TimeOut, DateVisited) VALUES (?, ?, ?, ?, NULL, ?)");
            $insertVisitorLog->bind_param("issss", $visitorLog['VisitorId'], $nfcTagId, $purpose, $fullNow, $today);
            $insertVisitorLog->execute();

            echo json_encode([
                "success" => true,
                "userName" => $visitorName,
                "role" => "Visitor",
                "schoolId" => "VISITOR",
                "department" => $purpose,
                "yearLevel" => "",
                "actionStatus" => "ENTRY",
                "punctuality" => "ON TIME",
                "message" => "Welcome back to Campus! 🛡️",
                "timestamp" => date("h:i A")
            ]);
        }
        exit();
    }

    // If neither users nor visitor logs match the card UID
    echo json_encode(["success" => false, "message" => "Unregistered NFC Card!"]);

    $conn->close();
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>