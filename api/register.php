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

// Helper to convert empty/whitespace strings into true NULL for clean DB entries
function cleanInput($val) {
    $v = trim($val ?? '');
    return ($v === '' || $v === 'null') ? null : $v;
}

try {
    $conn = new mysqli("localhost", "root", "", "attendance_db");

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $raw_input = file_get_contents("php://input");
    $data_raw = json_decode($raw_input, true) ?? [];

    // Convert all array keys to lowercase for seamless binding
    $data = array_change_key_case($data_raw, CASE_LOWER);

    // Required Fields
    $schoolId  = cleanInput($data['schoolid'] ?? '');
    $firstName = cleanInput($data['firstname'] ?? '');
    $lastName  = cleanInput($data['lastname'] ?? '');
    $nfcTagId  = cleanInput($data['nfctagid'] ?? '');

    // Role Handling & Smart Fallback
    $role = cleanInput($data['role'] ?? '');
    if (!$role) {
        $role = !empty($data['department'] ?? '') ? 'Teacher' : 'Student';
    }

    // Optional / Conditional Fields
    $suffix           = cleanInput($data['suffix'] ?? '');
    $educationalLevel = cleanInput($data['educationallevel'] ?? '');
    $department       = cleanInput($data['department'] ?? '');
    $course           = cleanInput($data['course'] ?? '');
    $yearLevel        = cleanInput($data['yearlevel'] ?? '');

    // Force clear student fields if user is NOT a student
    if ($role !== 'Student') {
        $educationalLevel = null;
        $course = null;
        $yearLevel = null;
    }

    // Validation check
    if (!$schoolId || !$firstName || !$lastName || !$nfcTagId) {
        echo json_encode(["success" => false, "message" => "Please fill in all required fields (School ID, First Name, Last Name) and tap an NFC card."]);
        exit();
    }

    // 1. Check if School ID already exists
    $checkSchool = $conn->prepare("SELECT Id FROM users WHERE SchoolId = ?");
    $checkSchool->bind_param("s", $schoolId);
    $checkSchool->execute();
    if ($checkSchool->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "School ID '$schoolId' is already registered to another user!"]);
        exit();
    }

    // 2. Check if NFC Tag ID is already assigned
    $checkNfc = $conn->prepare("SELECT Id FROM users WHERE NfcTagId = ?");
    $checkNfc->bind_param("s", $nfcTagId);
    $checkNfc->execute();
    if ($checkNfc->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "This NFC Tag/Card is already registered to another user!"]);
        exit();
    }

    // 3. Insert new user
    $stmt = $conn->prepare("INSERT INTO users (SchoolId, NfcTagId, Role, FirstName, LastName, Suffix, EducationalLevel, Department, Course, YearLevel) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssss", $schoolId, $nfcTagId, $role, $firstName, $lastName, $suffix, $educationalLevel, $department, $course, $yearLevel);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User $firstName $lastName successfully registered!"]);
    } else {
        throw new Exception("Execution error: " . $stmt->error);
    }

    $conn->close();

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>