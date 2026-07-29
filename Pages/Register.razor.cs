using Microsoft.AspNetCore.Components;
using Microsoft.JSInterop;
using System.Net.Http.Json;
using AttendanceSystem.Models;

namespace AttendanceSystem.Pages
{
    public partial class Register : ComponentBase, IAsyncDisposable
    {
        [Inject] public HttpClient Http { get; set; } = default!;
        [Inject] public IJSRuntime JSRuntime { get; set; } = default!;

        protected RegisterUserModel RegisterModel { get; set; } = new();
        protected string? StatusMessage { get; set; }
        protected bool IsSuccess { get; set; } = false;
        protected bool IsSubmitting { get; set; } = false;
        protected bool IsScanning { get; set; } = false;

        protected ElementReference nfcInputRef;
        private DotNetObjectReference<Register>? objRef;

        // Dynamic options arrays
        protected List<string> YearLevelOptions { get; set; } = new();

        protected override void OnInitialized()
        {
            objRef = DotNetObjectReference.Create(this);
            UpdateDynamicFields();
        }

        protected override async Task OnAfterRenderAsync(bool firstRender)
        {
            if (firstRender)
            {
                // Auto-focus the NFC input box for Desktop USB Readers
                try
                {
                    await nfcInputRef.FocusAsync();
                }
                catch
                {
                    // Ignore on devices without focus support
                }
            }
        }

        protected void OnRoleChanged(ChangeEventArgs e)
        {
            RegisterModel.Role = e.Value?.ToString() ?? "Student";
            UpdateDynamicFields();
        }

        protected void OnEducationalLevelChanged(ChangeEventArgs e)
        {
            RegisterModel.EducationalLevel = e.Value?.ToString() ?? "College";
            UpdateDynamicFields();
        }

        private void UpdateDynamicFields()
        {
            if (RegisterModel.Role == "Student")
            {
                switch (RegisterModel.EducationalLevel)
                {
                    case "Elementary":
                        YearLevelOptions = new List<string> { "Grade 1", "Grade 2", "Grade 3", "Grade 4", "Grade 5", "Grade 6" };
                        RegisterModel.Department = string.Empty;
                        RegisterModel.Course = string.Empty;
                        break;

                    case "Junior High School":
                        YearLevelOptions = new List<string> { "Grade 7", "Grade 8", "Grade 9", "Grade 10" };
                        RegisterModel.Department = string.Empty;
                        RegisterModel.Course = string.Empty;
                        break;

                    case "Senior High School":
                        YearLevelOptions = new List<string> { "Grade 11", "Grade 12" };
                        RegisterModel.Department = string.Empty;
                        break;

                    case "College":
                    default:
                        YearLevelOptions = new List<string> { "1st Year", "2nd Year", "3rd Year", "4th Year" };
                        break;
                }
            }

            // Reset selected year level if current value is invalid for new list
            if (!YearLevelOptions.Contains(RegisterModel.YearLevel) && YearLevelOptions.Count > 0)
            {
                RegisterModel.YearLevel = YearLevelOptions[0];
            }
        }

        protected async Task StartNfcScan()
        {
            StatusMessage = "Initializing NFC reader... Tap card against back of phone.";
            IsSuccess = true;
            IsScanning = true;

            try
            {
                var supported = await JSRuntime.InvokeAsync<bool>("nfcScanner.startScanning", objRef);
                if (!supported)
                {
                    StatusMessage = "Web NFC is not supported on this browser or origin is not flagged as secure.";
                    IsSuccess = false;
                    IsScanning = false;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"NFC Exception: {ex.Message}";
                IsSuccess = false;
                IsScanning = false;
            }

            StateHasChanged();
        }

        [JSInvokable]
        public async Task OnNfcCardScanned(string scannedUid)
        {
            RegisterModel.NfcTagId = scannedUid;
            StatusMessage = $"NFC Tag UID Captured: {scannedUid}";
            IsSuccess = true;
            IsScanning = false;
            await InvokeAsync(StateHasChanged);
        }

        protected async Task HandleRegistration()
        {
            IsSubmitting = true;
            StatusMessage = null;

            try
            {
                var response = await Http.PostAsJsonAsync("http://localhost/attendance-api/register.php", RegisterModel);
                var result = await response.Content.ReadFromJsonAsync<RegisterApiResponse>();

                if (response.IsSuccessStatusCode && result != null && result.Success)
                {
                    StatusMessage = "User registered successfully!";
                    IsSuccess = true;
                    RegisterModel = new RegisterUserModel();
                    UpdateDynamicFields();
                }
                else
                {
                    StatusMessage = result?.Message ?? "Registration failed.";
                    IsSuccess = false;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Error registering user: {ex.Message}";
                IsSuccess = false;
            }
            finally
            {
                IsSubmitting = false;
                try
                {
                    await nfcInputRef.FocusAsync();
                }
                catch
                {
                    // Ignore focus error
                }
            }
        }

        public async ValueTask DisposeAsync()
        {
            objRef?.Dispose();
        }
    }
}