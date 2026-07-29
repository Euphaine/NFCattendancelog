namespace AttendanceSystem.Models
{
    public class ReportLogItem
    {
        public string SchoolId { get; set; } = string.Empty;
        public string FullName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string DepartmentCourse { get; set; } = string.Empty;
        public string LogDate { get; set; } = string.Empty;
        public string FormattedTimeIn { get; set; } = string.Empty;
        public string FormattedTimeOut { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
    }

    public class ReportResponse
    {
        public bool Success { get; set; }
        public int Count { get; set; }
        public List<ReportLogItem> Data { get; set; } = new();
    }
}