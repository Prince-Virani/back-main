


function edittheme(id) {
    let nameTd = $("#themename-" + id);
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
    let nameTd = $("#themename-" + id);
    let originalName = $("#edit-input-" + id).val(); // Preserve original value

    // Restore text
    nameTd.text(originalName);

    // Show Edit button, Hide Update & Cancel
    $("#edit-btn-" + id).show();
    $("#update-btn-" + id).hide();
    $("#cancel-btn-" + id).hide();
}

// Update Theme (Save Changes)
function updatetheme(id) {
    let updatedName = $("#edit-input-" + id).val().trim();

    if (updatedName === "") {
        alert("Theme name cannot be empty!");
        return;
    }

    $.ajax({
        url: `/themes/${id}`,
        type: "PUT",
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content"),
            "themename": updatedName
        },
        success: function (response) {
            $("#themename-" + id).text(updatedName);
            $("#edit-btn-" + id).show();
            $("#update-btn-" + id).hide();
            $("#cancel-btn-" + id).hide();
        },
        error: function () {
            alert("Error updating Theme!");
        }
    });
}

// Delete Category (Remove Row)
function deletetheme(id) {
    if (!confirm("Are you sure you want to delete this category?")) {
        return;
    }

    $.ajax({
        url: `/themes/${id}`,
        type: "DELETE",
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content")
        },
        success: function () {
            //$("#row-" + id).remove();
            location.reload();
        },
        error: function () {
            alert("Error deleting Theme!");
        }
    });
}