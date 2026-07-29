namespace AttendanceSystem.Models
{
    public class AttendanceLogItem
    {
        public int Id { get; set; }
        public int UserId { get; set; }
        public string SchoolId { get; set; } = string.Empty;
        public string FullName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string? Department { get; set; }
        public string? Course { get; set; }
        public string Status { get; set; } = string.Empty; // "ON Campus" or "OFF Campus"
        public string FormattedTimeIn { get; set; } = string.Empty;
        public string FormattedTimeOut { get; set; } = string.Empty;
    }

    public class LogStats
    {
        public int OnCampus { get; set; }
        public int OffCampus { get; set; }
        public int TotalToday { get; set; }
    }

    public class GetLogsResponse
    {
        public bool Success { get; set; }
        public LogStats Stats { get; set; } = new();
        public List<AttendanceLogItem> Logs { get; set; } = new();
    }
}