namespace AttendanceSystem.Models
{
    public class ScanInputModel
    {
        public string NfcTagId { get; set; } = string.Empty;
    }

    public class DisplayModel
    {
        public string SchoolId { get; set; } = string.Empty;
        public string StudentName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
        public string Remarks { get; set; } = string.Empty;
        public string Timestamp { get; set; } = string.Empty;
        public string Message { get; set; } = string.Empty;
    }

    public class ScanRequest
    {
        public string NfcTagUid { get; set; } = string.Empty;
    }

    public class ScanUserResult
    {
        public string SchoolId { get; set; } = string.Empty;
        public string FullName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string DepartmentCourse { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
        public string Remarks { get; set; } = string.Empty;
        public string Timestamp { get; set; } = string.Empty;
    }

    public class ScanApiResponse
    {
        public bool Success { get; set; }
        public string Message { get; set; } = string.Empty;
        public ScanUserResult? User { get; set; }
    }
}