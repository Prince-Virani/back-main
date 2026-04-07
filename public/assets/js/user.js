
function deleteUser(id) {
    if (!confirm("Are you sure you want to delete this user?")) {
        return;
    }

    $.ajax({
        url: `/users/${id}`,
        type: "DELETE",
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content")
        },
        success: function () {
            $("#row-" + id).remove();
            // Optional: Show success message
            // alert("User deleted successfully!");
        },
        error: function () {
            alert("Error deleting user!");
        }
    });
}