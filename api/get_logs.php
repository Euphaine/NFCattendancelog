<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Manila');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $conn = new mysqli("localhost", "root", "", "attendance_db");
    if ($conn->connect_error) {
        throw new Exception("Database connection error");
    }

    $logs = [];

    // 1. Fetch regular student/staff attendance logs
    $sqlUsers = "SELECT 
                    l.Id,
                    l.LogDate,
                    l.TimeIn,
                    l.TimeOut,
                    l.ActionStatus,
                    l.Punctuality,
                    l.Status,
                    l.Remarks,
                    CONCAT(COALESCE(u.FirstName,''), ' ', COALESCE(u.LastName,'')) AS FullName,
                    u.Role,
                    u.SchoolId,
                    u.Department,
                    u.Course,
                    u.EducationalLevel,
                    COALESCE(u.YearLevel, '') AS YearLevel
                FROM attendancelogs l
                LEFT JOIN users u ON l.UserId = u.Id";

    $resultUsers = $conn->query($sqlUsers);
    if ($resultUsers) {
        while ($row = $resultUsers->fetch_assoc()) {
            $timeIn = (!empty($row['TimeIn']) && $row['TimeIn'] !== '0000-00-00 00:00:00') ? $row['TimeIn'] : null;
            $timeOut = (!empty($row['TimeOut']) && $row['TimeOut'] !== '0000-00-00 00:00:00') ? $row['TimeOut'] : null;

            $logs[] = [
                "sortTime" => max($timeIn ?? '0000-00-00 00:00:00', $timeOut ?? '0000-00-00 00:00:00'),
                "id" => (int)$row['Id'],
                "fullName" => trim($row['FullName']),
                "schoolId" => $row['SchoolId'] ?? '',
                "role" => $row['Role'] ?? 'Student',
                "department" => $row['Department'] ?? '',
                "course" => $row['Course'] ?? '',
                "educationalLevel" => $row['EducationalLevel'] ?? '',
                "yearLevel" => $row['YearLevel'] ?? '',
                "logDate" => $row['LogDate'] ?? '',
                "timeIn" => $timeIn ? date("h:i:s A", strtotime($timeIn)) : '—',
                "timeOut" => $timeOut ? date("h:i:s A", strtotime($timeOut)) : '—',
                "actionStatus" => $row['ActionStatus'] ?? 'ENTRY',
                "punctuality" => $row['Punctuality'] ?? '',
                "status" => $row['Status'] ?? 'ON Campus',
                "remarks" => $row['Remarks'] ?? ''
            ];
        }
    }

    // 2. Fetch visitor logs and map them to match the exact same structure
    $sqlVisitors = "SELECT 
                        vl.LogId,
                        vl.DateVisited,
                        vl.TimeIn,
                        vl.TimeOut,
                        vl.PurposeOfVisit,
                        vd.Name,
                        vd.Address
                    FROM visitor_logs vl
                    JOIN visitor_details vd ON vl.VisitorId = vd.VisitorId";

    $resultVisitors = $conn->query($sqlVisitors);
    if ($resultVisitors) {
        while ($row = $resultVisitors->fetch_assoc()) {
            $timeIn = (!empty($row['TimeIn']) && $row['TimeIn'] !== '0000-00-00 00:00:00') ? $row['TimeIn'] : null;
            $timeOut = (!empty($row['TimeOut']) && $row['TimeOut'] !== '0000-00-00 00:00:00') ? $row['TimeOut'] : null;
            $isStillIn = empty($timeOut);

            $logs[] = [
                "sortTime" => max($timeIn ?? '0000-00-00 00:00:00', $timeOut ?? '0000-00-00 00:00:00'),
                "id" => 900000 + (int)$row['LogId'], // offset ID to prevent collision
                "fullName" => trim($row['Name']),
                "schoolId" => "VISITOR",
                "role" => "Visitor",
                "department" => $row['PurposeOfVisit'] ?? 'Official Visit',
                "course" => $row['Address'] ?? '',
                "educationalLevel" => "Visitor Pass",
                "yearLevel" => "",
                "logDate" => $row['DateVisited'] ?? '',
                "timeIn" => $timeIn ? date("h:i:s A", strtotime($timeIn)) : '—',
                "timeOut" => $timeOut ? date("h:i:s A", strtotime($timeOut)) : '—',
                "actionStatus" => $isStillIn ? "ENTRY" : "EXIT",
                "punctuality" => "OFFICIAL",
                "status" => $isStillIn ? "ON Campus" : "OFF Campus",
                "remarks" => "Visitor"
            ];
        }
    }

    // 3. Sort combined logs descending by timestamp
    usort($logs, function($a, $b) {
        return strcmp($b['sortTime'], $a['sortTime']);
    });

    // Limit to top 50 recent items
    $logs = array_slice($logs, 0, 50);

    // Clean up temporary sorting helper key before json response
    foreach ($logs as &$log) {
        unset($log['sortTime']);
    }

    echo json_encode(["success" => true, "logs" => $logs]);
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "logs" => []]);
}
?>