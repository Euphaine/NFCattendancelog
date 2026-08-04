using Microsoft.AspNetCore.Components;
using Microsoft.AspNetCore.Components.Forms;
using Microsoft.JSInterop;
using System.Net.Http.Json;

namespace AttendanceSystem.Pages
{
    public class UsersBase : ComponentBase
    {
        [Inject] protected HttpClient Http { get; set; } = default!;
        [Inject] protected IJSRuntime JS { get; set; } = default!;

        protected List<UserModel> UserList { get; set; } = new();
        protected bool IsLoading { get; set; } = false;
        protected string SearchTerm { get; set; } = "";
        protected string SelectedRole { get; set; } = "All";

        protected bool IsEditing { get; set; } = false;
        protected UserModel EditingUser { get; set; } = new();

        protected override async Task OnInitializedAsync()
        {
            await LoadUsers();
        }

        protected async Task LoadUsers()
        {
            IsLoading = true;
            try
            {
                var url = $"http://localhost/attendance-api/manage_users.php?role={Uri.EscapeDataString(SelectedRole)}&search={Uri.EscapeDataString(SearchTerm)}";
                var response = await Http.GetFromJsonAsync<UserApiResponse>(url);
                if (response != null && response.Success)
                {
                    UserList = response.Data;
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error loading users: {ex.Message}");
            }
            finally
            {
                IsLoading = false;
            }
        }

        protected async Task OnRoleChanged(ChangeEventArgs e)
        {
            SelectedRole = e.Value?.ToString() ?? "All";
            await LoadUsers();
        }

        protected async Task OnEditPhotoSelected(InputFileChangeEventArgs e)
        {
            var file = e.File;
            if (file != null)
            {
                var resizedFile = await file.RequestImageFileAsync("image/jpeg", 400, 400);
                using var stream = resizedFile.OpenReadStream(maxAllowedSize: 1024 * 1024 * 5);
                using var ms = new MemoryStream();
                await stream.CopyToAsync(ms);
                EditingUser.NewPhoto = $"data:image/jpeg;base64,{Convert.ToBase64String(ms.ToArray())}";
            }
        }

        protected void OpenEditModal(UserModel user)
        {
            EditingUser = new UserModel
            {
                Id = user.Id,
                SchoolId = user.SchoolId,
                NfcTagId = user.NfcTagId,
                Role = user.Role,
                FirstName = user.FirstName,
                LastName = user.LastName,
                Suffix = user.Suffix,
                Department = user.Department,
                EducationalLevel = user.EducationalLevel,
                Course = user.Course,
                YearLevel = user.YearLevel,
                Photo = user.Photo
            };
            IsEditing = true;
        }

        protected void CloseEditModal()
        {
            IsEditing = false;
        }

       protected async Task SaveUserChanges()
{
    try
    {
        var response = await Http.PutAsJsonAsync("http://localhost/attendance-api/manage_users.php", EditingUser);
        var result = await response.Content.ReadFromJsonAsync<UserApiResponse>();

        if (result != null && result.Success)
        {
            IsEditing = false;
            await LoadUsers();
        }
        else
        {
            Console.WriteLine($"Update failed: {result?.Message}");
        }
    }
    catch (Exception ex)
    {
        Console.WriteLine($"Error updating user: {ex.Message}");
    }
}

        protected async Task DeleteUser(int id)
        {
            bool confirmed = await JS.InvokeAsync<bool>("confirm", "Are you sure you want to delete this user?");
            if (!confirmed) return;

            try
            {
                var url = $"http://localhost/attendance-api/manage_users.php?id={id}";
                var response = await Http.DeleteFromJsonAsync<UserApiResponse>(url);
                if (response != null && response.Success)
                {
                    await LoadUsers();
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine($"Error deleting user: {ex.Message}");
            }
        }

        protected string GetRoleBadgeClass(string role) => role switch
        {
            "Student" => "bg-blue-100 text-blue-800",
            "Teacher" => "bg-purple-100 text-purple-800",
            "Staff" => "bg-emerald-100 text-emerald-800",
            _ => "bg-gray-100 text-gray-800"
        };

        public class UserModel
        {
            public int Id { get; set; }
            public string SchoolId { get; set; } = string.Empty;
            public string NfcTagId { get; set; } = string.Empty;
            public string Role { get; set; } = string.Empty;
            public string FirstName { get; set; } = string.Empty;
            public string LastName { get; set; } = string.Empty;
            public string? Suffix { get; set; }
            public string? Department { get; set; }
            public string? EducationalLevel { get; set; }
            public string? Course { get; set; }
            public string? YearLevel { get; set; }
            public string? Photo { get; set; }
            public string? NewPhoto { get; set; }
        }

        public class UserApiResponse
        {
            public bool Success { get; set; }
            public string Message { get; set; } = string.Empty;
            public List<UserModel> Data { get; set; } = new();
        }
    }
}