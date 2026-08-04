using Microsoft.AspNetCore.Components;
using Microsoft.AspNetCore.Components.Forms;
using Microsoft.AspNetCore.Components.Web;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public partial class RegisterUser : ComponentBase
    {
        [Inject] public HttpClient Http { get; set; } = default!;

        protected UserRegisterModel UserModel { get; set; } = new();
        protected bool IsModalOpen { get; set; } = false;
        protected bool IsDataConfirmed { get; set; } = false;
        protected bool IsSuccess { get; set; } = false;
        protected string ErrorMessage { get; set; } = string.Empty;

        protected string RegisteredName { get; set; } = string.Empty;
        protected string RegisteredId { get; set; } = string.Empty;

        private ElementReference nfcInputRef;

        protected async Task OnPhotoSelected(InputFileChangeEventArgs e)
        {
            var file = e.File;
            if (file != null)
            {
                // Resize or limit size if needed, convert to base64
                var resizedFile = await file.RequestImageFileAsync("image/jpeg", 400, 400);
                using var stream = resizedFile.OpenReadStream(maxAllowedSize: 1024 * 1024 * 5);
                using var ms = new MemoryStream();
                await stream.CopyToAsync(ms);
                UserModel.Photo = $"data:image/jpeg;base64,{Convert.ToBase64String(ms.ToArray())}";
            }
        }

        protected void OnRoleOrEduChanged()
        {
            if (UserModel.Role == "Student")
            {
                if (string.IsNullOrEmpty(UserModel.EducationalLevel))
                {
                    UserModel.EducationalLevel = "College";
                    UserModel.YearLevel = "1st Year";
                }

                switch (UserModel.EducationalLevel)
                {
                    case "Elementary":
                        UserModel.YearLevel = "Grade 1";
                        UserModel.Department = string.Empty;
                        UserModel.Course = string.Empty;
                        break;
                    case "Junior High School":
                        UserModel.YearLevel = "Grade 7";
                        UserModel.Department = string.Empty;
                        UserModel.Course = string.Empty;
                        break;
                    case "Senior High School":
                        UserModel.YearLevel = "Grade 11";
                        UserModel.Department = string.Empty;
                        break;
                    case "College":
                        UserModel.YearLevel = "1st Year";
                        break;
                }
            }
            else
            {
                UserModel.EducationalLevel = string.Empty;
                UserModel.Course = string.Empty;
                UserModel.YearLevel = string.Empty;
            }
        }

        protected void OpenScanModal()
        {
            ErrorMessage = string.Empty;
            IsSuccess = false;
            UserModel.NfcTagId = string.Empty;
            IsModalOpen = true;
        }

        protected override async Task OnAfterRenderAsync(bool firstRender)
        {
            if (IsModalOpen && !IsSuccess)
            {
                try { await nfcInputRef.FocusAsync(); } catch { }
            }
        }

        protected async Task HandleKeyUp(KeyboardEventArgs e)
        {
            if (e.Key == "Enter" && !string.IsNullOrWhiteSpace(UserModel.NfcTagId))
            {
                await SubmitRegistrationAsync();
            }
        }

        private async Task SubmitRegistrationAsync()
        {
            try
            {
                if (UserModel.Role != "Student")
                {
                    UserModel.EducationalLevel = string.Empty;
                    UserModel.Course = string.Empty;
                    UserModel.YearLevel = string.Empty;
                }

                var response = await Http.PostAsJsonAsync("http://localhost/attendance-api/register.php", UserModel);
                var result = await response.Content.ReadFromJsonAsync<RegisterResponse>();

                if (result != null && result.Success)
                {
                    RegisteredName = $"{UserModel.FirstName} {UserModel.LastName}".Trim();
                    RegisteredId = UserModel.SchoolId;

                    IsSuccess = true;
                    IsDataConfirmed = false;
                    UserModel = new();
                    StateHasChanged();
                }
                else
                {
                    ErrorMessage = result?.Message ?? "Failed to save registration.";
                }
            }
            catch (Exception ex)
            {
                ErrorMessage = $"Server Error: {ex.Message}";
            }
        }

        protected void CloseModal()
        {
            IsModalOpen = false;
            IsSuccess = false;
        }
    }

    public class UserRegisterModel
    {
        public string SchoolId { get; set; } = string.Empty;
        public string FirstName { get; set; } = string.Empty;
        public string LastName { get; set; } = string.Empty;
        public string Suffix { get; set; } = string.Empty;
        public string Role { get; set; } = "Student";
        public string EducationalLevel { get; set; } = "College";
        public string Department { get; set; } = string.Empty;
        public string Course { get; set; } = string.Empty;
        public string YearLevel { get; set; } = "1st Year";
        public string NfcTagId { get; set; } = string.Empty;
        public string? Photo { get; set; }
    }

    public class RegisterResponse
    {
        public bool Success { get; set; }
        public string Message { get; set; } = string.Empty;
    }
}