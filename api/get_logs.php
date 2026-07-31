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

    // Sorting by greatest ID or latest timestamp ensures the most recent scan is always at the top
    $sql = "SELECT 
                l.Id,
                l.UserId,
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
            LEFT JOIN users u ON l.UserId = u.Id
            ORDER BY GREATEST(COALESCE(l.TimeIn, '0000-00-00 00:00:00'), COALESCE(l.TimeOut, '0000-00-00 00:00:00')) DESC, l.Id DESC 
            LIMIT 50";

    $result = $conn->query($sql);
    $logs = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $timeInFormatted = (!empty($row['TimeIn']) && $row['TimeIn'] !== '0000-00-00 00:00:00') ? date("h:i:s A", strtotime($row['TimeIn'])) : '—';
            $timeOutFormatted = (!empty($row['TimeOut']) && $row['TimeOut'] !== '0000-00-00 00:00:00') ? date("h:i:s A", strtotime($row['TimeOut'])) : '—';

            $logs[] = [
                "id" => (int)$row['Id'],
                "fullName" => trim($row['FullName']),
                "schoolId" => $row['SchoolId'] ?? '',
                "role" => $row['Role'] ?? 'Student',
                "department" => $row['Department'] ?? '',
                "course" => $row['Course'] ?? '',
                "educationalLevel" => $row['EducationalLevel'] ?? '',
                "yearLevel" => $row['YearLevel'] ?? '',
                "logDate" => $row['LogDate'] ?? '',
                "timeIn" => $timeInFormatted,
                "timeOut" => $timeOutFormatted,
                "actionStatus" => $row['ActionStatus'] ?? 'ENTRY',
                "punctuality" => $row['Punctuality'] ?? '',
                "status" => $row['Status'] ?? 'ON Campus',
                "remarks" => $row['Remarks'] ?? ''
            ];
        }
    }

    echo json_encode(["success" => true, "logs" => $logs]);
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage(), "logs" => []]);
}
?>