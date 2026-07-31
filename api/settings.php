<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = new mysqli("localhost", "root", "", "attendance_db");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// GET REQUEST: Load all settings or a specific level
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT EducationalLevel, OpeningTime, LateThresholdTime, ClosingTime, ThresholdEnabled FROM campussettings");
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[] = [
            "educationalLevel" => $row['EducationalLevel'],
            "openingTime" => $row['OpeningTime'],
            "lateThresholdTime" => $row['LateThresholdTime'],
            "closingTime" => $row['ClosingTime'],
            "thresholdEnabled" => (bool)$row['ThresholdEnabled']
        ];
    }
    echo json_encode(["success" => true, "data" => $settings]);
    exit();
}

// POST REQUEST: Save settings per level
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $level = $data['educationalLevel'] ?? 'College';
    $opening = $data['openingTime'] ?? null;
    $late = $data['lateThresholdTime'] ?? null;
    $closing = $data['closingTime'] ?? null;
    $enabled = isset($data['thresholdEnabled']) ? (int)$data['thresholdEnabled'] : 1;

    $stmt = $conn->prepare("UPDATE campussettings SET OpeningTime = ?, LateThresholdTime = ?, ClosingTime = ?, ThresholdEnabled = ?, UpdatedAt = NOW() WHERE EducationalLevel = ?");
    $stmt->bind_param("sssis", $opening, $late, $closing, $enabled, $level);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Settings for $level updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update settings"]);
    }
    $stmt->close();
    exit();
}
?>