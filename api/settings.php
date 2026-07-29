<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "attendance_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// GET REQUEST: Load Current Settings
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT OpeningTime, LateThresholdTime, ClosingTime FROM campussettings WHERE Id = 1");
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "openingTime" => $row['OpeningTime'],
            "lateThresholdTime" => $row['LateThresholdTime'],
            "closingTime" => $row['ClosingTime']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No settings found"]);
    }
    exit();
}

// POST REQUEST: Save Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $opening = $data['openingTime'] ?? null;
    $late = $data['lateThresholdTime'] ?? null;
    $closing = $data['closingTime'] ?? null;

    if (!$opening || !$late || !$closing) {
        echo json_encode(["success" => false, "message" => "Invalid input data"]);
        exit();
    }

    $stmt = $conn->prepare("UPDATE campussettings SET OpeningTime = ?, LateThresholdTime = ?, ClosingTime = ?, UpdatedAt = NOW() WHERE Id = 1");
    $stmt->bind_param("sss", $opening, $late, $closing);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Settings updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update settings"]);
    }

    $stmt->close();
    exit();
}
?>