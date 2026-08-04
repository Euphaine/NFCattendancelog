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
    $data = array_change_key_case($data_raw, CASE_LOWER);

    $schoolId  = cleanInput($data['schoolid'] ?? '');
    $firstName = cleanInput($data['firstname'] ?? '');
    $lastName  = cleanInput($data['lastname'] ?? '');
    $nfcTagId  = cleanInput($data['nfctagid'] ?? '');

    $role = cleanInput($data['role'] ?? '');
    if (!$role) {
        $role = !empty($data['department'] ?? '') ? 'Teacher' : 'Student';
    }

    $suffix           = cleanInput($data['suffix'] ?? '');
    $educationalLevel = cleanInput($data['educationallevel'] ?? '');
    $department       = cleanInput($data['department'] ?? '');
    $course           = cleanInput($data['course'] ?? '');
    $yearLevel        = cleanInput($data['yearlevel'] ?? '');
    $photoBase64      = $data['photo'] ?? null; // Base64 string payload

    if ($role !== 'Student') {
        $educationalLevel = null;
        $course = null;
        $yearLevel = null;
    }

    if (!$schoolId || !$firstName || !$lastName || !$nfcTagId) {
        echo json_encode(["success" => false, "message" => "Please fill in all required fields and tap an NFC card."]);
        exit();
    }

    // Check duplicate School ID
    $checkSchool = $conn->prepare("SELECT Id FROM users WHERE SchoolId = ?");
    $checkSchool->bind_param("s", $schoolId);
    $checkSchool->execute();
    if ($checkSchool->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "School ID '$schoolId' is already registered!"]);
        exit();
    }

    // Check duplicate NFC Tag
    $checkNfc = $conn->prepare("SELECT Id FROM users WHERE NfcTagId = ?");
    $checkNfc->bind_param("s", $nfcTagId);
    $checkNfc->execute();
    if ($checkNfc->get_result()->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "This NFC Tag/Card is already registered!"]);
        exit();
    }

    // Handle Photo upload if provided
    $photoFileName = null;
    if (!empty($photoBase64)) {
        // Strip data header if present (e.g. data:image/png;base64,)
        if (strpos($photoBase64, ',') !== false) {
            list($header, $photoBase64) = explode(',', $photoBase64);
        }
        $photoData = base64_decode($photoBase64);
        if ($photoData !== false) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $photoFileName = 'user_' . time() . '_' . mt_rand(1000, 9999) . '.jpg';
            file_put_contents($uploadDir . $photoFileName, $photoData);
        }
    }

    // Insert user with Photo
    $stmt = $conn->prepare("INSERT INTO users (SchoolId, NfcTagId, Role, FirstName, LastName, Suffix, EducationalLevel, Department, Course, YearLevel, Photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $schoolId, $nfcTagId, $role, $firstName, $lastName, $suffix, $educationalLevel, $department, $course, $yearLevel, $photoFileName);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User successfully registered!"]);
    } else {
        throw new Exception("Execution error: " . $stmt->error);
    }

    $conn->close();
} catch (Throwable $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>