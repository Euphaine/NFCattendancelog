using Microsoft.AspNetCore.Components;
using System.Net.Http.Json;
using AttendanceSystem.Models;

namespace AttendanceSystem.Pages
{
    public partial class Settings : ComponentBase
    {
        [Inject] public HttpClient Http { get; set; } = default!;

        protected CampusSettingsModel ScheduleModel { get; set; } = new();
        protected string? StatusMessage { get; set; }
        protected bool IsSuccess { get; set; } = false;
        protected bool IsSaving { get; set; } = false;

        protected override async Task OnInitializedAsync()
        {
            await LoadSettings();
        }

        private async Task LoadSettings()
        {
            try
            {
                var response = await Http.GetFromJsonAsync<SettingsApiResponse>("http://localhost/attendance-api/settings.php");
                if (response != null && response.Success)
                {
                    ScheduleModel.OpeningTime = response.OpeningTime;
                    ScheduleModel.LateThresholdTime = response.LateThresholdTime;
                    ScheduleModel.ClosingTime = response.ClosingTime;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Error loading settings: {ex.Message}";
                IsSuccess = false;
            }
        }

        protected async Task SaveSettings()
        {
            IsSaving = true;
            StatusMessage = null;

            try
            {
                var response = await Http.PostAsJsonAsync("http://localhost/attendance-api/settings.php", ScheduleModel);
                var result = await response.Content.ReadFromJsonAsync<SettingsApiResponse>();

                if (response.IsSuccessStatusCode && result != null && result.Success)
                {
                    StatusMessage = "Campus schedule settings updated successfully!";
                    IsSuccess = true;
                }
                else
                {
                    StatusMessage = result?.Message ?? "Failed to update settings.";
                    IsSuccess = false;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = $"Error saving settings: {ex.Message}";
                IsSuccess = false;
            }
            finally
            {
                IsSaving = false;
            }
        }
    }
}