using Microsoft.AspNetCore.Components;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public partial class Settings : ComponentBase
    {
        [Inject] public HttpClient Http { get; set; } = default!;

        protected string[] Levels = { "College", "Senior High School", "Junior High School", "Elementary" };
        protected string SelectedLevel = "College";
        protected Dictionary<string, ScheduleModel> AllSettings = new();
        protected ScheduleModel CurrentModel = new();
        protected string? StatusMessage;
        protected bool IsSuccess, IsSaving;

        protected override async Task OnInitializedAsync()
        {
            await LoadSettings();
        }

        protected async Task LoadSettings()
        {
            try
            {
                var res = await Http.GetFromJsonAsync<ApiResponse>("http://localhost/attendance-api/settings.php");
                if (res != null && res.Data != null)
                {
                    foreach (var item in res.Data)
                    {
                        AllSettings[item.EducationalLevel] = item;
                    }
                }
            }
            catch (Exception ex)
            {
                StatusMessage = "Error loading settings: " + ex.Message;
            }
            UpdateCurrentModel();
        }

        protected void SelectLevel(string level)
        {
            SelectedLevel = level;
            UpdateCurrentModel();
        }

        protected void UpdateCurrentModel()
        {
            if (AllSettings.ContainsKey(SelectedLevel))
            {
                CurrentModel = AllSettings[SelectedLevel];
            }
            else
            {
                CurrentModel = new ScheduleModel 
                { 
                    EducationalLevel = SelectedLevel, 
                    OpeningTime = new TimeOnly(7, 0), 
                    LateThresholdTime = new TimeOnly(7, 45), 
                    ClosingTime = new TimeOnly(17, 0), 
                    ThresholdEnabled = true 
                };
            }
        }

        protected async Task SaveSettings()
        {
            IsSaving = true;
            StatusMessage = null;
            try
            {
                CurrentModel.EducationalLevel = SelectedLevel;
                var response = await Http.PostAsJsonAsync("http://localhost/attendance-api/settings.php", CurrentModel);
                var result = await response.Content.ReadFromJsonAsync<BasicResponse>();

                if (result != null && result.Success)
                {
                    StatusMessage = $"{SelectedLevel} settings saved successfully!";
                    IsSuccess = true;
                }
                else
                {
                    StatusMessage = result?.Message ?? "Failed to save.";
                    IsSuccess = false;
                }
            }
            catch (Exception ex)
            {
                StatusMessage = "Error: " + ex.Message;
                IsSuccess = false;
            }
            finally
            {
                IsSaving = false;
            }
        }

        public class ScheduleModel
        {
            public string EducationalLevel { get; set; } = "College";
            public TimeOnly OpeningTime { get; set; } = new TimeOnly(7, 0);
            public TimeOnly LateThresholdTime { get; set; } = new TimeOnly(7, 45);
            public TimeOnly ClosingTime { get; set; } = new TimeOnly(17, 0);
            public bool ThresholdEnabled { get; set; } = true;
        }

        public class ApiResponse 
        { 
            public bool Success { get; set; } 
            public List<ScheduleModel> Data { get; set; } = new(); 
        }

        public class BasicResponse 
        { 
            public bool Success { get; set; } 
            public string? Message { get; set; } 
        }
    }
}