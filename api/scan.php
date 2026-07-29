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
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $nfcTagId = $data['nfcTagId'] ?? null;

    if (!$nfcTagId) {
        echo json_encode(["success" => false, "message" => "No NFC tag ID provided"]);
        exit();
    }

    // 1. Fetch User Details
    $userQuery = $conn->prepare("SELECT * FROM users WHERE NfcTagId = ?");
    if (!$userQuery) {
        throw new Exception("User query error: " . $conn->error);
    }
    $userQuery->bind_param("s", $nfcTagId);
    $userQuery->execute();
    $userResult = $userQuery->get_result();

    if ($userResult->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Unregistered NFC Card!"]);
        exit();
    }

    $user = $userResult->fetch_assoc();
    $userId = $user['Id'];
    $fullName = trim(($user['FirstName'] ?? '') . ' ' . ($user['LastName'] ?? '') . ' ' . ($user['Suffix'] ?? ''));

    // 2. Check Today's Latest Log
    $today = date("Y-m-d");
    $fullNow = date("Y-m-d H:i:s");
    $currentTimeStr = date("H:i:s");

    $logQuery = $conn->prepare("SELECT * FROM attendancelogs WHERE UserId = ? AND LogDate = ? ORDER BY Id DESC LIMIT 1");
    if (!$logQuery) {
        throw new Exception("Log query error: " . $conn->error);
    }
    $logQuery->bind_param("is", $userId, $today);
    $logQuery->execute();
    $logResult = $logQuery->get_result();

    $lastLog = ($logResult && $logResult->num_rows > 0) ? $logResult->fetch_assoc() : null;
    $nextAction = ($lastLog && ($lastLog['ActionStatus'] ?? 'EXIT') === 'ENTRY') ? 'EXIT' : 'ENTRY';

    // 3. Determine Early / Late Punctuality
    $punctuality = "ON TIME";
    if ($nextAction === 'ENTRY') {
        if ($currentTimeStr < "07:45:00") {
            $punctuality = "EARLY";
        } elseif ($currentTimeStr > "08:15:00") {
            $punctuality = "LATE";
        }
    } else {
        $punctuality = "OFF CAMPUS";
    }

    $overallStatus = ($nextAction === 'ENTRY') ? "ON Campus" : "OFF Campus";
    $remarks = ($punctuality === 'LATE') ? "Late" : "On Time";

    if ($nextAction === 'ENTRY') {
        // Create new log row for ENTRY
        $insertLog = $conn->prepare("INSERT INTO attendancelogs (UserId, NfcTagId, LogDate, TimeIn, TimeOut, Status, Remarks, ActionStatus, Punctuality) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?)");
        if (!$insertLog) {
            throw new Exception("Insert query error: " . $conn->error);
        }
        $insertLog->bind_param("isssssss", $userId, $nfcTagId, $today, $fullNow, $overallStatus, $remarks, $nextAction, $punctuality);
        $insertLog->execute();
    } else {
        // Update existing row for EXIT
        if ($lastLog && isset($lastLog['Id'])) {
            $updateLog = $conn->prepare("UPDATE attendancelogs SET TimeOut = ?, Status = 'OFF Campus', ActionStatus = 'EXIT', Punctuality = 'OFF CAMPUS' WHERE Id = ?");
            if (!$updateLog) {
                throw new Exception("Update query error: " . $conn->error);
            }
            $updateLog->bind_param("si", $fullNow, $lastLog['Id']);
            $updateLog->execute();
        } else {
            // Fallback if no entry log was found today
            $insertLog = $conn->prepare("INSERT INTO attendancelogs (UserId, NfcTagId, LogDate, TimeIn, TimeOut, Status, Remarks, ActionStatus, Punctuality) VALUES (?, ?, ?, NULL, ?, ?, ?, 'EXIT', 'OFF CAMPUS')");
            if (!$insertLog) {
                throw new Exception("Fallback insert query error: " . $conn->error);
            }
            $insertLog->bind_param("isssss", $userId, $nfcTagId, $today, $fullNow, $overallStatus, $remarks);
            $insertLog->execute();
        }
    }

    // Friendly Messages
    $customMessage = "";
    if ($nextAction === 'EXIT') {
        $customMessage = "Goodbye, Ingat sa pag-uwi! 👋";
    } else {
        if ($punctuality === 'EARLY') {
            $customMessage = "Good morning! Ang aga natin ngayon ah! ☀️";
        } elseif ($punctuality === 'LATE') {
            $customMessage = "Welcome! Medyo late na, hatak na sa room! 🏃‍♂️";
        } else {
            $customMessage = "Welcome to Campus! Have a great day! 🎓";
        }
    }

    // Smart Department / Strand Display
    $deptOrCourse = !empty($user['Department']) ? $user['Department'] : (!empty($user['Course']) ? $user['Course'] : '');

    echo json_encode([
        "success" => true,
        "userName" => $fullName,
        "role" => $user['Role'] ?? 'Student',
        "schoolId" => $user['SchoolId'] ?? '',
        "department" => $deptOrCourse,
        "yearLevel" => $user['YearLevel'] ?? '',
        "actionStatus" => $nextAction,
        "punctuality" => $punctuality,
        "message" => $customMessage,
        "timestamp" => date("h:i A")
    ]);

    $conn->close();

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        "success" => false, 
        "message" => "PHP Error: " . $e->getMessage()
    ]);
}
?>