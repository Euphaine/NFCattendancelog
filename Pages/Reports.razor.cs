using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public partial class Reports : ComponentBase
    {
        [Inject] private HttpClient Http { get; set; } = default!;
        [Inject] private IJSRuntime JS { get; set; } = default!;

        private List<ReportModel> ReportList = new();
        private bool IsLoading = true;
        private string SearchQuery = "";
        private string SelectedRole = "All";
        private DateTime? StartDate = DateTime.Today;
        private DateTime? EndDate = DateTime.Today;

        protected override async Task OnInitializedAsync()
        {
            await LoadReports();
        }

        private async Task LoadReports()
        {
            IsLoading = true;
            try
            {
                string url = $"http://localhost/attendance-api/get_reports.php?role={SelectedRole}&search={Uri.EscapeDataString(SearchQuery)}";
                if (StartDate.HasValue) url += $"&startDate={StartDate.Value:yyyy-MM-dd}";
                if (EndDate.HasValue) url += $"&endDate={EndDate.Value:yyyy-MM-dd}";

                var response = await Http.GetFromJsonAsync<ReportApiResponse>(url);
                if (response != null && response.Success)
                {
                    ReportList = response.Data ?? new();
                }
            }
            catch
            {
                ReportList = new();
            }
            IsLoading = false;
        }

        private async Task PrintReport()
        {
            await JS.InvokeVoidAsync("window.print");
        }

        private async Task ExportToExcel()
        {
            var sb = new System.Text.StringBuilder();
            sb.Append("<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>");
            sb.Append("<head><meta http-equiv='content-type' content='text/html; charset=UTF-8'>");
            sb.Append("<style>");
            sb.Append("body { font-family: Arial, sans-serif; }");
            sb.Append("table { border-collapse: collapse; width: 100%; margin-top: 10px; }");
            sb.Append("th { background-color: #1e3a8a; color: #ffffff; font-weight: bold; padding: 10px; border: 1px solid #d1d5db; text-align: left; font-size: 11pt; }");
            sb.Append("td { padding: 8px 10px; border: 1px solid #d1d5db; vertical-align: middle; font-size: 10pt; color: #374151; }");
            sb.Append(".status-on { background-color: #d1fae5; color: #065f46; font-weight: bold; text-align: center; }");
            sb.Append(".status-off { background-color: #ffedd5; color: #9a3412; font-weight: bold; text-align: center; }");
            sb.Append(".text-center { text-align: center; }");
            sb.Append("</style></head><body>");
            sb.Append("<div style='font-size: 16px; font-weight: bold; margin-bottom: 5px; color: #1f2937;'>Official Attendance & Activity Report</div>");
            sb.Append("<div style='font-size: 9pt; color: #6b7280; margin-bottom: 15px;'>Generated on: " + DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss") + "</div>");
            sb.Append("<table><thead><tr>");
            sb.Append("<th>School ID</th><th>Full Name</th><th>Role</th><th>Educational Level</th><th>Department</th><th>Course</th><th>Year Level</th><th>Status</th><th>Date</th><th>Time In</th><th>Time Out</th><th>Punctuality</th>");
            sb.Append("</tr></thead><tbody>");

            foreach (var r in ReportList)
            {
                bool isOnCampus = r.Status == "ON Campus" || r.TimeOut == "—";
                string statusClass = isOnCampus ? "status-on" : "status-off";
                string statusText = isOnCampus ? "ON CAMPUS" : "OFF CAMPUS";

                sb.Append("<tr>");
                sb.Append($"<td style='mso-number-format:\"\\@\";'>{r.SchoolId}</td>");
                sb.Append($"<td>{r.FullName}</td>");
                sb.Append($"<td>{r.Role}</td>");
                sb.Append($"<td>{r.EducationalLevel}</td>");
                sb.Append($"<td>{r.Department}</td>");
                sb.Append($"<td>{r.Course}</td>");
                sb.Append($"<td>{r.YearLevel}</td>");
                sb.Append($"<td class='{statusClass}'>{statusText}</td>");
                sb.Append($"<td class='text-center'>{r.LogDate}</td>");
                sb.Append($"<td class='text-center'>{r.TimeIn}</td>");
                sb.Append($"<td class='text-center'>{r.TimeOut}</td>");
                sb.Append($"<td class='text-center'>{r.Punctuality}</td>");
                sb.Append("</tr>");
            }

            sb.Append("</tbody></table></body></html>");

            string excelContent = sb.ToString();
            var encodedUri = "data:application/vnd.ms-excel;charset=utf-8," + Uri.EscapeDataString(excelContent);
            
            await JS.InvokeVoidAsync("eval", $@"
                let a = document.createElement('a');
                a.href = '{encodedUri}';
                a.download = 'Official_Attendance_Report_' + new Date().toISOString().slice(0,10) + '.xls';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            ");
        }

        public class ReportApiResponse
        {
            public bool Success { get; set; }
            public List<ReportModel> Data { get; set; } = new();
        }

        public class ReportModel
        {
            public int Id { get; set; }
            public string SchoolId { get; set; } = string.Empty;
            public string FullName { get; set; } = string.Empty;
            public string Role { get; set; } = string.Empty;
            public string Department { get; set; } = string.Empty;
            public string Course { get; set; } = string.Empty;
            public string EducationalLevel { get; set; } = string.Empty;
            public string YearLevel { get; set; } = string.Empty;
            public string LogDate { get; set; } = string.Empty;
            public string TimeIn { get; set; } = string.Empty;
            public string TimeOut { get; set; } = string.Empty;
            public string Status { get; set; } = string.Empty;
            public string Punctuality { get; set; } = string.Empty;
            public string Remarks { get; set; } = string.Empty;
        }
    }
}