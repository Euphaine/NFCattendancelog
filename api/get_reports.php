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

    $role = $_GET['role'] ?? 'All';
    $search = $_GET['search'] ?? '';
    $startDate = $_GET['startDate'] ?? '';
    $endDate = $_GET['endDate'] ?? '';

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
            WHERE 1=1";

    $params = [];
    $types = "";

    if ($role !== 'All') {
        $sql .= " AND u.Role = ?";
        $params[] = $role;
        $types .= "s";
    }

    if (!empty($search)) {
        $sql .= " AND (u.SchoolId LIKE ? OR u.FirstName LIKE ? OR u.LastName LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    if (!empty($startDate) && !empty($endDate)) {
        $sql .= " AND l.LogDate BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
        $types .= "ss";
    }

    // Default sorting order optimized for tracking latest entries / emergency checks
    $sql .= " ORDER BY GREATEST(COALESCE(l.TimeIn, '0000-00-00 00:00:00'), COALESCE(l.TimeOut, '0000-00-00 00:00:00')) DESC, l.Id DESC LIMIT 500";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $reports = [];

    while ($row = $result->fetch_assoc()) {
        $reports[] = [
            "id" => (int)$row['Id'],
            "schoolId" => $row['SchoolId'] ?? '',
            "fullName" => trim($row['FullName']),
            "role" => $row['Role'] ?? 'Student',
            "department" => $row['Department'] ?? '',
            "course" => $row['Course'] ?? '',
            "educationalLevel" => $row['EducationalLevel'] ?? '',
            "yearLevel" => $row['YearLevel'] ?? '',
            "logDate" => $row['LogDate'] ?? '',
            "timeIn" => (!empty($row['TimeIn']) && $row['TimeIn'] !== '0000-00-00 00:00:00') ? date("h:i:s A", strtotime($row['TimeIn'])) : '—',
            "timeOut" => (!empty($row['TimeOut']) && $row['TimeOut'] !== '0000-00-00 00:00:00') ? date("h:i:s A", strtotime($row['TimeOut'])) : '—',
            "status" => $row['Status'] ?? 'ON Campus',
            "punctuality" => $row['Punctuality'] ?? 'ON TIME',
            "remarks" => $row['Remarks'] ?? ''
        ];
    }

    echo json_encode(["success" => true, "data" => $reports]);
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(["success" => false, "message" => $e->getMessage(), "data" => []]);
}
?>