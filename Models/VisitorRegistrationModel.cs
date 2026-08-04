namespace AttendanceSystem.Models;

public class VisitorRegistrationModel
{
    public string NfcTagId { get; set; } = string.Empty;
    public string Name { get; set; } = string.Empty;
    public string Address { get; set; } = string.Empty;
    public string PurposeOfVisit { get; set; } = string.Empty;
}