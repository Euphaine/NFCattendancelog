using Microsoft.AspNetCore.Components;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public partial class LiveLogs : ComponentBase, IDisposable
    {
        [Inject] public HttpClient Http { get; set; } = default!;

        protected List<LogEntryModel> Logs { get; set; } = new();
        protected bool IsLoading { get; set; } = true;
        protected string SearchQuery { get; set; } = string.Empty;
        protected bool IsVisitorModalOpen { get; set; } = false;

        private CancellationTokenSource _cts = new();

        // Computed Filtered Logs for Search Bar
        protected IEnumerable<LogEntryModel> FilteredLogs =>
            string.IsNullOrWhiteSpace(SearchQuery)
                ? Logs
                : Logs.Where(l =>
                    (l.FullName?.Contains(SearchQuery, StringComparison.OrdinalIgnoreCase) ?? false) ||
                    (l.SchoolId?.Contains(SearchQuery, StringComparison.OrdinalIgnoreCase) ?? false) ||
                    (l.Department?.Contains(SearchQuery, StringComparison.OrdinalIgnoreCase) ?? false));

        // Real-time Stats for Top Summary Cards
        protected LogStats Stats => new()
        {
            TotalScans = Logs.Count,
            TotalInCampus = Logs.Count(l => l.ActionStatus == "ENTRY" || l.Status == "ON Campus"),
            TotalOffCampus = Logs.Count(l => l.ActionStatus == "EXIT" || l.Status == "OFF Campus")
        };

        protected override async Task OnInitializedAsync()
        {
            await FetchLogsAsync();
            IsLoading = false;

            // Auto-refresh every 2 seconds
            _ = StartLivePollingAsync(_cts.Token);
        }

        protected void OpenVisitorModal()
        {
            IsVisitorModalOpen = true;
        }

        protected void CloseVisitorModal()
        {
            IsVisitorModalOpen = false;
        }

        private async Task StartLivePollingAsync(CancellationToken token)
        {
            using var timer = new PeriodicTimer(TimeSpan.FromSeconds(2));
            while (!token.IsCancellationRequested && await timer.WaitForNextTickAsync(token))
            {
                await FetchLogsAsync();
                await InvokeAsync(StateHasChanged);
            }
        }

        private async Task FetchLogsAsync()
        {
            try
            {
                var response = await Http.GetFromJsonAsync<LogsApiResponse>("http://localhost/attendance-api/get_logs.php");
                if (response != null && response.Success)
                {
                    Logs = response.Logs ?? new();
                }
            }
            catch
            {
                // Silently handle polling drops
            }
        }

        public void Dispose()
        {
            _cts.Cancel();
            _cts.Dispose();
        }
    }

    public class LogStats
    {
        public int TotalScans { get; set; }
        public int TotalInCampus { get; set; }
        public int TotalOffCampus { get; set; }

        public int OnCampus => TotalInCampus;
        public int OffCampus => TotalOffCampus;
        public int TotalToday => TotalScans;
    }

    public class LogsApiResponse
    {
        public bool Success { get; set; }
        public List<LogEntryModel> Logs { get; set; } = new();
    }

    public class LogEntryModel
    {
        public int Id { get; set; }
        public string FullName { get; set; } = string.Empty;
        public string SchoolId { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string Department { get; set; } = string.Empty;
        public string Course { get; set; } = string.Empty;
        public string EducationalLevel { get; set; } = string.Empty;
        public string YearLevel { get; set; } = string.Empty;
        public string LogDate { get; set; } = string.Empty;
        public string TimeIn { get; set; } = string.Empty;
        public string TimeOut { get; set; } = string.Empty;
        public string ActionStatus { get; set; } = string.Empty;
        public string Punctuality { get; set; } = string.Empty;
        public string Status { get; set; } = string.Empty;
        public string Remarks { get; set; } = string.Empty;
    }
}