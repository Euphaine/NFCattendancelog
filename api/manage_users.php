<?php
// api/manage_users.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $role = $_GET['role'] ?? 'All';
        $search = $_GET['search'] ?? '';

        $sql = "SELECT Id, SchoolId, NfcTagId, Role, FirstName, LastName, Suffix, Department, EducationalLevel, Course, YearLevel, CreatedAt FROM users WHERE 1=1";
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

        $stmt = $pdo->prepare("DELETE FROM users WHERE Id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode([
            "success" => true,
            "message" => "User deleted successfully"
        ]);
    }
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['Id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "User ID is required for updating"]);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE users SET SchoolId = :schoolId, NfcTagId = :nfcTagId, Role = :role, FirstName = :firstName, LastName = :lastName, Suffix = :suffix, Department = :department, EducationalLevel = :educationalLevel, Course = :course, YearLevel = :yearLevel WHERE Id = :id");
        
        $stmt->execute([
            ':id' => $data['Id'],
            ':schoolId' => $data['SchoolId'],
            ':nfcTagId' => $data['NfcTagId'],
            ':role' => $data['Role'],
            ':firstName' => $data['FirstName'],
            ':lastName' => $data['LastName'],
            ':suffix' => !empty($data['Suffix']) ? $data['Suffix'] : null,
            ':department' => !empty($data['Department']) ? $data['Department'] : null,
            ':educationalLevel' => !empty($data['EducationalLevel']) ? $data['EducationalLevel'] : null,
            ':course' => !empty($data['Course']) ? $data['Course'] : null,
            ':yearLevel' => !empty($data['YearLevel']) ? $data['YearLevel'] : null,
        ]);

        echo json_encode(["success" => true, "message" => "User updated successfully"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>