


function editvertical(id) {
    let nameTd = $("#name-" + id);
    let currentName = nameTd.text().trim();

    // Replace text with input field
    nameTd.html(`<input type="text" id="edit-input-${id}" class="form-control" value="${currentName}" />`);

    // Show Update & Cancel buttons, Hide Edit
    $("#edit-btn-" + id).hide();
    $("#update-btn-" + id).show();
    $("#cancel-btn-" + id).show();
}

// Cancel Edit (Restore Original Text)
function cancelEdit(id) {
    let nameTd = $("#name-" + id);
    let originalName = $("#edit-input-" + id).val(); // Preserve original value

    // Restore text
    nameTd.text(originalName);

    // Show Edit button, Hide Update & Cancel
    $("#edit-btn-" + id).show();
    $("#update-btn-" + id).hide();
    $("#cancel-btn-" + id).hide();
}

// Update vertical (Save Changes)
function updatevertical(id) {
    let updatedName = $("#edit-input-" + id).val().trim();

    if (updatedName === "") {
        alert("vertical name cannot be empty!");
        return;
    }

    $.ajax({
        url: `/categories/${id}`,
        type: "PUT",
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content"),
            "name": updatedName
        },
        success: function (response) {
            $("#name-" + id).text(updatedName);
            $("#edit-btn-" + id).show();
            $("#update-btn-" + id).hide();
            $("#cancel-btn-" + id).hide();
        },
        error: function (xhr) {
            let errorMsg = "Error updating vertical!";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMsg = xhr.responseText;
            }
            alert(errorMsg);
        }
    });
}

// Delete vertical (Remove Row)
function deletevertical(id) {
    if (!confirm("Are you sure you want to delete this vertical?")) {
        return;
    }

    $.ajax({
        url: `/categories/${id}`,
        type: "DELETE",
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content")
        },
        success: function () {
            $("#row-" + id).remove();
        },
       error: function (xhr) {
            let errorMsg = "Error deleting vertical!";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                errorMsg = xhr.responseText;
            }
            alert(errorMsg);
        }
    });
}