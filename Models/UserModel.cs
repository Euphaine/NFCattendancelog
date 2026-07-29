namespace AttendanceSystem.Models
{
    public class RegisterUserModel
    {
        public string NfcTagId { get; set; } = string.Empty;
        public string SchoolId { get; set; } = string.Empty;
        public string FirstName { get; set; } = string.Empty;
        public string LastName { get; set; } = string.Empty;
        public string? Suffix { get; set; }
        public string Role { get; set; } = "Student";
        public string Department { get; set; } = string.Empty;
        public string EducationalLevel { get; set; } = "College";
        public string Course { get; set; } = string.Empty;
        public string YearLevel { get; set; } = "1st";
    }

    public class RegisterApiResponse
    {
        public bool Success { get; set; }
        public string Message { get; set; } = string.Empty;
    }
}