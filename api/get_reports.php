<?php
// api/get_reports.php
require_once 'config.php';

$startDate = $_GET['startDate'] ?? date('Y-m-d');
$endDate = $_GET['endDate'] ?? date('Y-m-d');
$roleFilter = $_GET['role'] ?? 'All';
$exportMode = $_GET['export'] ?? 'json'; // 'json' or 'excel'

try {
    $sql = "
        SELECT 
            u.SchoolId,
            CONCAT(u.FirstName, ' ', u.LastName, IF(u.Suffix IS NOT NULL AND u.Suffix != '', CONCAT(' ', u.Suffix), '')) AS FullName,
            u.Role,
            IFNULL(u.Course, IFNULL(u.Department, 'N/A')) AS DepartmentCourse,
            a.LogDate,
            DATE_FORMAT(a.TimeIn, '%h:%i:%s %p') AS FormattedTimeIn,
            IF(a.TimeOut IS NOT NULL, DATE_FORMAT(a.TimeOut, '%h:%i:%s %p'), '—') AS FormattedTimeOut,
            a.Status
        FROM AttendanceLogs a
        JOIN Users u ON a.UserId = u.Id
        WHERE a.LogDate BETWEEN :startDate AND :endDate
    ";

    if ($roleFilter !== 'All') {
        $sql .= " AND u.Role = :role";
    }

    $sql .= " ORDER BY a.LogDate DESC, a.Id DESC";

    $stmt = $pdo->prepare($sql);
    $params = [':startDate' => $startDate, ':endDate' => $endDate];
    if ($roleFilter !== 'All') $params[':role'] = $roleFilter;

    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // IF EXCEL EXPORT REQUESTED: Output styled Excel File directly
    if ($exportMode === 'excel') {
        $filename = "Attendance_Report_" . str_replace('-', '', $startDate) . "_to_" . str_replace('-', '', $endDate) . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<?xml version="1.0"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        ?>
        <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                  xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
         <Styles>
          <Style ss:ID="HeaderStyle">
           <Font ss:Bold="1" ss:Color="#FFFFFF"/>
           <Interior ss:Color="#1E293B" ss:Pattern="Solid"/>
           <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
          </Style>
          <Style ss:ID="DateStyle">
           <Alignment ss:Horizontal="Center"/>
          </Style>
          <Style ss:ID="StatusOn">
           <Font ss:Color="#065F46" ss:Bold="1"/>
           <Interior ss:Color="#D1FAE5" ss:Pattern="Solid"/>
           <Alignment ss:Horizontal="Center"/>
          </Style>
          <Style ss:ID="StatusOff">
           <Font ss:Color="#9A3412" ss:Bold="1"/>
           <Interior ss:Color="#FFEDD5" ss:Pattern="Solid"/>
           <Alignment ss:Horizontal="Center"/>
          </Style>
         </Styles>
         <Worksheet ss:Name="Attendance Log">
          <Table>
           <!-- AUTO-EXPANDED COLUMN WIDTHS -->
           <Column ss:Width="100"/> <!-- Date -->
           <Column ss:Width="110"/> <!-- School ID -->
           <Column ss:Width="180"/> <!-- Full Name -->
           <Column ss:Width="90"/>  <!-- Role -->
           <Column ss:Width="140"/> <!-- Dept / Course -->
           <Column ss:Width="120"/> <!-- Time IN -->
           <Column ss:Width="120"/> <!-- Time OUT -->
           <Column ss:Width="110"/> <!-- Status -->
           
           <Row ss:Height="25">
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Date</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">School ID</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Full Name</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Role</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Department / Course</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Time IN</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Time OUT</Data></Cell>
            <Cell ss:StyleID="HeaderStyle"><Data ss:Type="String">Status</Data></Cell>
           </Row>
           <?php foreach ($logs as $log): ?>
           <Row ss:Height="20">
            <Cell ss:StyleID="DateStyle"><Data ss:Type="String"><?= htmlspecialchars($log['LogDate']) ?></Data></Cell>
            <Cell><Data ss:Type="String"><?= htmlspecialchars($log['SchoolId']) ?></Data></Cell>
            <Cell><Data ss:Type="String"><?= htmlspecialchars($log['FullName']) ?></Data></Cell>
            <Cell><Data ss:Type="String"><?= htmlspecialchars($log['Role']) ?></Data></Cell>
            <Cell><Data ss:Type="String"><?= htmlspecialchars($log['DepartmentCourse']) ?></Data></Cell>
            <Cell ss:StyleID="DateStyle"><Data ss:Type="String"><?= htmlspecialchars($log['FormattedTimeIn']) ?></Data></Cell>
            <Cell ss:StyleID="DateStyle"><Data ss:Type="String"><?= htmlspecialchars($log['FormattedTimeOut']) ?></Data></Cell>
            <Cell ss:StyleID="<?= $log['Status'] === 'ON Campus' ? 'StatusOn' : 'StatusOff' ?>">
                <Data ss:Type="String"><?= htmlspecialchars($log['Status']) ?></Data>
            </Cell>
           </Row>
           <?php endforeach; ?>
          </Table>
         </Worksheet>
        </Workbook>
        <?php
        exit();
    }

    // Default JSON Response for Blazor UI rendering
    echo json_encode([
        "success" => true,
        "count" => count($logs),
        "data" => $logs
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>