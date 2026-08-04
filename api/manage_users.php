<?php
// api/manage_users.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $role = $_GET['role'] ?? 'All';
        $search = $_GET['search'] ?? '';

        $sql = "SELECT Id, SchoolId, NfcTagId, Role, FirstName, LastName, Suffix, Department, EducationalLevel, Course, YearLevel, Photo, CreatedAt FROM users WHERE 1=1";
        $params = [];

        if ($role !== 'All') {
            $sql .= " AND Role = :role";
            $params[':role'] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (SchoolId LIKE :search OR FirstName LIKE :search OR LastName LIKE :search OR NfcTagId LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY CreatedAt DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "data" => $users
        ]);
    } 
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "User ID is required"]);
            exit();
        }

        // Optional: fetch and unlink old photo file from disk
        $stmtPhoto = $pdo->prepare("SELECT Photo FROM users WHERE Id = :id");
        $stmtPhoto->execute([':id' => $id]);
        $oldPhoto = $stmtPhoto->fetchColumn();
        if (!empty($oldPhoto) && file_exists(__DIR__ . '/uploads/' . $oldPhoto)) {
            @unlink(__DIR__ . '/uploads/' . $oldPhoto);
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE Id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(["success" => true, "message" => "User deleted successfully"]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        // Support both PascalCase and camelCase for the ID field
        $id = $data['Id'] ?? $data['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "User ID is required for updating. Received data: " . json_encode($data)]);
            exit();
        }

        // Handle photo update if a new base64 photo is provided
        $photoFileName = $data['Photo'] ?? $data['photo'] ?? null;
        $newPhoto = $data['NewPhoto'] ?? $data['newPhoto'] ?? null;
        
        if (!empty($newPhoto)) {
            $photoBase64 = $newPhoto;
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

                // Delete old file if exists
                $oldFile = $data['Photo'] ?? $data['photo'] ?? null;
                if (!empty($oldFile) && file_exists($uploadDir . $oldFile)) {
                    @unlink($uploadDir . $oldFile);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE users SET SchoolId = :schoolId, NfcTagId = :nfcTagId, Role = :role, FirstName = :firstName, LastName = :lastName, Suffix = :suffix, Department = :department, EducationalLevel = :educationalLevel, Course = :course, YearLevel = :yearLevel, Photo = :photo WHERE Id = :id");
        
        $stmt->execute([
            ':id' => $id,
            ':schoolId' => $data['SchoolId'] ?? $data['schoolId'] ?? '',
            ':nfcTagId' => $data['NfcTagId'] ?? $data['nfcTagId'] ?? '',
            ':role' => $data['Role'] ?? $data['role'] ?? '',
            ':firstName' => $data['FirstName'] ?? $data['firstName'] ?? '',
            ':lastName' => $data['LastName'] ?? $data['lastName'] ?? '',
            ':suffix' => !empty($data['Suffix'] ?? $data['suffix']) ? ($data['Suffix'] ?? $data['suffix']) : null,
            ':department' => !empty($data['Department'] ?? $data['department']) ? ($data['Department'] ?? $data['department']) : null,
            ':educationalLevel' => !empty($data['EducationalLevel'] ?? $data['educationalLevel']) ? ($data['EducationalLevel'] ?? $data['educationalLevel']) : null,
            ':course' => !empty($data['Course'] ?? $data['course']) ? ($data['Course'] ?? $data['course']) : null,
            ':yearLevel' => !empty($data['YearLevel'] ?? $data['yearLevel']) ? ($data['YearLevel'] ?? $data['yearLevel']) : null,
            ':photo' => $photoFileName
        ]);

        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>