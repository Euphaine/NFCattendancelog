using Microsoft.AspNetCore.Components;
using Microsoft.AspNetCore.Components.Web;
using Microsoft.JSInterop;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public partial class IDScanner : ComponentBase, IAsyncDisposable
    {
        [Inject] public HttpClient Http { get; set; } = default!;
        [Inject] public IJSRuntime JSRuntime { get; set; } = default!;

        protected ElementReference terminalInputRef;
        protected string ScannedUid { get; set; } = string.Empty;
        protected string? ScanMessage { get; set; }
        protected ScanResponseModel? LastScannedUser { get; set; }

        private DotNetObjectReference<IDScanner>? objRef;

        protected override async Task OnAfterRenderAsync(bool firstRender)
        {
            if (firstRender)
            {
                objRef = DotNetObjectReference.Create(this);

                try { await terminalInputRef.FocusAsync(); } catch { }

                try
                {
                    await JSRuntime.InvokeAsync<bool>("nfcScanner.startScanning", objRef);
                }
                catch
                {
                    // Non-mobile browser or HTTP origin
                }
            }
        }

        [JSInvokable]
        public async Task OnNfcCardScanned(string scannedUid)
        {
            ScannedUid = scannedUid;
            await ProcessAttendanceScan();
            await InvokeAsync(StateHasChanged);
        }

        protected async Task HandleKeyUp(KeyboardEventArgs e)
        {
            if (e.Key == "Enter" && !string.IsNullOrWhiteSpace(ScannedUid))
            {
                await ProcessAttendanceScan();
            }
        }

        private async Task ProcessAttendanceScan()
        {
            if (string.IsNullOrWhiteSpace(ScannedUid)) return;

            try
            {
                var payload = new { NfcTagId = ScannedUid };
                var response = await Http.PostAsJsonAsync("http://localhost/attendance-api/scan.php", payload);
                var result = await response.Content.ReadFromJsonAsync<ScanResponseModel>();

                if (response.IsSuccessStatusCode && result != null && result.Success)
                {
                    LastScannedUser = result;
                    ScanMessage = null;
                }
                else
                {
                    LastScannedUser = null;
                    ScanMessage = result?.Message ?? "❌ Card not recognized!";
                }
            }
            catch (Exception ex)
            {
                LastScannedUser = null;
                ScanMessage = $"Error: {ex.Message}";
            }
            finally
            {
                ScannedUid = string.Empty;
                try { await terminalInputRef.FocusAsync(); } catch { }
            }
        }

        protected string GetPunctualityStyle(string punctuality)
        {
            return punctuality switch
            {
                "EARLY" => "bg-blue-100 text-blue-800",
                "LATE" => "bg-rose-100 text-rose-800",
                "OFF CAMPUS" => "bg-slate-100 text-slate-700",
                _ => "bg-emerald-100 text-emerald-800"
            };
        }

        public async ValueTask DisposeAsync()
        {
            objRef?.Dispose();
        }
    }

    public class ScanResponseModel
    {
        public bool Success { get; set; }
        public string Message { get; set; } = string.Empty;
        public string UserName { get; set; } = string.Empty;
        public string Role { get; set; } = string.Empty;
        public string SchoolId { get; set; } = string.Empty;
        public string Department { get; set; } = string.Empty;
        public string YearLevel { get; set; } = string.Empty;
        public string? Photo { get; set; }
        public string ActionStatus { get; set; } = string.Empty; // ENTRY or EXIT
        public string Punctuality { get; set; } = string.Empty;   // EARLY, ON TIME, LATE, OFF CAMPUS
        public string Timestamp { get; set; } = string.Empty;
    }
}