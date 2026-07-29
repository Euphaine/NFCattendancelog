namespace AttendanceSystem.Models
{
    public class CampusSettingsModel
    {
        public string OpeningTime { get; set; } = "08:00:00";
        public string LateThresholdTime { get; set; } = "08:30:00";
        public string ClosingTime { get; set; } = "17:00:00";
    }

    public class SettingsApiResponse
    {
        public bool Success { get; set; }
        public string Message { get; set; } = string.Empty;
        public string OpeningTime { get; set; } = string.Empty;
        public string LateThresholdTime { get; set; } = string.Empty;
        public string ClosingTime { get; set; } = string.Empty;
    }
}